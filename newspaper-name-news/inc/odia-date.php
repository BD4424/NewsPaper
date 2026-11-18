<?php
/** Odia numerals, month/day names and relative times. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function np_odia_digits( $value ): string {
	return strtr( (string) $value, array( '0'=>'୦','1'=>'୧','2'=>'୨','3'=>'୩','4'=>'୪','5'=>'୫','6'=>'୬','7'=>'୭','8'=>'୮','9'=>'୯' ) );
}

function np_odia_months(): array {
	return array( 1=>'ଜାନୁଆରୀ',2=>'ଫେବୃଆରୀ',3=>'ମାର୍ଚ୍ଚ',4=>'ଅପ୍ରେଲ',5=>'ମେ',6=>'ଜୁନ',7=>'ଜୁଲାଇ',8=>'ଅଗଷ୍ଟ',9=>'ସେପ୍ଟେମ୍ବର',10=>'ଅକ୍ଟୋବର',11=>'ନଭେମ୍ବର',12=>'ଡିସେମ୍ବର' );
}

function np_odia_weekdays(): array {
	return array( 0=>'ରବିବାର',1=>'ସୋମବାର',2=>'ମଙ୍ଗଳବାର',3=>'ବୁଧବାର',4=>'ଗୁରୁବାର',5=>'ଶୁକ୍ରବାର',6=>'ଶନିବାର' );
}

/**
 * Format a timestamp in Odia.
 * @param int|null $ts        Unix timestamp (null = now).
 * @param bool     $with_time Append time (hh:mm AM/PM in Odia).
 * @param bool     $weekday   Prefix weekday.
 */
function np_odia_date( ?int $ts = null, bool $with_time = false, bool $weekday = false ): string {
	$ts  = $ts ?? time();
	$out = np_odia_digits( wp_date( 'j', $ts ) ) . ' ' . np_odia_months()[ (int) wp_date( 'n', $ts ) ] . ' ' . np_odia_digits( wp_date( 'Y', $ts ) );
	if ( $weekday ) {
		$out = np_odia_weekdays()[ (int) wp_date( 'w', $ts ) ] . ', ' . $out;
	}
	if ( $with_time ) {
		$out .= ', ' . np_odia_digits( wp_date( 'g:i', $ts ) ) . ( wp_date( 'A', $ts ) === 'AM' ? ' ପୂର୍ବାହ୍ନ' : ' ଅପରାହ୍ନ' );
	}
	return $out;
}

/** "୫ ମିନିଟ ପୂର୍ବରୁ" style relative time; falls back to full date after 24h. */
function np_odia_time_ago( int $ts ): string {
	$diff = time() - $ts;
	if ( $diff < 60 )    { return 'ଏବେ'; }
	if ( $diff < 3600 )  { return np_odia_digits( (int) floor( $diff / 60 ) ) . ' ମିନିଟ ପୂର୍ବରୁ'; }
	if ( $diff < 86400 ) { return np_odia_digits( (int) floor( $diff / 3600 ) ) . ' ଘଣ୍ଟା ପୂର୍ବରୁ'; }
	return np_odia_date( $ts );
}
