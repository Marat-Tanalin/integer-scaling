#![allow(non_snake_case)]

pub struct Ratios {
	pub x : u32,
	pub y : u32
}

/// Calculates an integer scaling ratio common for X/Y axes (square pixels).
pub fn calculateRatio(areaWidth : u32, areaHeight : u32,
	imageWidth : u32, imageHeight : u32) -> u32
{
	let (areaSize, imageSize);

	if areaHeight * imageWidth < areaWidth * imageHeight {
		areaSize  = areaHeight;
		imageSize = imageHeight;
	}
	else {
		areaSize  = areaWidth;
		imageSize = imageWidth;
	}

	let mut ratio = areaSize / imageSize;

	if ratio < 1 {
		ratio = 1;
	}

	ratio
}

/// Calculates integer scaling ratios potentially different for X/Y axes
/// as a result of aspect-ratio correction (rectangular pixels).
pub fn calculateRatios(areaWidth : u32, areaHeight : u32,
	imageWidth : u32, imageHeight : u32,
	aspectX : f64, aspectY : f64) -> Ratios
{
	if imageWidth as f64 * aspectY == imageHeight as f64 * aspectX {
		let ratio = calculateRatio(areaWidth, areaHeight, imageWidth, imageHeight);

		return Ratios {
			x: ratio,
			y: ratio
		};
	}

	let maxRatioX        = areaWidth  / imageWidth;
	let maxRatioY        = areaHeight / imageHeight;
	let maxWidth         = imageWidth  * maxRatioX;
	let maxHeight        = imageHeight * maxRatioY;
	let maxWidthAspectY  = maxWidth  as f64 * aspectY;
	let maxHeightAspectX = maxHeight as f64 * aspectX;

	let mut ratioX : u32;
	let mut ratioY : u32;

	if maxWidthAspectY == maxHeightAspectX {
		ratioX = maxRatioX;
		ratioY = maxRatioY;
	}
	else {
		let maxAspectLessThanTarget = maxWidthAspectY < maxHeightAspectX;

		let (ratioA, maxSizeA, imageSizeB, aspectA, aspectB) : (u32, u32, u32, f64, f64);

		if maxAspectLessThanTarget {
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

		let ratioBFract = maxSizeA as f64 * aspectB / aspectA / imageSizeB as f64;
		let ratioBFloor = ratioBFract.floor();
		let ratioBCeil  = ratioBFract.ceil();

		let mut parFloor = ratioBFloor / ratioA as f64;
		let mut parCeil  = ratioBCeil  / ratioA as f64;

		if maxAspectLessThanTarget {
			parFloor = 1.0 / parFloor;
			parCeil  = 1.0 / parCeil;
		}

		let commonFactor = imageWidth as f64 * aspectY / aspectX / imageHeight as f64;
		let errorFloor   = (1.0 - commonFactor * parFloor).abs();
		let errorCeil    = (1.0 - commonFactor * parCeil).abs();

		let ratioB : u32;

		if (errorFloor - errorCeil).abs() < 0.001 {
			ratioB = if (ratioA as f64 - ratioBFloor).abs() < (ratioA as f64 - ratioBCeil).abs()
			         {ratioBFloor as u32}
			         else
			         {ratioBCeil as u32};
		}
		else {
			ratioB = if errorFloor < errorCeil
			         {ratioBFloor as u32}
			         else
			         {ratioBCeil as u32};
		}

		if maxAspectLessThanTarget {
			ratioX = ratioA;
			ratioY = ratioB;
		}
		else {
			ratioX = ratioB;
			ratioY = ratioA;
		}
	}

	if ratioX < 1 {
		ratioX = 1;
	}

	if ratioY < 1 {
		ratioY = 1;
	}

	Ratios {
		x: ratioX,
		y: ratioY
	}
}