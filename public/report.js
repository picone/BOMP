var a= navigator.userAgent.toLowerCase();
var is_op = (a.indexOf("opera") != -1);
var is_ie = (a.indexOf("msie") != -1) && document.all && !is_op;
function ResizeTextarea(a, row) {
    if (!a) {
        return
    }
    if (!row) {
        row = 5
    }
    var b = a.value.split("\n");
    var c = is_ie ? 1 : 0;
    c += b.length;
    var d = a.cols;
    if (d <= 20) {
        d = 40
    }
    for (var e = 0; e < b.length; e++) {
        if (b[e].length >= d) {
            c += Math.ceil(b[e].length / d)
        }
    }
    c = Math.max(c, row);
    if (c != a.rows) {
        a.rows = c
    }
}
jQuery('document').ready(function(){
    var a=jQuery('td[data-role=color]');
    var b=a.find('input:checked').parent();
    b.addClass(b.data('class'));
    a.find('input').click(function(){
        a.each(function(i,v){
            jQuery(v).removeClass(jQuery(v).data('class'));
            jQuery(v).find('input').prop('checked',false);
        });
        jQuery(this).parent().addClass(jQuery(this).parent().data('class'));
        jQuery(this).prop('checked',true);
    });
});