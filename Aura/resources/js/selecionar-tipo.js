
function mostrarRegistro(tipo) {
  const home = document.querySelector(".home");
  const registro = document.querySelector(".registro");
  const formArtista = document.getElementById("form-artista");
  const formUsuario = document.getElementById("form-usuario");

  home.classList.remove("active");

  setTimeout(() => {
    registro.classList.add("active");
    if (tipo === "Artista") {
      formArtista.classList.add("active");
      formUsuario.classList.remove("active");
    } else {
      formArtista.classList.remove("active");
      formUsuario.classList.add("active");
    }
  }, 400);
}

function volverInicio() {
  const home = document.querySelector(".home");
  const registro = document.querySelector(".registro");
  const formArtista = document.getElementById("form-artista");
  const formUsuario = document.getElementById("form-usuario");

  registro.classList.remove("active");
  formArtista.classList.remove("active");
  formUsuario.classList.remove("active");

  setTimeout(() => {
    home.classList.add("active");
  }, 400);
}



document.addEventListener("DOMContentLoaded", () => {
const planCards = document.querySelectorAll('.plan-card');

planCards.forEach(card => {
  card.addEventListener('click', () => {
    // Eliminar selección previa
    planCards.forEach(c => c.classList.remove('selected'));
    // Agregar selección al clicado
    card.classList.add('selected');
  });
});
});
