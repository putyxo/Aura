// hero-bubbles.js

// Esperamos que el documento esté completamente cargado antes de ejecutar el código
document.addEventListener('DOMContentLoaded', function () {

    // Crear un contenedor para las burbujas
    const container = document.createElement('div');
    container.style.position = 'absolute';
    container.style.top = '0';
    container.style.left = '0';
    container.style.width = '100vw';
    container.style.height = '100vh';
    container.style.pointerEvents = 'none'; // No bloqueará otros elementos al hacer clic
    document.body.appendChild(container);

    // Función para generar una burbuja
    function createBubble() {
        const bubble = document.createElement('div');
        bubble.style.position = 'absolute';
        bubble.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
        bubble.style.borderRadius = '50%';
        bubble.style.opacity = '0';
        bubble.style.animation = 'floatBubbles 5s ease-in-out infinite';
        bubble.style.animationDelay = `${Math.random() * 5}s`;

        // Tamaño aleatorio de las burbujas
        const size = Math.random() * 60 + 20;
        bubble.style.width = `${size}px`;
        bubble.style.height = `${size}px`;

        // Posición inicial aleatoria
        const xPos = Math.random() * window.innerWidth;
        const yPos = window.innerHeight;

        bubble.style.left = `${xPos}px`;
        bubble.style.top = `${yPos}px`;

        container.appendChild(bubble);

        // Animación de flotación
        setTimeout(() => {
            bubble.style.opacity = '1'; // Hacemos visible la burbuja
            bubble.style.transform = `translateY(-${window.innerHeight + 100}px)`; // Flota hacia arriba
        }, 0);

        // Eliminar la burbuja cuando termine la animación
        setTimeout(() => {
            bubble.remove();
        }, 5000);
    }

    // Crear burbujas continuamente
    setInterval(createBubble, 300); // Crear una nueva burbuja cada 300ms

});

// CSS para la animación de las burbujas
const style = document.createElement('style');
style.innerHTML = `
@keyframes floatBubbles {
    0% {
        transform: translateY(0);
        opacity: 0.6;
    }
    100% {
        transform: translateY(-100vh);
        opacity: 0;
    }
}
`;
document.head.appendChild(style);
