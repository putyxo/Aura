<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albums</title>
 @vite('resources/css/menu_album.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="script.js" defer></script>
</head>
<body>
            @yield('content')
@include('components.traductor')
    <div class="app-container">
        <!-- Navigation Bar -->
  @include('components.sidebar')   {{-- Sidebar fijo a la izquierda --}}

        <!-- Main Content -->
        <main class="main-content">
            <!-- Album Header -->
            <div class="album-header">
                <div class="album-info">
                    <h1>The Weeknd</h1>
                    <div class="album-meta">
                        <span class="artist-link">After Hours</span>
                        <span class="dot">•</span>
                        <span>Album</span>
                        <span class="dot">•</span>
                        <span>9 songs</span>
                        <span class="dot">•</span>
                        <span>mar 20, 2020</span>
                    </div>
                </div>
            </div>

            <!-- Album Actions -->
            <div class="album-actions">

                <button class="play-btn"><i class="fas fa-play"></i></button>
            </div>

            <!-- Track List -->
            <div class="track-list">
                <div class="track-header">
                    <div class="track-number">#</div>
                    <div class="track-title">Title</div>
                    <div class="track-duration">Duration</div>
                </div>

                <div class="track-item playing">
                    <div class="track-number">1</div>
                    <div class="track-info">
                        <div class="track-title">Alone Again</div>
                        <div class="track-artist">The Weeknd</div>
                    </div>
                    <div class="track-actions">
                        <button class="like-track"><i class="far fa-heart"></i></button>
                        <button class="save-track"><i class="far fa-plus-square"></i></button>
                        <span class="track-duration">4:09</span>
                        <button class="more-options"><i class="fas fa-ellipsis-h"></i></button>
                    </div>
                </div>

                <div class="track-item">
                    <div class="track-number">2</div>
                    <div class="track-info">
                        <div class="track-title">Too Late</div>
                        <div class="track-artist">The Weeknd</div>
                    </div>
                    <div class="track-actions">
                        <button class="like-track"><i class="far fa-heart"></i></button>
                        <button class="save-track"><i class="far fa-plus-square"></i></button>
                        <span class="track-duration">3:58</span>
                        <button class="more-options"><i class="fas fa-ellipsis-h"></i></button>
                    </div>
                </div>

                <div class="track-item">
                    <div class="track-number">3</div>
                    <div class="track-info">
                        <div class="track-title">Hardest To Love</div>
                        <div class="track-artist">The Weeknd</div>
                    </div>
                    <div class="track-actions">
                        <button class="like-track"><i class="far fa-heart"></i></button>
                        <button class="save-track"><i class="far fa-plus-square"></i></button>
                        <span class="track-duration">3:30</span>
                        <button class="more-options"><i class="fas fa-ellipsis-h"></i></button>
                    </div>
                </div>

                <div class="track-item">
                    <div class="track-number">4</div>
                    <div class="track-info">
                        <div class="track-title">Scared To Live</div>
                        <div class="track-artist">The Weeknd</div>
                    </div>
                    <div class="track-actions">
                        <button class="like-track"><i class="far fa-heart"></i></button>
                        <button class="save-track"><i class="far fa-plus-square"></i></button>
                        <span class="track-duration">3:10</span>
                        <button class="more-options"><i class="fas fa-ellipsis-h"></i></button>
                    </div>
                </div>

                <div class="track-item">
                    <div class="track-number">5</div>
                    <div class="track-info">
                        <div class="track-title">Snowchild</div>
                        <div class="track-artist">The Weeknd</div>
                    </div>
                    <div class="track-actions">
                        <button class="like-track"><i class="far fa-heart"></i></button>
                        <button class="save-track"><i class="far fa-plus-square"></i></button>
                        <span class="track-duration">4:06</span>
                        <button class="more-options"><i class="fas fa-ellipsis-h"></i></button>
                    </div>
                </div>

                <div class="track-item">
                    <div class="track-number">6</div>
                    <div class="track-info">
                        <div class="track-title">Escape From LA</div>
                        <div class="track-artist">The Weeknd</div>
                    </div>
                    <div class="track-actions">
                        <button class="like-track"><i class="far fa-heart"></i></button>
                        <button class="save-track"><i class="far fa-plus-square"></i></button>
                        <span class="track-duration">5:55</span>
                        <button class="more-options"><i class="fas fa-ellipsis-h"></i></button>
                    </div>
                </div>

                <div class="track-item">
                    <div class="track-number">7</div>
                    <div class="track-info">
                        <div class="track-title">Heartless</div>
                        <div class="track-artist">The Weeknd</div>
                    </div>
                    <div class="track-actions">
                        <button class="like-track"><i class="far fa-heart"></i></button>
                        <button class="save-track"><i class="far fa-plus-square"></i></button>
                        <span class="track-duration">3:17</span>
                        <button class="more-options"><i class="fas fa-ellipsis-h"></i></button>
                    </div>
                </div>

                <div class="track-item">
                    <div class="track-number">8</div>
                    <div class="track-info">
                        <div class="track-title">Faith</div>
                        <div class="track-artist">The Weeknd</div>
                    </div>
                    <div class="track-actions">
                        <button class="like-track"><i class="far fa-heart"></i></button>
                        <button class="save-track"><i class="far fa-plus-square"></i></button>
                        <span class="track-duration">4:43</span>
                        <button class="more-options"><i class="fas fa-ellipsis-h"></i></button>
                    </div>
                </div>

                <div class="track-item">
                    <div class="track-number">9</div>
                    <div class="track-info">
                        <div class="track-title">Blinding Lights</div>
                        <div class="track-artist">The Weeknd</div>
                    </div>
                    <div class="track-actions">
                        <button class="like-track"><i class="far fa-heart"></i></button>
                        <button class="save-track"><i class="far fa-plus-square"></i></button>
                        <span class="track-duration">3:19</span>
                        <button class="more-options"><i class="fas fa-ellipsis-h"></i></button>
                    </div>
                </div>
            </div>

            <!-- Album Cover and Genre Tags -->
            <div class="album-sidebar">
                <div class="album-cover">
                    <img src="img/after hours album.jpeg" alt="Random Access Memories">
                </div>
                <div class="genre-tags">
                    <span class="tag">Funk</span>
                    <span class="tag">Electronic music</span>
                    <span class="tag">Disco</span>
                    <span class="tag">Soft Rock</span>
                    <span class="tag">Progressive pop</span>
                </div>
                <div class="artist-section">
                    <div class="artist-item">
                        <img src="img/the hills cancion.jpeg" alt="Daft Punk" class="artist-img">
                        <span class="artist-name">The Hills</span>
                    </div>
                    <div class="artist-item">
                        <img src="img/trilogy album.jpeg" alt="Pharrell Williams" class="artist-img">
                        <span class="artist-name">Trilogy</span>
                    </div>
                    <div class="artist-item">
                        <img src="img/idol album.jpeg" alt="Nile Rodgers" class="artist-img">
                        <span class="artist-name">The idol</span>
     
                    </div>
                </div>
            </div>
              @include('components.footer')
        </main>
    </div>
</body>