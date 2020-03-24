/*! IntegerScaling by Marat Tanalin | http://tanalin.com/en/projects/integer-scaling/ */

var IntegerScaling;

(function() {
	/**
	 * Calculates an integer scaling ratio common for X/Y axes (square pixels).
	 * 
	 * @param {number} areaWidth
	 * @param {number} areaHeight
	 * @param {number} imageWidth
	 * @param {number} imageHeight
	 * @returns {number}
	 */
	var calculateRatio = function(areaWidth, areaHeight, imageWidth, imageHeight) {
		var areaSize, imageSize;

		if (areaHeight * imageWidth < areaWidth * imageHeight) {
			areaSize  = areaHeight;
			imageSize = imageHeight;
		}
		else {
			areaSize  = areaWidth;
			imageSize = imageWidth;
		}

		var ratio = Math.floor(areaSize / imageSize);
		
		if (ratio < 1) {
			ratio = 1;
		}

		return ratio;
	};

	/**
	 * @param {number} x
	 * @param {number} [y]
	 * @returns {Object}
	 */
	var createRatiosObject = function(x, y) {
		if ('undefined' === typeof y) {
			y = x;
		}

		return {
			x: x,
			y: y
		};
	};

	/**
	 * Calculates integer scaling ratios potentially different for X/Y axes
	 * as a result of aspect-ratio correction (rectangular pixels).
	 * 
	 * @param {number} areaWidth
	 * @param {number} areaHeight
	 * @param {number} imageWidth
	 * @param {number} imageHeight
	 * @param {number} aspectX
	 * @param {number} aspectY
	 * @returns {Object}
	 */
	var calculateRatios = function(areaWidth, areaHeight, imageWidth, imageHeight, aspectX, aspectY) {
		if (imageWidth * aspectY === imageHeight * aspectX) {
			return createRatiosObject(calculateRatio(areaWidth, areaHeight, imageWidth, imageHeight));
		}

		var maxRatioX        = Math.floor(areaWidth  / imageWidth),
		    maxRatioY        = Math.floor(areaHeight / imageHeight),
		    maxWidth         = imageWidth  * maxRatioX,
		    maxHeight        = imageHeight * maxRatioY,
		    maxWidthAspectY  = maxWidth  * aspectY,
		    maxHeightAspectX = maxHeight * aspectX;

		var ratioX, ratioY;

		if (maxWidthAspectY === maxHeightAspectX) {
			ratioX = maxRatioX;
			ratioY = maxRatioY;
		}
		else {
			var maxAspectLessThanTarget = maxWidthAspectY < maxHeightAspectX;

			var ratioA, maxSizeA, imageSizeB, aspectA, aspectB;

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

			var ratioBFract = maxSizeA * aspectB / aspectA / imageSizeB,
			    ratioBFloor = Math.floor(ratioBFract),
			    ratioBCeil  = Math.ceil(ratioBFract),
			    parFloor    = ratioBFloor / ratioA,
			    parCeil     = ratioBCeil  / ratioA;

			if (maxAspectLessThanTarget) {
				parFloor = 1 / parFloor;
				parCeil  = 1 / parCeil;
			}

			var commonFactor = imageWidth * aspectY / aspectX / imageHeight,
			    errorFloor   = Math.abs(1 - commonFactor * parFloor),
			    errorCeil    = Math.abs(1 - commonFactor * parCeil);

			var ratioB;

			if (Math.abs(errorFloor - errorCeil) < .001) {
				ratioB = Math.abs(ratioA - ratioBFloor) < Math.abs(ratioA - ratioBCeil)
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

		return createRatiosObject(ratioX, ratioY);
	};

	IntegerScaling = {
		calculateRatio  : calculateRatio,
		calculateRatios : calculateRatios
	};
})();