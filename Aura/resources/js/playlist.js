document.addEventListener('DOMContentLoaded', function() {
    const playlistGrid = document.querySelector('.playlist-grid');
    const tiles = playlistGrid.querySelectorAll('.tile');

    // Animación de entrada para las tarjetas de playlist
    tiles.forEach((tile, index) => {
        tile.style.opacity = '0';
        tile.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            tile.style.transition = 'all 0.5s ease';
            tile.style.opacity = '1';
            tile.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Efecto de hover en las tarjetas
    tiles.forEach(tile => {
        tile.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.2)';
            this.style.transform = 'scale(1.05)';
        });

        tile.addEventListener('mouseleave', function() {
            this.style.boxShadow = 'none';
            this.style.transform = 'scale(1)';
        });
    });

    // Mostrar detalle de playlist al hacer clic
    tiles.forEach(tile => {
        tile.addEventListener('click', function() {
            const detail = document.getElementById('playlistDetail');
            const cover = this.querySelector('.tile-cover img').src;
            const title = this.querySelector('.tile-name').textContent;
            const count = this.querySelector('.tile-count').textContent;

            document.getElementById('detailCover').src = cover;
            document.getElementById('detailTitle').textContent = title;
            document.getElementById('detailCount').textContent = count;

            detail.hidden = false;
        });
    });

    // Cerrar detalle de playlist
    document.getElementById('btnClosePlaylistModal').addEventListener('click', function() {
        document.getElementById('playlistDetail').hidden = true;
    });
});
