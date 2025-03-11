document.addEventListener("DOMContentLoaded", function () {
    // === LOGIN KEZELÉS ===
    var loginLink = document.getElementById("loginLink");

    // Ha a loginLink létezik és nem "Bejelentkezés" a tartalma, akkor kijelentkezés modális ablakot mutatunk
    if (loginLink && loginLink.innerText !== "Bejelentkezés") {
        loginLink.addEventListener("click", function (event) {
            event.preventDefault();
            document.getElementById("logoutModal").style.display = 'flex';
        });
    }

    var logoutConfirmBtn = document.getElementById("logoutConfirm");
    if (logoutConfirmBtn) {
        logoutConfirmBtn.addEventListener("click", function () {
            fetch('logout.php', {
                method: 'GET'
            })
            .then(response => {
                if (response.ok) {
                    // Töröljük a localStorage-ban tárolt kosár adatokat
                    localStorage.removeItem("cart");
                    document.getElementById("logoutModal").style.display = 'none';
                    // Átirányítunk a mama.php-ra
                    window.location.href = 'kijel/mama.php';
                }
            })
            .catch(error => console.error('Hiba a kijelentkezéskor:', error));
        });
    }

    var logoutCancelBtn = document.getElementById("logoutCancel");
    if (logoutCancelBtn) {
        logoutCancelBtn.addEventListener("click", function () {
            document.getElementById("logoutModal").style.display = 'none';
        });
    }

    let yearElement = document.getElementById("year");
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }

    updateCartCount();
    window.addEventListener("storage", function (event) {
        if (event.key === "cart") {
            updateCartCount();
        }
    });
});

// === KOSÁR FRISSÍTÉS ===
function updateCartCount() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    let cartCountElement = document.getElementById("cart-count");
    if (cartCountElement) {
        cartCountElement.textContent = totalItems;
    }
}