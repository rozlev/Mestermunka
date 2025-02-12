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
  fetch("get_palinka.php")
      .then(response => {
          if (!response.ok) {
              throw new Error(`Hálózati hiba: ${response.status}`);
          }
          return response.json();
      })
      .then(data => {
          console.log("Lekért adatok:", data);
          if (data.error) {
              console.error("Hiba:", data.error);
              return;
          }

          const container = document.querySelector(".product-container");
          if (!container) {
              console.error("Nincs .product-container az oldalon!");
              return;
          }

          container.innerHTML = "";

          data.forEach(palinka => {
              const productDiv = document.createElement("div");
              productDiv.classList.add("product");

              productDiv.innerHTML = `
                  <img src="${palinka.KepURL}" alt="${palinka.Nev}">
                  <p>${palinka.Nev} - ${palinka.AlkoholTartalom}% - ${palinka.Ar} HUF</p>
                  <p><strong>Készlet:</strong> <span class="stock">${palinka.DB_szam}</span> db</p>
                  <button class="add-to-cart-btn">Kosárba</button>
              `;
              container.appendChild(productDiv);
          });
      })
      .catch(error => console.error("Hiba történt:", error));
});

document.addEventListener("DOMContentLoaded", function () {
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

                  productDiv.innerHTML = `
                      <img src="${palinka.KepURL}" alt="${palinka.Nev}">
                      <p>${palinka.Nev} - ${palinka.AlkoholTartalom}% - ${palinka.Ar} HUF</p>
                      <p><strong>Készlet:</strong> <span class="stock">${palinka.DB_szam}</span> db</p>
                      <button class="add-to-cart-btn" data-id="${palinka.Nev}" data-price="${palinka.Ar}" data-image="${palinka.KepURL}">Kosárba</button>
                  `;
                  container.appendChild(productDiv);
              });

              // Kosárba gombok működése
              document.querySelectorAll(".add-to-cart-btn").forEach(button => {
                  button.addEventListener("click", function () {
                      const name = this.getAttribute("data-id");
                      const price = parseInt(this.getAttribute("data-price"));
                      const image = this.getAttribute("data-image");

                      addToCart(name, price, image);
                  });
              });
          })
          .catch(error => console.error("Hiba történt:", error));
  }

  function addToCart(name, price, image) {
      let cart = JSON.parse(localStorage.getItem("cart")) || [];

      // Ellenőrzés, hogy már benne van-e a kosárban
      const existingItem = cart.find(item => item.name === name);
      if (existingItem) {
          existingItem.quantity += 1; // Növeljük a darabszámot
      } else {
          cart.push({ name, price, image, quantity: 1 });
      }

      localStorage.setItem("cart", JSON.stringify(cart));

      // Visszajelzés a felhasználónak
      alert(`"${name}" hozzáadva a kosárhoz!`);
  }

  // Termékek betöltése
  loadProducts();

  // Rendelés után frissítsük a készletet
  window.addEventListener("storage", function (event) {
      if (event.key === "orderCompleted") {
          loadProducts();
          localStorage.removeItem("orderCompleted");
      }
  });
});
