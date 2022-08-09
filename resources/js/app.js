import './bootstrap';

let blackjack = {};
blackjack.app = {

    init: function () {

        this.eventListeners();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    },
    eventListeners: function ()
    {
        let self = this;

        $('.post-bet').on('submit',function(e){
            e.preventDefault();
            self.postBet();
        });

    },
    postBet: function()
    {
        $.ajax({
            url: "/post-bet",
            method: 'post',
            data: {
                bet:  $("input[name=bet]").val(),
            },
            success: function(result){

                // stake chips
                $('.stake-chips').html(result['bet']);
                // populate the placeholders for both players
                $('.player-cards, .dealer-cards').empty();
                //player first
                $('.player-cards').append('<div class="card-up">'+result['hand']['playerCards'][0]+'</div>');
                $('.dealer-cards').append('<div class="card-down">'+result['hand']['dealerCards'][0]+'</div>');
                $('.player-cards').append('<div class="card-up">'+result['hand']['playerCards'][1]+'</div>');
                $('.dealer-cards').append('<div class="card-down">'+result['hand']['dealerCards'][1]+'</div>');

                if(result['options'].double){
                    $('.dd-button').removeClass('hidden');
                }else if($('.dd-button').not('.hidden')){
                    $('.dd-button').addClass('hidden');
                }
                if(result['options'].split){
                    $('.split-button').removeClass('hidden');
                }else if($('.split-button').not('.hidden')){
                    $('.split-button').addClass('hidden');
                }
            },
            error: function(e){
                console.log(e.message);
            }
        });
    }
};

$(document).ready(function () {
    blackjack.app.init();
});