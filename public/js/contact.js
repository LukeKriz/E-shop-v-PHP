const socialbuttton = document.querySelector(".soc-but");
const social= document.querySelector(".social");
const mobile= document.querySelector("#mobile");
const xmark = document.querySelector("#close-mobile");
const fb = document.querySelector(".fb");
const phone = document.querySelector(".phone");
const mail = document.querySelector(".mail");

//const teleso = document.querySelector(".obsah");



socialbuttton.addEventListener("click", () => {
    socialbuttton.classList.toggle("active");
    social.classList.toggle("active");
    mobile.classList.toggle("active");
    xmark.classList.toggle("active");
    fb.classList.toggle("active");
    phone.classList.toggle("active");
    mail.classList.toggle("active");




});

/*
teleso.addEventListener("click", () => {
    socialbuttton.classList.remove("active");
    social.classList.remove("active");
    mobile.classList.remove("active");
    xmark.classList.remove("active");
    fb.classList.remove("active");
    phone.classList.remove("active");
    mail.classList.remove("active");




});*/
