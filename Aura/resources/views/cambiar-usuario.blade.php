<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Usuario - AURA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: #000000;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #ffffff;
            overflow-x: hidden;
            position: relative;
        }

        /* Fondo animado con partículas */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(138, 43, 226, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(186, 85, 211, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(147, 0, 211, 0.05) 0%, transparent 50%);
            z-index: -2;
        }

        /* Partículas de fondo */
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(2px 2px at 100px 50px, rgba(138, 43, 226, 0.3), transparent),
                radial-gradient(2px 2px at 400px 150px, rgba(186, 85, 211, 0.2), transparent),
                radial-gradient(1px 1px at 200px 300px, rgba(147, 0, 211, 0.4), transparent),
                radial-gradient(1px 1px at 600px 200px, rgba(138, 43, 226, 0.2), transparent);
            background-size: 800px 400px;
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }
        
        .container {
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        
        .change-user-form {
            background: linear-gradient(145deg, rgba(10, 10, 10, 0.95), rgba(20, 20, 20, 0.95));
            backdrop-filter: blur(20px);
            padding: 50px;
            border-radius: 24px;
            border: 1px solid rgba(138, 43, 226, 0.2);
            position: relative;
            width: 100%;
            max-width: 550px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.5),
                0 0 80px rgba(138, 43, 226, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .change-user-form:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 30px 80px rgba(0, 0, 0, 0.6),
                0 0 100px rgba(138, 43, 226, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        
        .change-user-form::before {
            content: '';
            position: absolute;
            top: -1px;
            left: -1px;
            right: -1px;
            bottom: -1px;
            background: linear-gradient(135deg, 
                rgba(138, 43, 226, 0.3) 0%, 
                rgba(186, 85, 211, 0.2) 25%,
                transparent 50%,
                rgba(147, 0, 211, 0.2) 75%,
                rgba(138, 43, 226, 0.3) 100%);
            border-radius: 25px;
            z-index: -1;
            opacity: 0;
            animation: borderGlow 4s ease-in-out infinite;
        }

        @keyframes borderGlow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.8; }
        }
        
        h2 {
            color: #ffffff;
            margin-bottom: 20px;
            text-align: center;
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff, #ba55d3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
        }

        h2 i {
            margin-right: 12px;
            color: #ba55d3;
            filter: drop-shadow(0 0 10px rgba(138, 43, 226, 0.5));
        }

        .info-text {
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
            margin-bottom: 40px;
            font-size: 15px;
            line-height: 1.6;
            font-weight: 400;
            padding: 20px;
            background: rgba(138, 43, 226, 0.05);
            border-radius: 16px;
            border: 1px solid rgba(138, 43, 226, 0.1);
        }
        
        .form-group {
            margin-bottom: 28px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #ba55d3;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 12px;
        }
        
        .form-group input {
            width: 100%;
            padding: 18px 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid rgba(138, 43, 226, 0.2);
            border-radius: 16px;
            color: #ffffff;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
            position: relative;
        }

        .form-group input:hover {
            border-color: rgba(138, 43, 226, 0.4);
            background: rgba(255, 255, 255, 0.05);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: rgba(138, 43, 226, 0.8);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 
                0 0 0 4px rgba(138, 43, 226, 0.1),
                0 8px 32px rgba(138, 43, 226, 0.2);
            transform: translateY(-2px);
        }
        
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.4);
            font-weight: 400;
        }

        /* Iconos en inputs */
        .form-group.with-icon {
            position: relative;
        }

        .form-group.with-icon input {
            padding-left: 50px;
        }

        .form-group .input-icon {
            position: absolute;
            left: 18px;
            top: 38px;
            color: rgba(138, 43, 226, 0.6);
            font-size: 16px;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .form-group input:focus + .input-icon {
            color: rgba(138, 43, 226, 1);
            transform: scale(1.1);
        }
        
        .btn-save {
            background: linear-gradient(135deg, #8a2be2 0%, #9932cc 50%, #ba55d3 100%);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            margin-top: 30px;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-save::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s;
        }

        .btn-save:hover::before {
            left: 100%;
        }
        
        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 15px 40px rgba(138, 43, 226, 0.4),
                0 5px 20px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, #9932cc 0%, #ba55d3 50%, #da70d6 100%);
        }

        .btn-save:active {
            transform: translateY(-1px);
        }
        
        .btn-back {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(20px);
            color: #ba55d3;
            padding: 14px 28px;
            border: 1px solid rgba(138, 43, 226, 0.3);
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            position: absolute;
            top: 30px;
            left: 30px;
            font-size: 14px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10;
        }
        
        .btn-back::before {
            content: '';
            position: absolute;
            inset: 0;
            padding: 1px;
            background: linear-gradient(135deg, rgba(138, 43, 226, 0.5), transparent, rgba(186, 85, 211, 0.5));
            border-radius: 12px;
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask-composite: xor;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .btn-back:hover::before {
            opacity: 1;
        }
        
        .btn-back i {
            font-size: 14px;
            transition: transform 0.3s ease;
        }
        
        .btn-back:hover {
            background: rgba(138, 43, 226, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(138, 43, 226, 0.2);
            color: #ffffff;
        }
        
        .btn-back:hover i {
            transform: translateX(-3px);
        }

        /* Indicador de campo obligatorio */
        .required::after {
            content: " *";
            color: #ff6b6b;
            font-weight: bold;
        }

        /* Animaciones de carga */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            animation: fadeInUp 0.6s ease forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }

        .info-text {
            animation: fadeInUp 0.6s ease forwards;
            animation-delay: 0.05s;
        }

        /* Efecto de security indicator */
        .security-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }

        .security-indicator i {
            color: #ba55d3;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .change-user-form {
                padding: 30px 25px;
                border-radius: 20px;
            }
            
            .btn-back {
                top: 20px;
                left: 20px;
                padding: 12px 20px;
                font-size: 13px;
            }

            h2 {
                font-size: 26px;
                margin-bottom: 15px;
            }

            .info-text {
                font-size: 14px;
                margin-bottom: 30px;
                padding: 15px;
            }

            .form-group {
                margin-bottom: 24px;
            }

            .form-group input {
                padding: 16px 18px;
                font-size: 15px;
            }

            .btn-save {
                padding: 16px 32px;
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            .change-user-form {
                padding: 25px 20px;
            }

            .btn-back {
                position: relative;
                top: 0;
                left: 0;
                margin-bottom: 20px;
                align-self: flex-start;
            }

            .container {
                justify-content: flex-start;
                padding-top: 40px;
            }
        }

        /* Scroll personalizado */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #8a2be2, #ba55d3);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #9932cc, #da70d6);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="cuenta.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Regresar
        </a>
        
        <div class="change-user-form">
            <h2>
                <i class="fas fa-user-cog"></i> Cambiar Usuario
            </h2>
            
            <p class="info-text">
                <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
                Actualiza tu información de usuario. Puedes cambiar tu correo electrónico y nombre de usuario aquí.
            </p>
            
            <form>
                <div class="form-group with-icon">
                    <label for="nuevo-email" class="required">Nuevo Correo Electrónico</label>
                    <input type="email" id="nuevo-email" name="nuevo-email" placeholder="nuevo@email.com">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
                
                <div class="form-group with-icon">
                    <label for="nuevo-usuario" class="required">Nuevo Nombre de Usuario</label>
                    <input type="text" id="nuevo-usuario" name="nuevo-usuario" placeholder="nuevo_usuario">
                    <i class="fas fa-user input-icon"></i>
                </div>
                
                <div class="form-group with-icon">
                    <label for="confirmar-password" class="required">Confirmar Contraseña Actual</label>
                    <input type="password" id="confirmar-password" name="confirmar-password" placeholder="Ingresa tu contraseña actual">
                    <i class="fas fa-lock input-icon"></i>
                    <div class="security-indicator">
                        <i class="fas fa-shield-alt"></i>
                        <span>Tu contraseña actual es necesaria para confirmar los cambios</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Actualizar Usuario
                </button>
            </form>
        </div>
    </div>
</body>
</html>
    <!-- Agregar antes del cierre de </body> -->
    <script src="validaciones.js"></script>
</body>
</html>