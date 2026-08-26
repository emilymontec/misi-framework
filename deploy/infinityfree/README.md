# deploy/infinityfree/

Material de despliegue para InfinityFree (y hosting compartido
equivalente sin SSH). Guía completa paso a paso:
`DEPLOYMENT.md` (raíz del repo).

## Restricción clave del plan gratuito

La cuenta está **enjaulada dentro de `htdocs/`**: no se pueden crear
carpetas fuera de ese directorio (ni por FTP ni por el administrador de
archivos — error confirmado: "Read-only file system"). Por eso el
framework completo vive dentro de `htdocs/` y un `.htaccess` en la raíz
reenvía cada petición hacia `public/`, de modo que `framework/`,
`business/`, `config/`, `.env`, etc. nunca sean servidos directamente.

## Qué se sube al hosting

| Archivo del repo | Se sube a (en el hosting) | Notas |
|---|---|---|
| Todo el proyecto | `htdocs/` | El contenido va DIRECTAMENTE en `htdocs/`, no en `htdocs/misi-framework/`. |
| `/.htaccess` (raíz) | `htdocs/.htaccess` | Ya tiene el nombre correcto; solo verifica que se haya subido (los FTP suelen omitir dotfiles). |
| `public/__deploy-runner.php.example` | `htdocs/public/<nombre-que-elijas>.php` | Renombrar; requiere `DEPLOY_TOKEN` en `.env`. Borrar después de usar. |
| `public/__host-check.php.example` | `htdocs/public/<nombre-que-elijas>.php` | Opcional: diagnóstico (versión de PHP, mbstring, permisos). Borrar después. |

> Las versiones anteriores de este directorio (`htdocs.htaccess`,
> `web-runner.php`) asumían que `framework/` etc. vivían UN NIVEL ARRIBA
> de `htdocs/` — imposible en el plan gratuito. Fueron eliminadas; la
> fuente canónica es ahora el `.htaccess` de la raíz del repo.

Nada de este directorio se referencia desde `framework/`, `app/` ni
`routes/` — es exclusivamente material de despliegue, no código de la
aplicación.
