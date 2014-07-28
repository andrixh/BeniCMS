(function($){

    function getfromForm(s){ //range 0-2 to
        return (s/2)*satSteps;
    }

    function setToForm(s){
        return s/satSteps*2;
    }
    var hueSteps = 36;
    var satSteps = 25;

    var hueControl = $('#brand_hue');
    var saturationControl = $('#brand_saturation');

    var hue = hueControl.val();
    var saturation = getfromForm(saturationControl.val());

    if (hue == '') {
        hue = 0;
    }

    if (saturation == ''){
        saturation = 1;
    }





//    hueControl.hide();
//    saturationControl.hide();

    var i;
    var hueGradient = $('<div class="brandingBar hue"><div class="gradient hue"></div></div>');
    var saturationGradient = $('<div class="brandingBar saturation"><div class="gradient saturation"></div></div>');
    for (i=0; i<hueSteps; i++) {
        var swatch = $('<div class="swatch"></div>');
        swatch.css('background-color','hsl('+(i*10)+',100%,50%)');
        swatch.css('width',''+(100/hueSteps)+'%');
        hueGradient.find('.gradient').append(swatch);
    }

    for (i=0; i<satSteps; i++) {
        var swatch = $('<div class="swatch"></div>');
        swatch.css('background-color','hsl('+hue+','+(i*(100/satSteps))+'%,50%)');
        swatch.css('width',''+(100/satSteps)+'%');
        saturationGradient.find('.gradient').append(swatch);
    }

    hueGradient.insertAfter(hueControl);
    saturationGradient.insertAfter(saturationControl);

    var resultColors = $('<label><span class="label">Resulting Interface Colors</span><div class="brandingBar"><div class="result"></div><div class="result"></div><div class="result"></div><div class="result"></div><div class="result"></div></div></label>');
    resultColors.insertAfter(saturationControl.parent());

    refreshSelection();

    function redrawSaturation(){
        $('.brandingBar.saturation .swatch').each(function(e){
            $(this).css('background-color','hsl('+hue+','+($(this).index()*(100/satSteps))+'%,50%)');
        });
    }

    $('.brandingBar.hue').on('click','.swatch',function(e){
        hue = $(this).index() * (360/hueSteps);
        redrawSaturation();
        refreshSelection();
    });

    $('.brandingBar.saturation').on('click','.swatch',function(e){
        saturation = $(this).index();
        refreshSelection();
    });

    function refreshSelection(){
        $(hueGradient).find('.swatch').eq(hue/(360/hueSteps)).addClass('selected').siblings().removeClass('selected');
        $(saturationGradient).find('.swatch').eq(saturation).addClass('selected').siblings().removeClass('selected');
        hueControl.val(hue);
        saturationControl.val(setToForm(saturation));
        refreshResults();
    }

    function refreshResults(){
        var s = saturation/satSteps*2;
        var h = hue;
        $(resultColors).find('.result').eq(0).css('background-color','hsl('+h+','+(55*s)+'%,73%)');
        $(resultColors).find('.result').eq(1).css('background-color','hsl('+h+','+(60*s)+'%,46%)');
        $(resultColors).find('.result').eq(2).css('background-color','hsl('+h+','+(64*s)+'%,34%)');
        $(resultColors).find('.result').eq(3).css('background-color','hsl('+h+','+(73*s)+'%,27%)');
        $(resultColors).find('.result').eq(4).css('background-color','hsl('+h+','+(80*s)+'%,10%)');
    }


})(jQuery);