document.addEventListener("DOMContentLoaded", () => {
    const editBtn = document.getElementById("editBtn");
    const cancelBtn = document.getElementById("cancelEdit");
    const modal = document.getElementById("editModal");

    if (!editBtn || !modal) return;

    // Abrir modal
    editBtn.addEventListener("click", () => {
        modal.setAttribute("aria-hidden", "false");
    });

    // Cerrar modal con botón cancelar
    if (cancelBtn) {
        cancelBtn.addEventListener("click", () => {
            modal.setAttribute("aria-hidden", "true");
        });
    }

    // Cerrar haciendo click fuera del contenido
    modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.setAttribute("aria-hidden", "true");
        }
    });
});
