<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta - AURA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #000000;
            color: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }




        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            grid-template-areas:
                "profile profile"
                "experience experience"
                "tools education"
                "portfolio details";
            position: relative;
        }

        /* Efectos de glassmorphism mejorados */
        .glass-card {
            background: linear-gradient(145deg, 
                rgba(26, 26, 26, 0.8),
                rgba(42, 26, 42, 0.6)
            );
            backdrop-filter: blur(20px);
            border: 1px solid rgba(138, 43, 226, 0.3);
            border-radius: 24px;
            padding: 32px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(138, 43, 226, 0.1),
                transparent
            );
            transition: left 0.6s ease;
        }

        .glass-card:hover::before {
            left: 100%;
        }

        .glass-card:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: rgba(138, 43, 226, 0.6);
            box-shadow: 
                0 20px 40px rgba(138, 43, 226, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        /* Profile Section mejorada */
        .profile-section {
            grid-area: profile;
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .profile-image {
            position: relative;
        }

        .profile-image img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid transparent;
            background: linear-gradient(45deg, #8a2be2, #ba55d3) border-box;
            position: relative;
        }

        .profile-image::before {
            content: '';
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            background: linear-gradient(45deg, #8a2be2, #ba55d3, #9932cc, #8a2be2);
            z-index: -1;
            animation: rotate 3s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .profile-info {
            flex: 1;
        }

        .profile-info h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ba55d3, #9932cc);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { 
                filter: drop-shadow(0 0 10px rgba(138, 43, 226, 0.5));
                text-shadow: 0 0 20px rgba(138, 43, 226, 0.3);
            }
            to { 
                filter: drop-shadow(0 0 20px rgba(138, 43, 226, 0.8));
                text-shadow: 0 0 30px rgba(138, 43, 226, 0.6);
            }
        }

        .welcome-text {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 24px;
            font-weight: 300;
        }

        .interest-tags {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .tag {
            background: linear-gradient(135deg, rgba(138, 43, 226, 0.2), rgba(186, 85, 211, 0.3));
            padding: 10px 18px;
            border-radius: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(138, 43, 226, 0.4);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .tag::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.4s ease;
        }

        .tag:hover::before {
            left: 100%;
        }

        .tag:hover {
            transform: translateY(-3px);
            border-color: rgba(138, 43, 226, 0.8);
            box-shadow: 0 8px 25px rgba(138, 43, 226, 0.4);
        }

        /* Experience Section mejorada */
        .experience-section {
            grid-area: experience;
            display: flex;
            gap: 30px;
        }

        .job-card {
            flex: 1;
            position: relative;
        }

        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .job-header h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #ba55d3;
        }

        .date {
            background: linear-gradient(135deg, #8a2be2, #9932cc);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .job-title {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: 300;
        }

        .job-details {
            list-style: none;
            margin-bottom: 24px;
        }

        .job-details li {
            margin-bottom: 12px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            position: relative;
            padding-left: 20px;
            line-height: 1.5;
        }

        .job-details li::before {
            content: "→";
            position: absolute;
            left: 0;
            color: #ba55d3;
            font-weight: bold;
        }

        .btn-redirect {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #8a2be2, #9932cc);
            color: white;
            text-decoration: none;
            border-radius: 16px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-redirect::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-redirect:hover::before {
            left: 100%;
        }

        .btn-redirect:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(138, 43, 226, 0.5);
            background: linear-gradient(135deg, #9932cc, #ba55d3);
        }

        /* Tools Section mejorada */
        .tools-section {
            grid-area: tools;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .tools-card h3 {
            margin-bottom: 20px;
            font-size: 1.2rem;
            color: #ba55d3;
            font-weight: 600;
        }

        .tool-icons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .tool-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .tool-icon.ai {
            background: linear-gradient(135deg, #ff9a00, #ff6b00);
            color: #000;
        }

        .tool-icon.ps {
            background: linear-gradient(135deg, #31a8ff, #0078d4);
            color: #fff;
        }

        .tool-icon:hover {
            transform: translateY(-8px) rotate(5deg) scale(1.1);
            box-shadow: 0 15px 30px rgba(138, 43, 226, 0.4);
        }

        .language-flags {
            display: flex;
            gap: 16px;
        }

        .flag {
            padding: 12px 20px;
            background: linear-gradient(135deg, rgba(138, 43, 226, 0.2), rgba(186, 85, 211, 0.3));
            border: 1px solid rgba(138, 43, 226, 0.4);
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .flag:hover {
            transform: translateY(-3px);
            border-color: rgba(138, 43, 226, 0.8);
            box-shadow: 0 8px 25px rgba(138, 43, 226, 0.3);
        }

        /* Education Section mejorada */
        .education-section {
            grid-area: education;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .education-card {
            position: relative;
        }

        .education-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .education-header h3 {
            font-size: 1.2rem;
            color: #ba55d3;
            font-weight: 600;
        }

        .education-details {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .profile-section .glass-card {
            animation: float 6s ease-in-out infinite;
        }

        .experience-section .glass-card:nth-child(1) {
            animation: float 6s ease-in-out infinite 0.5s;
        }

        .experience-section .glass-card:nth-child(2) {
            animation: float 6s ease-in-out infinite 1s;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "profile"
                    "experience"
                    "tools"
                    "education";
                gap: 20px;
                padding: 20px 15px;
            }
            
            .profile-section {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .profile-info h1 {
                font-size: 2rem;
            }
            
            .experience-section {
                flex-direction: column;
            }
            
            .glass-card {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Profile Section -->
        <div class="profile-section glass-card">
            <div class="profile-image">
                <img src="descarga.jpeg" alt="username">
            </div>
            <div class="profile-info">
                <h1>Natalia Guevara</h1>
                <p class="welcome-text">¡Hola! Bienvenido de nuevo</p>
                <div class="interest-tags">
                    <span class="tag"><i class="fas fa-music"></i> Música</span>
                    <span class="tag"><i class="fas fa-drum"></i> Ritmo</span>
                    <span class="tag"><i class="fas fa-palette"></i> Estilo</span>
                </div>
            </div>
        </div>

        <!-- Experience Section -->
        <div class="experience-section">
            <div class="job-card glass-card">
                <div class="job-header">
                    <h3>Editar tu perfil</h3>
                    <span class="date">Ayuda</span>
                </div>
                <p class="job-title">Edita tu perfil a tu mejor manera!</p>
                <ul class="job-details">
                    <li>Edita tu información personal</li>
                    <li>Consigue ayuda sobre como mejorarlo a tu estilo</li>
                </ul>
                <a href="{{ url('/editar-perfil') }}" class="btn-redirect">
                    <i class="fas fa-user-edit"></i> Ir a Editar Perfil
                </a>
            </div>

            <div class="job-card glass-card">
                <div class="job-header">
                    <h3>Cambia de artista/usuario</h3>
                    <span class="date">Ayuda</span>
                </div>
                <p class="job-title">Actualiza tu información de usuario</p>
                <ul class="job-details">
                    <li>Cambia tu correo electrónico y nombre de usuario</li>
                    <li>Actualiza tu información de acceso</li>
                    <li>Mejora la seguridad de tu cuenta</li>
                </ul>
                <a href="{{ url('/cambiar-usuario') }}" class="btn-redirect">
                    <i class="fas fa-user-cog"></i> Cambiar Usuario
                </a>
            </div>
        </div>

        <!-- Tools Section -->
        <div class="tools-section">
            <div class="tools-card glass-card">
                <h3><i class="fas fa-star"></i> Verifica a tus artistas</h3>
                <div class="tool-icons">
                    <span class="tool-icon ai">Na</span>
                    <span class="tool-icon ps">Int</span>
                </div>
            </div>

            <div class="tools-card glass-card">
                <h3><i class="fas fa-globe"></i> Lenguaje</h3>
                <div class="language-flags">
                    <span class="flag">🇺🇸 ING</span>
                    <span class="flag">🇪🇸 ESP</span>
                </div>
            </div>
        </div>

        <!-- Education Section -->
        <div class="education-section">
            <div class="education-card glass-card">
                <div class="education-header">
                    <h3><i class="fas fa-shield-alt"></i> Seguridad</h3>
                    <span class="date">Ayuda</span>
                </div>
                <p class="education-details">Información sobre seguridad y privacidad</p>
                <a href="{{ url('/seguridad') }}" class="btn-redirect">
                    <i class="fas fa-shield-alt"></i> Ver Seguridad
                </a>
            </div>

            <div class="education-card glass-card">
                <div class="education-header">
                    <h3><i class="fas fa-headset"></i> Soporte</h3>
                    <span class="date">Ayuda</span>
                </div>
                <p class="education-details">Contactar con el soporte técnico<br><strong>aura@gmail.com</strong></p>
            </div>
        </div>
    </div>
        @include('components.footer')
</body>
</html>