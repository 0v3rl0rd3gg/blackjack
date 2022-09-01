<?php

namespace App\Http\Controllers;

use App\Library\Balance;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Http\Request;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use function Termwind\breakLine;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public $deck = [
		'AH',
		'2H',
		'3H',
		'4H',
		'5H',
		'6H',
		'7H',
		'8H',
		'9H',
		'10H',
		'JH',
		'QH',
		'KH',
		'AD',
		'2D',
		'3D',
		'4D',
		'5D',
		'6D',
		'7D',
		'8D',
		'9D',
		'10D',
		'JD',
		'QD',
		'KD',
		'AS',
		'2S',
		'3S',
		'4S',
		'5S',
		'6S',
		'7S',
		'8S',
		'9S',
		'10S',
		'JS',
		'QS',
		'KS',
		'AC',
		'2C',
		'3C',
		'4C',
		'5C',
		'6C',
		'7C',
		'8C',
		'9C',
		'10C',
		'JC',
		'QC',
		'KC'
	];

	public $chips = [
		[ 'value' => 5, 'colour' => 'White' ],
		[ 'value' => 10, 'colour' => 'Blue' ],
		[ 'value' => 25, 'colour' => 'Green' ],
		[ 'value' => 50, 'colour' => 'Red' ],
		[ 'value' => 100, 'colour' => 'Black' ],
	];

	public CONST WINNER_MESSAGE = 'Congratulations, you\'ve won.  Fancy another game?';
	public CONST LOSER_MESSAGE  = 'Unlucky, the Dealer won that hand.  Fancy another game?';
	public CONST DRAW_MESSAGE  = 'That hand was a draw.  Fancy another game?';

	public function index()
    {
    	$balance = $this->chipBalance();
	    return view('game',[ 'balance' => $balance]);
    }


	/**
	 * @return int
	 * @desc Return how much the user has available
	 */
	public function chipBalance() : int
	{
		// check user has enough chips,  // todo if not - need option to buy back in.
		return auth()->user()->balance;
	}

	/**
	 * @param Request $request
	 * @desc Player to define how much they want to bet on next hand
	 * @return array
	 */
	public function postBet(Request $request) : array
	{
		// This is the initial deal, so shuffle the deck.
		$this->shuffleDeck();

		// add in validation here, only want bid
		$this->setBet( $request->input('bet') );
		// we also need to work out how much they have left and whether they can afford the bet
		// todo for now, I'll just return what they submitted

		// update record in DB.
		// todo check in here, only update if they have the funds.  Otherwise, ask them if they want to refill their pot.
		$user = User::find(auth()->user()->id);
		$user->balance = $user->balance - $this->getBet();
		$user->save();

		// now we've collected the bet, continue the game
		$hand = $this->deal();

		// find the available options (stick, slice, double down, hit)
		$options = $this->bettingOptions( $this->getPlayerHand() );

		return [ 'bet' => $this->getBet(), 'hand' => $hand, 'balance' => $user->balance, 'options' => $options ];
	}


	/**
	 * @return array
	 */
	public function dealersTurn()
	{
		$score = $this->calculateScore( $this->getDealerHand() );

		if( $score > 21 ) { // bust
			return $this->dealerResult(true);
		}elseif( $score > 16 ){ // stand
			return $this->dealerResult(false);
		}else{ // if the score is 16 or less, then HIT
			$newHand = array_merge($this->getDealerHand(),$this->pickCards(1));
			$this->setDealerHand($newHand);

			$score = $this->calculateScore( $this->getDealerHand() );

			if( $score <= 16 ){
				return $this->dealersTurn();
			}
			elseif( $score > 21 ) { // bust
				return $this->dealerResult(true);
			}
			else{
				return $this->dealerResult(false);
			}
		}
	}

	/**
	 * @param bool $bust
	 *
	 * @return array
	 */
	public function dealerResult( bool $bust )
	{
		$winnings   = 0;
		$winner     = 'player';
		$user       = User::find( auth()->user()->id );

		if( $bust === true ) { // Dealer is bust, Player collects winnings
			$winnings = $this->getBet() * 2;
			$user->balance = $user->balance + $winnings; // Win your stake + equal amount back.
			$user->save();
		}else{ // Dealer is not bust, so calculate winner
			$winner = $this->calculateWinner();
		}

		// todo at the moment, this is a turnary calc, it needs to consider draws.
		$message = '';

		// todo also - I got blackJack (see screenshot), but the computer beat me on 21.  This should not have happened.  Even if we're tied, I win
		// todo Maybe have a check after the initial deal.  If it's blackjack - end it there.
		return [
			'winner'    => $winner,
	         'hand'     => $this->getDealerHand(),
	         'balance'  => $user->balance,
	         'winnings' => $winnings,
			 'message'  => ( ( $winner == 'player')? self::WINNER_MESSAGE : self::LOSER_MESSAGE ),
			 'dealerScore' => $this->calculateScore($this->getDealerHand()),
			 'playerScore' => $this->calculateScore($this->getplayerHand())
		];
	}

	public function calculateWinner()
	{
		// If we're calling this function, we know both the player and dealer have not gone bust.
		//  So we need to calculate both scores, and see who has the higher score.

		$playerHandScore = $this->calculateScore($this->getPlayerHand());
		$dealerHandScore = $this->calculateScore($this->getDealerHand());


		$user = User::find( auth()->user()->id );

		if( $playerHandScore > $dealerHandScore ){ // Player wins
			$winner = 'player';
			// update pot with winnings
			$user->balance = $user->balance + ( $this->getBet() * 2 ); // Win your stake + equal amount back.
			$user->save();
		}elseif( $dealerHandScore === $playerHandScore ){ // Draw
			$winner = 'draw';
			$user->balance = $user->balance + $this->getBet(); // receive original bet back
			$user->save();
		}
		else{
			$winner = 'dealer';
		}
		return [ 'winner' => $winner, 'balance' => $user->balance ];
	}


	public function hit()
	{
		$newHand = array_merge($this->getPlayerHand(), $this->pickCards(1));
		$this->setPlayerHand($newHand);

		$score = $this->calculateScore( $this->getPlayerHand() );
		$bust = ( ( $score > 21 )? true : false );
		return [
			'hand' => $this->getPlayerHand(),
			'bust' => $bust,
			'message' => ( ( $bust )? self::LOSER_MESSAGE : '' ),
			'score' => $score
		];
	}

	public function split() : array
	{
		$user = Auth::user();

		// 1. Check additional funds available // todo this needs to be moved as it's duplicated in DoubleDown
		if($this->getBet() > $user->balance){
			// the user doesn't have enough cash.
			// todo display message - allow user to purchase additional funds
		}else{
			// If available - Add additional funds
			$this->setBet( $this->getBet() * 2 );
		}
		// 4. Separate cards
			// need to create a Hand_1 and Hand
		// 5. Deal a card to the A hand
		// 6. Give the option to Hit or Stand
		// 7. If Hit - deal another card
		// 8. If not bust, give option to Hit or Stand
		// 9. If Bust or Stand - then move to the next card
		// 10. Go back to step 4.
		// 11. Once finished on the second hand
		// 12. Move to the Dealer
	}

	public function doubleDown() : array
	{
		$user = Auth::user();

		// Check additional funds available
		if($this->getBet() > $user->balance){
			// the user doesn't have enough cash.
			// todo display message - allow user to purchase additional funds
		}else{
			// If available - Add additional funds
			$this->setBet( $this->getBet() * 2);

			// update the balance with the new bet // todo offload this to a service
			$user = User::find(auth()->user()->id);
			$user->balance = $user->balance .'+'. $this->getBet();
			$user->save();
		}
		// Deal 1 card, lay it over the other two
		// Add new card to existing hand
		$newHand = array_merge($this->getPlayerHand(),$this->pickCards(1));
		$this->setPlayerHand($newHand);
		// Calculate if they have gone bust

		$result = ( ( $this->calculateScore($this->getPlayerHand() ) > 21 )? false : true );

		// 5. Return the card and move to the dealer

		// 6. Move play to the dealer
		return [ 'bet' => $this->getBet(), 'playerHand' => $this->getPlayerHand(), 'result' => $result ];
	}

	/**
	 * @param $hand
	 *
	 * @return float|int
	 */
	public function calculateScore($hand)
	{
		$suitRemoved = $this->removeSuit($hand);
		$convertedCards = $this->convertPictureCards($suitRemoved); // removeSuit

		// Find out if there are Aces in the hand
		if( $acesCount = count( array_keys($convertedCards, 'A') ) ){  // are there any aces
			$aces  = ['A']; // set the array up to remove aces from the deck
			$score = array_sum( array_diff( $convertedCards, $aces ) ); // calculate the score of the remaining cards (minus the ace(s))

			$scoreWithAcesAsOne = $score + $acesCount;
			$scoreWithAcesAsEleven = ( $score + $acesCount ) + 10;

			if( $scoreWithAcesAsEleven > 21){ // Do you bust with Aces as 11's
				return $scoreWithAcesAsOne; // If so, return the score with Ace's as 1's
			}else{
				return $scoreWithAcesAsEleven;
			}
		}
		else{ // No aces in the hand, so just return the sum of the hand
		    return array_sum($convertedCards);
		}
	}

	/**
	 * Pick 2 cards for the dealer, and two for the player
	 */
	public function deal() : array
	{
		$this->setPlayerHand($this->pickCards(2));
		$this->setDealerHand($this->pickCards(2));

		return [
			'playerHand' => $this->getPlayerHand(),
			'dealerHand' => $this->getDealerHand()
		];
	}

	public function getBet()
	{
		return session('bet');
	}

	public function setBet($bet)
	{
		session(['bet' => $bet]);
	}

	public function getPlayerHand()
	{
		return session('playerHand');
	}

	public function setPlayerHand($cards)
	{
		session(['playerHand' => $cards]);
	}

	public function getDealerHand()
	{
		return session('dealerHand');
	}

	public function setDealerHand($cards)
	{
		session(['dealerHand' => $cards]);
	}

	public function getDeck()
	{
		return session('deck');
	}

	public function setDeck($deck)
	{
		session(['deck' => $deck]);
	}

	/**
	 * @param $num
	 * @desc Pick cards from the deck
	 * @return array
	 */
	public function pickCards($num) : array
	{
		// collect $num cards from the deck
		$cards = array_slice($this->getDeck(),0,$num);
		// remove them from the deck, so they can't be re-retrieved
		$this->setDeck( array_diff( $this->getDeck(),$cards ) );
		// return the selected cards
		return $cards;
	}

	/**
	 *
	 */
	public function shuffleDeck()
	{
		$shuffledDeck = $this->deck;
		shuffle($shuffledDeck);
		$this->setDeck( $shuffledDeck );
	}

	public function bettingOptions($hand)
	{
		// need to separate the last character, as that is suit
		// (e.g. 7D, 10H, KS need to become 7, 10, K)
		// Also need to convert K, J, Q to 10)

		$hand = $this->removeSuit($hand);
		$options['split'] = $this->calculateSplit($hand);

		$sanitisedCards = $this->convertPictureCards($hand);
		$options['double'] = $this->calculateDoubleDown($sanitisedCards);
		return $options;
	}

	public function removeSuit($hand)
	{
		$cards = [];
		foreach($hand as $key => $card) {
			// remove suit
			$cards[] = substr( $card, 0, - 1 );
		}
		return $cards;
	}

	/**
	 * @param $hand
	 *
	 * @return bool
	 */
	public function calculateDoubleDown($hand) : bool
	{
		$soft = $this->softOrHard($hand);

		// If it's hard then we need to work out if it can make 9, 10 or 11
		if($soft === false){
			$handTotal = $hand[0] + $hand[1];
			if( $handTotal > 8 && $handTotal < 12 ){
				return true;
			}
		}
		// if it's soft then we need to work out if it can make 16, 17 or 18
		else{
			foreach($hand as $card){
				if( $card > 4 && $card < 8 ){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Determine if the player has BlackJack
	 */
	public function calculateBlackJack()
	{
		// if cards in hand == 2 AND it's soft AND other card value is === 10

	}

	/**
	 * @param $hand
	 *
	 * @return bool
	 * @desc check to see if the hand contains an ace
	 */
	public function softOrHard($hand) : bool
	{
		return in_array('A',$hand);
	}

	/**
	 * @param $hand
	 *
	 * @return bool
	 */
	public function calculateSplit($hand)
	{
		return ( ( $hand[0] === $hand[1] )? true : false );
	}

	/**
	 * @param $hand
	 *
	 * @return array
	 */
	public function convertPictureCards($hand)
	{
		$cards = [];
		foreach($hand as $card) {
			// define picture cards
			$pictureCards = 'K,Q,J';
			// If card is an ace, return an ace,
			// if it's a King Queen or Jack, convert to a 10
			// Otherwise return the card number as an int
			if($card == 'A'){
				$cards[] = 'A';
			}elseif( stripos( $pictureCards, $card ) !== false ){
				$cards[] = 10;
			}else{
				$cards[] = (int) $card;
			}
		}
		return $cards;
	}
}
