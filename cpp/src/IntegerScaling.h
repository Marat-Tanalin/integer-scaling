/*! Marat Tanalin | http://tanalin.com | 2019 */

#pragma once

#include <cinttypes>

namespace MaratTanalin {

	class IntegerScaling
	{
	public:
		struct Ratios {
			uint32_t x, y;
		};

		auto calculateRatio(uint32_t areaWidth, uint32_t areaHeight,
			uint32_t imageWidth, uint32_t imageHeight) -> uint32_t;

		auto calculateRatios(uint32_t areaWidth, uint32_t areaHeight,
			uint32_t imageWidth, uint32_t imageHeight,
			double aspectX, double aspectY) -> Ratios;
	};

}