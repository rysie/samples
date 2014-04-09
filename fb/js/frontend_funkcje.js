function block_ie6() {
    if (navigator.userAgent.indexOf("MSIE 6") >= 0) {
        $("body").empty();
        $("<div>")
                .css({
                    'position': 'absolute',
                    'top': '0px',
                    'left': '0px',
                    backgroundColor: 'black',
                    'opacity': '0.75',
                    'width': '100%',
                    'height': $(window).height(),
                    zIndex: 5000
                })
                .appendTo("body");

        $("<div><img src='/images/no-ie6.png' alt='' style='float: left;'/><p><br /><strong>Uwaga! Używasz przestarzałej przeglądarki<br/>(Internet Explorer 6).</strong><br />Aby obejrzeć zawartość tej strony, zaktualizuj swoją przeglądarkę:\n\
        <br/><br/></p><ul><li><a href='http://getfirefox.org'>Mozilla Firefox</a></li>\n\
        <li><a href='http://windows.microsoft.com/pl-PL/internet-explorer/products/ie/home'>Internet Explorer</a></li>\n\
        <li><a href='http://getfirefox.org'>Mozilla Firefox</a></li>\n\
        <li><a href='http://www.google.com/chrome'>Google Chrome</a></li>\n\
        <li><a href='http://www.opera.com/download'>Opera</a></li>\n\
        <li><a href='http://www.apple.com/safari/download'>Safari</a></li></ul></div>")
                .css({
                    backgroundColor: 'white',
                    'top': '50%',
                    'left': '50%',
                    marginLeft: -210,
                    marginTop: -100,
                    width: 410,
                    paddingRight: 10,
                    height: 230,
                    'position': 'absolute',
                    zIndex: 6000
                })
                .appendTo("body");
    }
}




$().ready(function() {
    block_ie6();

    $.ajaxSetup({cache: true});
    $.getScript('//connect.facebook.net/pl_PL/all.js', function() {
        FB.init({
            appId: $("meta[name=appid]").attr("content"),
            status: true,
            xfbml: true
        });
        FB.Event.subscribe('edge.create', function() {
            $(".isFan").slideDown();
            $(".noFan").slideUp();
            //$(".fb-like").hide();
        });
        FB.Event.subscribe('edge.remove', function() {
            $(".isFan").slideUp();
            $(".noFan").slideDown();
            //$(".fb-like").show();
        });
    });




    $('.accordion').on('click', '.caption', function(e) {
        $(this).parent().find('.content:visible').not($(this).next()).slideUp(100);
        $(this).next().slideDown(100);
        return e.preventDefault();
    });

    $("#zakwaterowanie_inne").on("focus", function() {
        $(this).parent("div").find(":checked").prop("checked", false);
        $(this).parent().find("#p02-6").prop("checked", true);
    });

    $("#czy_nocowales_gdzie").on("focus", function() {
        $(this).parent("div").find(":checked").prop("checked", false);
        $(this).parent().find("#p06-2").prop("checked", true);
    });

    $("#czy_aktywny_gdzie").on("focus", function() {
        $(this).parent("div").find(":checked").prop("checked", false);
        $(this).parent("div").find("#p08-2").prop("checked", true);
    });


    $('#contest-form').validate({
        //onkeyup: true,
//        errorPlacement: function(error, element) {
//            return true;
//        },
        errorElement: "div",
        rules: {
            hotel: {
                required: true,
                minlength: 3
            },
            description: {
                required: true,
                minlength: 3
            },
            firstname: {
                required: true,
                minlength: 2
            },
            surname: {
                required: true,
                minlength: 2
            },
            email: {
                email: true,
                required: true
            },
            phone: {
                required: true,
                minlength: 8
            },
            agr_1: {
                required: true
            },
            agr_2: {
                required: true
            }

        },
        messages: {
            email: {
                email: "<i class='icon-circle-arrow-up'/> Wprowadź poprawny adres e-mail!"
            }
        }
    });



});