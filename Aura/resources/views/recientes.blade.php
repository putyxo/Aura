<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>AURA — Historial de canciones reproducidas</title>
  @vite('resources/css/recientes.css')
</head>
<body>
  <div class="with-sidebar">
    @include('components.sidebar')

    <main class="main-content">
      <div id="historyContainer" class="history-container">
        <h2>Historial de canciones reproducidas</h2>
        <ul id="historyList" class="history-list"></ul>
        <div id="emptyState" class="empty-state">
          <h3>Aún no se ha reproducido ninguna canción</h3>
        </div>
      </div>
    </main>

    @include('components.footer')
  </div>

  <script>
    (function() {
      const userId = @json(Auth::id());
      const HISTORY_KEY = 'song_history_' + userId;
      const historyList = document.getElementById('historyList');
      const emptyState = document.getElementById('emptyState');

      function renderHistory() {
        const history = JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]');
        if (!history.length) {
          emptyState.style.display = 'block';
          historyList.style.display = 'none';
          return;
        }
        emptyState.style.display = 'none';
        historyList.style.display = 'block';
        historyList.innerHTML = '';
        history.forEach((song, index) => {
          const li = document.createElement('li');
          li.className = 'history-item';
          li.style.opacity = '0';
          li.style.transform = 'translateY(20px)';
          li.style.transition = 'all 0.3s ease';
          li.innerHTML = `
            <img src="${song.cover || '{{ asset('img/default-cancion.png') }}'}" alt="Portada" class="history-cover" />
            <div class="history-info">
              <span class="history-title" title="${song.title}">${song.title}</span>
              <span class="history-artist" title="${song.artist}">${song.artist}</span>
            </div>
            <div class="history-actions">
              <button class="history-play-btn" data-index="${index}" title="Reproducir">
                <i class="fas fa-play"></i>
              </button>
              <button class="history-add-queue-btn" data-index="${index}" title="Agregar a cola">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          `;
          historyList.appendChild(li);

          // Animate item appearance
          setTimeout(() => {
            li.style.opacity = '1';
            li.style.transform = 'translateY(0)';
          }, index * 50);
        });

        // Add event listeners to buttons
        attachButtonListeners();
      }

      function attachButtonListeners() {
        // Play buttons
        document.querySelectorAll('.history-play-btn').forEach(btn => {
          // Add hover animations
          btn.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(5deg)';
            this.style.transition = 'all 0.2s ease';
          });
          btn.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
          });

          btn.addEventListener('click', function() {
            // Click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
              this.style.transform = 'scale(1.1) rotate(5deg)';
            }, 100);

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
        document.querySelectorAll('.history-add-queue-btn').forEach(btn => {
          // Add hover animations
          btn.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(-5deg)';
            this.style.transition = 'all 0.2s ease';
          });
          btn.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
          });

          btn.addEventListener('click', function() {
            // Click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
              this.style.transform = 'scale(1.1) rotate(-5deg)';
            }, 100);

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
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        // Style the notification
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

        // Animate in with bounce effect
        setTimeout(() => {
          notification.style.opacity = '1';
          notification.style.transform = 'translateY(0) scale(1)';
        }, 10);

        // Add a subtle pulse animation
        let pulseCount = 0;
        const pulseInterval = setInterval(() => {
          if (pulseCount < 2) {
            notification.style.transform = 'translateY(0) scale(1.02)';
            setTimeout(() => {
              notification.style.transform = 'translateY(0) scale(1)';
            }, 100);
            pulseCount++;
          } else {
            clearInterval(pulseInterval);
          }
        }, 500);

        // Remove after 3 seconds with exit animation
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

      // Listen for storage changes to update history in real-time
      window.addEventListener('storage', function(e) {
        if (e.key === HISTORY_KEY) {
          // Add a smooth transition when history updates
          historyList.style.opacity = '0.7';
          setTimeout(() => {
            renderHistory();
            historyList.style.opacity = '1';
          }, 150);
        }
      });

      // Add loading animation on page load
      document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('historyContainer');
        container.style.opacity = '0';
        container.style.transform = 'translateY(30px)';
        container.style.transition = 'all 0.6s ease';

        setTimeout(() => {
          container.style.opacity = '1';
          container.style.transform = 'translateY(0)';
        }, 100);

        renderHistory();
      });

      // Add smooth transitions for empty state changes
      function updateEmptyStateVisibility() {
        const hasHistory = JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]').length > 0;
        const emptyState = document.getElementById('emptyState');
        const historyList = document.getElementById('historyList');

        if (hasHistory) {
          emptyState.style.opacity = '0';
          setTimeout(() => {
            emptyState.style.display = 'none';
            historyList.style.display = 'block';
            setTimeout(() => {
              historyList.style.opacity = '1';
            }, 50);
          }, 300);
        } else {
          historyList.style.opacity = '0';
          setTimeout(() => {
            historyList.style.display = 'none';
            emptyState.style.display = 'block';
            setTimeout(() => {
              emptyState.style.opacity = '1';
            }, 50);
          }, 300);
        }
      }
    })();
  </script>
</body>
</html>
