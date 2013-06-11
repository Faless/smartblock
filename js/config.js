function addValue(elem) {
    var table = document.getElementById('params_table_1');
    var total = table.getElementsByTagName('input');
    if( ( (total.length / 2) - 1) >= 9 ) {
        alert("A maximum of 9 values is allowed");
        return;
    }
    var row = elem;
    var clone = row.cloneNode(true);
    clone.id = "";
    clone.removeAttribute("id");
    clone.style.display = "";
    var inputs = clone.getElementsByTagName('input');
    for( var i = 0; i < inputs.length; i++ ) {
        inputs[i].value = "";
        var name = inputs[i].getAttribute("name");
        inputs[i].setAttribute("name", name.substring(1));
    }
    row.parentNode.insertBefore( clone, row );
}
function delValue(elem) {
    var row = elem.parentNode.parentNode;
    row.parentNode.removeChild(row);
}

function addParam(elem) {
    var table = document.getElementById('params_table_2');
    var total = table.getElementsByTagName('input');
    if( ( total.length - 1) >= 8 ) {
        alert("A maximum of 8 parameters is allowed");
        return;
    }
    var clone = elem.cloneNode(true);
    clone.id = "";
    clone.removeAttribute("id");
    clone.style.display = "";
    var inputs = clone.getElementsByTagName('input');
    for( var i = 0; i < inputs.length; i++ ) {
        inputs[i].value = "";
        var name = inputs[i].getAttribute("name");
        inputs[i].setAttribute("name", name.substring(1));
    }
    elem.parentNode.insertBefore( clone, elem );
}
function delParam(elem) {
    var row = elem.parentNode.parentNode;
    row.parentNode.removeChild(row);
}

function toggleParams(elem) {
    if(elem.checked)
        document.getElementById("params_div").style.display = "";
    else
        document.getElementById("params_div").style.display = "none";
}

function numbersOnly(e) {
    var evt = (e) ? e : window.event; 
    var key = (evt.keyCode) ? evt.keyCode : evt.which; 
    
    if(key != null) { 
        key = parseInt(key, 10); 
        
        if((key < 48 || key > 57) && (key < 96 || key > 105)) { 
            if(!isUserFriendlyChar(key)) 
                return false; 
        } 
        else { 
            if(evt.shiftKey) 
                return false; 
        } 
    } 
    
    
    return true; 
} 


function isUserFriendlyChar(val) { 
    // Backspace, Tab, Enter, Insert, and Delete 
    if(val == 8 || val == 9 || val == 13 || val == 45 || val == 46) 
        return true; 
    
    
    // Ctrl, Alt, CapsLock, Home, End, and Arrows 
    if((val > 16 && val < 21) || (val > 34 && val < 41)) 
        return true; 
    
    
    // The rest 
    return false; 
}
