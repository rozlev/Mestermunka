document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registration-form"); // Az űrlapot az ID alapján keressük
    const errorBox = document.getElementById("error-message");
    const successBox = document.getElementById("success-message");

    form.addEventListener("submit", async function (event) {
        event.preventDefault(); // Megakadályozza az alapértelmezett elküldést

        const formData = new FormData(form);

        try {
            const response = await fetch("regisztracio.php", {
                method: "POST",
                body: formData
            });

            const data = await response.json();

            if (data.error) {
                errorBox.textContent = data.error;
                errorBox.style.display = "block";
                successBox.style.display = "none";
            } else if (data.success) {
                successBox.textContent = data.success;
                successBox.style.display = "block";
                errorBox.style.display = "none";

                setTimeout(() => {
                    window.location.href = "../bejelentkezes/bejelentkezes.html"; // Átirányítás
                }, 2000);
            }
        } catch (error) {
            console.error("Hiba történt:", error);
            errorBox.textContent = "Szerverhiba történt. Próbáld újra később!";
            errorBox.style.display = "block";
        }
    });
});
