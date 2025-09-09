<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad y Privacidad - AURA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Arial', sans-serif;
            background: #000000;
            color: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Fondo animado con partículas flotantes */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: 
                radial-gradient(circle at 25% 25%, rgba(138, 43, 226, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(186, 85, 211, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(147, 51, 234, 0.08) 0%, transparent 50%);
            animation: backgroundPulse 15s ease-in-out infinite;
            pointer-events: none;
            z-index: -2;
        }

        @keyframes backgroundPulse {
            0%, 100% {
                transform: scale(1) rotate(0deg);
                filter: hue-rotate(0deg);
            }
            50% {
                transform: scale(1.05) rotate(180deg);
                filter: hue-rotate(30deg);
            }
        }

        /* Partículas flotantes */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: linear-gradient(45deg, #ba55d3, #9932cc);
            border-radius: 50%;
            opacity: 0.6;
            animation: floatParticle 20s linear infinite;
        }

        @keyframes floatParticle {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
            }
            90% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        .container {
            min-height: 100vh;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
        }

        /* Botón de regreso mejorado */
        .btn-back {
            background: linear-gradient(135deg, rgba(138, 43, 226, 0.2), rgba(186, 85, 211, 0.3));
            color: #ba55d3;
            padding: 16px 32px;
            border: 2px solid rgba(138, 43, 226, 0.4);
            border-radius: 20px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            position: fixed;
            top: 30px;
            left: 30px;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(138, 43, 226, 0.2);
            overflow: hidden;
        }

        .btn-back::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(138, 43, 226, 0.3), transparent);
            transition: left 0.6s ease;
        }

        .btn-back:hover::before {
            left: 100%;
        }

        .btn-back:hover {
            background: linear-gradient(135deg, rgba(138, 43, 226, 0.4), rgba(186, 85, 211, 0.5));
            transform: translateY(-4px) translateX(-4px);
            box-shadow: 0 15px 40px rgba(138, 43, 226, 0.4);
            border-color: rgba(138, 43, 226, 0.8);
        }

        .btn-back i {
            transition: transform 0.4s ease;
        }

        .btn-back:hover i {
            transform: translateX(-4px);
        }

        /* Header mejorado */
        .security-header {
            text-align: center;
            margin: 100px 0 60px;
            position: relative;
        }

        .security-header::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(138, 43, 226, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            animation: headerGlow 4s ease-in-out infinite alternate;
        }

        @keyframes headerGlow {
            0% {
                transform: translate(-50%, -50%) scale(0.8);
                opacity: 0.5;
            }
            100% {
                transform: translate(-50%, -50%) scale(1.2);
                opacity: 0.8;
            }
        }

        .security-header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ba55d3, #9932cc, #8a2be2);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 24px;
            position: relative;
            animation: titleGlow 3s ease-in-out infinite alternate;
        }

        @keyframes titleGlow {
            0% {
                filter: drop-shadow(0 0 20px rgba(138, 43, 226, 0.5));
                text-shadow: 0 0 40px rgba(138, 43, 226, 0.3);
            }
            100% {
                filter: drop-shadow(0 0 40px rgba(138, 43, 226, 0.9));
                text-shadow: 0 0 60px rgba(138, 43, 226, 0.6);
            }
        }

        .security-header p {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.8);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.7;
            font-weight: 300;
        }

        /* Tarjetas de seguridad mejoradas */
        .security-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 40px;
            margin-bottom: 60px;
        }

        .security-card {
            background: linear-gradient(145deg, 
                rgba(26, 26, 26, 0.9),
                rgba(42, 26, 42, 0.7)
            );
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 24px;
            border: 1px solid rgba(138, 43, 226, 0.3);
            position: relative;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            cursor: pointer;
        }

        .security-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                #8a2be2, #9932cc, #ba55d3, #8a2be2
            );
            border-radius: 26px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.5s ease;
            animation: borderRotate 4s linear infinite;
        }

        @keyframes borderRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .security-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent,
                rgba(138, 43, 226, 0.1),
                transparent
            );
            transition: left 0.7s ease;
        }

        .security-card:hover::before {
            opacity: 1;
        }

        .security-card:hover::after {
            left: 100%;
        }

        .security-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px rgba(138, 43, 226, 0.4);
            border-color: rgba(138, 43, 226, 0.6);
        }

        .card-icon {
            font-size: 60px;
            background: linear-gradient(135deg, #ba55d3, #9932cc);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 24px;
            transition: transform 0.4s ease;
        }

        .security-card:hover .card-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .card-title {
            color: #ba55d3;
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
            transition: color 0.3s ease;
        }

        .security-card:hover .card-title {
            color: #ffffff;
            text-shadow: 0 0 20px rgba(138, 43, 226, 0.6);
        }

        .card-content {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.7;
            font-size: 15px;
            font-weight: 400;
        }

        .card-content ul {
            padding-left: 24px;
            margin: 20px 0;
        }

        .card-content li {
            margin-bottom: 12px;
            position: relative;
        }

        .card-content li::before {
            content: "→";
            position: absolute;
            left: -20px;
            color: #ba55d3;
            font-weight: bold;
            transition: transform 0.3s ease;
        }

        .security-card:hover .card-content li::before {
            transform: translateX(4px);
            color: #ffffff;
        }

        /* Política de privacidad mejorada */
        .privacy-policy {
            background: linear-gradient(145deg, 
                rgba(26, 26, 26, 0.95),
                rgba(42, 26, 42, 0.8)
            );
            backdrop-filter: blur(25px);
            padding: 50px;
            border-radius: 28px;
            border: 1px solid rgba(138, 43, 226, 0.4);
            margin-bottom: 50px;
            position: relative;
            overflow: hidden;
        }

        .privacy-policy::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, 
                transparent 30%,
                rgba(138, 43, 226, 0.05) 50%,
                transparent 70%
            );
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .privacy-policy h2 {
            color: #ba55d3;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 40px;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .privacy-section {
            margin-bottom: 35px;
            position: relative;
            z-index: 1;
        }

        .privacy-section h3 {
            color: #9932cc;
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 16px;
            position: relative;
        }

        .privacy-section h3::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, #ba55d3, transparent);
        }

        .privacy-section p {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.7;
            font-size: 15px;
            margin-bottom: 15px;
            font-weight: 400;
        }

        /* Contacto de seguridad mejorado */
        .contact-security {
            text-align: center;
            padding: 40px;
            background: linear-gradient(145deg, 
                rgba(42, 26, 42, 0.8), 
                rgba(26, 26, 26, 0.9)
            );
            backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(138, 43, 226, 0.4);
            position: relative;
            overflow: hidden;
        }

        .contact-security::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(138, 43, 226, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: contactGlow 6s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes contactGlow {
            0%, 100% {
                transform: translate(-50%, -50%) scale(0.5);
                opacity: 0.3;
            }
            50% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0.6;
            }
        }

        .contact-security h3 {
            color: #ba55d3;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .contact-security p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 15px;
            font-size: 16px;
            line-height: 1.6;
        }

        .security-email {
            color: #9932cc;
            font-weight: 700;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .security-email::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #ba55d3, #9932cc);
            transition: width 0.3s ease;
        }

        .security-email:hover {
            color: #ba55d3;
            text-shadow: 0 0 10px rgba(138, 43, 226, 0.5);
        }

        .security-email:hover::after {
            width: 100%;
        }

        /* Animaciones de entrada */
        .fade-in-up {
            opacity: 0;
            transform: translateY(50px);
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .fade-in-left {
            opacity: 0;
            transform: translateX(-50px);
            animation: fadeInLeft 0.8s ease-out forwards;
        }

        .fade-in-right {
            opacity: 0;
            transform: translateX(50px);
            animation: fadeInRight 0.8s ease-out forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .security-header {
                margin: 80px 0 40px;
            }

            .security-header h1 {
                font-size: 2.5rem;
            }

            .security-header p {
                font-size: 1.1rem;
            }

            .security-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }

            .security-card {
                padding: 30px;
            }

            .privacy-policy {
                padding: 30px;
            }

            .contact-security {
                padding: 30px;
            }

            .btn-back {
                top: 20px;
                left: 20px;
                padding: 12px 24px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Partículas flotantes -->
    <div class="particles"></div>

    <a href="cuenta.php" class="btn-back fade-in-left">
        <i class="fas fa-arrow-left"></i> Regresar
    </a>
    
    <div class="container">
        <div class="security-header fade-in-up">
            <h1><i class="fas fa-shield-alt"></i> Seguridad y Privacidad</h1>
            <p>Tu seguridad y privacidad son nuestra prioridad. Conoce cómo protegemos tu información y qué medidas puedes tomar para mantener tu cuenta segura.</p>
        </div>

        <div class="privacy-policy fade-in-up" style="animation-delay: 1.4s">
            <h2><i class="fas fa-file-contract"></i> Política de Privacidad</h2>
            
            <div class="privacy-section">
                <h3>Recopilación de Información</h3>
                <p>Recopilamos únicamente la información necesaria para brindarte nuestros servicios. Esto incluye información de perfil, preferencias de usuario y datos de uso de la plataforma de manera transparente y con tu consentimiento.</p>
            </div>
            
            <div class="privacy-section">
                <h3>Uso de la Información</h3>
                <p>Tu información se utiliza exclusivamente para mejorar tu experiencia en AURA, personalizar contenido y mantener la seguridad de la plataforma. Nunca utilizamos tus datos para fines comerciales sin tu autorización.</p>
            </div>
            
            <div class="privacy-section">
                <h3>Compartir Información</h3>
                <p>No vendemos, alquilamos ni compartimos tu información personal con terceros sin tu consentimiento explícito, excepto cuando sea requerido por ley o para proteger la seguridad de nuestra comunidad.</p>
            </div>
            
            <div class="privacy-section">
                <h3>Retención de Datos</h3>
                <p>Conservamos tu información solo durante el tiempo necesario para cumplir con los propósitos descritos en esta política o según lo requiera la ley. Tienes el derecho de solicitar la eliminación de tus datos en cualquier momento.</p>
            </div>
            
            <div class="privacy-section">
                <h3>Tus Derechos</h3>
                <p>Tienes derecho a acceder, corregir, eliminar o transferir tu información personal. También puedes oponerte al procesamiento de tus datos en cualquier momento. Facilitamos herramientas intuitivas para ejercer estos derechos.</p>
            </div>
        </div>
        
        <div class="contact-security fade-in-up" style="animation-delay: 1.6s">
            <h3><i class="fas fa-headset"></i> Contacto de Seguridad</h3>
            <p>Si tienes preguntas sobre seguridad o privacidad, o necesitas reportar un incidente de seguridad:</p>
            <p>Email: <a href="mailto:security@aura.com" class="security-email">aura@gmail.com</a></p>
            <p><strong>Respuesta garantizada en 24 horas</strong></p>
            <p>Nuestro equipo de seguridad está disponible las 24 horas del día, los 7 días de la semana.</p>
        </div>
    </div>

    <script>
        // Crear partículas flotantes
        function createParticles() {
            const particlesContainer = document.querySelector('.particles');
            const particleCount = 15;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 20 + 's';
                particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Animaciones al hacer scroll
        function handleScrollAnimations() {
            const elements = document.querySelectorAll('.fade-in-up, .fade-in-left, .fade-in-right');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                    }
                });
            }, { threshold: 0.1 });

            elements.forEach(el => {
                el.style.animationPlayState = 'paused';
                observer.observe(el);
            });
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', () => {
            createParticles();
            handleScrollAnimations();
        });

        // Efecto de hover mejorado para las tarjetas
        document.querySelectorAll('.security-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-12px) scale(1.02) rotateX(5deg)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1) rotateX(0deg)';
            });
        });
    </script>
</body>
</html>