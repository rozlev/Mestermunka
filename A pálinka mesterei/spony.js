console.log("Kód betöltve!");

document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM betöltve!");

    const carouselItems = document.querySelectorAll(".carousel-item");
    const dots = document.querySelectorAll(".dot");

    console.log("Talált carousel-elemek:", carouselItems.length);
    console.log("Talált pontok:", dots.length);

    let currentIndex = 0;
    let autoSlide;

    function updateCarousel() {
        console.log("Frissítés! Új index:", currentIndex);
        carouselItems.forEach((item, index) => {
            item.style.display = "none"; 
            item.classList.remove("active");
        });

        dots.forEach((dot, index) => {
            dot.classList.remove("active");
        });

        if (carouselItems[currentIndex]) {
            carouselItems[currentIndex].style.display = "flex"; 
            carouselItems[currentIndex].classList.add("active");
        } else {
            console.error("HIBA: Rossz index!", currentIndex);
        }

        if (dots[currentIndex]) {
            dots[currentIndex].classList.add("active");
        }
    }

    function startAutoSlide() {
        console.log("Automata indítva...");
        stopAutoSlide();
        autoSlide = setInterval(() => {
            currentIndex = (currentIndex + 1) % carouselItems.length;
            console.log("Automatikusan váltás indexre:", currentIndex);
            updateCarousel();
        }, 10000);
    }

    function stopAutoSlide() {
        console.log("Automata megállítva.");
        clearInterval(autoSlide);
    }

    dots.forEach((dot, index) => {
        dot.addEventListener("click", () => {
            console.log("Klikkelt pont:", index);
            stopAutoSlide();
            currentIndex = index;
            updateCarousel();
            startAutoSlide();
        });
    });

    updateCarousel();
    startAutoSlide();
});
