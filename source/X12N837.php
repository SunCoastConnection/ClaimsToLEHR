<?php

namespace SunCoastConnection\ClaimsToOEMR;

use \SunCoastConnection\ClaimsToOEMR\Document\Root;

// TODO: Cleanup, rename, and replace properties with sub-objects
class X12N837 extends Root {

	static protected $descendantSequence = [
		['name' => 'InterchangeControl', 'required' => true, 'repeat' => 1],
	];

	//** GLOBALS **//

	// public $insuranceCount = 0;
	// public $insurance = array();

	// public $billingCount = 0;

	// public $encounterCount = 0;

	// public $facilityCount = 0;
	// public $facility = array();

	// public $insDataCount = 0;
	// public $insData = array();

	// public $patientCount = 0;
	// public $patient = array();

	// public $providerCount = 0;
	// public $provider = array();

	// public $x12PartnersCount = 0;
	// public $x12Partners = array();



	// //** Document Values **//

	// public $servicefacilityid = '';
	// public $billproein = '';

	// public $servicefacilityadd1 = '';
	// public $servicefacilityadd2 = '';
	// public $servicefacilitycity = '';
	// public $servicefacilitystate = '';
	// public $servicefacilityzip = '';
	// public $insdata_exists = false;
	// public $pat_exists = false;
	// public $pro_exists = false;
	// public $ins_exists = false;
	// public $current_fac = '';
	// public $current_ins = 0;
	// public $fac_exists = false;
	// public $currentloop = '';
	// public $current_pro = '';
	// public $userstableid = '';
	// public $chunk = '';
	// public $tag = '';
	// public $cur_ins_type = 0;
	// public $primarypatrel = '';
	// public $secondarypatrel = '';
	// public $tertiarypatrel = '';
	// public $txcount = 0;
	// public $dxcount = 0;
	// public $form_text_out1 = '';
	// public $form_text_out2 = '';
	// public $enc_text_out = '';
	// public $bill_text_out = '';
	// public $tx = array(
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// );
	// public $pow = 0;
	// public $sok = 0;
	// public $txmod = array(
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// );
	// public $dx = array(
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// );
	// public $dxtype = array(
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// );
	// public $txamount = array(
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// 	'',
	// );
	// public $patid = '';
	// public $patlname = '';
	// public $patfname = '';
	// public $patmname = '';
	// public $patadd1 = '';
	// public $patadd2 = '';
	// public $patcity = '';
	// public $patstate = '';
	// public $patzip = '';
	// public $patsex = '';
	// public $patdob = '';
	// public $patrel = '';


	// public $primarysubid = '';
	// public $primarysublname = '';
	// public $primarysubfname = '';
	// public $primarysubmname = '';
	// public $primarysubadd1 = '';
	// public $primarysubadd2 = '';
	// public $primarysubcity = '';
	// public $primarysubstate = '';
	// public $primarysubzip = '';
	// public $primarysubsex = '';
	// public $primarysubdob = '';
	// public $primarysubrel = '';
	// public $secondarysubid = '';
	// public $secondarysublname = '';
	// public $secondarysubfname = '';
	// public $secondarysubmname = '';
	// public $secondarysubadd1 = '';
	// public $secondarysubadd2 = '';
	// public $secondarysubcity = '';
	// public $secondarysubstate = '';
	// public $secondarysubzip = '';
	// public $secondarysubsex = '';
	// public $secondarysubdob = '';
	// public $secondarysubrel = '';
	// public $tertiarysubid = '';
	// public $tertiarysublname = '';
	// public $tertiarysubfname = '';
	// public $tertiarysubmname = '';
	// public $tertiarysubadd1 = '';
	// public $tertiarysubadd2 = '';
	// public $tertiarysubcity = '';
	// public $tertiarysubstate = '';
	// public $tertiarysubzip = '';
	// public $tertiarysubsex = '';
	// public $tertiarysubdob = '';
	// public $tertiarysubrel = '';

	// public $primarypayerid = '';
	// public $primarypayername = '';
	// public $primarypayeradd1 = '';
	// public $primarypayeradd2 = '';
	// public $primarypayercity = '';
	// public $primarypayerstate = '';
	// public $primarypayerzip = '';

	// public $secondarypayerid = '';
	// public $secondarypayername = '';
	// public $secondarypayeradd1 = '';
	// public $secondarypayeradd2 = '';
	// public $secondarypayercity = '';
	// public $secondarypayerst = '';
	// public $secondarypayerzip = '';

	// public $tertiarypayerid = '';
	// public $tertiarypayername = '';
	// public $tertiarypayeradd1 = '';
	// public $tertiarypayeradd2 = '';
	// public $tertiarypayercity = '';
	// public $tertiarypayerstate = '';
	// public $tertiarypayerzip = '';

	// public $claimid = '';
	// public $claimamt = '';
	// public $provsignatureonfile = '';
	// public $provacceptassignmentcode = '';
	// public $benefitindicator = '';
	// public $releaseofinformation = '';
	// public $relatedcause = '';
	// public $facilitycodevalue = '';
	// public $facilitycodequalifier = '';
	// public $frequencytypecode = '';
	// public $medicalrecordnumber = '';
	// public $claimnumber = '';
	// public $priorauth = '';
	// public $k3data = '';
	// public $notecode = '';
	// public $notedesc = '';

	// public $referringtype = '';
	// public $referringid = '';
	// public $referringlname = '';
	// public $referringfname = '';
	// public $referringmname = '';
	// public $referringsuffix = '';
	// public $referringidcode = '';
	// public $referringtaxonomy = '';

	// public $renderingtype = '';
	// public $renderingid = '';
	// public $renderinglname = '';
	// public $renderingfname = '';
	// public $renderingmname = '';
	// public $renderingsuffix = '';
	// public $renderingidcode = '';
	// public $renderingtaxonomy = '';

	// public $supervisingtype = '';
	// public $supervisingid = '';
	// public $supervisinglname = '';
	// public $supervisingfname = '';
	// public $supervisingmname = '';
	// public $supervisingsuffix = '';
	// public $supervisingidcode = '';

	// public $orderingtype = '';
	// public $orderingid = '';
	// public $orderinglname = '';
	// public $orderingfname = '';
	// public $orderingmname = '';
	// public $orderingsuffix = '';
	// public $orderingidcode = '';


	// public $billingtype = '';
	// public $billingproviderid = '';
	// public $billingprovidername = '';
	// public $billingprovideradd1 = '';
	// public $billingprovideradd2 = '';
	// public $billingprovidercity = '';
	// public $billingproviderstate = '';
	// public $billingproviderzip = '';
	// public $billingprovidertaxonomy = '';

	// public $paytotype = '';
	// public $paytoproviderid = '';
	// public $paytoprovidername = '';
	// public $paytoprovideradd1 = '';
	// public $paytoprovideradd2 = '';
	// public $paytoprovidercity = '';
	// public $paytoproviderstate = '';
	// public $paytoproviderzip = '';


	// public $servicefacilityname = '';
	// public $servicefaciliyid = '';
	// public $proc1 = '';

	// public $amt1 = '';
	// public $units1 = '';
	// public $qty1 = '';
	// public $facilitycode1 = '';
	// public $modifier1 = false;
	// public $modifier2 = false;
	// public $dos1 = '';
	// public $dos2 = '';
	// public $submitterid = '';
	// public $submittername = '';
	// public $submitteradd1 = '';
	// public $submitteradd2 = '';
	// public $submittercity = '';
	// public $submitterst = '';
	// public $submitterzip = '';

	// public $transactionsetcode = '';
	// public $transactionid = '';
	// public $transactiondate = '';
	// public $transactiontime = '';
	// public $transactiontype = '';

}