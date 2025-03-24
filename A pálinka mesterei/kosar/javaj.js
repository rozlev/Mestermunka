document.addEventListener("DOMContentLoaded", function () {
    let cart = [];
    let discountApplied = false;
    let discountPercentage = 0;
    let couponCode = "";

    const cartItemsContainer = document.getElementById("cart-items");
    const totalPriceContainer = document.getElementById("total-price");
    const orderButton = document.createElement("button");
    const couponSection = document.getElementById("coupon-section");
    const couponCodeInput = document.getElementById("coupon-code");
    const couponMessage = document.getElementById("coupon-message");
    const applyCouponButton = document.getElementById("apply-coupon");

    orderButton.textContent = "Rendelés leadás";
    orderButton.classList.add("btn", "btn-success", "mt-3");
    orderButton.id = "submit-order";
    document.querySelector(".container").appendChild(orderButton);

    const confirmModal = document.getElementById("confirm-modal");
    const confirmOrderBtn = document.getElementById("confirm-order-btn");
    const cancelOrderBtn = document.getElementById("cancel-order-btn");

    function showNotification(title, message) {
        const modal = document.getElementById("notification-modal");
        document.getElementById("notification-title").textContent = title;
        document.getElementById("notification-message").textContent = message;
        modal.style.display = "flex";

        document.getElementById("close-modal-btn").onclick = function () {
            modal.style.display = "none";
        };

        modal.addEventListener("click", function (event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    }

    orderButton.addEventListener("click", function () {
        if (cart.length === 0) {
            showNotification("Hiba", "A kosár üres!");
            return;
        }
        confirmModal.style.display = "flex";
    });

    cancelOrderBtn.addEventListener("click", function () {
        confirmModal.style.display = "none";
    });

    confirmOrderBtn.addEventListener("click", function () {
        confirmModal.style.display = "none";

        fetch("process_order.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ 
                cart: cart,
                discountApplied: discountApplied,
                discountPercentage: discountPercentage,
                couponCode: couponCode
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showNotification("Hiba", data.error);
            } else {
                showNotification("Rendelés sikeres", "Köszönjük a vásárlást!");
                cart = [];
                localStorage.setItem("cart", JSON.stringify(cart));
                totalPriceContainer.textContent = "0 HUF";
                discountApplied = false;
                discountPercentage = 0;
                couponCode = "";
                couponMessage.textContent = "";
                couponCodeInput.value = "";
                renderCart();
            }
        })
        .catch(error => {
            showNotification("Hiba", "Hiba történt a rendelés során!");
        });
    });

    // Kupon beváltás kezelése
    applyCouponButton.addEventListener("click", function () {
        couponCode = couponCodeInput.value.trim();
        if (!couponCode) {
            couponMessage.textContent = "Kérlek, add meg a kupon kódot!";
            return;
        }

        if (discountApplied) {
            couponMessage.textContent = "Már beváltottál egy kupont!";
            return;
        }

        // Kupon ellenőrzése a szerveren keresztül
        fetch("validate_coupon.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ couponCode: couponCode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                couponMessage.textContent = data.error;
            } else if (data.success) {
                discountApplied = true;
                discountPercentage = data.discountPercentage;
                couponMessage.textContent = `Kupon beváltva! ${discountPercentage}% kedvezmény alkalmazva.`;
                renderCart();
            }
        })
        .catch(error => {
            couponMessage.textContent = "Hiba történt a kupon ellenőrzése során!";
            console.error("Error:", error);
        });
    });

    function renderCart() {
        cartItemsContainer.innerHTML = "";
        let totalPrice = 0;

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = "<tr><td colspan='6'>A kosár üres</td></tr>";
            totalPriceContainer.textContent = "0 HUF";
            orderButton.style.display = "none";
            couponSection.style.display = "none";
            return;
        } else {
            orderButton.style.display = "block";
            couponSection.style.display = "block";
        }

        cart.forEach((item, index) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td><img src="${item.image}" width="50"></td>
                <td>${item.name}</td>
                <td>${item.price} HUF</td>
                <td>
                    <button class="decrease-qty" data-index="${index}">-</button>
                    <input type="number" value="${item.quantity}" class="qty-input" data-index="${index}">
                    <button class="increase-qty" data-index="${index}">+</button>
                </td>
                <td class="total-item-price" data-index="${index}">${item.price * item.quantity} HUF</td>
                <td><button class="remove-item" data-index="${index}">❌</button></td>
            `;
            cartItemsContainer.appendChild(row);
            totalPrice += item.price * item.quantity;
        });

        if (discountApplied) {
            const discountAmount = totalPrice * (discountPercentage / 100);
            totalPrice -= discountAmount;
        }

        totalPriceContainer.textContent = totalPrice + " HUF";
        attachEventListeners();
    }

    function attachEventListeners() {
        document.querySelectorAll(".qty-input").forEach(input => {
            input.addEventListener("input", function () {
                const index = this.getAttribute("data-index");
                let newQuantity = parseInt(this.value);
                if (isNaN(newQuantity) || newQuantity < 1) {
                    newQuantity = 1;
                }
                cart[index].quantity = newQuantity;
                localStorage.setItem("cart", JSON.stringify(cart));
                renderCart();
            });
        });

        document.querySelectorAll(".increase-qty").forEach(button => {
            button.addEventListener("click", function () {
                const index = this.getAttribute("data-index");
                cart[index].quantity += 1;
                localStorage.setItem("cart", JSON.stringify(cart));
                renderCart();
            });
        });

        document.querySelectorAll(".decrease-qty").forEach(button => {
            button.addEventListener("click", function () {
                const index = this.getAttribute("data-index");
                if (cart[index].quantity > 1) {
                    cart[index].quantity -= 1;
                    localStorage.setItem("cart", JSON.stringify(cart));
                    renderCart();
                }
            });
        });

        document.querySelectorAll(".remove-item").forEach(button => {
            button.addEventListener("click", function () {
                const index = this.getAttribute("data-index");
                cart.splice(index, 1);
                localStorage.setItem("cart", JSON.stringify(cart));
                renderCart();
            });
        });
    }

    function loadCartFromLocalStorage() {
        const savedCart = localStorage.getItem("cart");
        if (savedCart) {
            cart = JSON.parse(savedCart);
        }
        renderCart();
    }

    document.getElementById("clear-cart").addEventListener("click", function () {
        cart = [];
        localStorage.setItem("cart", JSON.stringify(cart));
        totalPriceContainer.textContent = "0 HUF";
        discountApplied = false;
        discountPercentage = 0;
        couponCode = "";
        couponMessage.textContent = "";
        couponCodeInput.value = "";
        showNotification("Kosár törölve", "A kosár kiürítve!");
        renderCart();
    });

    loadCartFromLocalStorage();
});