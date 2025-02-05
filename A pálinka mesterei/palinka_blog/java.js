document.addEventListener("DOMContentLoaded", function () {
    // Ellenőrizzük, hogy van-e mentett felhasználói név
    var nev = localStorage.getItem("felhasznaloNev");
    
    if (nev) {
      // Ha van felhasználó, megjelenítjük a nevét
      var felhasznaloElem = document.getElementById("felhasznaloNev");
      var loginLink = document.getElementById("loginLink");
      loginLink.innerText = nev; // Felhasználó nevét megjelenítjük
      loginLink.setAttribute("href", "#"); // Az URL-t eltávolítjuk
      
      // Kijelentkezés kezelése
      loginLink.addEventListener("click", function(event) {
        event.preventDefault(); // Ne navigáljon el
        document.getElementById("logoutModal").style.display = 'block'; // Megjelenítjük a modális ablakot
      });
    }
    
    // Kijelentkezés megerősítése
    document.getElementById("logoutConfirm").addEventListener("click", function() {
      localStorage.removeItem("felhasznaloNev"); // Töröljük a felhasználó nevét
      document.getElementById("felhasznaloNev").innerHTML = '<a href="../bejelentkezes/bejelentkezes.html" id="loginLink">Bejelentkezés</a>';
      document.getElementById("logoutModal").style.display = 'none'; // Bezárjuk a modális ablakot
    });
  
    // Kijelentkezés törlésének elutasítása
    document.getElementById("logoutCancel").addEventListener("click", function() {
      document.getElementById("logoutModal").style.display = 'none'; // Bezárjuk a modális ablakot
    });
  });
  