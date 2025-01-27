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
