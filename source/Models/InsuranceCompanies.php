<?php

namespace SunCoastConnection\ClaimsToOEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class InsuranceCompanies extends Eloquent {
	protected $table = 'insurance_companies';

	public $timestamps = false;

	// INSERT INTO `insurance_companies` (`id`, `name`, `attn`,`cms_id`,`x12_receiver_id`, `x12_default_partner_id`) VALUES

}