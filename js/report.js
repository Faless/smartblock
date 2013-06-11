var TABS = new Array();

function findSelection() {
    if(window.location.hash) {
        var hash = window.location.hash;
        var found = false;
        for( var i = 0; i < TABS.length; i++ ) {
            var t = TABS[i];
            if( "#" + t.id == hash + "_tab") {
                t.className = "selected";
                found = true;
            }
            else
                t.className = "";
        }
        if( !found )
            TABS[0].className = "selected";
    }
    else
        TABS[0].className = "selected";
}

function showSelection() {
    for( var i = 0; i < TABS.length; i++ ) {
        var t = TABS[i];
        var id = "report_" + t.id.substr(0, t.id.length-4);
        if(t.className != "selected")
            document.getElementById(id).style.display = "none";
        else 
            document.getElementById(id).style.display = "block";
    }
};

function setHandler() {
    for( var i = 0; i < TABS.length; i++ ) {
        var THAT = TABS[i];
        function handler() {
            for( var i = 0; i < TABS.length; i++ ) {
                TABS[i].className = "";
            }
            this.className = "selected";
            showSelection();
        }
        if( THAT.addEventListenerasd )
            THAT.addEventListener("click", handler, false);
        else 
            THAT.onclick = handler;
    }
};

function load() {
  var nodes = document.getElementById("report_tabs").getElementsByTagName("a");
  for( var i = 0; i < nodes.length; i++ ) {
      TABS.push(nodes[i]);
  }
  findSelection();
  showSelection();
  setHandler();  
};
window.onload = load;
