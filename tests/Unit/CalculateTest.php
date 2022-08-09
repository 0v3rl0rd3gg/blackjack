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
}
