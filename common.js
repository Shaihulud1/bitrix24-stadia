$(document).ready(function(){
    $('.arrow_down').last().hide();
    $('.choose_worker').first().css('background-color','#93b81a');
    $('.choose_worker').last().css('background-color','#d14545');
    $('.deal-item').last().css('margin-bottom','130px');
    
    $('.accept-leads').on('click', function(){
        var stageArray = [];
        $('.deal-item').each(function(index, element){
            var i = stageArray.length;
            stageArray[i] = {};
            stageArray[i].stageID = $(this).attr('stage-id');
            stageArray[i].userID = $(this).find('input').val();
        })
        console.log('stageArray');
        console.log(stageArray);
        $.ajax({
            type: "POST",
            url: "ajax/stage.php",
            data: {
                stageArray: stageArray,
            },
            beforeSend: function(){
                $('.download').show();          
            },             
            success: 
                function(data){
                    alert(data);
                    console.log(data);
                    $('.download').hide();  
                    $('.bitsend').hide();
                },
            error: function(){
                alert('ошибка');
            }
        });
    });
    
});

$('.dropdown-menu li').click(function(){
    var dropdownVal;
    dropdownVal = $(this).find('a').text();
    $(this).parent().parent().find('.downtext').text(dropdownVal);
    $(this).parent().parent().parent().attr('user-id', $(this).find('.uid').val());
    $('.bitsend').show();
});

