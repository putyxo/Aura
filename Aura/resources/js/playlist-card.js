export default function initPlaylistC() {
  console.log("Inicializando scripts de Playlist card...");
(function(){
  const $  = (sel,ctx=document)=>ctx.querySelector(sel);
  const $$ = (sel,ctx=document)=>Array.from(ctx.querySelectorAll(sel));
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

  // Abrir/Cerrar menú kebab
  function closeMenus(except=null){
    $$('.kebab-menu.open').forEach(m=>{
      if (except && m===except) return;
      m.classList.remove('open');
      m.parentElement?.querySelector('.more-btn')?.setAttribute('aria-expanded','false');
    });
  }
  document.addEventListener('click', (e)=>{
    const more = e.target.closest('.more-btn');
    if (more){
      const menu = more.parentElement.querySelector('.kebab-menu');
      const open = !menu.classList.contains('open');
      closeMenus();
      menu.classList.toggle('open',open);
      more.setAttribute('aria-expanded', open ? 'true' : 'false');
      return;
    }
    if (!e.target.closest('.menu-wrap')) closeMenus();
  });

  // Reproducir al hacer click en la fila (excepto si clic en acciones)
  function isInsideActions(t){
    return !!t.closest('.song-actions, .menu-wrap, form, button, a, input');
  }
  document.addEventListener('click', (e)=>{
    const row = e.target.closest('.song-row');
    if (!row) return;
    if (isInsideActions(e.target)) return;

    const meta = {
      id: row.dataset.id,
      src: row.dataset.src,
      title: row.dataset.title,
      artist: row.dataset.artist,
      cover: row.dataset.cover
    };
    if (!meta.src) return;

    if (window.AuraQueue?.externalPlay) window.AuraQueue.externalPlay(meta);
    else if (window.AuraPlayer?.play) window.AuraPlayer.play(meta);
  });

  // Like (UI optimista)
  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('.inline-like .like-btn');
    if (!btn) return;
    e.preventDefault();
    const form = btn.closest('form.inline-like');
    const icon = btn.querySelector('i');
    const wasLiked = btn.classList.contains('is-liked');

    // optimista
    btn.classList.toggle('is-liked', !wasLiked);
    btn.setAttribute('aria-pressed', (!wasLiked).toString());
    icon.classList.toggle('fa-regular', wasLiked);
    icon.classList.toggle('fa-solid', !wasLiked);

    try{
      const res = await fetch(form.action, {
        method:'POST',
        headers: {'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        credentials:'same-origin'
      });
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json().catch(()=>({}));
      const finalLiked = !!data.liked;
      btn.classList.toggle('is-liked', finalLiked);
      btn.setAttribute('aria-pressed', String(finalLiked));
      icon.classList.toggle('fa-regular', !finalLiked);
      icon.classList.toggle('fa-solid', finalLiked);
    }catch(err){
      // revertir
      btn.classList.toggle('is-liked', wasLiked);
      btn.setAttribute('aria-pressed', String(wasLiked));
      icon.classList.toggle('fa-regular', !wasLiked);
      icon.classList.toggle('fa-solid', wasLiked);
      console.error(err);
    }
  });

  // Añadir a cola desde menú
  document.addEventListener('click', (e)=>{
    const mi = e.target.closest('.menu-item[data-action="queue"]');
    if (!mi) return;
    const row = e.target.closest('.song-row');
    if (!row) return;
    const song = {
      id: row.dataset.id,
      title: row.dataset.title,
      artist: row.dataset.artist,
      cover: row.dataset.cover,
      audio: row.dataset.src,
      duration: 0
    };
    if (window.AuraQueue?.addToEnd) window.AuraQueue.addToEnd([song]);
    closeMenus();
  });

  // Quitar de playlist (botón rojo pequeño)
  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('.remove-pl-btn');
    if (!btn) return;
    const form = btn.closest('form.inline-remove');
    const row  = btn.closest('.song-row');
    if (!form || !row) return;

    if (!confirm('¿Quitar esta canción de la playlist?')) return;

    try{
      const res = await fetch(form.action, {
        method: 'POST', // soporta backends que requieren POST + _method
        headers: {'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest','Accept':'application/json','Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
        body: new URLSearchParams({'_method':'DELETE'})
      });
      if (!res.ok) throw new Error('HTTP '+res.status);
      // quitar del DOM
      row.style.pointerEvents='none';
      row.style.opacity='.55';
      setTimeout(()=> row.remove(), 120);
    }catch(err){
      alert('No se pudo quitar de la playlist');
      console.error(err);
    }
  });

  // Quitar de playlist (desde menú 3 puntos)
  document.addEventListener('click', async (e)=>{
    const mi = e.target.closest('.remove-from-pl');
    if (!mi) return;
    const row = e.target.closest('.song-row');
    if (!row) return;

    if (!confirm('¿Quitar esta canción de la playlist?')) return;

    try{
      const res = await fetch(mi.dataset.action, {
        method:'POST',
        headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest','Accept':'application/json','Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
        body: new URLSearchParams({'_method':'DELETE'})
      });
      if (!res.ok) throw new Error('HTTP '+res.status);
      closeMenus();
      row.style.pointerEvents='none';
      row.style.opacity='.55';
      setTimeout(()=> row.remove(), 120);
    }catch(err){
      alert('No se pudo quitar de la playlist');
      console.error(err);
    }
  });

})();
}