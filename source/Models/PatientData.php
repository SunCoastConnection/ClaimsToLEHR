<?php

namespace SunCoastConnection\ClaimsToOEMR\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class PatientData extends Eloquent {
	protected $table = 'patient_data';

	public $timestamps = false;

	// INSERT INTO `patient_data` (`id`, `language`, `fname`, `lname`, `mname`, `DOB`,`street`,`postal_code`, `city`, `state`, `date`, `sex`,`providerID`, `pubpid`,`pid`) VALUES

}