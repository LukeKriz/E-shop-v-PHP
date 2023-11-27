const odkaz = document.querySelector("#about_main");
const rozklik = document.querySelector(".nas");
const sipka = document.querySelector(".sipka");
const onich = document.querySelector(".onich");
const codelame = document.querySelector(".codelame");
const dotacniprogramy = document.querySelector(".dotacniprogramy");
const odkaz1 = document.querySelector("#servis_main");
const rozklik1 = document.querySelector(".ser");
const listag = document.querySelector(".lista");
const listag2 = document.querySelector(".lista2");


const listag3 = document.querySelector(".lista3");

const odkaz2 = document.querySelector("#programy_main");
const rozklik2 = document.querySelector(".prog");


odkaz.addEventListener("click", () => {
    rozklik.classList.toggle("active");
    odkaz.classList.toggle("active");
    listag.classList.toggle("active");
    onich.classList.toggle("active");

    rozklik2.classList.remove("active");
    odkaz2.classList.remove("active");
    listag3.classList.remove("active");
    dotacniprogramy.classList.remove("active");

    rozklik1.classList.remove("active");
    odkaz1.classList.remove("active");  
    listag2.classList.remove("active");
    codelame.classList.remove("active");
})

odkaz1.addEventListener("click", () => {
    rozklik1.classList.toggle("active");
    odkaz1.classList.toggle("active");  
    listag2.classList.toggle("active");
    codelame.classList.toggle("active");

    rozklik.classList.remove("active");
    odkaz.classList.remove("active");
    listag.classList.remove("active");
    onich.classList.remove("active");

    rozklik2.classList.remove("active");
    odkaz2.classList.remove("active");
    listag3.classList.remove("active");
    dotacniprogramy.classList.remove("active");

})


odkaz2.addEventListener("click", () => {
    rozklik2.classList.toggle("active");
    odkaz2.classList.toggle("active");
    listag3.classList.toggle("active");
    dotacniprogramy.classList.toggle("active");

    rozklik1.classList.remove("active");
    odkaz1.classList.remove("active");  
    listag2.classList.remove("active");
    codelame.classList.remove("active");

    rozklik.classList.remove("active");
    odkaz.classList.remove("active");
    listag.classList.remove("active");
    onich.classList.remove("active");    

})