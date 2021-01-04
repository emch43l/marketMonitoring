window.onload = start;

function start()
{
    enableSwitch();
    throwAlert();
    overlay();
}

function overlay()
{
    var close = $('#close-form');
    var open = $('#open-form');
    var form = $('#form-window');
    close.on('click', function(event)
    {
        form.fadeOut(400);
        $('.items-table').animate({width: "100%"}, 400, function(){
            $('.open-form').fadeIn();
        });
    });

    open.on('click', function(event)
    {
        $('.open-form').fadeOut(200);
        form.fadeIn(400, function(){
        });
        $('.items-table').css("width","calc(100% - 250px)");
    });
}

function enableSwitch()
{
    var checkboxSwitch = $('.switch-switchMe');
    console.log(checkboxSwitch);
    if(!checkboxSwitch.prop("checked"))
    {
        $('.switch-circle').removeClass('checked');
        $('.switch-cover').removeClass('enabled');
    } 
    checkboxSwitch.on('change', function(event)
    {
        console.log(event.target);
        var divcircle = $(this).prev('div');
        var switchcover = $(this).closest('label');
        // var divcircle = $('.switch-circle');
        // var switchcover = $('.switch-cover');
        if($(this).prop("checked"))
        {
            switchcover.addClass('enabled');
            divcircle.addClass('checked');
        } 
        if(!$(this).prop("checked")) 
        {
            switchcover.removeClass('enabled');
            divcircle.removeClass('checked');
        }
    });
}