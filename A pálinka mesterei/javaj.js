document.addEventListener("DOMContentLoaded", function () { 
    // === LOGIN KEZELÉS ===
    var nev = localStorage.getItem("felhasznaloNev");
    var loginLink = document.getElementById("loginLink");

    if (nev && loginLink) {
        loginLink.innerText = nev; 
        loginLink.setAttribute("href", "#");

        loginLink.addEventListener("click", function (event) {
            event.preventDefault();
            document.getElementById("logoutModal").style.display = 'flex';
        });
    }

    var logoutConfirmBtn = document.getElementById("logoutConfirm");
    if (logoutConfirmBtn) {
        logoutConfirmBtn.addEventListener("click", function () {
            localStorage.removeItem("felhasznaloNev");
            localStorage.removeItem("cart");
            document.getElementById("logoutModal").style.display = 'none';
            window.location.reload();
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

// === KOSÁR FRISSÍTÉS ===
function updateCartCount() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    let cartCountElement = document.getElementById("cart-count");
    if (cartCountElement) {
        cartCountElement.textContent = totalItems;
    }
}
