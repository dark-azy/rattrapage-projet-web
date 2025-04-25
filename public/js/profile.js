document.addEventListener('DOMContentLoaded', function() {
    const profileIcon = document.getElementById('profileIcon');
    const burgerIcon = document.getElementById('burgerIcon');
    const accountOverlay = document.getElementById('accountOverlay');

    if (profileIcon && accountOverlay) {
        // Afficher/cacher le menu au clic sur l'icône
        profileIcon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            accountOverlay.classList.toggle('active');
        });

        // Fermer le menu si on clique ailleurs sur la page
        document.addEventListener('click', function(e) {
            if (!profileIcon.contains(e.target) && !accountOverlay.contains(e.target)) {
                accountOverlay.classList.remove('active');
            }
        });
    }
    if (burgerIcon && accountOverlay) {
        // Afficher/cacher le menu au clic sur l'icône
        burgerIcon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            accountOverlay.classList.toggle('active');
        });

        // Fermer le menu si on clique ailleurs sur la page
        document.addEventListener('click', function(e) {
            if (!burgerIcon.contains(e.target) && !accountOverlay.contains(e.target)) {
                accountOverlay.classList.remove('active');
            }
        });
    }
});