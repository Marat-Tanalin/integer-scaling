#![allow(non_snake_case)]

pub fn calculate_ratio(area_width : u32, area_height : u32,
	image_width : u32, image_height : u32) -> u32
{
	IntegerScaling::calculateRatio(area_width, area_height, image_width, image_height)
}

pub fn calculate_ratios(area_width : u32, area_height : u32,
	image_width : u32, image_height : u32,
	aspect_x : f64, aspect_y : f64) -> IntegerScaling::Ratios
{
	IntegerScaling::calculateRatios(area_width, area_height, image_width, image_height, aspect_x, aspect_y)
}