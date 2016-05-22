<?php

namespace SunCoastConnection\ClaimsToOEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class FormEncounter extends Eloquent {
	protected $table = 'form_encounter';

	public $timestamps = false;

	// INSERT INTO `form_encounter` (`id`, `date`, `reason`, `facility`, `facility_id`, `pid`, `encounter`,
	// `onset_date`, `sensitivity`, `billing_note`, `pc_catid`, `last_level_billed`, `last_level_closed`, `last_stmt_date`, `stmt_count`,
	// `provider_id`, `supervisor_id`, `invoice_refno`, `referral_source`, `billing_facility`) VALUES

}