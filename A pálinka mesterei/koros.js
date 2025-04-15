document.addEventListener("DOMContentLoaded", function () {
    let ageOverlay = document.getElementById("ageVerificationOverlay");
    let ageConfirm = document.getElementById("ageConfirm");
    let ageDeny = document.getElementById("ageDeny");
    let ageWarningText = document.getElementById("ageWarningText");

 
    if (!localStorage.getItem("ageVerified")) {
        ageOverlay.style.display = "flex"; 
    } else {
        ageOverlay.style.display = "none"; 
    }

    ageConfirm.addEventListener("click", function () {
        localStorage.setItem("ageVerified", "true");
        ageOverlay.style.display = "none"; 
    });

    ageDeny.addEventListener("click", function () {
        ageWarningText.innerText = "Sajnáljuk, de az oldal használatához 18 évesnek kell lenned! Átirányítás 3 másodperc múlva...";
        ageWarningText.style.display = "block"; 

        setTimeout(function () {
            setTimeout(function () {
                window.location.href = "https://www.google.com";
                
            }, 3000);
        }, 100);
    });
});
