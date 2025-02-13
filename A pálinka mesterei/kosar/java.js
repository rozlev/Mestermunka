document.addEventListener("DOMContentLoaded", function () {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const cartItemsContainer = document.getElementById("cart-items");
    const totalPriceContainer = document.getElementById("total-price");
    const orderButton = document.createElement("button");

    orderButton.textContent = "Rendelés leadás";
    orderButton.classList.add("btn", "btn-success", "mt-3");
    orderButton.id = "submit-order";
    document.querySelector(".container").appendChild(orderButton);

    function showNotification(title, message) {
        const modal = document.getElementById("notification-modal");
        const titleContainer = document.getElementById("notification-title");
        const messageContainer = document.getElementById("notification-message");
        const closeButton = document.getElementById("close-modal-btn");

        titleContainer.textContent = title;
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

    function renderCart() {
        cartItemsContainer.innerHTML = "";
        let totalPrice = 0;

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = "<tr><td colspan='6'>A kosár üres</td></tr>";
            totalPriceContainer.textContent = "0 HUF";
            orderButton.style.display = "none";
            return;
        } else {
            orderButton.style.display = "block";
        }

        cart.forEach((item, index) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td><img src="${item.image}" width="50"></td>
                <td>${item.name}</td>
                <td>${item.price} HUF</td>
                <td>
                    <button class="decrease-qty" data-index="${index}">-</button>
                    <input type="text" value="${item.quantity}" class="qty-input" data-index="${index}" readonly>
                    <button class="increase-qty" data-index="${index}">+</button>
                </td>
                <td class="total-item-price" data-index="${index}">${item.price * item.quantity} HUF</td>
                <td><button class="remove-item" data-index="${index}">❌</button></td>
            `;
            cartItemsContainer.appendChild(row);
            totalPrice += item.price * item.quantity;
        });

        totalPriceContainer.textContent = totalPrice + " HUF";
        attachEventListeners();
    }

    function attachEventListeners() {
        document.querySelectorAll(".increase-qty").forEach(button => {
            button.addEventListener("click", function () {
                const index = this.getAttribute("data-index");
                cart[index].quantity += 1;
                updateCart();
            });
        });

        document.querySelectorAll(".decrease-qty").forEach(button => {
            button.addEventListener("click", function () {
                const index = this.getAttribute("data-index");
                if (cart[index].quantity > 1) {
                    cart[index].quantity -= 1;
                    updateCart();
                }
            });
        });

        document.querySelectorAll(".remove-item").forEach(button => {
            button.addEventListener("click", function () {
                const index = this.getAttribute("data-index");
                cart.splice(index, 1);
                updateCart();
            });
        });
    }

    function updateCart() {
        localStorage.setItem("cart", JSON.stringify(cart));
        renderCart();
    }

    document.getElementById("clear-cart").addEventListener("click", function () {
        localStorage.removeItem("cart");
        cart = [];
        totalPriceContainer.textContent = "0 HUF";
        showNotification("Kosár törölve", "A kosár kiürítve!");
        renderCart();
    });

    document.getElementById("submit-order").addEventListener("click", function () {
        if (cart.length === 0) {
            showNotification("Hiba", "A kosár üres!");
            return;
        }

        fetch("process_order.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ cart })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showNotification("Hiba", data.error);
            } else {
                showNotification("Rendelés sikeres", "Köszönjük a vásárlást!");
                localStorage.removeItem("cart");
                cart = [];
                totalPriceContainer.textContent = "0 HUF";
                renderCart();
            }
        })
        .catch(error => {
            showNotification("Hiba", "Hiba történt a rendelés során!");
        });
    });

    renderCart();
});
