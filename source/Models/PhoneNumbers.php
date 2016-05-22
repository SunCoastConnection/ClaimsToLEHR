<?php

namespace SunCoastConnection\ClaimsToOEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class PhoneNumbers extends Eloquent {
	protected $table = 'phone_numbers';

	public $timestamps = false;

	// INSERT INTO `phone_numbers` ( `id`, `country_code`, `area_code`, `prefix`, `number`, `type`, `foreign_id`) VALUES

}