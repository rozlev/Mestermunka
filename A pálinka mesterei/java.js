document.addEventListener("DOMContentLoaded", function () {
  // Ellenőrizni, van-e mentett felhasználói név
  var nev = localStorage.getItem("felhasznaloNev");
  if (nev) {
    // Beállítjuk a név megjelenítését
    var felhasznaloElem = document.getElementById("felhasznaloNev");
    var loginLink = document.getElementById("loginLink");
    loginLink.innerText = nev; // Felhasználó nevét megjelenítjük
    loginLink.setAttribute("href", "#"); // Az URL-t eltávolítjuk, hogy ne navigáljon
  }
});
