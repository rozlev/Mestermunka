document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("loginForm");
    const errorMessageDiv = document.getElementById("errorMessage");

    form.addEventListener("submit", function (event) {
        event.preventDefault(); // Megakadályozza az oldal újratöltését

        const formData = new FormData(form);

        fetch("bejelentkezes.php", {
            method: "POST",
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("Hálózati válasz hibás!");
            }
            return response.json();
        })
        .then(data => {
            if (data.status === "success") {
                // Mentse a felhasználói nevet és irányítsa át a főoldalra
                localStorage.setItem("felhasznaloNev", data.name);
                window.location.href = "../index.html";
            } else {
                // Hibaüzenet megjelenítése az oldalon
                errorMessageDiv.textContent = data.message;
                errorMessageDiv.style.display = "block";
            }
        })
        .catch(error => {
            console.error("Hiba történt:", error);
            errorMessageDiv.textContent = "Szerverhiba történt. Próbáld újra később!";
            errorMessageDiv.style.display = "block";
        });
    });
});