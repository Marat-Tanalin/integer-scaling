<?php

/*! Marat Tanalin | http://tanalin.com */

namespace MaratTanalin;

require_once 'IntegerScaling/Ratios.php';

class IntegerScaling
{
	/**
	 * Calculates an integer scaling ratio common for X/Y axes (square pixels).
	 * 
	 * @param int $areaWidth
	 * @param int $areaHeight
	 * @param int $imageWidth
	 * @param int $imageHeight
	 * @return int;
	 */
	public static function calculateRatio(int $areaWidth, int $areaHeight, int $imageWidth, int $imageHeight) {
		if ($areaHeight * $imageWidth < $areaWidth * $imageHeight) {
			$areaSize  = $areaHeight;
			$imageSize = $imageHeight;
		}
		else {
			$areaSize  = $areaWidth;
			$imageSize = $imageWidth;
		}

		$ratio = floor($areaSize / $imageSize);

		if ($ratio < 1) {
			$ratio = 1;
		}

		return $ratio;
	}

	/**
	 * Calculates integer scaling ratios potentially different for X/Y axes
	 * as a result of aspect-ratio correction (rectangular pixels).
	 * 
	 * @param int $areaWidth
	 * @param int $areaHeight
	 * @param int $imageWidth
	 * @param int $imageHeight
	 * @param float $aspectX
	 * @param float $aspectY
	 * @return array(string => int)
	 */
	public static function calculateRatios(int $areaWidth, int $areaHeight, int $imageWidth, int $imageHeight, float $aspectX = 0.0, float $aspectY = 0.0) {
		if ($imageWidth * $aspectY === $imageHeight * $aspectX) {
			$ratio = self::calculateRatio($areaWidth, $areaHeight, $imageWidth, $imageHeight);

			return new IntegerScaling\Ratios($ratio);
		}

		$maxRatioX        = floor($areaWidth  / $imageWidth);
		$maxRatioY        = floor($areaHeight / $imageHeight);
		$maxWidth         = $imageWidth  * $maxRatioX;
		$maxHeight        = $imageHeight * $maxRatioY;
		$maxWidthAspectY  = $maxWidth  * $aspectY;
		$maxHeightAspectX = $maxHeight * $aspectX;

		if ($maxWidthAspectY === $maxHeightAspectX) {
			$ratioX = $maxRatioX;
			$ratioY = $maxRatioY;
		}
		else {
			$maxAspectLessThanTarget = $maxWidthAspectY < $maxHeightAspectX;

			if ($maxAspectLessThanTarget) {
				$ratioA     = $maxRatioX;
				$maxSizeA   = $maxWidth;
				$imageSizeB = $imageHeight;
				$aspectA    = $aspectX;
				$aspectB    = $aspectY;
			}
			else {
				$ratioA     = $maxRatioY;
				$maxSizeA   = $maxHeight;
				$imageSizeB = $imageWidth;
				$aspectA    = $aspectY;
				$aspectB    = $aspectX;
			}

			$ratioBFract = $maxSizeA * $aspectB / $aspectA / $imageSizeB;
			$ratioBFloor = floor($ratioBFract);
			$ratioBCeil  = ceil($ratioBFract);
			$parFloor    = $ratioBFloor / $ratioA;
			$parCeil     = $ratioBCeil  / $ratioA;

			if ($maxAspectLessThanTarget) {
				$parFloor = 1 / $parFloor;
				$parCeil  = 1 / $parCeil;
			}

			$commonFactor = $imageWidth * $aspectY / $aspectX / $imageHeight;
			$errorFloor   = abs(1 - $commonFactor * $parFloor);
			$errorCeil    = abs(1 - $commonFactor * $parCeil);

			if (abs($errorFloor - $errorCeil) < .001) {
				$ratioB = abs($ratioA - $ratioBFloor) < abs($ratioA - $ratioBCeil)
				        ? $ratioBFloor
				        : $ratioBCeil;
			}
			else {
				$ratioB = $errorFloor < $errorCeil
				        ? $ratioBFloor
				        : $ratioBCeil;
			}

			if ($maxAspectLessThanTarget) {
				$ratioX = $ratioA;
				$ratioY = $ratioB;
			}
			else {
				$ratioX = $ratioB;
				$ratioY = $ratioA;
			}
		}

		if ($ratioX < 1) {
			$ratioX = 1;
		}

		if ($ratioY < 1) {
			$ratioY = 1;
		}

		return new IntegerScaling\Ratios($ratioX, $ratioY);
	}
}