
class WishlistManager {
    constructor() {
        this.initializeWishlistButtons();
    }

    initializeWishlistButtons() {
        // Initialiser les boutons de suppression dans la wishlist
        document.querySelectorAll('.remove-from-wishlist').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const card = button.closest('.wishlist-card');
                const offerId = button.dataset.offerId;
                this.removeFromWishlist(offerId, card);
            });
        });

        // Initialiser les boutons d'ajout sur la page des annonces
        document.querySelectorAll('.add-to-wishlist').forEach(button => {
            const offerId = button.dataset.offerId;
            this.checkWishlistStatus(offerId, button);

            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleWishlist(offerId, button);
            });
        });
    }

    async checkWishlistStatus(offerId, button) {
        try {
            const response = await fetch(`/wishlist/check/${offerId}`);
            const data = await response.json();
            this.updateButtonState(button, data.inWishlist);
        } catch (error) {
            console.error('Erreur lors de la vérification du statut:', error);
        }
    }

    async toggleWishlist(offerId, button) {
        const isInWishlist = button.classList.contains('in-wishlist');
        const url = isInWishlist ? `/wishlist/remove/${offerId}` : `/wishlist/add/${offerId}`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();

            if (data.success) {
                this.updateButtonState(button, !isInWishlist);
            } else {
                console.error('Erreur:', data.error);
            }
        } catch (error) {
            console.error('Erreur lors de la modification de la wishlist:', error);
        }
    }

    updateButtonState(button, inWishlist) {
        if (inWishlist) {
            button.classList.add('in-wishlist');
            button.innerHTML = '<i class="fas fa-heart"></i>';
        } else {
            button.classList.remove('in-wishlist');
            button.innerHTML = '<i class="far fa-heart"></i>';
        }
    }

    async removeFromWishlist(offerId, card) {
        try {
            const response = await fetch(`/wishlist/remove/${offerId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();

            if (data.success) {
                this.animateAndRemoveCard(card);
            }
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
        }
    }

    animateAndRemoveCard(card) {
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'translateX(100px)';

        setTimeout(() => {
            card.remove();
            this.checkEmptyWishlist();
        }, 300);
    }

    checkEmptyWishlist() {
        const remainingCards = document.querySelectorAll('.wishlist-card');
        if (remainingCards.length === 0) {
            const wishlistItems = document.querySelector('.wishlist-items');
            if (wishlistItems) {
                const emptyWishlist = document.createElement('div');
                emptyWishlist.className = 'empty-wishlist';
                emptyWishlist.innerHTML = `
                    <p>Votre wishlist est vide</p>
                    <a href="/annonces" class="browse-offers-btn">Parcourir les offres</a>
                `;
                wishlistItems.appendChild(emptyWishlist);
            }
        }
    }
}

// Initialiser le gestionnaire de wishlist quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    new WishlistManager();
});