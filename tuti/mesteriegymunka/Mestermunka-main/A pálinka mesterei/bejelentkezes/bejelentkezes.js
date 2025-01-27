document.addEventListener("DOMContentLoaded", function () {
    // Az űrlap beküldése
    document.getElementById("loginForm").addEventListener("submit", function (event) {
        event.preventDefault(); // Alapértelmezett működés megakadályozása

        // Adatok lekérése az űrlapból
        var email = document.getElementById("email").value;
        var password = document.getElementById("password").value;

        // FormData objektum
        var formData = new FormData();
        formData.append("email", email);
        formData.append("password", password);

        // AJAX kérés
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "bejelentkezes.php", true);
        xhr.onload = function () {
            var response = JSON.parse(xhr.responseText);
            if (response.status === "success") {
                // Felhasználó név elmentése a localStorage-be
                localStorage.setItem("felhasznaloNev", response.name);

                // Átirányítás a főoldalra
                window.location.href = "../index.html";
            } else {
                // Hiba megjelenítése
                var errorMessageDiv = document.getElementById("errorMessage");
                errorMessageDiv.innerText = response.message;
                errorMessageDiv.style.display = "block";
            }
        };
        xhr.send(formData);
    });
});
