const colorpicker = document.getElementById("colorpicker");
const charbody = document.getElementById("char");
const mousestt = document.getElementById("mousestate");
const bdpartstt = document.getElementById("whatdiduselect");
const clrstt = document.getElementById("colorstuff");
var bodypart = "";
let pickingcolor = false;

function updcolor(event) {
    const spanel = event.target;
    if (spanel.hasAttribute("colorbrick")) {
        const color = spanel.getAttribute("style").slice(17, -1);
        const bcolor = spanel.getAttribute("colorbrick");
        document.getElementById(bodypart).setAttribute("color", bcolor);
        document.getElementById(bodypart).style.backgroundColor = color;
    }
}

colorpicker.addEventListener("mousedown", function(event) {
    pickingcolor = true;
    updcolor(event);
});

document.addEventListener("mousemove", function(event) {
    if (pickingcolor) {
        updcolor(event);
    }
});

document.addEventListener("mouseup", function() {
    pickingcolor = false;
});

charbody.addEventListener("click", function(event) {
    const spanel = event.target;
    if (spanel.nodeName == "SPAN") {
        bodypart = spanel.getAttribute("id");
        console.log(bodypart)
        switch(bodypart) {
            case "head":
                bdpartstt.innerHTML = "head";
                break;
            case "lleg":
                bdpartstt.innerHTML = "left<br>leg";
                break;
            case "rleg":
                bdpartstt.innerHTML = "right<br>leg";
                break;
            case "larm":
                bdpartstt.innerHTML = "left<br>arm";
                break;
            case "rarm":
                bdpartstt.innerHTML = "right<br>arm";
                break;
            case "trso":
                bdpartstt.innerHTML = "torso";
                break;
        }
        
    }
});

document.getElementById('plrform').addEventListener('submit', function(event) {
    const form = this;
    const bodyparts = document.querySelectorAll('#char .bodypart');
    console.log(bodypart);
    bodyparts.forEach(function(part) {
        const id = part.id;
        const color = part.getAttribute('color');

        if (id && color) {
            const hiddeninpt = document.createElement('input');
            hiddeninpt.type = 'hidden';
            hiddeninpt.name = id + '_color';
            hiddeninpt.value = color;
            form.appendChild(hiddeninpt);
        }
    });
});