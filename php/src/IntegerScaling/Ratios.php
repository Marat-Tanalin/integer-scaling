<?php

/*! Marat Tanalin | http://tanalin.com */

namespace MaratTanalin\IntegerScaling;

class Ratios
{
	public $x, $y;

	public function __construct(int $x, int $y = 0) {
		$this->x = $x;
		$this->y = 0 === $y ? $x : $y;
	}
}