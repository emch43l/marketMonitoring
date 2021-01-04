window.onload = checkInputValues;

function checkInputValues()
{
    var redirectError = $("#redirectError");
    console.log(redirectError);
    if(redirectError.data('error')) throwAlert('error',redirectError.data('error-content'));
    var inputs = $(".form-control");
    var form = $('#login-form');

    inputs.on('keydown',function()
    {
        if($(this).data('error'))
        {
            $(this).css('border-color','');
        } 
    });

    form.on('submit', function(e)
    {
        var error = false;
        e.preventDefault();
        inputs.each(function(index)
        {
            if(!this.value)
            {
                error = true;
                $(this).data('error',true);
                $(this).css('border-color','var(--main-red)');
                throwAlert('error','Fill all blanks !');
            }
        });
        if(!error)
        {
            form[0].submit();
        }
    });
}