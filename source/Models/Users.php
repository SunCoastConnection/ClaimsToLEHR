<?php

namespace SunCoastConnection\ClaimsToOEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Users extends Eloquent {
	protected $table = 'users';

	public $timestamps = false;

	// INSERT INTO `users` (`id`,`username`,`password`,`authorized`,`fname`,`mname`,`lname`,`federaltaxid`,`federaldrugid`,`facility`,`facility_id`,`see_auth`,`active`,`npi`,`cal_ui`,`taxonomy`,`calendar`,`abook_type`,`state_license_number`) VALUES

}