document.addEventListener("DOMContentLoaded", function () {
    let ageOverlay = document.getElementById("ageVerificationOverlay");
    let ageConfirm = document.getElementById("ageConfirm");
    let ageDeny = document.getElementById("ageDeny");
    let ageWarningText = document.getElementById("ageWarningText");

  

    // Ha az "Igen" gombra kattint
    ageConfirm.addEventListener("click", function () {
        localStorage.setItem("ageVerified", "true");
        ageOverlay.style.display = "none"; // Eltünteti a modalt
    });

    // Ha a "Nem" gombra kattint
    ageDeny.addEventListener("click", function () {
        ageWarningText.innerText = "Sajnáljuk, de az oldal használatához 18 évesnek kell lenned! Átirányítás 3 másodperc múlva...";
        ageWarningText.style.display = "block"; // Megjeleníti a figyelmeztető szöveget

        // DOM frissítése előtt várunk egy kis időt (100ms), hogy biztosan megjelenjen a szöveg
        setTimeout(function () {
            setTimeout(function () {
                window.location.href = "https://www.google.com";
                
            }, 3000);
        }, 100);
    });
});
