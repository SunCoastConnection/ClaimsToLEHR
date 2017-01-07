<?php

namespace SunCoastConnection\ClaimsToEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Addresses extends Eloquent {
	protected $table = 'addresses';

	public $timestamps = false;

	// INSERT INTO `addresses` (`id`, `line1`, `line2`, `city`, `state`, `zip`, `plus_four`, `country`, `foreign_id`) VALUES

}