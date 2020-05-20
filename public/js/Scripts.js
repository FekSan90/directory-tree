
function create_node() {
    var ref = $('#data').jstree(true),
        sel = ref.get_selected();
    if(!sel.length) { return false; }
    sel = sel[0];
  sel = ref.create_node(sel, {"type":"file"});
    if(sel) {
        ref.edit(sel);
        signal ="create";
    }
}

function rename_node() {
    var ref = $('#data').jstree(true),
        sel = ref.get_selected();
    if(!sel.length) { return false; }
    sel = sel[0];
    if(ref.get_selected()[0]!=0)
    ref.edit(sel);
    signal ="rename";
}

function delete_node(){

    var ref = $('#data').jstree(true),
        sel = ref.get_selected();
    if(!sel.length) { return false; }
    if(ref.get_selected()[0]!=0)
        ref.delete_node(sel);
}

function showError(str){
    $("#error").show();
    document.getElementById("error").innerHTML = str;
    $("#error").fadeOut(5000,function () {
        document.getElementById("error").innerHTML = "";
        $("#error").show();
    });
}