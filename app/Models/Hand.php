<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hand extends Model
{
    use HasFactory;

	protected $fillable = [
		'card_1',
		'card_2',
		'card_3',
		'card_4',
		'card_5',
		'hand_value',
		'is_split',
		'is_double_down',
	];

	public static function getCurrentHand(User $user, $split) : Collection
	{
		$limit = ( ( $split )? 2 : 1 ); // if current hand is split - return both hands

		return Hand::where('user_id', $user->id)
			->latest()
			->take($limit)
			->orderBy('created_at','DESC')
			->get();
	}
}
