const colorpicker = document.getElementById("colorpicker");
const charbody = document.getElementById("char");
const bdpartstt = document.getElementById("whatdiduselect");
const rs = document.getElementById("renderstat");
const renderimg = document.getElementById("render");
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

async function save() {
    const bodyparts = document.querySelectorAll('#char .bodypart');
    const formData = new FormData(); 

    formData.append('is_ajax_save', '1');

    bodyparts.forEach(function(part) {
        const id = part.id;
        const color = part.getAttribute('color');
        
        if (id && color) {
            formData.append(id + '_color', color);
        }
    });

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData 
        });

        if (response.ok) {
            rs.innerHTML = "Saving...";
        } else {
            console.error('Save failed:', await response.text());
        }

    } catch (error) {
        console.error('Fetch operation failed:', error);
    }
}

async function render() {
    await save();
    rs.disabled = true;
    rs.innerHTML = "Saving...";
    try {
        rs.innerHTML = "Rendering...";
        const request = await fetch("render.php", {
            method: 'GET'
        });

        const resp = await request.text();
        const stat = request.status;

        if (stat == 429) {
            rs.innerHTML = "Saved <br>(render on cooldown).";
        } else
        if (request.ok) {
            renderimg.setAttribute('src', resp + "?t=" + new Date().getTime());
            console.log("Received URL is: ", resp);
            rs.innerHTML = "Done."
        } else {
            rs.innerHTML = "Error: " + stat;
            console.error("Got error code: ", stat)
            console.error("Unhandled error: ", resp)
        }
    } catch (error) {
        console.error("Gasp! An error. ", error)
        rs.innerHTML = "Error: " + error;
    } finally {
        setTimeout(() => {
            rs.innerHTML = "Save"
            rs.disabled = false
        }, 3000);
    }
}