const colorpicker = document.getElementById("colorpicker");
const charbody = document.getElementById("char");
const bdpartstt = document.getElementById("whatdiduselect");
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
    if (spanel.nodeName == "SPAN" || spanel.closest('.bodypart')) {
        const partElement = spanel.nodeName == "SPAN" ? spanel : spanel.closest('.bodypart');
        bodypart = partElement.getAttribute("id");
        console.log(bodypart);
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

document.getElementById('saveButton').addEventListener('click', function(e) {
    const bodyparts = document.querySelectorAll('#char .bodypart');
    const formData = new URLSearchParams();
    
    formData.append('is_ajax_save', '1');

    bodyparts.forEach(function(part) {
        const id = part.id;
        const color = part.getAttribute('color');
        
        if (id && color) {
            formData.append(id + '_color', color);
        }
    });

    const xhr = new XMLHttpRequest();
    xhr.open('POST', window.location.href, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function () {
        if (xhr.status === 200) {
            bdpartstt.innerHTML = xhr.responseText; 
            setTimeout(() => {
                if (bdpartstt.innerHTML === 'Saved!') {
                    bdpartstt.innerHTML = 'click<br>guy';
                }
            }, 3000); 
        } else {
            bdpartstt.innerHTML = 'Error saving: ' + xhr.status;
            console.error('Save failed:', xhr.responseText);
        }
    };

    xhr.onerror = function() {
        bdpartstt.innerHTML = 'Network Error';
    };

    xhr.send(formData.toString());
});


function render() {
    var xmlhttp = new XMLHttpRequest();
    const btn = document.getElementById("renderstat");
    btn.innerHTML = "Standby...";
    btn.disabled = true;
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("render").src = this.responseText + "?t=" + new Date().getTime();
            btn.innerHTML = "Done.";
            console.log(this.responseText);
        }
    };

    let data = {};
    const bodyparts = document.querySelectorAll('#char .bodypart');

    bodyparts.forEach(function(part) {
        const id = part.id;
        const color = part.getAttribute('color');
        
        data[id] = color;
    });
    xmlhttp.open("POST", "render.php", true);
    xmlhttp.setRequestHeader("Content-Type", "application/json"); 
    xmlhttp.send(JSON.stringify(data));
}