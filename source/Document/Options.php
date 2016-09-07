<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \Illuminate\Config\Repository;

class Options extends Repository {

	static public function getInstance(array $options) {
		return new static($options);
	}

}