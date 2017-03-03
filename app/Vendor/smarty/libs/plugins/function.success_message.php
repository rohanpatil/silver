<?php 

	function smarty_function_success_message($params, $template) {

		$strMsg = '';
		
		if (isset($_SESSION['success_msg'])) {
			$strMsg = $_SESSION['success_msg'];
			unset( $_SESSION['success_msg'] );
		}
		
		return $strMsg;
	}
?>