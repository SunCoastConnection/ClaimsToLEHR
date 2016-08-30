<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

// D.1	Global Changes
// 92. The Home Health Care Plan Information Loop (Loop ID-2305) has been de-
//     leted. This loop included the CR7 and HSD segments.
// 195. The Health care Services Delivery (HSD) segment has been removed.
class HSD extends Segment {

	static protected $elementSequence = [];

	static protected $elementNames = [];

}