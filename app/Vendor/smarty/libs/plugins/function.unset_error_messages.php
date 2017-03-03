<?php

	function smarty_function_unset_error_messages($params, $template) {

		if (isset($_SESSION['error_msgs'])) {
			unset( $_SESSION['error_msgs'] );
		}
		
		if (isset($_SESSION['post_data'])) {
			unset( $_SESSION['post_data'] );
		}
		
		if(isset($_SESSION['is_add_event_validated'])) {
			unset( $_SESSION['is_add_event_validated'] );
		}
 
		if(isset($_SESSION['is_add_event_validated_follow_up'])) {
			unset( $_SESSION['is_add_event_validated_follow_up'] );
		} 
		
		if(isset($_SESSION['schedule_error_msgs'])) {
			unset( $_SESSION['schedule_error_msgs'] );
		} 
		if(isset($_SESSION['log_error_msgs'])) {
			unset( $_SESSION['log_error_msgs'] );
		} 
		if(isset($_SESSION['sr_event_error_msgs'])){
			unset( $_SESSION['sr_event_error_msgs'] );
		}
		if(isset($_SESSION['log_sr_event_error_msgs'])){
			unset( $_SESSION['log_sr_event_error_msgs'] );
		}
		if(isset($_SESSION['is_add_email_validated'])){
			unset( $_SESSION['is_add_email_validated'] );
		}
		if(isset($_SESSION['is_add_sms_validated'])){
			unset( $_SESSION['is_add_sms_validated'] );
		}
		return;
	}
?>