<?php
$titulo_pagina = 'Iniciar sesión | JENI HDZ';
require_once 'includes/header.php';
?>

<section class="section" style="min-height:calc(100vh - 72px); display:flex; align-items:center; justify-content:center; padding-top:72px;">
  <div class="login-container">
    <h2>Iniciar sesión<br><span>Fotógrafa</span></h2>
    <p>Acceso exclusivo para subir y gestionar tus fotografías de moto velocidad.</p>
    <form id="formLogin">
      <div class="form-login-group">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" required placeholder="jeni@tucorreo.com">
      </div>
      <div class="form-login-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn--primary btn-login">Iniciar sesión</button>
    </form>
    <p style="margin-top:var(--space-5); font-size:0.9rem; color:var(--color-text-secondary);">¿No tienes cuenta? Contacta a JENI HDZ para acceder.</p>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>