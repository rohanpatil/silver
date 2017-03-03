<?php 

	function smarty_function_show_timezone_warning($params, $template) {

		if( false == isset($_SESSION['show_timezone_warning'])) {
			$_SESSION['show_timezone_warning'] = false;
		}
	}
?>