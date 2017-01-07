<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837;

use \SunCoastConnection\ClaimsToEMR\Document\Section\Root;

// TODO: Cleanup, rename, and replace properties with sub-objects
class Document extends Root {

	static protected $descendantSequence = [
		['name' => 'InterchangeControl', 'required' => true, 'repeat' => 1],
	];

}