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

        $('.split-button').on('click',function(e){
            e.preventDefault();
            self.split();
        });

        $('.double-down-button').on('click',function(e){
            e.preventDefault();
            self.doubleDown();
        });

    },
    validateBet: function(val){
        if( $.isNumeric(val) && ( val > 0 && val < 1001 ) ){
            return true;
        }
        return false;
    },
    postBet: function()
    {
        let self = this;
        if( self.validateBet($("input[name=bet]").val() ) === false ){
            alert('invalid bet placed.  Please refresh and try again');
            return;
        }
        // hide the place bet button and don't allow them to change their bet.
        // todo - remove comments once done testing
        $("input[name=bet]").addClass('hidden');
        $('.place-bet').addClass('hidden');


        $.ajax({
            url: "/post-bet",
            method: 'post',
            data: {
                bet:  $("input[name=bet]").val(),
            },
            success: function(result){
                let playerCards = $('.player-cards');
                let dealerCards = $('.dealer-cards');
                // stake chips
                $('.stake-chips').html(result['bet']);
                // populate the placeholders for both players
                $('.player-cards, .dealer-cards').empty();

                //player first
                playerCards.append('<div class="card-up">'+result['hand']['playerHand'][0]+'</div>');
                dealerCards.append('<div class="card-down">'+result['hand']['dealerHand'][0]+'</div>');
                playerCards.append('<div class="card-up">'+result['hand']['playerHand'][1]+'</div>');
                dealerCards.append('<div class="card-down">'+result['hand']['dealerHand'][1]+'</div>');

               /* if(result['options'].double){
                    $('.double-down-button').removeClass('hidden');
                }else if($('.double-down-button').not('.hidden')){
                    $('.double-down-button').addClass('hidden');
                }
                if(result['options'].split){
                    $('.split-button').removeClass('hidden');
                }else if($('.split-button').not('.hidden')){
                    $('.split-button').addClass('hidden');
                }*/
            },
            error: function(e){
                console.log(e.message);
            }
        });
    },

    split: function() {
        $.ajax({
            url: "/split",
            method: 'get',
            data: {
                bet: $("input[name=bet]").val(),
            },
            success: function (result) {
                console.log(result);
            }
        })
    },
    doubleDown: function() {
        $.ajax({
            url: "/double-down",
            method: 'get',
            data: {
                bet: $("input[name=bet]").val(),
            },
            success: function (result) {

                let playerCards = $('.player-cards');
                // update the pot
                $('.stake-chips').html(result['bet']);
                playerCards.append('<div class="card-up">'+result['card']+'</div>');
                $('.double-down-button').addClass('hidden');
            }
        })
    }
};

$(document).ready(function () {
    blackjack.app.init();
});