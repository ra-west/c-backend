
<?php
namespace Northplay\NorthplayApi\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
class CardShuffler
{
	public function shuffle_deck() {
			$ranks = [2, 3, 4, 5, 6, 7, 8, 9, 10, "Jack", "Queen", "King", "Ace"];
			$suits = ["Spades", "Hearts", "Diamonds", "Clubs"];

			foreach ($suits as $suit)
			{
					foreach ($ranks as $rank)
					{
							$this->deck[] = $rank.":".$suit.":0";
					}
			}

			return shuffle($my_array);
	}



}

