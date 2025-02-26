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
    const prevButton = document.getElementById("prev");
    const nextButton = document.getElementById("next");

    let currentIndex = 0;

    function updateCarousel() {
        // Minden elem alapból rejtve
        carouselItems.forEach(item => {
            item.classList.remove("active");
            item.style.display = "none"; // Eltüntetjük a nem aktív elemeket
        });

        // Az aktuális elemet láthatóvá tesszük
        carouselItems[currentIndex].classList.add("active");
        carouselItems[currentIndex].style.display = "flex"; // Csak az aktív elem legyen flex
    }

    prevButton.addEventListener("click", () => {
        currentIndex = (currentIndex > 0) ? currentIndex - 1 : carouselItems.length - 1;
        updateCarousel();
    });

    nextButton.addEventListener("click", () => {
        currentIndex = (currentIndex < carouselItems.length - 1) ? currentIndex + 1 : 0;
        updateCarousel();
    });

    // Automatikus váltás 10 másodpercenként
    setInterval(() => {
        currentIndex = (currentIndex < carouselItems.length - 1) ? currentIndex + 1 : 0;
        updateCarousel();
    }, 10000);

    // **Az első elem aktiválása oldalbetöltéskor**
    carouselItems[0].classList.add("active");
    carouselItems[0].style.display = "flex"; // Az első elem jelenjen meg
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
