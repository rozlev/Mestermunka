document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registration-form"); 
    const errorBox = document.getElementById("error-message");
    const successBox = document.getElementById("success-message");

    form.addEventListener("submit", async function (event) {
        event.preventDefault(); 

        const birthdateInput = document.getElementById("birthdate").value;
        const birthdate = new Date(birthdateInput);
        const today = new Date();
        const age = today.getFullYear() - birthdate.getFullYear();
        const monthDiff = today.getMonth() - birthdate.getMonth();
        const dayDiff = today.getDate() - birthdate.getDate();

        if (age < 18 || (age === 18 && (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)))) {
            errorBox.textContent = "Csak 18 éven felüliek regisztrálhatnak!";
            errorBox.style.display = "block";
            return;
        }

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
                    window.location.href = "../bejelentkezes/bejelentkezes.html"; 
                }, 2000);
            }
        } catch (error) {
            console.error("Hiba történt:", error);
            errorBox.textContent = "Szerverhiba történt. Próbáld újra később!";
            errorBox.style.display = "block";
        }
    });
});
