(function() {
  const userId = window.userId;
  const HISTORY_KEY = 'song_history_' + userId;
  const historyList = document.getElementById('axrcGrid');
  const emptyState = document.getElementById('axrcEmptyState');

  function renderHistory() {
    const history = JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]');
    if (!history.length) {
      emptyState.style.display = 'block';
      historyList.style.display = 'none';
      return;
    }
    emptyState.style.display = 'none';
    historyList.style.display = 'grid';
    historyList.innerHTML = '';

    // Create tiles for each song
    history.forEach((song, index) => {
      const tile = document.createElement('div');
      tile.className = 'axrc-tile';
      tile.title = song.title;
      tile.innerHTML = `
        <div class="axrc-tile-cover">
          <img src="${song.cover || window.defaultCover}" alt="Portada de ${song.title}" width="260" height="260" loading="lazy" decoding="async">
          <button type="button" class="axrc-play-btn" data-index="${index}" aria-label="Reproducir ${song.title}">
            <i class="fa-solid fa-play"></i>
          </button>
        </div>
        <div class="axrc-tile-name">${song.title}</div>
        <div class="axrc-tile-artist">${song.artist}</div>
        <div class="axrc-tile-actions">
          <button class="axrc-add-queue-btn" data-index="${index}" title="Agregar a cola">
            <i class="fas fa-plus"></i>
          </button>
        </div>
      `;
      historyList.appendChild(tile);
    });

    // Add event listeners
    attachButtonListeners();
  }

  function attachButtonListeners() {
    // Play buttons
    document.querySelectorAll('.axrc-play-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const index = this.dataset.index;
        const history = JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]');
        const song = history[index];
        if (song && window.AuraPlayer) {
          window.AuraPlayer.play({
            id: song.id,
            src: song.src || '',
            title: song.title,
            artist: song.artist,
            cover: song.cover
          });
        }
      });
    });

    // Add to queue buttons
    document.querySelectorAll('.axrc-add-queue-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const index = this.dataset.index;
        const history = JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]');
        const song = history[index];
        if (song && window.AuraQueue) {
          window.AuraQueue.addToEnd([{
            id: song.id,
            title: song.title,
            artist: song.artist,
            cover: song.cover,
            src: song.src || ''
          }]);
          showNotification('Canción agregada a la cola', 'success');
        }
      });
    });
  }

  function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    Object.assign(notification.style, {
      position: 'fixed',
      top: '20px',
      right: '20px',
      padding: '12px 16px',
      borderRadius: '8px',
      color: '#fff',
      fontWeight: '500',
      zIndex: '1000',
      opacity: '0',
      transform: 'translateY(-20px) scale(0.8)',
      transition: 'all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55)',
      boxShadow: '0 4px 12px rgba(0,0,0,0.3)'
    });

    if (type === 'success') {
      notification.style.backgroundColor = '#22c55e';
      notification.style.borderLeft = '4px solid #16a34a';
    } else {
      notification.style.backgroundColor = '#7c3aed';
      notification.style.borderLeft = '4px solid #6d28d9';
    }

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.opacity = '1';
      notification.style.transform = 'translateY(0) scale(1)';
    }, 10);

    setTimeout(() => {
      notification.style.opacity = '0';
      notification.style.transform = 'translateY(-20px) scale(0.8)';
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification);
        }
      }, 400);
    }, 3000);
  }

  // Listen for storage changes
  window.addEventListener('storage', function(e) {
    if (e.key === HISTORY_KEY) {
      renderHistory();
    }
  });

  // Initialize on load
  document.addEventListener('DOMContentLoaded', function() {
    renderHistory();
  });
})();
