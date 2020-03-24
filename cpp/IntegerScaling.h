/*! Marat Tanalin | http://tanalin.com/en/projects/integer-scaling/ */

#pragma once

#include <cinttypes>

namespace MaratTanalin {

	class IntegerScaling
	{
	public:
		struct Ratios {
			uint32_t x, y;
		};

		static auto calculateRatio(uint32_t areaWidth, uint32_t areaHeight,
			uint32_t imageWidth, uint32_t imageHeight) -> uint32_t;

		static auto calculateRatios(uint32_t areaWidth, uint32_t areaHeight,
			uint32_t imageWidth, uint32_t imageHeight,
			double aspectX, double aspectY) -> Ratios;
	};

}