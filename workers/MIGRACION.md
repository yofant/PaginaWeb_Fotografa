# Documentación Técnica — Migración a Cloudflare D1

## Proyecto: JENI HDZ — Fotografía Moto Velocidad

## 1. Arquitectura objetivo

```
┌─────────────────┐      HTTPS/REST         ┌──────────────────────┐
│   Frontend      │  ─────────────────────► │   Cloudflare Worker  │
│  (PHP/HTML/JS)  │                           │  (src/index.ts)      │
└─────────────────┘                           └──────────┬───────────┘
                                                        │
                                                        │ Prepared Statements
                                                        │ (env.DB.prepare().bind())
                                                        ▼
                                               ┌──────────────────────┐
                                               │   Cloudflare D1      │
                                               │   (SQLite)           │
                                               └──────────────────────┘
```

## 2. Cambios realizados

### 2.1. Base de datos

| Aspecto | Antes (MySQL) | Después (D1/SQLite) |
|---------|---------------|---------------------|
| Motor | MySQL / MariaDB | SQLite (D1) |
| Driver | `pdo_mysql` | `D1Database` (Binding de Workers) |
| Tipos de datos | `INT`, `VARCHAR`, `DECIMAL`, `TIMESTAMP` | `INTEGER`, `TEXT`, `REAL`, `TEXT (ISO-8601)` |
| Llaves foráneas | `ON DELETE SET NULL / CASCADE` | `ON DELETE CASCADE / SET NULL` (verificadas con `PRAGMA foreign_keys = ON`) |
| Autonumérico | `AUTO_INCREMENT` | `AUTOINCREMENT` |
| Consultas | `PDO::prepare()->execute([...])` | `env.DB.prepare(sql).bind(...).run() / .all() / .first()` |

### 2.2. Capa de aplicación

| Antes | Después |
|-------|---------|
| PHP + `php/config.php` | Cloudflare Workers (TypeScript) |
| Credenciales en `php/config.php` | Variables de entorno y `wrangler secret` |
| Sesiones PHP (`$_SESSION`) | No hay sesiones en Workers; autenticación vía token CSRF o JWT externo |
| `database.sql` importado manualmente | Migraciones versionadas con `wrangler d1 execute --file=...` |

### 2.3. Nuevos componentes

- `workers/migrations/0001_initial.sql`: Esquema DDL completo compatible con D1.
- `workers/wrangler.jsonc`: Configuración de despliegue y binding `DB`.
- `workers/src/index.ts`: Controlador de Workers con endpoints REST.
- `workers/MIGRACION.md`: Esta documentación.
- `workers/VALIDACION.md`: Comandos de prueba y auditoría.

## 3. Arquitectura de seguridad (Zero-Trust)

### 3.1. Cero credenciales en código

Ningún archivo del repositorio contiene usuarios, contraseñas, host de base de datos ni tokens de acceso. Toda la autenticación hacia D1 se resuelve mediante el **Binding `DB`** inyectado por Cloudflare al Worker.

### 3.2. Secretos fuera del código

El token CSRF se inyecta mediante:

```bash
wrangler secret put CSRF_TOKEN
```

El Worker lo lee como `env.CSRF_TOKEN` y nunca lo imprime ni lo envía al cliente.

### 3.3. Prepared Statements obligatorios

Todas las consultas a D1 usan parámetros vinculados:

```typescript
// ✅ Correcto
await env.DB.prepare('SELECT * FROM fotos WHERE evento_id = ?').bind(eventoId).all();

// ❌ Prohibido
await env.DB.prepare(`SELECT * FROM fotos WHERE evento_id = ${eventoId}`).all();
```

### 3.4. Principio de menor privilegio

- El binding `DB` tiene acceso completo a la base de datos D1, pero el Worker solo expiende las operaciones estrictamente necesarias a través de endpoints definidos.
- No se expone el objeto `D1Database` al frontend.
- Cada endpoint valida campos de entrada antes de construir la consulta.
- Los endpoints mutantes (POST/PUT/DELETE) requieren `X-CSRF-Token`.

## 4. Flujo de despliegue

```text
1. wrangler login
2. wrangler d1 create jeni_hdz_gallery
3. wrangler secret put CSRF_TOKEN
4. Editar wrangler.jsonc con database_id
5. wrangler d1 execute jeni_hdz_gallery --file=./migrations/0001_initial.sql
6. wrangler deploy
```

## 5. Consideraciones de almacenamiento de archivos

D1 es una base de datos relacional, no un almacén de objetos. Para las imágenes
reales (fotos de moto velocidad), se recomienda usar **Cloudflare R2**:

- `workers/src/index.ts` almacena solo la `ruta_archivo` en D1.
- El archivo binario se sube a R2 y se guarda la URL o clave en `ruta_archivo`.
- Esto mantiene la base de datos liviana y aprovecha el almacenamiento duradero de R2.

## 6. Compatibilidad con el frontend existente

El frontend PHP/JS puede seguir operando si sus llamadas `fetch` se redirigen a
la URL del Worker desplegado. Los contratos de respuesta JSON se mantienen:

```json
{
  "exito": true,
  "mensaje": "...",
  "id": 123,
  "datos": [...]
}
```

## 7. Comandos clave

| Acción | Comando |
|--------|---------|
| Crear BD | `wrangler d1 create jeni_hdz_gallery` |
| Ejecutar migración | `wrangler d1 execute jeni_hdz_gallery --file=./migrations/0001_initial.sql` |
| Integridad | `wrangler d1 execute jeni_hdz_gallery --command="PRAGMA integrity_check;"` |
| Claves foráneas | `wrangler d1 execute jeni_hdz_gallery --command="PRAGMA foreign_key_check;"` |
| Desplegar Worker | `wrangler deploy` |
| Agregar secreto | `wrangler secret put CSRF_TOKEN` |
