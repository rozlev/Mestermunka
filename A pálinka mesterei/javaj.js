document.addEventListener("DOMContentLoaded", function () {
  // Bejelentkezett felhasználó kezelése
  var nev = localStorage.getItem("felhasznaloNev");
  var loginLink = document.getElementById("loginLink");

  if (nev && loginLink) {
      loginLink.innerText = nev; // Megjelenítjük a nevet
      loginLink.setAttribute("href", "#"); // Megakadályozzuk az átirányítást

      // Kijelentkezési modal megnyitása
      loginLink.addEventListener("click", function (event) {
          event.preventDefault();
          document.getElementById("logoutModal").style.display = 'flex';
      });
  }

  // Kijelentkezési eseménykezelés
  var logoutConfirmBtn = document.getElementById("logoutConfirm");
  if (logoutConfirmBtn) {
      logoutConfirmBtn.addEventListener("click", function () {
          localStorage.removeItem("felhasznaloNev");
          localStorage.removeItem("cart"); // Kosár törlése kijelentkezéskor
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

  // Kosár frissítése az oldal betöltésekor
  updateCartCount();

  // Storage esemény figyelése, hogy a kosár frissüljön másik fülön is
  window.addEventListener("storage", function (event) {
      if (event.key === "cart") {
          updateCartCount();
      }
  });

  const carouselItems = document.querySelectorAll(".carousel-item");
    const dots = document.querySelectorAll(".dot");

    let currentIndex = 0;

    function updateCarousel() {
        carouselItems.forEach((item, index) => {
            item.classList.remove("active");
            dots[index].classList.remove("active");
        });

        carouselItems[currentIndex].classList.add("active");
        dots[currentIndex].classList.add("active");
    }

    // Automatikus váltás 10 másodpercenként
    setInterval(() => {
        currentIndex = (currentIndex < carouselItems.length - 1) ? currentIndex + 1 : 0;
        updateCarousel();
    }, 10000);

    // PONTOKRA KATTINTVA VÁLTÁS
    dots.forEach(dot => {
        dot.addEventListener("click", () => {
            currentIndex = parseInt(dot.getAttribute("data-index"));
            updateCarousel();
        });
    });

    // Alapból az első elem aktív
    updateCarousel();
});
  

// Kosár számának frissítése
function updateCartCount() {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];
  let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  
  let cartCountElement = document.getElementById("cart-count");
  if (cartCountElement) {
      cartCountElement.textContent = totalItems;
  }
}
