/* === Reproducir desde resultados del buscador === */
function playFromSearch(el) {
  if (!el || !window.audio || !window.playBtn) {
    console.warn("⚠️ Reproductor global no inicializado");
    return;
  }

  // Extraer datos del elemento
  const src    = el.dataset.src;
  const title  = el.dataset.title;
  const artist = el.dataset.artist;
  const cover  = el.dataset.cover;

  if (!src) {
    console.warn("⚠️ Esta canción no tiene audio válido");
    return;
  }

  // Asignar al reproductor global
  window.audio.src = src;
  document.querySelector('.track-title').textContent  = title;
  document.querySelector('.track-artist').textContent = artist;
  document.querySelector('.track-cover').src          = cover;

  // Estado global
  window.currentSongId = el.dataset.id;

  // Reproducir
  window.audio.play();
  window.playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
}
