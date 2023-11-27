const cerpadlaRozklik = document.querySelector("#cerpadla-rozklik");
const text1= document.querySelector(".text1")
const krizek1 = document.querySelector("#krizek-cerpadla")

const RucniPumpyRozklik = document.querySelector("#rucnipumpy-rozklik");
const text2= document.querySelector(".text2")
const krizek2 = document.querySelector("#krizek-pumpy")

const nadrzeRozklik = document.querySelector("#retencninadrze-rozklik");
const text3= document.querySelector(".text3")
const krizek3 = document.querySelector("#krizek-nadrze")

const filtraceRozklik = document.querySelector("#filtrace-rozklik");
const text4= document.querySelector(".text4")
const krizek4 = document.querySelector("#krizek-filtrace")

const zahlaviRozklik = document.querySelector("#zahlavi-rozklik");
const text5= document.querySelector(".text5")
const krizek5 = document.querySelector("#krizek-zahlavi")

const vodarnyRozklik = document.querySelector("#vodarny-rozklik");
const text6= document.querySelector(".text6")
const krizek6 = document.querySelector("#krizek-vodarny")




cerpadlaRozklik.addEventListener("click", () => {
    cerpadlaRozklik.classList.toggle("active");
    text1.classList.toggle("active");
    krizek1.classList.toggle("active")


    nadrzeRozklik.classList.remove("active");
    text3.classList.remove("active");
    krizek3.classList.remove("active")

    RucniPumpyRozklik.classList.remove("active");
    text2.classList.remove("active");
    krizek2.classList.remove("active")


    filtraceRozklik.classList.remove("active");
    text4.classList.remove("active");
    krizek4.classList.remove("active")

    vodarnyRozklik.classList.remove("active");
    text6.classList.remove("active");
    krizek6.classList.remove("active")

    zahlaviRozklik.classList.remove("active");
    text5.classList.remove("active");
    krizek5.classList.remove("active")



})


RucniPumpyRozklik.addEventListener("click", () => {
    RucniPumpyRozklik.classList.toggle("active");
    text2.classList.toggle("active");
    krizek2.classList.toggle("active")


    cerpadlaRozklik.classList.remove("active");
    text1.classList.remove("active");
    krizek1.classList.remove("active")


    nadrzeRozklik.classList.remove("active");
    text3.classList.remove("active");
    krizek3.classList.remove("active")


    filtraceRozklik.classList.remove("active");
    text4.classList.remove("active");
    krizek4.classList.remove("active")

    vodarnyRozklik.classList.remove("active");
    text6.classList.remove("active");
    krizek6.classList.remove("active")

    zahlaviRozklik.classList.remove("active");
    text5.classList.remove("active");
    krizek5.classList.remove("active")



})





nadrzeRozklik.addEventListener("click", () => {
    nadrzeRozklik.classList.toggle("active");
    text3.classList.toggle("active");
    krizek3.classList.toggle("active")


    cerpadlaRozklik.classList.remove("active");
    text1.classList.remove("active");
    krizek1.classList.remove("active")


    RucniPumpyRozklik.classList.remove("active");
    text2.classList.remove("active");
    krizek2.classList.remove("active")

    filtraceRozklik.classList.remove("active");
    text4.classList.remove("active");
    krizek4.classList.remove("active")

    vodarnyRozklik.classList.remove("active");
    text6.classList.remove("active");
    krizek6.classList.remove("active")

    zahlaviRozklik.classList.remove("active");
    text5.classList.remove("active");
    krizek5.classList.remove("active")


})


filtraceRozklik.addEventListener("click", () => {

    filtraceRozklik.classList.toggle("active");
    text4.classList.toggle("active");
    krizek4.classList.toggle("active")






    cerpadlaRozklik.classList.remove("active");
    text1.classList.remove("active");
    krizek1.classList.remove("active")


    RucniPumpyRozklik.classList.remove("active");
    text2.classList.remove("active");
    krizek2.classList.remove("active")

    nadrzeRozklik.classList.remove("active");
    text3.classList.remove("active");
    krizek3.classList.remove("active")

    vodarnyRozklik.classList.remove("active");
    text6.classList.remove("active");
    krizek6.classList.remove("active")

    zahlaviRozklik.classList.remove("active");
    text5.classList.remove("active");
    krizek5.classList.remove("active")

})


vodarnyRozklik.addEventListener("click", () => {

    vodarnyRozklik.classList.toggle("active");
    text6.classList.toggle("active");
    krizek6.classList.toggle("active")




    filtraceRozklik.classList.remove("active");
    text4.classList.remove("active");
    krizek4.classList.remove("active")


    cerpadlaRozklik.classList.remove("active");
    text1.classList.remove("active");
    krizek1.classList.remove("active")


    RucniPumpyRozklik.classList.remove("active");
    text2.classList.remove("active");
    krizek2.classList.remove("active")

    nadrzeRozklik.classList.remove("active");
    text3.classList.remove("active");
    krizek3.classList.remove("active")

    zahlaviRozklik.classList.remove("active");
    text5.classList.remove("active");
    krizek5.classList.remove("active")


})

zahlaviRozklik.addEventListener("click", () => {

    zahlaviRozklik.classList.toggle("active");
    text5.classList.toggle("active");
    krizek5.classList.toggle("active")






    vodarnyRozklik.classList.remove("active");
    text6.classList.remove("active");
    krizek6.classList.remove("active")


    filtraceRozklik.classList.remove("active");
    text4.classList.remove("active");
    krizek4.classList.remove("active")


    cerpadlaRozklik.classList.remove("active");
    text1.classList.remove("active");
    krizek1.classList.remove("active")


    RucniPumpyRozklik.classList.remove("active");
    text2.classList.remove("active");
    krizek2.classList.remove("active")

    nadrzeRozklik.classList.remove("active");
    text3.classList.remove("active");
    krizek3.classList.remove("active")



})
