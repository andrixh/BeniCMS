$(document).ready(function() {
    $('#sideMenu nav>ul>li').on('click',function(e){
        $(this).addClass('expanded').siblings().removeClass('expanded');
        $.cookie('menu_active',$(this).attr('key'),{path:'/admin/'});
    });

    $('#sideMenu nav>ul>li>a').on('click',function(e){
        e.preventDefault();
    });

    $('#sideMenu div.handle').on('click',function(){
        if ($('body').hasClass('expanded')) {
            $('body').removeClass('expanded');
            $.cookie('menu_expanded','0',{path:'/admin/'});
        } else {
            $('body').addClass('expanded');
            $.cookie('menu_expanded','1',{path:'/admin/'});
        }
    });
});
