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
	public $playerHand = [];
	public $dealerHand = [];

	public $chips = [
		[ 'value' => 5, 'colour' => 'White' ],
		[ 'value' => 10, 'colour' => 'Blue' ],
		[ 'value' => 25, 'colour' => 'Green' ],
		[ 'value' => 50, 'colour' => 'Red' ],
		[ 'value' => 100, 'colour' => 'Black' ],
	];

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
		// add in validation here, only want bid
		$bet = $request->input('bet');
		// we also need to work out how much they have left and whether they can afford the bet
		// todo for now, I'll just return what they submitted

		// update record in DB.
		// todo check in here, only update if they have the funds.  Otherwise, ask them if they want to refill their pot.
		$user = User::find(auth()->user()->id);
		$user->balance = $user->balance - $bet;
		$user->save();

		// This is the initial deal, so shuffle the deck.
		$this->shuffleDeck();

		// now we've collected the bet, continue the game
		$deal = $this->deal();

		// find the available options (stick, slice, double down, hit)
		$options = $this->bettingOptions( $this->getPlayerHand() );

		return [ 'bet' => $bet, 'hand' => $deal, 'options' => $options ];
	}


	public function split(Request $request) : array
	{
		$user = Auth::user();

		// 1. Check additional funds available // todo this needs to be moved as it's duplicated in DoubleDown
		if($request->bet > $user->balance){
			// the user doesn't have enough cash.
			// todo display message - allow user to purchase additional funds
		}else{
			// If available - Add additional funds
			$bet = ( $request->bet * 2 );
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

	public function doubleDown(Request $request) : array
	{
		$user = Auth::user();

		// Check additional funds available
		if($request->bet > $user->balance){
			// the user doesn't have enough cash.
			// todo display message - allow user to purchase additional funds
		}else{
			// If available - Add additional funds
			$bet = ( $request->bet * 2 );

			// update the balance with the new bet // todo offload this to a service
			$user = User::find(auth()->user()->id);
			$user->balance = $user->balance - $bet;
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
		return [ 'bet' => $bet, 'playerHand' => $this->getPlayerHand(), 'result' => $result ];
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

	/**
	 *
	 */
	public function dealersTurn()
	{
		// The dealer does NOT get to choose how to play.
		// If their total is less than 17, they hit.
		// If their total is 17 or higher, they stand.

		if( $this->calculateScore($this->getDealerHand() ) > 16 ){ // stand
			return ['status'=>'stand','hand' => $this->getDealerHand()];
		}else{
			// hit
			$newHand = array_merge($this->getDealerHand(),$this->pickCards(1));
			$this->setDealerHand($newHand);

			// if hand is greater than 21   - then bust
			// if hand is greater than 17   - then stand
			// if hand is less than 17      - then hit again
			$bust = ( ( $this->calculateScore($this->getPlayerHand() ) > 21 )? true : false );
			return [ 'bust' => $bust, 'hand' => $this->getDealerHand() ];
		}

	}
}
