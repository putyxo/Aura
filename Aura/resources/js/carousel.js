document.addEventListener('DOMContentLoaded', function() {
  const carouselTrack = document.querySelector('.carousel-track');
  if (!carouselTrack) return;

  const items = Array.from(carouselTrack.children);
  const itemWidth = 144; // 120px width + 24px gap
  const totalItems = items.length;
  const originalWidth = totalItems * itemWidth;

  // Clone items to create seamless loop
  items.forEach(item => {
    const clone = item.cloneNode(true);
    carouselTrack.appendChild(clone);
  });

  let position = 0;
  const speed = 1; // pixels per frame

  function animate() {
    position -= speed;
    if (position <= -originalWidth) {
      position = 0;
    }
    carouselTrack.style.transform = `translateX(${position}px)`;
    requestAnimationFrame(animate);
  }

  animate();
});
