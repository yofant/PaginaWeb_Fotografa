# Validación post-migración — Cloudflare D1

Este documento contiene los comandos CLI y pruebas para auditar que la base de
datos D1 se desplegó correctamente y que el Worker funciona como esperado.

## 0. Requisitos

```bash
# Autenticarse en Cloudflare (una sola vez)
wrangler login

# Ver versión de Wrangler
wrangler --version
```

## 1. Crear / listar bases de datos D1

```bash
# Crear base de datos (solo la primera vez)
wrangler d1 create jeni_hdz_gallery

# Listar bases disponibles
wrangler d1 list
```

> Copiar el `database_id` resultante y pegarlo en `workers/wrangler.jsonc`
> en el campo `database_id`.

## 2. Aplicar esquema (migración)

### Entorno local

```bash
cd workers
wrangler d1 execute jeni_hdz_gallery --local --file=./migrations/0001_initial.sql
```

### Entorno de producción

```bash
cd workers
wrangler d1 execute jeni_hdz_gallery --file=./migrations/0001_initial.sql
```

## 3. Auditar integridad de la base de datos

### 3.1. Integridad estructural

```bash
wrangler d1 execute jeni_hdz_gallery --command="PRAGMA integrity_check;"
```

Resultado esperado: `ok`

### 3.2. Claves foráneas

```bash
wrangler d1 execute jeni_hdz_gallery --command="PRAGMA foreign_key_check;"
```

Resultado esperado: (vacío / sin filas) = todas las FK válidas.

### 3.3. Listar tablas creadas

```bash
wrangler d1 execute jeni_hdz_gallery --command="SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;"
```

Resultado esperado: tablas `usuarios`, `eventos`, `subcarpetas`, `fotos`,
`paquetes`, `compras`, `contactos`, `auditoria_acceso`.

## 4. Pruebas CRUD (Create, Read, Update, Delete)

Estos comandos insertan, leen, actualizan y borran un registro de prueba.
**Ejecutar en entorno de pruebas, no en producción con datos reales.**

### 4.1. Crear usuario de prueba

```bash
wrangler d1 execute jeni_hdz_gallery \
  --command="INSERT INTO usuarios (nombre, email, password_hash, rol, telefono, whatsapp, activo) VALUES ('Usuario Prueba','prueba@correo.com','hash_prueba','fotografa','+573000000000','+573000000000',1);"
```

### 4.2. Leer el usuario insertado

```bash
wrangler d1 execute jeni_hdz_gallery \
  --command="SELECT id, nombre, email, rol, activo FROM usuarios WHERE email='prueba@correo.com';"
```

### 4.3. Crear evento asociado

```bash
wrangler d1 execute jeni_hdz_gallery \
  --command="INSERT INTO eventos (nombre, fecha, circuito, pais, descripcion, activo) VALUES ('Prueba Evento','2026-12-01','Circuito Prueba','Colombia','Evento de prueba para validar D1',1);"
```

### 4.4. Crear subcarpeta dentro del evento

```bash
wrangler d1 execute jeni_hdz_gallery \
  --command="INSERT INTO subcarpetas (evento_id, nombre, descripcion) VALUES (1,'Manga 1','Sección de manga');"
```

### 4.5. Crear foto vinculada a evento y subcarpeta

```bash
wrangler d1 execute jeni_hdz_gallery \
  --command="INSERT INTO fotos (nombre_archivo, ruta_archivo, nombre_original, titulo, evento_id, subcarpeta_id, precio, subido_por) VALUES ('img_001.jpg','eventos/1/1/img_001.jpg','img_001.jpg','Foto prueba',1,1,40000.00,1);"
```

### 4.6. Leer fotos filtradas por evento y subcarpeta

```bash
wrangler d1 execute jeni_hdz_gallery \
  --command="SELECT f.id, f.titulo, f.precio, s.nombre AS subcarpeta FROM fotos f LEFT JOIN subcarpetas s ON f.subcarpeta_id = s.id WHERE f.evento_id = 1 AND f.disponible = 1;"
```

### 4.7. Actualizar registro

```bash
wrangler d1 execute jeni_hdz_gallery \
  --command="UPDATE eventos SET descripcion = 'Evento actualizado para prueba' WHERE id = 1;"
```

### 4.8. Soft-delete (desactivar) subcarpeta y evento

```bash
wrangler d1 execute jeni_hdz_gallery \
  --command="UPDATE subcarpetas SET activo = 0 WHERE id = 1;"

wrangler d1 execute jeni_hdz_gallery \
  --command="UPDATE eventos SET activo = 0 WHERE id = 1;"
```

### 4.9. Borrado en cascada (eliminar evento y sus subcarpetas automáticamente)

```bash
wrangler d1 execute jeni_hdz_gallery \
  --command="DELETE FROM eventos WHERE id = 1;"
```

Luego verificar que la subcarpeta asociada ya no exista:

```bash
wrangler d1 execute jeni_hdz_gallery \
  --command="SELECT * FROM subcarpetas WHERE evento_id = 1;"
```

Resultado esperado: 0 filas (borrado en cascada funcionó).

## 5. Validar consultas del Worker (Desplegado)

Una vez desplegado el Worker (`wrangler deploy`), probar los endpoints:

```bash
# Listar eventos
curl -s https://TU_WORKER.workers.dev/api/eventos | jq .

# Obtener un evento por ID
curl -s https://TU_WORKER.workers.dev/api/eventos/1 | jq .

# Crear evento (requiere CSRF_TOKEN configurado)
curl -s -X POST https://TU_WORKER.workers.dev/api/eventos \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: TU_TOKEN_AQUI" \
  -d '{"nombre":"Evento API","fecha":"2026-12-15","circuito":"Circuito API","pais":"Colombia","descripcion":"Prueba desde API"}' | jq .

# Listar fotos con filtro
curl -s "https://TU_WORKER.workers.dev/api/fotos?evento_id=1" | jq .
```

## 6. Lista de verificación final

| Prueba | Comando | Resultado esperado |
|--------|---------|-------------------|
| Integridad DB | `PRAGMA integrity_check;` | `ok` |
| Foreign Keys | `PRAGMA foreign_key_check;` | vacío |
| Tablas creadas | `SELECT name FROM sqlite_master...` | 8 tablas |
| Insert evento | `INSERT INTO eventos...` | fila insertada |
| Leer evento | `SELECT * FROM eventos...` | datos correctos |
| Borrado en cascada | `DELETE FROM eventos...` | subcarpetas eliminadas automáticamente |
| Worker responde | `curl /api/eventos` | JSON con `{ exito: true }` |
| CSRF bloquea | `POST /api/eventos` sin token | `403 Token CSRF inválido` |

## 7. Comandos de limpieza (solo entorno de pruebas)

```bash
# Eliminar datos de prueba si es necesario
wrangler d1 execute jeni_hdz_gallery \
  --command="DELETE FROM compras; DELETE FROM contactos; DELETE FROM fotos; DELETE FROM subcarpetas; DELETE FROM eventos; DELETE FROM usuarios;"
```

---

> ⚠️ Nota: En D1, `PRAGMA foreign_keys = ON` debe estar activo en cada conexión
> para que `ON DELETE CASCADE` funcione. Wrangler lo gestiona automáticamente
> en las consultas ejecutadas mediante `wrangler d1 execute`.
