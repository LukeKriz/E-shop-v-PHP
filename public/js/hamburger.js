const hamburger = document.querySelector(".toggle-button");
const navMenu = document.querySelector(".navigation");
const menu = document.querySelector(".menu");
const lista = document.querySelector(".lista");
const lista2 = document.querySelector(".lista2");
const lista3 = document.querySelector(".lista3");
const nas = document.querySelector(".nas");
const ser = document.querySelector(".ser");
const prog = document.querySelector(".prog");
const aboutmain = document.querySelector("#about_main")
const servismain = document.querySelector("#servis_main")
const programmain = document.querySelector("#programy_main")

const body = document.querySelector(".obsah")

const aboutThem = document.querySelector(".onich");
const whatWeDo = document.querySelector(".codelame");
const donation = document.querySelector(".dotacniprogramy");






hamburger.addEventListener("click", () => {

    hamburger.classList.toggle("active");
    navMenu.classList.toggle("active");
    menu.classList.toggle("active");
    
    
    lista.classList.remove("active");
    lista2.classList.remove("active");
    lista3.classList.remove("active");
    nas.classList.remove("active");
    ser.classList.remove("active");
    prog.classList.remove("active");
    aboutmain.classList.remove("active");
    servismain.classList.remove("active");
    programmain.classList.remove("active");
    aboutThem.classList.remove("active");
    whatWeDo.classList.remove("active");
    donation.classList.remove("active");

})


body.addEventListener("click", () => {

    aboutThem.classList.remove("active");
    whatWeDo.classList.remove("active");
    donation.classList.remove("active");

    hamburger.classList.remove("active");
    navMenu.classList.remove("active");
    menu.classList.remove("active");
    
    
    lista.classList.remove("active");
    lista2.classList.remove("active");
    lista3.classList.remove("active");
    nas.classList.remove("active");
    ser.classList.remove("active");
    prog.classList.remove("active");
    aboutmain.classList.remove("active");
    servismain.classList.remove("active");
    programmain.classList.remove("active");
    
})


