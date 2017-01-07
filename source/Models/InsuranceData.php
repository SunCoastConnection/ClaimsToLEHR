<?php

namespace SunCoastConnection\ClaimsToEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class InsuranceData extends Eloquent {
	protected $table = 'insurance_data';

	public $timestamps = false;

	// INSERT INTO `insurance_data` (`id`, `type`, `provider`, `plan_name`, `policy_number`,`group_number`, `subscriber_lname`, `subscriber_mname`, `subscriber_fname`,`subscriber_relationship`,`subscriber_DOB`, `subscriber_street`, `subscriber_postal_code`, `subscriber_city`,`subscriber_state`, `subscriber_country`,`subscriber_sex`, `accept_assignment`, `pid`, `date`) VALUES

}