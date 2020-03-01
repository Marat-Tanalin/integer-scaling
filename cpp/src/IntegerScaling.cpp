/*! Marat Tanalin | http://tanalin.com | 2019 */

#include <cmath>

#include "IntegerScaling.h"

namespace MaratTanalin {

	/**
	 * Calculates an integer scaling ratio common for X/Y axes (square pixels).
	 */
	auto IntegerScaling::calculateRatio(uint32_t areaWidth, uint32_t areaHeight,
		uint32_t imageWidth, uint32_t imageHeight) -> uint32_t
	{
		uint32_t areaSize, imageSize;

		if (areaHeight * imageWidth < areaWidth * imageHeight) {
			areaSize  = areaHeight;
			imageSize = imageHeight;
		}
		else {
			areaSize  = areaWidth;
			imageSize = imageWidth;
		}

		uint32_t ratio = areaSize / imageSize;

		if (ratio < 1) {
			ratio = 1;
		}

		return ratio;
	}

	/**
	 * Calculates integer scaling ratios potentially different for X/Y axes
	 * as a result of aspect-ratio correction (rectangular pixels).
	 */
	auto IntegerScaling::calculateRatios(uint32_t areaWidth, uint32_t areaHeight, 
		uint32_t imageWidth, uint32_t imageHeight,
		double aspectX, double aspectY) -> Ratios
	{
		if (imageWidth * aspectY == imageHeight * aspectX) {
			auto ratio = calculateRatio(areaWidth, areaHeight, imageWidth, imageHeight);
			return {ratio, ratio};
		}

		uint32_t maxRatioX        = areaWidth  / imageWidth,
		         maxRatioY        = areaHeight / imageHeight,
		         maxWidth         = imageWidth  * maxRatioX,
		         maxHeight        = imageHeight * maxRatioY;

		double maxWidthAspectY  = maxWidth  * aspectY,
		       maxHeightAspectX = maxHeight * aspectX;

		uint32_t ratioX, ratioY;

		if (maxWidthAspectY == maxHeightAspectX) {
			ratioX = maxRatioX;
			ratioY = maxRatioY;
		}
		else {
			bool maxAspectLessThanTarget = maxWidthAspectY < maxHeightAspectX;

			uint32_t ratioA, maxSizeA, imageSizeB;
			double aspectA, aspectB;

			if (maxAspectLessThanTarget) {
				ratioA     = maxRatioX;
				maxSizeA   = maxWidth;
				imageSizeB = imageHeight;
				aspectA    = aspectX;
				aspectB    = aspectY;
			}
			else {
				ratioA     = maxRatioY;
				maxSizeA   = maxHeight;
				imageSizeB = imageWidth;
				aspectA    = aspectY;
				aspectB    = aspectX;
			}

			double ratioBFract = maxSizeA * aspectB / aspectA / imageSizeB,
			       ratioBFloor = floor(ratioBFract),
			       ratioBCeil  = ceil(ratioBFract),
			       parFloor    = ratioBFloor / ratioA,
			       parCeil     = ratioBCeil  / ratioA;

			if (maxAspectLessThanTarget) {
				parFloor = 1.0 / parFloor;
				parCeil  = 1.0 / parCeil;
			}

			double commonFactor = imageWidth * aspectY / aspectX / imageHeight,
			       errorFloor   = abs(1.0 - commonFactor * parFloor),
			       errorCeil    = abs(1.0 - commonFactor * parCeil);

			uint32_t ratioB;

			if (abs(errorFloor - errorCeil) < .001) {
				ratioB = abs(ratioA - ratioBFloor) < abs(ratioA - ratioBCeil)
				       ? ratioBFloor
				       : ratioBCeil;
			}
			else {
				ratioB = errorFloor < errorCeil
				       ? ratioBFloor
				       : ratioBCeil;
			}

			if (maxAspectLessThanTarget) {
				ratioX = ratioA;
				ratioY = ratioB;
			}
			else {
				ratioX = ratioB;
				ratioY = ratioA;
			}
		}

		if (ratioX < 1) {
			ratioX = 1;
		}

		if (ratioY < 1) {
			ratioY = 1;
		}

		return {
			ratioX,
			ratioY
		};
	}

}