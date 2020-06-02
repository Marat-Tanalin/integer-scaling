<?php

/*! IntegerScaling by Marat Tanalin | http://tanalin.com/en/projects/integer-scaling/ */

namespace MaratTanalin\IntegerScaling;

class Size
{
	public $width, $height;

	public function __construct(int $width, int $height) {
		$this->width  = $width;
		$this->height = $height;
	}
}