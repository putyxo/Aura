export default function initLikeMenu() {
  console.log("Inicializando scripts de like menu...");
(() => {
  const $  = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));

  const tiles   = $$('.lk-tile');
  const grid    = $('#lkGrid');
  const search  = $('#lkSearch');
  const clear   = $('#lkClear');
  const sortSel = $('#lkSort');

  // Fmt helpers
  const toKey = v => (v||'').toString().trim().toLowerCase();

  // =============== BÚSQUEDA ===============
  const applyFilter = () => {
    const q = toKey(search?.value);
    tiles.forEach(t => {
      const hay = (t.dataset.name || '');
      t.style.display = hay.includes(q) ? '' : 'none';
    });
  };
  search?.addEventListener('input', applyFilter);
  clear?.addEventListener('click', () => { if (!search) return; search.value=''; applyFilter(); search.focus(); });

  // =============== ORDEN ===============
  const sorters = {
    recent: (a,b) => (new Date(b.dataset.recent||0)) - (new Date(a.dataset.recent||0)),
    title:  (a,b) => (a.dataset.title> b.dataset.title) ? 1 : -1,
    artist: (a,b) => (a.dataset.artist>b.dataset.artist)? 1 : -1,
    year:   (a,b) => (+b.dataset.year||0) - (+a.dataset.year||0),
  };
  const applySort = () => {
    if (!grid || !sortSel) return;
    const val = sortSel.value || 'recent';
    const nodes = tiles.filter(el => el.style.display !== 'none');
    nodes.sort(sorters[val] || sorters.recent).forEach(el => grid.appendChild(el));
  };
  sortSel?.addEventListener('change', applySort);

  // =============== UNLIKE (fallback por form) ===============
  $$('.lk-like').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const tile = e.currentTarget.closest('.lk-tile');
      const form = $('.lk-like-form', tile);
      if (form) form.submit();
    });
  });

  // =============== PLAY ÁLBUM (evento global para el footer) ===============
  $$('.lk-play').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const tile = e.currentTarget.closest('.lk-tile');
      const albumId = tile?.dataset.albumId;
      // Señal al reproductor global
      window.dispatchEvent(new CustomEvent('aura:playAlbum', {
        detail: { albumId, source: 'likes-albums' }
      }));
      // Feedback visual: toggle icon (no maneja pausa real sin el footer)
      const icon = e.currentTarget.querySelector('i');
      if (icon?.classList.contains('fa-play')) {
        icon.classList.replace('fa-play','fa-pause');
      } else {
        icon?.classList.replace('fa-pause','fa-play');
      }
    });
  });

  // Inicial
  applyFilter();
  applySort();
})();
}