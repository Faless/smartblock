/*
 * This file is part of MySmarkEDU.
 *
 * MySmarkEDU is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MySmarkEDU is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MySmarkEDU. If not, see <http://www.gnu.org/licenses/>.
 */

var urlParams = {};
(function () {
    var match,
        pl     = /\+/g,  // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
        query  = window.location.search.substring(1);

    while (match = search.exec(query))
       urlParams[decode(match[1])] = decode(match[2]);
})();

function Student( row ) {
    /* Row */
    this.row = row;
    var cells = row.getElementsByTagName("td");
    /* Checkbox Input */
    this.inp = row.getElementsByTagName("input")[0];
    /* User Id */
    this.id = parseInt( this.inp.getAttribute("name")
                        .replace(/[^0-9]*([0-9]+)[^0-9]*/, "$1"), 10);
    /* Original state */
    this.wasChecked = this.inp.checked;
    /* Current state */
    this.checked = this.wasChecked;
    /* Student Name */
    this.name = cells[0].firstChild.data;
    /* Enrollemnet date */
    this.enrolled = cells[1].firstChild.data;

    /* Setting handlers */
    var THAT = this;
    this.inp.onclick = function( event ) {
        THAT.checked = THAT.inp.checked;
        if( THAT.wasChecked == THAT.inp.checked )
            THAT.inp.parentNode.removeAttribute("class");            
        else
            THAT.inp.parentNode.setAttribute("class", "changed");
    };
}

Student.prototype.update = function( data ) {
    var enabled = data['enabled'] ? true : false;
    /* If values were the same we update the checkbox */
    if( this.checked == this.wasChecked ) {
        this.wasChecked = this.checked = enabled;
        this.inp.checked = enabled;
        this.inp.parentNode.removeAttribute("class");
    }
    /* If checked value was changed we don't update the form */
    else {
        this.wasChecked = enabled;
        this.inp.parentNode.setAttribute("class", "changed");
    }
};

function EventForm( form ) {
    this.form = form;
    this.body = form.getElementsByTagName("tbody")[0];
    this.rows = this.body.getElementsByTagName("tr");
    this.students = new Array(this.rows.length);
    this.event = urlParams['event'];
    for( var i = 0; i < this.rows.length; i++ ) {
        var stud = new Student(this.rows[i]);
        this.students[stud.id] = stud;
    }
    var THAT = this;
    setTimeout(function() {
                   THAT.makeRequest("refresh_students.php?event="
                                    + THAT.event, null);
               }, 1000);
}

EventForm.prototype.refresh = function( data ) {
    dlen = data.length;
    for(var i = 0; i < dlen; i++) {
        var stud = data[i];
        if( this.students[stud.id] ) {
            this.students[stud.id].update(stud);
        }
    }
    var THAT = this;
    setTimeout(function() {
                   THAT.makeRequest("refresh_students.php?event="
                                    + THAT.event, null);
                }, 5000);
};

EventForm.prototype.makeRequest = function(script, data) {
    var req = null;
    if (window.XMLHttpRequest) {
        req = new XMLHttpRequest();
    }
    // Old shitty IE
    else if (window.ActiveXObject) {
        req = new ActiveXObject("Microsoft.XMLHTTP");
    }
    if (req) {
        // Increment the request ID
        // Set the request in the array
        req.open("POST", script, true);
        req.setRequestHeader("Content-type",
                             "application/x-www-form-urlencoded; " +
                             "charset=utf-8;");
        req.send("req=" + encodeURIComponent(JSON.stringify( data )));
        var THAT = this;
        req.onreadystatechange = function( ) {
            THAT.responseHandler( req );
        };
    }    
};

EventForm.prototype.responseHandler = function( req ) {
    if ( req.readyState == 4 ) {
        try {
            // Only if "OK"
            if ( req.status != 200 )
                return alert("Error updating UI:\n" + req.statusText);
            var response = JSON.parse( req.responseText );
            if( !(response instanceof Array) )
                return alert(response);
            this.refresh(response);
        } catch(e) {
            alert("Error updating UI:\n" + e);
        }
    }
};

window.onload = function() {
    new EventForm( document.getElementById("EVENT_FORM") );
};
