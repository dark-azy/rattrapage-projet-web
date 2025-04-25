document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de la carte
    var map = L.map('map').setView([48.8566, 2.3522], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Récupération des éléments
    const filterButtons = document.querySelectorAll('.filter-btn');
    const applicationCards = document.querySelectorAll('.application-card');
    const searchInput = document.querySelector('.search-input');
    
    // Création des marqueurs pour chaque candidature
    const markers = [];
    applicationCards.forEach(card => {
        const lat = parseFloat(card.dataset.lat);
        const lng = parseFloat(card.dataset.lng);
        if (lat && lng) {
            const marker = L.marker([lat, lng])
                .bindPopup(card.querySelector('.application-title').textContent)
                .addTo(map);
            markers.push({ marker, card });

            // Lien entre le marqueur et la carte
            marker.on('click', () => {
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                highlightCard(card);
            });

            // Clic sur la carte pour centrer sur le marqueur
            card.addEventListener('click', () => {
                map.setView([lat, lng], 13);
                marker.openPopup();
                highlightCard(card);
            });
        }
    });

    // Fonction pour mettre en surbrillance une carte
    function highlightCard(card) {
        // Retirer la surbrillance des autres cartes
        applicationCards.forEach(c => c.classList.remove('highlighted'));
        // Ajouter la surbrillance à la carte cliquée
        card.classList.add('highlighted');
        setTimeout(() => card.classList.remove('highlighted'), 2000);
    }

    // Fonction pour filtrer les candidatures
    function filterApplications(status) {
        applicationCards.forEach(card => {
            const cardStatus = card.querySelector('.status-badge').textContent.trim().toLowerCase();
            const shouldShow = status === 'all' || cardStatus === status.toLowerCase();
            card.style.display = shouldShow ? '' : 'none';

            // Mettre à jour la visibilité des marqueurs
            const marker = markers.find(m => m.card === card)?.marker;
            if (marker) {
                marker.setOpacity(shouldShow ? 1 : 0.2);
            }
        });
    }

    // Gestionnaire de clic pour les boutons de filtre
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Retirer la classe active de tous les boutons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Ajouter la classe active au bouton cliqué
            button.classList.add('active');

            // Filtrer selon le statut
            const status = button.classList.contains('accepted') ? 'Acceptée' :
                          button.classList.contains('pending') ? 'En attente' :
                          button.classList.contains('refused') ? 'Refusée' : 'all';
            filterApplications(status);
        });
    });

    // Recherche en temps réel
    searchInput.addEventListener('input', (e) => {
        const searchText = e.target.value.toLowerCase();
        applicationCards.forEach(card => {
            const title = card.querySelector('.application-title').textContent.toLowerCase();
            const description = card.querySelector('.application-description').textContent.toLowerCase();
            const shouldShow = title.includes(searchText) || description.includes(searchText);
            
            card.style.display = shouldShow ? '' : 'none';
            
            // Mettre à jour la visibilité des marqueurs
            const marker = markers.find(m => m.card === card)?.marker;
            if (marker) {
                marker.setOpacity(shouldShow ? 1 : 0.2);
            }
        });
    });

    // Activer le filtre "En attente" par défaut
    const pendingButton = document.querySelector('.filter-btn.pending');
    if (pendingButton) {
        pendingButton.classList.add('active');
        filterApplications('En attente');
    }

    // Gestion de la suppression des candidatures
    const removeButtons = document.querySelectorAll('.btn-remove');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.candidature-card');
            const candidatureId = card.dataset.candidatureId;
            
            // Ajouter la classe pour l'animation
            card.classList.add('removing');
            
            // Attendre la fin de l'animation avant de supprimer
            setTimeout(() => {
                // Envoyer la requête de suppression
                fetch(`/candidature/remove/${candidatureId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Supprimer la carte du DOM
                        card.remove();
                        
                        // Vérifier s'il reste des candidatures
                        const remainingCards = document.querySelectorAll('.candidature-card');
                        if (remainingCards.length === 0) {
                            // Afficher le message "aucune candidature"
                            const candidatureGrid = document.querySelector('.candidature-grid');
                            candidatureGrid.innerHTML = `
                                <div class="empty-candidature">
                                    <h2>Aucune candidature</h2>
                                    <p>Vous n'avez pas encore postulé à des offres de stage.</p>
                                    <a href="{{ path('app_annonces') }}" class="browse-offers-btn">Parcourir les offres</a>
                                </div>
                            `;
                        }
                    } else {
                        alert('Une erreur est survenue lors de la suppression de la candidature.');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la suppression de la candidature.');
                });
            }, 300); // Attendre la fin de l'animation (300ms)
        });
    });
}); 
 