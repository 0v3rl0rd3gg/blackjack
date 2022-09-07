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
            self.stand();
        });

        $('.hit-button').on('click',function(e){
            e.preventDefault();
            self.hit();
        });

        $('.hit-split-button').on('click',function(e){
            e.preventDefault();
            self.hit(true);
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
        let playerCards = $('.player-cards');
        let playerSplitCards = $('.player-split-cards');

        $.ajax({
            url: "/split",
            method: 'get',
            success: function (result) {
                console.log('split');
                console.log(result);

                playerCards.empty();

                for( let i = 0; i < result['playerHand'].length; i++ ) {
                    playerCards.append('<div class="card-down">' + result['playerHand'][i] + '</div>');
                }

                for( let i = 0; i < result['playerSplitHand'].length; i++ ) {
                    playerSplitCards.append('<div class="card-down">' + result['playerSplitHand'][i] + '</div>');
                }

            }
        })
    },
    hit: function(splitHand = false)
    {
        $.ajax({
            url: "/hit",
            method: 'post',
            data:{
                splitHand: splitHand
            },
            success: function (result) {

                console.log(result);
                let cards = $('.player-cards');
                if( result.currentSplitHand !== false ){
                    cards = ( ( result.currentSplitHand === 'playerSplitHand' )? $('.player-split-cards') : $('.player-cards') );
                }

                cards.empty();

                for( let i = 0; i < result['hand'].length; i++ ){
                    cards.append('<div class="card-down">'+result['hand'][i]+'</div>');
                }

                if(result.bust === true){
                    if(result.currentSplitHand !== false){
                        // check which hand.
                        // if it's hand 0, then move to hand 1.
                        if(result.currentSplitHand !== 'playerHand'){
                            // todo - need to work out how I can easily determine whether I"m in a split, and if so - which one
                            // I need a function that I can call after the inital split is called
                            // it's called if the hand 0 goes bust OR if hand 0 stands.
                            // it is called when hand 1 goes bust or hand 1 stands
                            // I also need to consider, when I check if my hand is better than the dealer, which ones.
                            // if none are better than the dealer, then I  lose
                            // if one is better than the dealer then I break even
                            // if both hands are better than the dealer, I win big
                        }
                        // if it's hand 1, then move to dealer.
                    }else {
                        $('.message').html('<p>' + result.message + '</p>').show();
                        $('.next-hand').show();
                        $('.hit-button, .stand-button').hide();
                    }
                }
            }
        })
    },
    stand: function(){
        let self = this;
        let splitHand = false;
        // need to understand if I'm standing on a split,
        // and if so,
        //      is it split 0
        //      or split 1

        $.ajax({
            url: "/is-split",
            method: 'get',

            success: function (result) {
                console.log(result);
                splitHand = result;



                // if split 0, then save that hand and move to the next, where I can hit or stand
                // if split 1, then move to the dealer
                if( splitHand !== false && splitHand === 'playerHand' ){
                    //otherwise we've played both hands, so move to the dealer
                    console.log('now playing split hand');
                    self.hit('playerSplitHand');
                }

                // Otherwise, if its not a split, then just move to the dealer
                self.dealerTurn();

            }
        });



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
        $('.player-cards, .player-split-cards, .dealer-cards, .stake-chips').empty();

    }
};

$(document).ready(function () {
    blackjack.app.init();
});