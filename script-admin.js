document.getElementById('form-generar-datos').addEventListener('submit', function(e) {
  e.preventDefault();

  const tipo = document.getElementById('tipo-registro').value;
  const nombre = document.getElementById('nombre').value;
  const identificador = document.getElementById('identificador').value;
  const email = document.getElementById('email').value;
  const estado = document.getElementById('estado').value;
  const descripcion = document.getElementById('descripcion').value;

  // Objeto con los datos capturados
  const nuevoRegistro = {
    tipo,
    nombre,
    identificador,
    email,
    estado,
    descripcion,
    fechaCreacion: new Date().toISOString()
  };

  console.log('Datos listos para enviar al backend:', nuevoRegistro);

  // Mostrar mensaje de éxito simulado
  const mensajeEl = document.getElementById('mensaje-respuesta');
  mensajeEl.textContent = `¡Registro de tipo "${tipo}" guardado exitosamente con ID ${identificador}!`;
  mensajeEl.className = 'mensaje exito';

  // Opcional: Reiniciar formulario
  document.getElementById('form-generar-datos').reset();
});