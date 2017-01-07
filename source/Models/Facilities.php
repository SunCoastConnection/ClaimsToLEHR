<?php

namespace SunCoastConnection\ClaimsToEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Facilities extends Eloquent {
	protected $table = 'facility';

	public $timestamps = false;

	// INSERT INTO `facility` (`id`, `name`, `street`, `city`, `state`, `postal_code`, `country_code`, `federal_ein`, `service_location`, `billing_location`, `accepts_assignment`, `pos_code`, `attn`, `facility_npi`, `tax_id_type`, `color`, `primary_business_entity`) VALUES

}