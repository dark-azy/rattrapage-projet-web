// public/js/overlay.js
document.addEventListener("DOMContentLoaded", function () {
    const accountButton = document.querySelector("#account-button");
    const accountOverlay = document.querySelector("#account-overlay");

    accountButton.addEventListener("click", function () {
        accountOverlay.classList.toggle("hidden");
    });
});
