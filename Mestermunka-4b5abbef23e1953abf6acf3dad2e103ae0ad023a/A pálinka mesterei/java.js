document.addEventListener("DOMContentLoaded", function () {
    // Ellenőrizzük az URL paramétert és töröljük a localStorage-t
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("logout") === "true") {
        localStorage.removeItem("felhasznaloNev");
        localStorage.removeItem("cart");
        // Töröljük az URL paramétert
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // LOGIN KEZELÉS
    var nev = localStorage.getItem("felhasznaloNev");
    var loginLink = document.getElementById("loginLink");

    if (nev && loginLink) {
        loginLink.innerText = nev;
        loginLink.setAttribute("href", "#");
        loginLink.addEventListener("click", function (event) {
            event.preventDefault();
            document.getElementById("logoutModal").style.display = 'flex';
        });
    } else if (loginLink) {
        loginLink.innerText = "Bejelentkezés";
        loginLink.setAttribute("href", "bejelentkezes/bejelentkezes.html");
    }

    var logoutConfirmBtn = document.getElementById("logoutConfirm");
    if (logoutConfirmBtn) {
        logoutConfirmBtn.addEventListener("click", function () {
            // Átirányítunk a logout.php-ra, amely törli a munkamenetet
            window.location.href = "bejelentkezes/logout.php";
        });
    }

    let yearElement = document.getElementById("year");
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }

    var logoutCancelBtn = document.getElementById("logoutCancel");
    if (logoutCancelBtn) {
        logoutCancelBtn.addEventListener("click", function () {
            document.getElementById("logoutModal").style.display = 'none';
        });
    }

    updateCartCount();
    window.addEventListener("storage", function (event) {
        if (event.key === "cart") {
            updateCartCount();
        }
    });
});

// KOSÁR FRISSÍTÉS
function updateCartCount() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

    let cartCountElement = document.getElementById("cart-count");
    if (cartCountElement) {
        cartCountElement.textContent = totalItems;
    }
}