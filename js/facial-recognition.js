/**
 * Módulo de Registro e Identificación Facial - JENI HDZ
 * Simulación de identificación automática de fotos por piloto.
 * En producción, se conectaría a una API de reconocimiento facial (AWS Rekognition, Azure Face API, etc.)
 */

(function () {
  'use strict';

  const video = document.getElementById('video');
  const btnCapturar = document.getElementById('btnCapturar');
  const statusFacial = document.getElementById('statusFacial');
  const videoContainer = document.getElementById('videoContainer');

  let stream = null;
  let fotoCapturada = null;

  // Iniciar cámara
  async function iniciarCamara() {
    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user', width: 320, height: 240 },
        audio: false
      });
      video.srcObject = stream;
      statusFacial.textContent = 'Cámara activa - Captura tu foto';
      statusFacial.className = 'status-facial info';
      btnCapturar.disabled = false;
    } catch (error) {
      console.error('Error accediendo a la cámara:', error);
      statusFacial.textContent = 'Cámara no disponible. Sube una foto desde tu dispositivo.';
      statusFacial.className = 'status-facial error';
      btnCapturar.disabled = true;
      // Mostrar alternativa de subida de archivo
      mostrarAlternativaSubida();
    }
  }

  // Mostrar input de archivo como alternativa
  function mostrarAlternativaSubida() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.style.marginTop = '1rem';
    input.style.display = 'block';
    input.style.marginLeft = 'auto';
    input.style.marginRight = 'auto';
    input.onchange = function (e) {
      const file = e.target.files[0];
      if (file) {
        const url = URL.createObjectURL(file);
        videoContainer.innerHTML = `<img src="${url}" style="width:320px; height:240px; object-fit:cover; display:block;">`;
        fotoCapturada = url;
        btnCapturar.textContent = 'Analizar Foto';
        statusFacial.textContent = 'Foto cargada. Listo para analizar.';
        statusFacial.className = 'status-facial info';
      }
    };
    videoContainer.parentNode.insertBefore(input, btnCapturar);
  }

  // Capturar foto de la cámara
  function capturarFoto() {
    if (!stream) return;

    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth || 320;
    canvas.height = video.videoHeight || 240;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    fotoCapturada = canvas.toDataURL('image/jpeg');

    // Mostrar la foto capturada en el contenedor
    videoContainer.innerHTML = `<img src="${fotoCapturada}" style="width:320px; height:240px; object-fit:cover; display:block; border:3px solid var(--color-primary); border-radius:var(--radius-md);">`;

    btnCapturar.textContent = 'Analizar Rostro';
    btnCapturar.disabled = true;

    // Simular análisis facial
    setTimeout(() => {
      simularIdentificacionFacial();
    }, 1500);
  }

  // Simular identificación facial (en producción sería una llamada a API real)
  function simularIdentificacionFacial() {
    const pilotos = [
      { nombre: 'Piloto #1 - Marc Márquez', moto: 'Ducati', numero: '93', evento: 'MotoGP' },
      { nombre: 'Piloto #12 - Maverick Viñales', moto: 'Aprilia', numero: '12', evento: 'Superbike' },
      { nombre: 'Piloto #1 - Francesco Bagnaia', moto: 'Ducati', numero: '1', evento: 'MotoGP' }
    ];

    const pilotoAleatorio = pilotos[Math.floor(Math.random() * pilotos.length)];

    statusFacial.textContent = `Rostro identificado: ${pilotoAleatorio.nombre}`;
    statusFacial.className = 'status-facial exito';

    // Mostrar alerta con resultados
    setTimeout(() => {
      alert(
        `🔐 Identificación Facial Completada\n\n` +
        `Piloto detectado: ${pilotoAleatorio.nombre}\n` +
        `Moto: ${pilotoAleatorio.moto} #${pilotoAleatorio.numero}\n` +
        `Evento: ${pilotoAleatorio.evento}\n\n` +
        `Se han filtrado las fotos disponibles para este piloto.`
      );

      // Aquí se podrían aplicar filtros automáticos a la galería
      // gallery.filtrarPorPiloto(pilotoAleatorio.numero);
    }, 500);

    btnCapturar.disabled = false;
    btnCapturar.textContent = 'Capturar Nueva Foto';
  }

  // Detener cámara al salir de la página
  function detenerCamara() {
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      stream = null;
    }
  }

  // Eventos
  if (btnCapturar) {
    btnCapturar.addEventListener('click', capturarFoto);
  }

  // Inicializar
  document.addEventListener('DOMContentLoaded', iniciarCamara);
  window.addEventListener('beforeunload', detenerCamara);

})();
