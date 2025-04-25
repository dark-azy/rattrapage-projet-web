// public/js/search.js
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchOverlay = document.getElementById('search-overlay');
    const closeOverlay = document.getElementById('close-overlay');
    const searchButton = document.getElementById('search-button');
    const quoiField = document.getElementById('quoi');
    const ouField = document.getElementById('ou');
    const optionButtons = document.querySelectorAll('.option-btn');

    // Ajout d'un bouton pour effacer le champ de recherche
    const searchInputWrapper = document.querySelector('.search-input-wrapper');
    const clearSearch = document.createElement('button');
    clearSearch.id = 'clear-search';
    clearSearch.innerHTML = '&times;';
    clearSearch.style.position = 'absolute';
    clearSearch.style.right = '30px';
    clearSearch.style.background = 'none';
    clearSearch.style.border = 'none';
    clearSearch.style.fontSize = '18px';
    clearSearch.style.cursor = 'pointer';
    clearSearch.style.display = 'none';
    searchInputWrapper.appendChild(clearSearch);

    applySorting("default");

    // Afficher le bouton clearSearch quand le champ contient du texte
    searchInput.addEventListener('input', function() {
        clearSearch.style.display = searchInput.value ? 'block' : 'none';
        
        // Mettre à jour également le champ QUOI dans l'overlay pour plus de cohérence
        quoiField.value = searchInput.value;
        
        // Appliquer la recherche en temps réel (si les marqueurs sont définis)
        if (typeof markers !== 'undefined') {
            applySearch();
        }
    });

    // Ouvrir l'overlay lors du click dans la barre de recherche
    searchInput.addEventListener('click', function() {
        searchOverlay.classList.remove('hidden');
        searchOverlay.style.display = 'flex';
    });

    // Fermer l'overlay
    closeOverlay.addEventListener('click', function() {
        searchOverlay.classList.add('hidden');
        searchOverlay.style.display = 'none';
    });

    document.addEventListener('click', function(e) {
        if (!searchOverlay.contains(e.target) && !searchInput.contains(e.target)) {
            searchOverlay.classList.add('hidden');
            searchOverlay.style.display = 'none';
        }
    });

    // Effacer le champ de recherche
    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        quoiField.value = '';
        clearSearch.style.display = 'none';
        
        // Réinitialiser les résultats (si les marqueurs sont définis)
        if (typeof markers !== 'undefined') {
            resetSearch();
        }
    });

    // Bouton de recherche avancée
    searchButton.addEventListener('click', function() {
        // Synchroniser l'input principal avec le champ QUOI
        searchInput.value = quoiField.value;
        clearSearch.style.display = searchInput.value ? 'block' : 'none';
        
        // Appliquer la recherche avancée
        if (typeof markers !== 'undefined') {
            applyAdvancedSearch();
        }
        
        // Fermer l'overlay
        searchOverlay.classList.add('hidden');
        searchOverlay.style.display = 'none';
    });

    // Gestion des boutons de filtre (Default, A-Z, List view)
    optionButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Enlever la classe active de tous les boutons
            optionButtons.forEach(btn => btn.classList.remove('active'));
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');

            // Appliquer le tri correspondant
            const sortType = this.dataset.sortType; // Utilisez data-attributes
            applySorting(sortType);
        });
    });

    // Fonction pour appliquer la recherche simple
    function applySearch() {
        const searchText = searchInput.value.toLowerCase();
        
        markers.forEach(function(item) {
            const title = item.card.querySelector('.offer-title').textContent.toLowerCase();
            const company = item.card.querySelector('.offer-company').textContent.toLowerCase();
            
            if (title.includes(searchText) || company.includes(searchText)) {
                item.card.style.display = '';
                item.marker.setOpacity(1);
            } else {
                item.card.style.display = 'none';
                item.marker.setOpacity(0.2);
            }
        });
    }

    // Fonction pour appliquer la recherche avancée
    function applyAdvancedSearch() {
        const quoi = quoiField.value.toLowerCase();
        const ou = ouField.value.toLowerCase();
        
        markers.forEach(function(item) {
            const title = item.card.querySelector('.offer-title').textContent.toLowerCase();
            const company = item.card.querySelector('.offer-company').textContent.toLowerCase();
            const address = item.card.dataset.address ? item.card.dataset.address.toLowerCase() : '';
            let matched = true;
            
            // Vérifier le critère QUOI
            if (quoi && !(title.includes(quoi) || company.includes(quoi))) {
                matched = false;
            }
            
            // Vérifier le critère OÙ
            if (matched && ou) {
                // Recherche dans l'adresse de l'entreprise
                if (!address.includes(ou)) {
                    matched = false;
                }
            }
            
            if (matched) {
                item.card.style.display = '';
                item.marker.setOpacity(1);
                // Centrer la carte sur le premier résultat trouvé
                if (ou && item.marker) {
                    map.setView(item.marker.getLatLng(), 13);
                }
            } else {
                item.card.style.display = 'none';
                item.marker.setOpacity(0.2);
            }
        });
    }

    // Fonction pour réinitialiser la recherche
    function resetSearch() {
        markers.forEach(function(item) {
            item.card.style.display = '';
            item.marker.setOpacity(1);
        });
    }

    // Fonction pour appliquer le tri
    function applySorting(sortType) {
        const cardsList = document.querySelector('.offers-list');
        const cards = Array.from(cardsList.querySelectorAll('.offer-card'));

        if (sortType === 'a-z') {
            // Trier par titre alphabétique
            cards.sort((a, b) => {
                const titleA = a.querySelector('.offer-title').textContent.trim();
                const titleB = b.querySelector('.offer-title').textContent.trim();
                return titleA.localeCompare(titleB);
            });
        } else if (sortType === 'z-a') {
                // Trier par titre alphabétique
                cards.sort((a, b) => {
                    const titleA = b.querySelector('.offer-title').textContent.trim();
                    const titleB = a.querySelector('.offer-title').textContent.trim();
                    return titleA.localeCompare(titleB);
                });
        } else if (sortType === 'date^') {
            // Trier par date (les plus récentes en premier)
            cards.sort((a, b) => {
                const dateA = b.dataset.startDate; // Récupération depuis les data-attributes
                const dateB = a.dataset.startDate;

                // Conversion en timestamp pour comparer les dates
                const toTimestamp = (dateString) => {
                    const [day, month, year] = dateString.split('/');
                    return new Date(year, month - 1, day).getTime();
                };

                return toTimestamp(dateB) - toTimestamp(dateA); // Tri décroissant
            });
        }
        else if (sortType === 'datev') {
            // Trier par date (les plus récentes en premier)
            cards.sort((a, b) => {
                const dateA = a.dataset.startDate; // Récupération depuis les data-attributes
                const dateB = b.dataset.startDate;

                // Conversion en timestamp pour comparer les dates
                const toTimestamp = (dateString) => {
                    const [day, month, year] = dateString.split('/');
                    return new Date(year, month - 1, day).getTime();
                };

                return toTimestamp(dateB) - toTimestamp(dateA); // Tri décroissant
            });
        }
        else {
            // Trier par date (les plus récentes en premier)
            cards.sort((a, b) => {
                const dateA = b.dataset.startDate; // Récupération depuis les data-attributes
                const dateB = a.dataset.startDate;

                // Conversion en timestamp pour comparer les dates
                const toTimestamp = (dateString) => {
                    const [day, month, year] = dateString.split('/');
                    return new Date(year, month - 1, day).getTime();
                };

                return toTimestamp(dateB) - toTimestamp(dateA); // Tri décroissant
            });
        }

        // Réorganiser les cartes dans le DOM
        cards.forEach(card => cardsList.appendChild(card));
    }
});
