document.addEventListener('DOMContentLoaded', function() {
    // Gestion du menu latéral
    const menuLinks = document.querySelectorAll('.sidebar-menu a');
    const sections = document.querySelectorAll('.settings-section');
    
    // Fonction pour afficher une section
    function showSection(sectionId) {
        // Cacher toutes les sections
        sections.forEach(section => {
            section.classList.remove('active');
        });
        
        // Afficher la section sélectionnée
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.add('active');
        }
        
        // Mettre à jour le lien actif
        menuLinks.forEach(link => {
            if (link.dataset.section === sectionId) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }
    
    // Gestionnaire d'événements pour les liens du menu
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.dataset.section;
            showSection(sectionId);
        });
    });

    // Gestion du formulaire
    const saveButton = document.querySelector('.btn-save');
    saveButton.addEventListener('click', function() {
        // Récupérer toutes les valeurs des champs
        const formData = {
            theme: document.getElementById('theme')?.value,
            notifications: document.getElementById('notifications')?.value,
            language: document.getElementById('language')?.value,
            emailFrequency: document.getElementById('email-frequency')?.value,
            cookiePreferences: document.getElementById('cookie-preferences')?.value,
            emailVisibility: document.getElementById('email-visibility')?.value
        };

        // Ajouter les champs spécifiques au rôle
        if (document.getElementById('dashboard')) {
            formData.dashboard = document.getElementById('dashboard').value;
        }
        if (document.getElementById('student-notifications')) {
            formData.studentNotifications = document.getElementById('student-notifications').value;
        }
        if (document.getElementById('job-alerts')) {
            formData.jobAlerts = document.getElementById('job-alerts').value;
            formData.cvVisibility = document.getElementById('cv-visibility').value;
        }

        // TODO: Envoyer les données au serveur
        console.log('Données à sauvegarder:', formData);
        alert('Les modifications ont été enregistrées');
    });
}); 