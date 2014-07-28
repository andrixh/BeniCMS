$(document).ready(function(){
    if ($('#templatePhp').length > 0){
        $('input#typeID').on('change keyup',function(e){
            var className = $(this).val().charAt(0).toUpperCase()+$(this).val().substr(1);
            var cm = codeMirrors['templatePhp'];
            var codeVal = cm.getValue();
            codeVal = codeVal.replace(/(class)(.*)(extends)/,"$1 "+className+" $3");
            cm.setValue(codeVal);
        });
    }
});