import initProfile   from './ed-perfil.js';
import initPlaylists from './playlist.js';
import initUpload    from './subir.js';
import initMenu      from './menu.js';
import initCuenta    from './cuenta.js'; 
import LikeMenu    from './like-menu.js';
import initLike from './like.js';
import initPlaylistC from './playlist-card.js';
import initPreferencias from './preferencias.js';
import initRecientes from './recientes.js';
import initMenuAlbum from './menu_album.js';


function boot() {
  const page = document.body.dataset.page;

  if (page === 'perfil')     initProfile();
  if (page === 'playlists') initPlaylists();
  if (page === 'upload')    initUpload();
  if (page === 'menu')      initMenu();
  if (page === 'account')   initCuenta(); 
  if (page === 'like-menu')     initLikeMenu();
  if (page === 'likes')     initLike();
  if (page === 'playlist-card')     initPlaylistC();
  if (page === 'preferencias')     initPreferencias();
  if (page === 'recientes')     initRecientes();
  if (page === 'menu-album')     initMenuAlbum();
}

document.addEventListener('turbo:load', boot);
document.addEventListener('DOMContentLoaded', boot);
