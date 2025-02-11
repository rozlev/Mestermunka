document.addEventListener("DOMContentLoaded", function () {
  var userName = localStorage.getItem("felhasznaloNev");

  // Ha van felhasználó név, akkor változtassuk meg a bejelentkezés linket
  if (userName) {
    var userLink = document.getElementById("user-name");
    userLink.textContent = userName; // A link szövege a felhasználó neve
    userLink.href = "#"; // Ne vezessen sehova

    // Modális ablak megjelenítése a felhasználó nevére kattintáskor
    userLink.addEventListener("click", function () {
      var modal = document.getElementById("logoutModal");
      modal.style.display = "flex"; // Flex használata a modal középre helyezéséhez
    });

    // Kijelentkezés gomb
    var logoutConfirmBtn = document.getElementById("logoutConfirm");
    logoutConfirmBtn.addEventListener("click", function () {
      // Töröljük a felhasználói adatokat a localStorage-ból
      localStorage.removeItem("felhasznaloNev");

      // Frissítsük az oldalt, hogy visszatérjen a "Bejelentkezés" szöveg
      window.location.reload();
    });

    // Mégse gomb (bezárja a modális ablakot)
    var logoutCancelBtn = document.getElementById("logoutCancel");
    logoutCancelBtn.addEventListener("click", function () {
      var modal = document.getElementById("logoutModal");
      modal.style.display = "none"; // Modális ablak bezárása
    });
  } else {
    var loginLink = document.getElementById("user-name");
    loginLink.textContent = "Bejelentkezés";
    loginLink.href = "../bejelentkezes/bejelentkezes.html";
  }
});
document.addEventListener("DOMContentLoaded", function () {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  // Frissíti a kosár gombot a főoldalon
  function updateCartButton() {
      let cartLink = document.getElementById("cart-link");
      if (cartLink) {
          cartLink.textContent = `Kosár (${cart.length})`;
      }
  }

  // Termék hozzáadása a kosárhoz
  function addToCart(event) {
    let product = event.target.closest(".product");
    let name = product.querySelector("p").textContent.split(" - ")[0];
    let price = parseInt(product.querySelector("p").textContent.match(/\d+/)[0]);
    let image = product.querySelector("img").src;

    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    let existingProduct = cart.find(item => item.name === name);
    if (existingProduct) {
        existingProduct.quantity++;
    } else {
        cart.push({ name, price, image, quantity: 1 });
    }

    localStorage.setItem("cart", JSON.stringify(cart));

    console.log("Kosár tartalma:", cart); // Debug kiírás
    updateCartButton();
}


  // Kosár gombok eseményfigyelője
  document.querySelectorAll(".add-to-cart-btn").forEach(button => {
      button.addEventListener("click", addToCart);
  });

  updateCartButton();

  // Ha a kosár oldalon vagyunk, akkor betöltjük a kosarat
  if (document.getElementById("cart-items")) {
      loadCart();
  }
});

// Kosár betöltése a kosár oldalon
function loadCart() {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];
  let cartTable = document.getElementById("cart-items");
  let totalPrice = 0;

  cartTable.innerHTML = ""; // Kiürítjük a táblázatot, hogy ne legyen duplikáció

  if (cart.length === 0) {
      cartTable.innerHTML = `<tr><td colspan="6" class="text-center">A kosár üres.</td></tr>`;
  } else {
      cart.forEach((item, index) => {
          let row = document.createElement("tr");
          row.innerHTML = `
              <td><img src="${item.image}" width="50"></td>
              <td>${item.name}</td>
              <td>${item.price} HUF</td>
              <td><input type="number" min="1" value="${item.quantity}" data-index="${index}" class="cart-quantity"></td>
              <td>${item.price * item.quantity} HUF</td>
              <td><button class="btn btn-danger remove-item" data-index="${index}">Törlés</button></td>
          `;
          cartTable.appendChild(row);
          totalPrice += item.price * item.quantity;
      });
  }

  document.getElementById("total-price").textContent = totalPrice;

  // Mennyiség módosítása
  document.querySelectorAll(".cart-quantity").forEach(input => {
      input.addEventListener("change", updateQuantity);
  });

  // Termék törlése a kosárból
  document.querySelectorAll(".remove-item").forEach(button => {
      button.addEventListener("click", removeItem);
  });

  console.log("Kosár betöltve:", cart); // Debug kiírás
}


// Mennyiség módosítása
function updateQuantity(event) {
  let index = event.target.dataset.index;
  let cart = JSON.parse(localStorage.getItem("cart"));
  let newQuantity = parseInt(event.target.value);

  if (newQuantity <= 0) {
      cart.splice(index, 1); // Törli, ha 0
  } else {
      cart[index].quantity = newQuantity;
  }

  localStorage.setItem("cart", JSON.stringify(cart));
  loadCart();
}

// Termék eltávolítása a kosárból
function removeItem(event) {
  let index = event.target.dataset.index;
  let cart = JSON.parse(localStorage.getItem("cart"));

  cart.splice(index, 1);
  localStorage.setItem("cart", JSON.stringify(cart));
  loadCart();
}

// Kosár ürítése
function clearCart() {
  localStorage.removeItem("cart");
  loadCart();
}
document.addEventListener("DOMContentLoaded", function () {
  if (document.getElementById("cart-items")) {
      loadCart();
  }
});
