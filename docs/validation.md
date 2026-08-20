# Validation

`Misi\Validation\Validator` valida arrays asociativos (típicamente
`$request->all()`) contra un conjunto de reglas, y lanza una excepción
estructurada si algo falla.

## Uso básico

```php
use Misi\Http\JsonResponse;
use Misi\Http\Request;

final class CustomerController
{
    public function store(Request $request): JsonResponse
    {
        $data = app()->validator()->validate($request->all(), [
            'name'  => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:customers,email'],
            'age'   => ['nullable', 'integer', 'min:18'],
        ]);

        // $data solo contiene name, email, age — ya validados
        $id = app()->database()->insert('customers', $data);

        return JsonResponse::success(['id' => $id], 'Cliente creado', 201);
    }
}
```

Si algo falla, `validate()` lanza `Misi\Exceptions\ValidationException`.
No hace falta capturarla a mano: `Application::handleException()` ya la
convierte automáticamente en una respuesta `422` con este formato:

```json
{
    "success": false,
    "data": null,
    "message": "Validation failed",
    "errors": {
        "email": ["El campo email debe ser un correo electrónico válido."],
        "age": ["El campo age debe tener al menos 18."]
    }
}
```

## `app()->validator()` vs `new Validator()`

`app()->validator()` (recomendado) crea el `Validator` ya conectado a la
base de datos configurada, necesario para `unique`/`exists`. Si tu
validación no usa esas dos reglas, `new Validator()` a secas funciona
igual.

## Reglas disponibles

| Regla | Qué valida | Ejemplo |
|---|---|---|
| `required` | El valor está presente y no vacío | `required` |
| `nullable` | Marca el campo como opcional (comportamiento por defecto si no se declara `required`, pero se recomienda declararlo por legibilidad) | `nullable` |
| `string` | Es una cadena | `string` |
| `integer` | Es un entero (o string numérico entero) | `integer` |
| `numeric` | Es numérico | `numeric` |
| `boolean` | Es `true/false/0/1/"0"/"1"` | `boolean` |
| `email` | Formato de email válido | `email` |
| `url` | Formato de URL válido | `url` |
| `date` | `strtotime()` puede interpretarlo | `date` |
| `min:N` | Mínimo — contextual, ver abajo | `min:18` |
| `max:N` | Máximo — contextual, ver abajo | `max:150` |
| `in:a,b,c` | El valor está en la lista | `in:borrador,publicado` |
| `unique:tabla,columna` | No existe otra fila con ese valor | `unique:users,email` |
| `exists:tabla,columna` | Existe una fila con ese valor | `exists:categories,id` |
| `file` | Es un archivo subido válido | `file` |
| `image` | Es un archivo subido y su MIME real es `image/*` | `image` |
| `mimes:jpg,png,pdf` | La extensión del archivo está en la lista | `mimes:jpg,png` |
| `max_size:N` | Tamaño del archivo en KB, máximo N | `max_size:2048` |

### `min` / `max` son contextuales

El significado depende de qué más se declaró para ese campo:

- Si el valor es un **archivo subido**: compara el tamaño en KB.
- Si la regla incluye `numeric` o `integer`: compara el valor numérico.
- Si es un **string**: compara la longitud (con `mb_strlen`, soporta
  acentos/UTF-8 correctamente).
- Si es un **array**: compara la cantidad de elementos.

### `unique` para ediciones (ignorar el propio registro)

```php
'email' => ['required', 'email', "unique:users,email,{$user->id},id"],
```

Esto genera `... WHERE email = ? AND id != ?`, permitiendo que un usuario
guarde su propio email sin que "unique" lo rechace por sí mismo.

### Validación de archivos

```php
$data = app()->validator()->validate([
    ...$request->all(),
    'avatar' => $request->file('avatar'),
], [
    'avatar' => ['required', 'image', 'mimes:jpg,png', 'max_size:2048'],
]);
```

`$request->all()` no incluye archivos — hay que agregarlos explícitamente
desde `$request->file('campo')` al array que se valida.

`image`/`mimes` verifican el **MIME real** del archivo subido (vía
`finfo`), no solo la extensión — evitar renombrar un `.php` a `.jpg` es
justamente el tipo de problema que `Validation` + `Storage` (Fase 8)
resuelven juntos: Validation decide si el archivo es válido, Storage
decide dónde y cómo guardarlo de forma segura.

## Qué NO hace Validation (por ahora)

- No permite mensajes de error personalizados por campo/regla.
- No soporta reglas condicionales (`required_if`, `required_with`, etc.).
- No valida arrays anidados de forma recursiva (`items.*.price`).

Se agregan solo si un proyecto real los necesita — ver la regla de oro de
abstracciones en `docs/architecture.md`.
