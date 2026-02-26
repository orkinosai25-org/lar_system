<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package    Provab
 * @subpackage General
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */

class General extends CI_Controller {
	public function __construct()
	{
		parent::__construct();		
	}
/**
	 * index page of application will be loaded here
	 */
	public function index(): void
	{
		if (is_logged_in_user()) {
			redirect('menu/index');
		} else {
			//show login
			echo $this->template->view('general/login');
		}
	}

	/**
	 * Logout function for logout from account and unset all the session variables
	 */
	public function initilize_logout(): void {
		if (is_logged_in_user()) {
			$this->user_model->update_login_manager($this->session->userdata(LOGIN_POINTER));
			$this->session->unset_userdata(array(AUTH_USER_POINTER => '',LOGIN_POINTER => '', DOMAIN_AUTH_ID => '', DOMAIN_KEY => ''));
			redirect('general/index');
		}
	}
}