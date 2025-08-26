
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AURA</title>
    @vite('resources/css/welcome.css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://www.mshr.app/mesh/1734362949630">


</head>
        <!-- Nueva sección de auriculares -->
        <section class="aurionas-section">
            <div class="aurionas-container">
                <nav class="aurionas-nav">
                    <div class="aurionas-logo">
                        <img src="img/logo 2.png" alt="AURA Logo" class="nav-logo-image">
                        <span class="logo-text"></span>
                    </div>
                    
                    <div class="nav-buttons">
                    <a href="login" class="nav-btn" id="header-login-link">Iniciar Sesión</a>
                    <a href="seleccionar-tipo" class="nav-btn" id="header-login-link">Registrate</a>
                    </div>
                </nav>
                
                <div class="aurionas-content">
                    <div class="content-left">
                        <h1 class="main-title">AURA</h1>
                        <p class="description">Tu arte merece ser escuchado, tu talento merece brillar.</p>
                        <button class="preorder-btn">Inicia ya!</button>
                    </div>
                    
                   <div class="content-center">
                        <div class="headphones-container">
                            <img src="img/auriculares.pgn.png" alt="Auriculares Aurionas" class="headphones-image">
                            </div>
                        </div>
                    </div>
                    
                    <div class="content-right">
                        <div class="battery-info">
                            <div class="battery-circle">
                                <div class="battery-progress"></div>
                                <div class="battery-number">60</div>
                                <div class="battery-label">Hours</div>
                            </div>
                            <h3>Extended<br>Battery Life</h3>
                            <p>A powerful battery that keeps the music playing.</p>
                        </div>
                        
                        <div class="charging-info">
                            <span class="charging-icon">⚡</span>
                            <span class="charging-text">Fast Charging</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <main>

            <section class="ai-solutions">
                <div class="ai-content">
                    <div class="animated-text-container">
                        <h2 class="overlay-text">Donde la nueva generación de artistas salvadoreños encuentra su escenario digital</h2>
                        <span class="animated-letter">A</span>
                        <span class="animated-letter">U</span>
                        <span class="animated-letter">R</span>
                        <span class="animated-letter">A</span>
                    </div>
                </div>
            </section>

            <!-- Stats Section -->
            <section class="stats-section">
                <div class="stats-container">
                    <div class="stat-item">
                        <div class="stat-number">Música</div>
                        <div class="stat-label">Artistas nuevos cada semana</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <div class="stat-number">Playlist</div>
                        <div class="stat-label">Escoge a tus favoritos</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <div class="stat-number">Nuevos lanzamientos</div>
                        <div class="stat-label">Lo más nuevo del momento</div>
                    </div>
                </div>
            </section>

            <!-- Business Section -->
            <section class="business-section">
                <div class="business-container">
                    <div class="business-content">
                        <div class="business-text">
                            <h2>AURA<br>¿Qué somos?</h2>
                            <p>Nos enfocamos en ayudar al talento emergente de El Salvador, apoyando a pequeños artistas permitiendole subir música y albumnes, asi tmabién brindale beneficios a los usuarios</p>
                            <a href="#" class="btn-get-started">Get Started</a>
                        </div>
                        <div class="business-features">
                            <div class="feature-item">
                                <div class="feature-icon rewards">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="currentColor"/>
                                    </svg>
                                </div>
                                <div class="feature-content">
                                    <h3>Reconocimiento a artistas internacionales</h3>
                                    <p>Contamos con algunos artistas internacionales, para que siempre estes en sintonia</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon secured">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 1L3 5V11C3 16.55 6.84 21.74 12 23C17.16 21.74 21 16.55 21 11V5L12 1ZM10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" fill="currentColor"/>
                                    </svg>
                                </div>
                                <div class="feature-content">
                                    <h3>Seguridad</h3>
                                    <p>Ofrecemos seguridad a los usuarios brindandole proteción en todo momento, incluso a la hora del pago</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon transfer">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 11H15L12 8L9 11ZM12 2C6.48 2 2 6.48 2 12S6.48 22 12 22 22 17.52 22 12 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12S7.59 4 12 4 20 7.59 20 12 16.41 20 12 20Z" fill="currentColor"/>
                                    </svg>
                                </div>
                                <div class="feature-content">
                                    <h3>Planes</h3>
                                    <p>Contamos con planes para que puedas disfrutar de más beneficios</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Billing Section -->
            <section class="billing-section">
                <div class="billing-container">
                    <div class="billing-mockup">
                        <div class="paypal-card">
                            <div class="paypal-header">
                                <div class="paypal-logo">Analu Dada</div>
                                <div class="paypal-balance">12 meses</div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                        </div>
                        <div class="transaction-list">
                            <div class="transaction-header">Popular Hot Artist</div>
                            <div class="transaction-item">
                                <div class="transaction-icon dribbble"></div>
                                <div class="transaction-info">
                                    <div class="transaction-name">Analu Dada</div>
                                    <div class="transaction-date">Singer</div>
                                </div>
                                <div class="transaction-amount">86.6k</div>
                            </div>
                            <div class="transaction-item">
                                <div class="transaction-icon netflix"></div>
                                <div class="transaction-info">
                                    <div class="transaction-name">Dasokeii</div>
                                    <div class="transaction-date">Singer</div>
                                </div>
                                <div class="transaction-amount">10.6k</div>
                            </div>
                            <div class="transaction-item">
                                <div class="transaction-icon cash"></div>
                                <div class="transaction-info">
                                    <div class="transaction-name">Fuerza Regida</div>
                                    <div class="transaction-date">Artist</div>
                                </div>
                                <div class="transaction-amount">36.4M</div>
                            </div>
                            <div class="success-message">
                                <div class="success-icon">✓</div>
                                Artistas verificados 
                            </div>
                        </div>
                    </div>
                    <div class="billing-content">
                        <h2>Hot Top<br>Artist</h2>
                        <p>Nuestros artistas verificados más escuchados y seleccionados por los usuarios.</p>
                        <div class="app-downloads">
                            <a href="#" class="app-store-btn">
                                <img src="img/artista 1.jpeg" alt="App Store">
                            </a>
                            <a href="#" class="google-play-btn">
                                <img src="img/artista 2.jpeg" alt="Google Play">
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Card Deal Section -->
            <section class="card-deal-section">
                <div class="card-deal-container">
                    <div class="card-deal-content">
                        <h2>Conoce <br>más sobre el talento salvadoreño.</h2>
                        <p>Registrate / Inicia sesión con nostros para conocer más sobre la música de los talentos salvadoreños </p>
                        <a href="#" class="btn-get-started">Inicia ya!</a>
                    </div>
                    <div class="card-deal-mockup">
                        <div class="online-analysis">
                            <div class="analysis-header">
                                <div class="analysis-icon"></div>
                                <span>Fuerza Regida</span>
                            </div>
                            <div class="analysis-content">
                                <div class="analysis-item">
                                    <span>Tu sancho</span>
                                    <span>261,627,981</span>
                                </div>
                                <div class="analysis-item">
                                    <span>Malboro Rojo</span>
                                    <span>242,467,608</span>
                                </div>
                                <div class="analysis-item">
                                    <span>Coqueta</span>
                                    <span>256,944,845</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            </section>
            
            </section>
            
            
            <!-- Footer -->
            <footer class="main-footer">
                <div class="footer-container">
                    <div class="footer-content">
                        <!-- Logo y descripción -->
                        <div class="footer-section">
                            </div>
                        
                        <!-- Enlaces rápidos -->
                        <div class="footer-section">
                        <!-- Contacto -->
                        <div class="footer-section">
                            <h4 class="footer-title">Contacto</h4>
                            <div class="contact-info">
                                <p>📧 aura@gmail.com</p>
                                <p>📞 +503 2222-3333</p>
                                <p>📍 San Salvador, El Salvador</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Copyright -->
                    <div class="footer-bottom">
                        <div class="footer-divider"></div>
                        <div class="copyright">
                            <p>&copy; 2025 AURA. Todos los derechos reservados. Hecho con amor en El Salvador.</p>
                        </div>
                    </div>
                </div>
            </footer>
            
<script src="hero-bubbles.js"></script>    
</body>
</html>