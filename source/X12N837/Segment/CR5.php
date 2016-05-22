<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

// D.1	Global Changes
// 166. The Home Oxygen Therapy Information (CR5) segment was removed.
class CR5 extends Segment {

	static protected $elementSequence = [];

	static protected $elementNames = [];

}