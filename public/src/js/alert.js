function throwAlert()
{
    var redirectError = $("#redirectError");
    console.log(redirectError);
    var type = redirectError.data('error');
    if(!type || type == null)
    {
        return false;
    }
    var alertText = redirectError.data('error-content');
    var alert = $("#alert");
    var iconHolder = $(".alert-icon");
    var icon = $("#icon");
    var text = $(".alert-text");
    icon.removeClass("fa fa-times fa-exclamation fa-check");
    iconHolder.removeClass("error success alert");
    text.text(alertText);

    console.log(type);

    switch(type)
    {
        case 'alert':
            alert.addClass("border-orange");
            icon.addClass("fa fa-exclamation");
            iconHolder.addClass("alert");
        break;
        case 'error':
            alert.addClass("border-red");
            icon.addClass("fa fa-times");
            iconHolder.addClass("error");
        break;
        case 'success':
            alert.addClass("border-green");
            icon.addClass("fa fa-check");
            iconHolder.addClass("success");
        break;
        default:
            alert.addClass("border-default");
            icon.addClass("fa fa-question");
            iconHolder.addClass("alertUndefined");
        break;
    }

    alert.animate({bottom: "80px"},200,function()
    {
        $(this).delay(3000).animate({bottom: "-80px"},200);
    })
}