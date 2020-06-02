<?php

/*! IntegerScaling by Marat Tanalin | http://tanalin.com/en/projects/integer-scaling/ */

namespace MaratTanalin;

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
	 * @return IntegerScaling\Ratios
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

	/**
	 * Calculates size (width and height) of scaled image
	 * without aspect-ratio correction (square pixels).
	 * 
	 * @param int $areaWidth
	 * @param int $areaHeight
	 * @param int $imageWidth
	 * @param int $imageHeight
	 * @return IntegerScaling\Size
	 */
	public static function calculateSize(int $areaWidth, int $areaHeight, int $imageWidth, int $imageHeight) {
		$ratio = self::calculateRatio($areaWidth, $areaHeight, $imageWidth, $imageHeight);

		return new IntegerScaling\Size(
			$imageWidth  * $ratio,
			$imageHeight * $ratio
		);
	}

	/**
	 * Calculates size (width and height) of scaled image
	 * with aspect-ratio correction (rectangular pixels).
	 * 
	 * @param int $areaWidth
	 * @param int $areaHeight
	 * @param int $imageWidth
	 * @param int $imageHeight
	 * @param float $aspectX
	 * @param float $aspectY
	 * @return IntegerScaling\Size
	 */
	public static function calculateSizeCorrected(int $areaWidth, int $areaHeight,
		int $imageWidth, int $imageHeight, float $aspectX = 0.0, float $aspectY = 0.0)
	{
		$ratios = self::calculateRatios($areaWidth, $areaHeight, $imageWidth, $imageHeight, $aspectX, $aspectY);

		return new IntegerScaling\Size(
			$imageWidth  * $ratios->x,
			$imageHeight * $ratios->y
		);
	}

	/**
	 * Calculates size (width and height) of scaled image with aspect-ratio
	 * correction with integer vertical scaling ratio, but fractional horizontal
	 * scaling ratio for the purpose of achieving precise aspect ratio while
	 * still having integer vertical scaling e.g. for uniform scanlines.
	 * 
	 * @param int $areaWidth
	 * @param int $areaHeight
	 * @param int $imageWidth
	 * @param int $imageHeight
	 * @param float $aspectX
	 * @param float $aspectY
	 * @return IntegerScaling\Size
	 */
	public static function calculateSizeCorrectedPerfectY(int $areaWidth, int $areaHeight, int $imageHeight, float $aspectX, float $aspectY) {
		$imageWidth = $imageHeight * $aspectX / $aspectY;

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

		$width = round($imageWidth * $ratio);

		if ($width > $areaWidth) {
			$width--;
		}

		return new IntegerScaling\Size(
			$width,
			$imageHeight * $ratio
		);
	}
}