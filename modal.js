function openModal(idReunion) {
    document.getElementById('modalReunionId').value = idReunion;
    const modal = document.getElementById('modalCompteRendu');
    modal.style.display = 'flex';
}

function closeModal() {
    const modal = document.getElementById('modalCompteRendu');
    modal.style.animation = "fadeOut 0.2s ease";
    setTimeout(() => {
        modal.style.display = 'none';
        modal.style.animation = "";
        document.getElementById('formCompteRendu').reset();
    }, 200);
}

window.onclick = function(event) {
    const modal = document.getElementById('modalCompteRendu');
    if (event.target === modal) {
        closeModal();
    }
};
