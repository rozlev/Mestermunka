

document.addEventListener("DOMContentLoaded", function () {
    var userName = localStorage.getItem("felhasznaloNev");
    var isLoggedIn = !!userName; // Check if user is logged in

    // Ellenőrizzük, hogy van-e előzőleg mentett oldal URL-je
    var returnUrl = localStorage.getItem("returnUrl");
    if (isLoggedIn && returnUrl) {
        localStorage.removeItem("returnUrl"); // Töröljük az URL-t, hogy ne maradjon ott feleslegesen
        window.location.href = returnUrl; // Visszairányítjuk a felhasználót
    }

    // Frissítjük a felhasználónév megjelenítését a fejlécben
    var userNameElement = document.getElementById("user-name");
    if (userNameElement) {
        if (isLoggedIn) {
            userNameElement.textContent = userName;
            userNameElement.href = "#";
            userNameElement.addEventListener("click", function () {
                var logoutModal = document.getElementById("logoutModal");
                if (logoutModal) {
                    logoutModal.style.display = "flex";
                }
            });
        } else {
            userNameElement.textContent = "Bejelentkezés";
            userNameElement.href = "../bejelentkezes/bejelentkezes.html";
            localStorage.setItem("returnUrl", window.location.href); // Elmentjük az aktuális oldalt
        }

        
    }

    // Kijelentkezési modal események
    var logoutConfirmBtn = document.getElementById("logoutConfirm");
    var logoutCancelBtn = document.getElementById("logoutCancel");
    var logoutModal = document.getElementById("logoutModal");

    if (logoutConfirmBtn) {
        logoutConfirmBtn.addEventListener("click", function () {
            localStorage.removeItem("felhasznaloNev");
            localStorage.removeItem("cart"); // Kosár törlése kijelentkezéskor
            window.location.reload();
        });
    }

    if (logoutCancelBtn) {
        logoutCancelBtn.addEventListener("click", function () {
            if (logoutModal) {
                logoutModal.style.display = "none";
            }
        });
    }

    function loadProducts() {
        fetch("get_palinka.php")
            .then(response => response.json())
            .then(data => {
                const container = document.querySelector(".product-container");
                if (!container) {
                    console.error("Nincs .product-container az oldalon!");
                    return;
                }

                container.innerHTML = "";

                data.forEach(palinka => {
                    const productDiv = document.createElement("div");
                    productDiv.classList.add("product");

                    let buttonHTML = "";
                    if (palinka.DB_szam > 0) {
                        if (isLoggedIn) {
                            buttonHTML = `<button class="add-to-cart-btn" data-id="${palinka.Nev}" data-price="${palinka.Ar}" data-image="${palinka.KepURL}">Kosárba</button>`;
                        } else {
                            buttonHTML = `<button class="add-to-cart-btn" disabled style="background-color: grey; cursor: not-allowed;">Kosárba (Bejelentkezés szükséges)</button>`;
                        }
                    } else {
                        buttonHTML = `<p class="out-of-stock">❌ Nincs készleten</p>`;
                    }

                    productDiv.innerHTML = `
                        <img src="${palinka.KepURL}" alt="${palinka.Nev}">
                        <p>${palinka.Nev} <br>${palinka.AlkoholTartalom} Alk%  <br>${palinka.Ar} HUF</p>
                        <p><strong>Készlet: </strong> <span class="stock">${palinka.DB_szam} </span>  db</p>
                        ${buttonHTML}
                    `;
                    container.appendChild(productDiv);
                });

                if (isLoggedIn) {
                    document.querySelectorAll(".add-to-cart-btn").forEach(button => {
                        button.addEventListener("click", function () {
                            const name = this.getAttribute("data-id");
                            const price = parseInt(this.getAttribute("data-price"));
                            const image = this.getAttribute("data-image");
                            addToCart(name, price, image);
                        });
                    });
                }
            })
            .catch(error => console.error("Hiba történt:", error));
    }

    // Kosárba helyezés
    function addToCart(name, price, image) {
        if (!isLoggedIn) {
            alert("Bejelentkezés szükséges a vásárláshoz!");
            return;
        }
    
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
    
        // Ellenőrizzük, hogy már van-e a kosárban
        const existingItem = cart.find(item => item.name === name);
        if (existingItem) {
            existingItem.quantity += 1; // Növeljük a mennyiséget
        } else {
            cart.push({ name, price, image, quantity: 1 });
        }
    
        localStorage.setItem("cart", JSON.stringify(cart));
        updateCartCount(); // Azonnali frissítés
    
        // "storage" esemény manuális kiváltása más tabok frissítéséhez
        window.dispatchEvent(new Event("storage"));
    
        showCartNotification(`${name} hozzáadva a kosárhoz!`);
    }
    


    loadProducts();

    window.addEventListener("storage", function (event) {
        if (event.key === "orderCompleted") {
            loadProducts();
            localStorage.removeItem("orderCompleted");
        }
    });

    function showCartNotification(message) {
        const modal = document.getElementById("cartNotificationModal");
        const messageContainer = document.getElementById("cartNotificationMessage");
        const closeButton = document.getElementById("cartCloseModalBtn");

        messageContainer.textContent = message;
        modal.style.display = "flex";

        closeButton.onclick = function () {
            modal.style.display = "none";
        };

        modal.addEventListener("click", function (event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    }

    let yearElement = document.getElementById("year");
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }

});



// Kosár számának frissítése
function updateCartCount() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    let cartCountElement = document.getElementById("cart-count");

    if (cartCountElement) {
        cartCountElement.textContent = totalItems; // Beállítja az értéket
    } else {
        console.error("Nem található az #cart-count elem!");
    }
}
updateCartCount(); // Kosár számának frissítése az oldal betöltésekor
updateUserName();  // Felhasználónév frissítése

// Storage esemény figyelése más tabok esetére
window.addEventListener("storage", function (event) {
    if (event.key === "cart") {
        updateCartCount();
    }
});

