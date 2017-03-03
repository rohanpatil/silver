<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsModifier
 */

/**
 * Smarty date_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     timezone<br>
 * Purpose:  change timezone as per user's profile setting<br>
 * Input:<br>
 *          - $strTime: input date string
 *          - $format: format in which date/time is expected after converting timezone
 *
  * @uses $_SESSION['timezone']
 */

function smarty_modifier_change_timezone($strTime, $format='Y m d h i A', $strFromTimezone='UTC') {
	if( $strTime == '' )
		return '';
	
	try {
        $dateTime = new DateTime($strTime, new DateTimeZone($strFromTimezone));
        if( isset($_SESSION['timezone']) ) {
        	$dateTime->setTimezone(new DateTimeZone($_SESSION['timezone']));
        }
        return $dateTime->format($format);
    } catch (Exception $e) {
        return '';
    }
}