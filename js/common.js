$(document).ready(function(){
    $('.arrow_down').last().hide();
    $('.deal_arr').last().hide();      
    $('.choose_worker').first().css('background-color','#93b81a');
    $('.choose_worker').last().css('background-color','#d14545');
    $('.deal-item').last().css('margin-bottom','130px'); 
    $('.true_deal').last().css('margin-bottom','130px'); 
    $('.accept-leads').on('click', function(){
        var stageArray = [];
        var dealLead = '';
        $('.deal-item').each(function(index, element){
            if($(this).is(':visible')){
                if($(this).hasClass('true_deal')){
                    dealLead = 'deal';
                }else{
                    dealLead = 'lead';
                }
                var i = stageArray.length;
                stageArray[i] = {};
                if($(this).find('.users').is(':checked')) {                    
                    stageArray[i].stageID = $(this).attr('stage-id');
                    stageArray[i].userID = $(this).find('input').val();
                    stageArray[i].groupID = '';
                    //stageArray[i].dealLead = dealLead;
                }else if($(this).find('.group').is(':checked')){
                    stageArray[i].stageID = $(this).attr('stage-id');
                    stageArray[i].groupID = $(this).find('input').val();
                    stageArray[i].userID = '';
                    //stageArray[i].dealLead = dealLead;
                }
                

            }
        })
        console.log('stageArray');
        console.log(stageArray);
        console.log(dealLead);
        $.ajax({
            type: "POST",
            url: "ajax/stage.php",
            data: {
                stageArray: stageArray,
                dealLead: dealLead
            },
            beforeSend: function(){
                $('.download').show();          
            },             
            success: 
                function(data){
                    console.log(data);
                    $('.download').hide();  
                    $('.bitsend').hide();
                },
            error: function(){
                alert('ошибка');
            }
        });
    });
    $('.arrows').on('click',function(){
        $('.deals').fadeToggle();
        if($('.logo').find('t').text() == 'Стадии сделок'){
            $('.logo').find('t').text('Статусы лидов');
        }else if($('.logo').find('t').text() == 'Статусы лидов'){           
            $('.logo').find('t').text('Стадии сделок');
        }
        $('.leads').fadeToggle();
    });
});

$('.dropdown-menu li').click(function(){
    var dropdownVal;
    dropdownVal = $(this).find('a').text();
    $(this).parent().parent().find('.downtext').text(dropdownVal);
    $(this).parent().parent().parent().attr('user-id', $(this).find('.uid').val());
    $('.bitsend').show();
});

