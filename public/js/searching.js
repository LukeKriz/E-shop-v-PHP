const searchButton = document.querySelector(".icon-search");
const searchMain = document.querySelector(".search-main")
const searching = document.querySelector(".searching")
const lupa = document.querySelector("#lupa")
const lupaClose = document.querySelector("#lupa-close")


searchButton.addEventListener("click", () => {
    searchButton.classList.toggle("active");
    searchMain.classList.toggle("active");
    searching.classList.toggle("active");
    lupa.classList.toggle("active");
    lupaClose.classList.toggle("active");




})

