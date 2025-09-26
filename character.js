const colorpicker = document.getElementById("colorpicker");
const charbody = document.getElementById("char");
var bodypart = "head";

colorpicker.addEventListener("click", function(event) {
    const spanel = event.target;

    if (spanel.hasAttribute("colorbrick")) {
        const bcolor = spanel.getAttribute("colorbrick");
        const color = spanel.getAttribute("style").slice(17, -1);

        document.getElementById(bodypart).style.backgroundColor = color;
        console.log("colorbrick:", bcolor, "\nhex:", color);
    }
});

charbody.addEventListener("click", function(event) {
    const spanel = event.target;
    console.log(spanel);
    if (spanel.hasAttribute("style")) {
        bodypart = spanel.getAttribute("id");
        console.log(spanel.getAttribute("id"));
    }
});