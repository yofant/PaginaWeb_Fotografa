# Flujos y Procesos - JENI HDZ

## Flujo 1: Navegación de usuario público

```
Usuario entra a index.php (Bienvenida)
      ↓
Navega a: Nosotros / Paquetes / Galería / Contacto
      ↓
En Galería: aplica filtros (color, moto, piloto, fecha, evento)
      ↓
Selecciona foto(s) → se agregan al carrito (localStorage)
      ↓
Abre panel carrito → hace clic en "Finalizar en WhatsApp"
      ↓
Ingresa nombre, email, WhatsApp
      ↓
Sistema guarda intención de compra en BD (compras)
      ↓
Se abre WhatsApp con mensaje prellenado → cliente concluye con la fotógrafa
```

## Flujo 2: Subida de fotos por la fotógrafa

```
Fotógrafa entra a subir-fotos.php (requiere login)
      ↓
Si no tiene sesión → se redirige a login.php
      ↓
Inicia sesión con email y contraseña (auth.php)
      ↓
Arrastra o selecciona imágenes (JPG/PNG/WEBP, máx. 10 MB)
      ↓
Llena metadatos: título, color, número de moto, número de piloto, fecha, evento, precio
      ↓
Envía formulario a upload.php
      ↓
PHP valida archivo, mueve a assets/uploads/
      ↓
PHP genera marca de agua con GD (wm_archivo)
      ↓
Inserta registro en tabla `fotos` con ruta de la versión con marca de agua
      ↓
Foto disponible inmediatamente en la galería pública
```

## Flujo 3: Compra de fotos (carrito → WhatsApp)

```
Usuario selecciona fotos en galería
      ↓
Carrito se guarda en localStorage (JS)
      ↓
Usuario abre panel del carrito
      ↓
Hace clic en "Finalizar en WhatsApp"
      ↓
Sistema solicita nombre, email, WhatsApp
      ↓
save_purchase.php guarda en tabla `compras` (JSON con fotos, total, mensaje)
      ↓
Se genera URL de WhatsApp con mensaje prellenado
      ↓
window.open(whatsapp_url) en nueva pestaña
      ↓
Fotógrafa coordina pago y entrega por WhatsApp
```

## Flujo 4: Seguridad y protección de imágenes

```
Usuario intenta clic derecho sobre imagen
      ↓
security.js intercepta → e.preventDefault() → muestra bloqueo visual
      ↓
Usuario intenta PrintScreen
      ↓
security.js detecta tecla → muestra bloqueo visual
      ↓
Imagen en galería tiene marca de agua (watermark-overlay + versión wm_ en servidor)
      ↓
Incluso si se captura, la imagen lleva la marca "JENI HDZ - FOTOGRAFÍA MOTO VELOCIDAD"
```

## Diagrama de entidades (resumido)

```
usuarios ──< fotos
eventos ──< fotos
fotos ──< compras (vía JSON de IDs)
compras ──> WhatsApp (canal externo de pago)
auditoria_acceso ──< usuarios
```
