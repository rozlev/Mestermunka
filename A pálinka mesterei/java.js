document.addEventListener("DOMContentLoaded", function () {
  // Ellenőrizzük, hogy van-e mentett felhasználói név
  var nev = localStorage.getItem("felhasznaloNev");

  if (nev) {
    // Ha van felhasználó, megjelenítjük a nevét
    var felhasznaloElem = document.getElementById("felhasznaloNev");
    var loginLink = document.getElementById("loginLink");
    loginLink.innerText = nev; // Felhasználó nevét megjelenítjük
    loginLink.setAttribute("href", "#"); // Az URL-t eltávolítjuk
    
    // Kijelentkezés kezelése - modális ablak megnyitása
    loginLink.addEventListener("click", function(event) {
      event.preventDefault(); // Ne navigáljon el
      document.getElementById("logoutModal").style.display = 'flex'; // Megjelenítjük a modális ablakot
    });
  }

  // Kijelentkezés megerősítése
  document.getElementById("logoutConfirm").addEventListener("click", function() {
    localStorage.removeItem("felhasznaloNev"); // Töröljük a felhasználói adatokat
    localStorage.removeItem("cart"); // Töröljük a kosár tartalmát is!

    // Visszaállítjuk a bejelentkezés gombot
    var felhasznaloElem = document.getElementById("felhasznaloNev");
    felhasznaloElem.innerHTML = '<a href="bejelentkezes/bejelentkezes.html" id="loginLink">Bejelentkezés</a>';

    // Bezárjuk a modális ablakot és frissítjük az oldalt
    document.getElementById("logoutModal").style.display = 'none';
    window.location.reload();
  });

  // Kijelentkezés megszakítása (modal bezárása)
  document.getElementById("logoutCancel").addEventListener("click", function() {
    document.getElementById("logoutModal").style.display = 'none'; // Bezárjuk a modális ablakot
  });
});
