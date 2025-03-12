
$(document).ready(function () {
    $('.navbar-toggler').click(function () {
        $('#navbarSupportedContent').toggleClass('show');
    });
});

document.addEventListener("DOMContentLoaded", function () {


    let yearElement = document.getElementById("year");
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }

});

