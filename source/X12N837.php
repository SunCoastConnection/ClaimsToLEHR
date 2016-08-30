<?php

namespace SunCoastConnection\ClaimsToOEMR;

use \SunCoastConnection\ClaimsToOEMR\Document\Section\Root;

// TODO: Cleanup, rename, and replace properties with sub-objects
class X12N837 extends Root {

	static protected $descendantSequence = [
		['name' => 'InterchangeControl', 'required' => true, 'repeat' => 1],
	];

}