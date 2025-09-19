import initProfile from './ed-perfil.js';
import initPlaylists from './playlist.js';
import initUpload from './subir.js';

function boot() {
  const page = document.body.dataset.page;

  if (page === 'perfil') initProfile();
  if (page === 'playlists') initPlaylists();
  if (page === 'upload') initUpload();
}

document.addEventListener('turbo:load', boot);
document.addEventListener('DOMContentLoaded', boot);
