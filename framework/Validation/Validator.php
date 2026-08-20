<?php

declare(strict_types=1);

namespace Misi\Validation;

use Misi\Database\Database;
use Misi\Exceptions\ValidationException;
use RuntimeException;

/**
 * Validador de input reutilizable entre proyectos.
 *
 * Uso:
 *
 *   $validator = new Validator();
 *   $data = $validator->validate($request->all(), [
 *       'name'  => ['required', 'string', 'max:150'],
 *       'email' => ['nullable', 'email'],
 *   ]);
 *
 * Si algún campo falla, lanza Misi\Exceptions\ValidationException con
 * errores estructurados por campo. Application::handleException() ya la
 * traduce automáticamente a una respuesta JSON 422 (ver Fase 1).
 *
 * Reglas que consultan la base de datos ('unique', 'exists') requieren
 * pasar una instancia de Database al constructor.
 *
 * No se implementa: mensajes personalizados por regla/campo, reglas
 * condicionales tipo "required_if", ni validación anidada de arrays
 * complejos. Se agregan solo si un proyecto real lo necesita.
 */
final class Validator
{
    /** @var array<string, array<int, string>> */
    private array $errors = [];

    public function __construct(private readonly ?Database $db = null)
    {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, array<int, string>|string> $rules
     * @return array<string, mixed> solo los campos que tenían regla definida
     */
    public function validate(array $data, array $rules): array
    {
        $this->errors = [];
        $validated = [];

        foreach ($rules as $field => $fieldRules) {
            $ruleList = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);
            $value = $data[$field] ?? null;

            $isEmpty = $this->isEmpty($value);
            $isRequired = in_array('required', $ruleList, true);

            // Campo opcional y ausente: se omite sin error (nullable es
            // el marcador explícito recomendado, pero el comportamiento
            // por defecto ya es "opcional si no se declara required").
            if ($isEmpty && !$isRequired) {
                continue;
            }

            foreach ($ruleList as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }

                [$name, $params] = $this->parseRule($rule);

                if (!$this->applyRule($name, $value, $params, $ruleList, $data)) {
                    $this->addError($field, $name, $params);
                }
            }

            if (!isset($this->errors[$field])) {
                $validated[$field] = $value;
            }
        }

        if ($this->errors !== []) {
            throw new ValidationException($this->errors);
        }

        return $validated;
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        if (is_array($value) && $value === []) {
            return true;
        }

        // Estructura de $_FILES sin archivo subido.
        if (is_array($value) && ($value['error'] ?? null) === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        return false;
    }

    /** @return array{0: string, 1: array<int, string>} */
    private function parseRule(string $rule): array
    {
        if (!str_contains($rule, ':')) {
            return [$rule, []];
        }

        [$name, $paramsStr] = explode(':', $rule, 2);

        return [$name, explode(',', $paramsStr)];
    }

    /**
     * @param array<int, string> $params
     * @param array<int, string> $ruleList
     * @param array<string, mixed> $data
     */
    private function applyRule(string $name, mixed $value, array $params, array $ruleList, array $data): bool
    {
        return match ($name) {
            'required' => !$this->isEmpty($value),
            'string' => is_string($value),
            'integer' => $this->isInteger($value),
            'numeric' => is_numeric($value),
            'boolean' => $this->isBoolean($value),
            'email' => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false,
            'date' => is_string($value) && strtotime($value) !== false,
            'min' => $this->size($value, $ruleList) >= (float) ($params[0] ?? 0),
            'max' => $this->size($value, $ruleList) <= (float) ($params[0] ?? 0),
            'in' => in_array((string) $value, $params, true),
            'unique' => $this->checkUnique($value, $params),
            'exists' => $this->checkExists($value, $params),
            'file' => $this->isUploadedFile($value),
            'image' => $this->isImage($value),
            'mimes' => $this->hasAllowedMime($value, $params),
            'max_size' => $this->checkMaxSize($value, $params),
            default => throw new RuntimeException("Regla de validación desconocida: {$name}"),
        };
    }

    private function isInteger(mixed $value): bool
    {
        if (is_int($value)) {
            return true;
        }

        return is_string($value) && filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function isBoolean(mixed $value): bool
    {
        return in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true);
    }

    /**
     * Tamaño contextual del valor: KB si es archivo, número si es
     * integer/numeric, longitud si es string, cantidad de elementos si
     * es array.
     *
     * @param array<int, string> $ruleList
     */
    private function size(mixed $value, array $ruleList): float
    {
        if (is_array($value) && isset($value['size'], $value['tmp_name'])) {
            return ((float) $value['size']) / 1024;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (in_array('numeric', $ruleList, true) || in_array('integer', $ruleList, true)) {
            return (float) $value;
        }

        if (is_string($value)) {
            return (float) mb_strlen($value);
        }

        if (is_array($value)) {
            return (float) count($value);
        }

        return 0.0;
    }

    /** @param array<int, string> $params */
    private function checkUnique(mixed $value, array $params): bool
    {
        if (!$this->db instanceof Database) {
            throw new RuntimeException('La regla "unique" requiere una instancia de Database en el Validator.');
        }

        $table = $params[0] ?? throw new RuntimeException('Uso: unique:tabla,columna');
        $column = $params[1] ?? throw new RuntimeException('Uso: unique:tabla,columna');

        $sql = "SELECT 1 FROM {$table} WHERE {$column} = ?";
        $bindings = [$value];

        // unique:tabla,columna,valor_excepcion,columna_excepcion
        // (para permitir el propio registro al editar)
        if (isset($params[2], $params[3])) {
            $sql .= " AND {$params[3]} != ?";
            $bindings[] = $params[2];
        }

        return $this->db->selectOne($sql, $bindings) === null;
    }

    /** @param array<int, string> $params */
    private function checkExists(mixed $value, array $params): bool
    {
        if (!$this->db instanceof Database) {
            throw new RuntimeException('La regla "exists" requiere una instancia de Database en el Validator.');
        }

        $table = $params[0] ?? throw new RuntimeException('Uso: exists:tabla,columna');
        $column = $params[1] ?? throw new RuntimeException('Uso: exists:tabla,columna');

        return $this->db->selectOne("SELECT 1 FROM {$table} WHERE {$column} = ?", [$value]) !== null;
    }

    private function isUploadedFile(mixed $value): bool
    {
        return is_array($value)
            && isset($value['tmp_name'], $value['error'])
            && $value['error'] === UPLOAD_ERR_OK
            && is_uploaded_file((string) $value['tmp_name']);
    }

    private function isImage(mixed $value): bool
    {
        if (!$this->isUploadedFile($value)) {
            return false;
        }

        return str_starts_with($this->realMimeType((string) $value['tmp_name']), 'image/');
    }

    /** @param array<int, string> $params */
    private function hasAllowedMime(mixed $value, array $params): bool
    {
        if (!$this->isUploadedFile($value)) {
            return false;
        }

        $extension = strtolower(pathinfo((string) $value['name'], PATHINFO_EXTENSION));

        return in_array($extension, array_map('strtolower', $params), true);
    }

    /** @param array<int, string> $params */
    private function checkMaxSize(mixed $value, array $params): bool
    {
        if (!is_array($value) || !isset($value['size'])) {
            return false;
        }

        $maxKb = (float) ($params[0] ?? 0);

        return ((float) $value['size']) / 1024 <= $maxKb;
    }

    private function realMimeType(string $path): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo !== false ? finfo_file($finfo, $path) : false;

        if ($finfo !== false) {
            finfo_close($finfo);
        }

        return $mime !== false && $mime !== null ? $mime : 'application/octet-stream';
    }

    /** @param array<int, string> $params */
    private function addError(string $field, string $rule, array $params): void
    {
        $this->errors[$field][] = $this->message($field, $rule, $params);
    }

    /** @param array<int, string> $params */
    private function message(string $field, string $rule, array $params): string
    {
        return match ($rule) {
            'required' => "El campo {$field} es obligatorio.",
            'string' => "El campo {$field} debe ser una cadena de texto.",
            'integer' => "El campo {$field} debe ser un número entero.",
            'numeric' => "El campo {$field} debe ser numérico.",
            'boolean' => "El campo {$field} debe ser verdadero o falso.",
            'email' => "El campo {$field} debe ser un correo electrónico válido.",
            'url' => "El campo {$field} debe ser una URL válida.",
            'date' => "El campo {$field} debe ser una fecha válida.",
            'min' => "El campo {$field} debe tener al menos {$params[0]}.",
            'max' => "El campo {$field} no debe superar {$params[0]}.",
            'in' => "El campo {$field} debe ser uno de: " . implode(', ', $params) . '.',
            'unique' => "El valor de {$field} ya está en uso.",
            'exists' => "El valor seleccionado para {$field} no es válido.",
            'file' => "El campo {$field} debe ser un archivo válido.",
            'image' => "El campo {$field} debe ser una imagen válida.",
            'mimes' => "El campo {$field} debe ser un archivo de tipo: " . implode(', ', $params) . '.',
            'max_size' => "El campo {$field} no debe pesar más de {$params[0]} KB.",
            default => "El campo {$field} no es válido.",
        };
    }
}
