<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use PHPUnit\Framework\TestCase;

class CalculateTest extends TestCase
{

	public function test_calculateSplit_Fails()
	{
		$hand = ['AH','KD'];
		$controller = new Controller();
		$hand = $controller->removeSuit($hand);

		$this->assertFalse($controller->calculateSplit($hand));
	}


    public function test_calculateDoubleDown_FailsWithSoftHand()
    {
	    $softHand = ['A',2];
	    $controller = new Controller();
	    $sanitisedCards = $controller->convertPictureCards($softHand);

	    $this->assertFalse($controller->calculateDoubleDown($sanitisedCards));
    }

	public function test_calculateDoubleDown_PassWithSoftHand()
	{
		$softHand = ['A',7];
		$controller = new Controller();
		$sanitisedCards = $controller->convertPictureCards($softHand);

		$this->assertTrue($controller->calculateDoubleDown($sanitisedCards));
	}


	public function test_calculateDoubleDown_PassWithHardHand()
	{
		$softHand = [2,7];
		$controller = new Controller();
		$sanitisedCards = $controller->convertPictureCards($softHand);

		$this->assertTrue($controller->calculateDoubleDown($sanitisedCards));
	}

	public function test_calculateDoubleDown_FailWithHardHand()
	{
		$softHand = [6,7];
		$controller = new Controller();
		$sanitisedCards = $controller->convertPictureCards($softHand);

		$this->assertFalse($controller->calculateDoubleDown($sanitisedCards));
	}

	public function test_softOrHard_Fail()
	{
		$hand = ['A',7];
		$controller = new Controller();
		$this->assertTrue($controller->softOrHard($hand));
	}

	public function test_softOrHard_Pass()
	{
		$hand = ['K',7];
		$controller = new Controller();
		$this->assertFalse($controller->softOrHard($hand));
	}

	public function test_calculateSplit_PairAcesPass()
	{
		$hand = ['A','A'];
		$controller = new Controller();
		$this->assertTrue($controller->calculateSplit($hand));
	}

	public function test_calculateSplit_OddHandPictureFail()
	{
		$hand = ['A','K'];
		$controller = new Controller();
		$this->assertFalse($controller->calculateSplit($hand));
	}

	public function test_calculateSplit_OddHandNumericFail()
	{
		$hand = [8,9];
		$controller = new Controller();
		$this->assertFalse($controller->calculateSplit($hand));
	}

	public function test_calculateSplit_MatchingHandNumericPass()
	{
		$hand = [8,8];
		$controller = new Controller();
		$this->assertTrue($controller->calculateSplit($hand));
	}

	public function test_convertPictureCards_With_King()
	{
		$hand = ['K',8];
		$output = [10,8];
		$controller = new Controller();
		$convertedHand = $controller->convertPictureCards($hand);
		$this->assertEquals($output,$convertedHand);
	}


	public function test_calculateScore_Safe()
	{
		$controller = new Controller();
		$handOfTwenty = ['AK','AS','5S','6C','7D'];    // 20
		$handOfTwentyOne = ['AS','AD','5C','6D','8S']; // 21
		$handOfSix = ['AS','AD','AC','AH','2C'];   // 16
		$handOfTwelve = ['AS','AD','AC','AH','8C'];   // 12
		$handOfTwentyOneAces = ['AS','AC','AD','AH','7H'];   // 21
		$handOfTwentyOneKing = ['KH','AS'];   // 21
		$handOfTwentyOneTen = ['10H','AS'];     // 21
		$handOfTwentyOneTwoPictures = ['JH','AS','KD'];  // 21

		$scoreOfTwenty = $controller->calculateScore($handOfTwenty);
		$this->assertEquals($scoreOfTwenty,20);

		$scoreOfTwentyOne = $controller->calculateScore($handOfTwentyOne);
		$this->assertEquals($scoreOfTwentyOne,21);

		$scoreOfSix = $controller->calculateScore($handOfSix);
		$this->assertEquals($scoreOfSix,16);

		$scoreOfTwelve = $controller->calculateScore($handOfTwelve);
		$this->assertEquals($scoreOfTwelve,12);

		$scoreOfTwentyOneAces = $controller->calculateScore($handOfTwentyOneAces);
		$this->assertEquals($scoreOfTwentyOneAces,21);

		$scoreOfTwentyOneKing = $controller->calculateScore($handOfTwentyOneKing);
		$this->assertEquals($scoreOfTwentyOneKing,21);

		$scoreOfTwentyOneTen = $controller->calculateScore($handOfTwentyOneTen);
		$this->assertEquals($scoreOfTwentyOneTen,21);

		$scoreOfTwentyOneTwoPictures = $controller->calculateScore($handOfTwentyOneTwoPictures);
		$this->assertEquals($scoreOfTwentyOneTwoPictures,21);

	}

	public function test_calculateScore_Bust()
	{
		$controller = new Controller();
		$handOfTwentyTwo = ['AH','AS','5S','6D','9C'];  // 22
		$handOfTwentyTwoKing = ['3D','AS','10H', 'QC'];  // 24

		$scoreOfTwentyTwo = $controller->calculateScore($handOfTwentyTwo);
		$this->assertEquals($scoreOfTwentyTwo, 22);

		$scoreOfTwentyTwoKing = $controller->calculateScore($handOfTwentyTwoKing);
		$this->assertEquals($scoreOfTwentyTwoKing, 24 );
	}
}
