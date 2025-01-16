document.addEventListener("DOMContentLoaded", function() {
    // Az űrlap beküldése
    document.getElementById("loginForm").addEventListener("submit", function(event){
        event.preventDefault(); // Megakadályozzuk az alapértelmezett űrlap elküldést
        
        // űrlap adatainak lekérése
        var email = document.getElementById("email").value;
        var password = document.getElementById("password").value;
        
        // FormData létrehozása az adatok küldésére
        var formData = new FormData();
        formData.append("email", email);
        formData.append("password", password);
        
        // AJAX kérés
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "bejelentkezes.php", true);
        
        // A válasz kezelése
        xhr.onload = function() {
            var response = JSON.parse(xhr.responseText);
            if(response.status === "success") {
                // Ha sikeres a bejelentkezés, átirányítjuk a felhasználót
                window.location.href = "../index.html"; // Itt átirányítjuk a főoldalra vagy bárhová
            } else {
                // Ha hiba történt, megjelenítjük a hibaüzenetet
                var errorMessageDiv = document.getElementById("errorMessage");
                errorMessageDiv.innerText = response.message;
                errorMessageDiv.style.display = "block"; // Megjelenítjük a hibaüzenetet
            }
        };
        
        // Kérés küldése
        xhr.send(formData);
    });
});
