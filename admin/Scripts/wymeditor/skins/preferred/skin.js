WYMeditor.SKINS['preferred'] = {

    init: function(wym) {
    
        //move the containers panel to the top area
        jQuery(wym._options.containersSelector, wym._box)
          .appendTo( jQuery("div.wym_area_top", wym._box) )
          .addClass("wym_dropdown")
          .css({ "margin-top": "5px", "width": "90px", "float": "left"});
					
        jQuery(wym._options.classesSelector, wym._box)
          .appendTo( jQuery("div.wym_area_top", wym._box) )
          .addClass("wym_dropdown")
          .css({ "margin-top": "5px", "width": "90px", "float": "left"});
					
        jQuery(wym._options.status, wym._box)
          .addClass("wym_hidden")
          .css({"width": "120px", "float": "left"});

        //render following sections as buttons
        jQuery(wym._options.toolsSelector, wym._box)
          .addClass("wym_buttons")
          .css({"float": "left"});

        //make hover work under IE < 7
        jQuery(".wym_section", wym._box).hover(function(){
          jQuery(this).addClass("hover");
        },function(){
          jQuery(this).removeClass("hover");
        });

        var postInit = wym._options.postInit;
        wym._options.postInit = function(wym) {

            if(postInit) postInit.call(wym, wym);
            var rule = {
                name: 'body'
                //css: 'background-color: #eee;'
            };
            wym.addCssRule( wym._doc.styleSheets[0], rule);
        };
    }
};
