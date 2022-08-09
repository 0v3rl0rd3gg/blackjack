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

                //player first
                $('.player-cards').append('<div class="card-up">'+result['hand']['playerCards'][0]+'</div>');
                $('.dealer-cards').append('<div class="card-down">'+result['hand']['dealerCards'][0]+'</div>');
                $('.player-cards').append('<div class="card-up">'+result['hand']['playerCards'][1]+'</div>');
                $('.dealer-cards').append('<div class="card-down">'+result['hand']['dealerCards'][1]+'</div>');
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