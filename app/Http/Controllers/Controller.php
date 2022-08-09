<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Http\Request;

use Illuminate\Routing\Controller as BaseController;
use function Termwind\breakLine;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public $cards = [
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
	public $hand = [];

	public $chips = [
		[ 'value' => 5, 'colour' => 'White' ],
		[ 'value' => 10, 'colour' => 'Blue' ],
		[ 'value' => 25, 'colour' => 'Green' ],
		[ 'value' => 50, 'colour' => 'Red' ],
		[ 'value' => 100, 'colour' => 'Black' ],
	];


	public function index()
    {

    	$chips = $this->chipBalance();

	    return view('game');
    }


	/**
	 * @return int
	 * @desc Return how much the user has available
	 */
	public function chipBalance() : int
	{
		// check user has enough chips,  // todo if not - need option to buy back in.
		return auth()->user()->chips;
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

		// now we've collected the bet, continue the game

		$this->deal();

		echo '<pre>';
		var_dump($this->hand['playerCards']);
		echo '</pre>';
		// find the available options (stick, slice, double down, hit)
		$options = $this->bettingOptions($this->hand['playerCards']);

		dd($options);
		return [ 'bet' => $bet, 'hand' => $this->deal(), 'options' =>[] ];
	}

	/**
	 * Pick 2 cards for the dealer, and two for the player
	 */
	public function deal() : array
	{
		// only shuffle before the initial deal
		$this->shuffleDeck();
		$this->hand['playerCards'] = $this->pickCards(2);
		$this->hand['dealerCards'] = $this->pickCards(2);

		return $this->hand;
	}


	/**
	 * @param $num
	 * @desc Pick cards from the deck
	 * @return array
	 */
	public function pickCards($num) : array
	{
		// collect $num cards from the deck
		$cards = array_slice($this->cards,0,$num);
		// remove them from the deck, so they can't be re-retrieved
		$this->cards = array_diff($this->cards,$cards);
		// return the selected cards
		return $cards;

	}

	public function shuffleDeck()
	{
		shuffle($this->cards);
	}

	public function bettingOptions($hand)
	{
		// need to seperate the last character, as that is suit
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
