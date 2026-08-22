# deploy/infinityfree/

Material de despliegue para InfinityFree (y hosting compartido
equivalente sin SSH). Guía completa: `docs/deployment-infinityfree.md`.

Este directorio **no se sube tal cual** a producción — sus archivos se
usan como plantilla y se colocan en las ubicaciones correctas del
hosting:

| Archivo aquí | Se sube a (en el hosting) | Notas |
|---|---|---|
| `htdocs.htaccess` | `htdocs/.htaccess` | Renombrar al subir (agregar el punto inicial). |
| `web-runner.php` | `htdocs/<nombre-que-elijas>.php` | Renombrar a algo no obvio; requiere `DEPLOY_TOKEN` en `.env`. Borrar después de usar. |

Nada de este directorio se referencia desde `framework/`, `app/` ni
`routes/` — es exclusivamente material de despliegue, no código de la
aplicación.
