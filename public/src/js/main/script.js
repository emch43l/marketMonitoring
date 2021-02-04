window.onload = start;

function start()
{
    console.log($('#price-history'));
    if($('#price-history').length)
    {
        var chart = $.jqplot('price-history',  [items], {
            // title:{
            //     text: itemName,
            // },
            axes:{
                xaxis:{
                    renderer:$.jqplot.DateAxisRenderer,
                    tickOptions:{formatString:'%Y-%m-%d'},
                },
                yaxis: {
                    tickOptions: {
                        formatString: '$%.2f'
                    },
                    min: 0,
                }
            },
            series:[
                {
                    color: "#e7c944",
                    lineWidth:2,
                    showMarker: false,
                }
            ],
            cursor:{
                show: true, 
                zoom: true
            },
            grid:{
                background: '#202020',
                borderColor: '#151515',
                gridLineColor: '#151515',
                shadow: false,
                borderWidth: 1.0, 
            },
            
        });

        prices();

        var timer;

        window.onresize = function(){
            clearTimeout(timer);
            timer = setTimeout(function () {
                chart.replot(
                    { 
                        resetAxes: true,
                    }
                );
            }, 100);
        };

    }

    
    $("#price-status").text(calculatePrice());
    enableSwitch();
    throwAlert();
    overlay();
}

function calculatePrice()
{
    if(typeof items === 'undefined')
    {
        return false;
    }
    var calculateArray = items.reverse();
    var daysNum = 31;
    var days = 0;
    var calculate = 0;
    var priceArr = [];
    var diffArray = [];
    var helpItem = false;
    var avgPrice = false;
    var avgPrcnt = false;


    calculateArray.some(function(item){

        priceArr.push(item[1]);
        days++;

        if(daysNum == days)
        {
            return priceArr.reverse();
        }

    });



    priceArr.forEach(item => {

        if(helpItem)
        {
            var prct = 0;
            var diff = 0;
            avgPrice += item;
            if(item < helpItem)
            {
                prct = 100 - parseFloat(item * 100/helpItem).toFixed(2);
                diff = -parseFloat(helpItem - item).toFixed(2);
                prct = -prct;
            } 
            if(item > helpItem)
            {
                prct = 100 - parseFloat(helpItem * 100/item).toFixed(2);
                diff = +parseFloat(item - helpItem).toFixed(2);
            }
            avgPrcnt += prct;
            prct = parseFloat(prct/100).toFixed(2);
            diffArray.push(diff);
            console.log("diff between "+item+" and "+helpItem+" is: "+diff+" multiplier is :"+prct);
        }
        
        helpItem = item;
    });
    avgPrice = parseFloat(avgPrice / priceArr.length).toFixed(2);
    console.log("AVG % is "+avgPrcnt / priceArr.length);
    var helpDiff = null;

    diffArray.forEach(item => {
        avgPrice += item;
    });



    return false;
    
}


function prices()
{
    var priceMax = 0;
    var priceMin = 1000000;
    var max = 0;
    var min = 0;
 
    items.forEach(item => {
        if(item[1] > priceMax)
        {
            priceMax = item[1];
            max = item[1];
        }
        if(item[1] < priceMin)
        {
            priceMin = item[1];
            min = item[1];
        }
    });

    console.log(min);
    console.log(max);

    return max;
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

    $('#details-img, #zoom-img').on('click',function()
    {
        var url = $('#zoom-img').data('url');
        console.log(url);
    })
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