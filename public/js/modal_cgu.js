document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('open-cgu-modal').onclick = function(e) {
        e.preventDefault();
        document.getElementById('cguModal').style.display = 'flex';
    };
    document.getElementById('closeCguModal').onclick = function() {
        document.getElementById('cguModal').style.display = 'none';
    };
    document.getElementById('closeCguModalBtn').onclick = function() {
        document.getElementById('cguModal').style.display = 'none';
    };
    // Fermer la modale si on clique en dehors du contenu
    document.getElementById('cguModal').onclick = function(e) {
        if (e.target === this) this.style.display = 'none';
    };
}); 