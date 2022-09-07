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

        $('.stand-button').on('click',function(e){
            e.preventDefault();
            self.dealerTurn();
        });

        $('.hit-button').on('click',function(e){
            e.preventDefault();
            self.hit();
        });

        $('.split-button').on('click',function(e){
            e.preventDefault();
            self.split();
        });

        $('.double-down-button').on('click',function(e){
            e.preventDefault();
            self.doubleDown();
        });

        $('.next-hand').on('click',function(e){
            e.preventDefault();
            self.nextHand();
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
        $('.hit-button, .stand-button').show();

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
                $('.balance').html(result['balance']);
                // populate the placeholders for both players
                $('.player-cards, .dealer-cards').empty();

                //player first
                playerCards.append('<div class="card-up">'+result['hand']['playerHand'][0]+'</div>');
                dealerCards.append('<div class="card-down">'+result['hand']['dealerHand'][0]+'</div>');
                playerCards.append('<div class="card-up">'+result['hand']['playerHand'][1]+'</div>');
                dealerCards.append('<div class="card-down">'+result['hand']['dealerHand'][1]+'</div>');

                // If not split or double, allow them to hit or stand
                if( result['options'].double === false && result['options'].split === false ){

                }

                if( result['options'].double ){
                    $('.double-down-button').removeClass('hidden');
                }else if($('.double-down-button').not('.hidden')){
                    $('.double-down-button').addClass('hidden');
                }
                /*
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
    dealerTurn: function()
    {
        let self = this;
        $('.hit-button, .stand-button').hide();
        // todo 1) turn over face down card.

        $.ajax({
            url: "/dealers-turn",
            method: 'get',
            success: function (result) {
                console.log(result);
                let dealerCards = $('.dealer-cards');
                dealerCards.empty();

                // todo as there may be multiple cards coming back, we need to turn them over one by one, slowly
                // display the first two first, as they will be replacing the exiting ones
                // then if there are any further cards, loop through them with a 1sec timeout.
                for( let i = 0; i < result['hand'].length; i++ ) {
                    dealerCards.append('<div class="card-down">' + result['hand'][i] + '</div>');
                }
                $('.message').html('<p>'+result.message+'</p>').show();
                $('.next-hand').show();
                $('.balance').html(result['balance']);
            }
        });
    },
    split: function()
    {
        $.ajax({
            url: "/split",
            method: 'get',
            success: function (result) {
                console.log(result);
            }
        })
    },
    hit: function()
    {
        $.ajax({
            url: "/hit",
            method: 'get',
            success: function (result) {
                console.log(result);
                let playerCards = $('.player-cards');
                playerCards.empty();

                for( let i = 0; i < result['hand'].length; i++ ){
                    playerCards.append('<div class="card-down">'+result['hand'][i]+'</div>');
                }

                if(result.bust === true){
                    $('.message').html('<p>'+result.message+'</p>').show();
                    $('.next-hand').show();
                    $('.hit-button, .stand-button').hide();
                }
            }
        })
    },
    doubleDown: function()
    {
        $.ajax({
            url: "/double-down",
            method: 'get',
            success: function (result) {
                console.log(result);
                let playerCards = $('.player-cards');
                // update the pot
                $('.stake-chips').html(result['bet']);
                // update the balance
                // todo this will be added once I've added DB updates.
                playerCards.empty();
                for( let i = 0; i < result['hand'].length; i++ ){
                    playerCards.append('<div class="card-down">'+result['hand'][i]+'</div>');
                }
                //playerCards.append('<div class="card-up">'+result['playerHand'][2]+'</div>');
                $('.double-down-button').addClass('hidden');
                if( result.result === false ){
                    alert('bust!');
                }
                // todo need to pass play to the dealer now.
            }
        })
    },
    nextHand: function()
    {
        $('.next-hand').hide();
        $("input[name=bet]").val("").removeClass('hidden');
        $('.message').hide();
        $('.place-bet').removeClass('hidden');
        $('.player-cards, .dealer-cards, .stake-chips').empty();

    }
};

$(document).ready(function () {
    blackjack.app.init();
});