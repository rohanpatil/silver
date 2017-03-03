<?php 
	function smarty_function_show_external_email_warning($params, $template) {
		if( false == isset($_SESSION['show_external_email_warning'])) {
			$_SESSION['show_external_email_warning'] = false;
		}
	}
?>