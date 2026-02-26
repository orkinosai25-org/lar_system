<?php if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/**
 *
 * @package    Provab - vibrant holidays
 * @subpackage Client
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V1
 */
class Menu extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * index page of application will be loaded here
	 */
	public function index(): void
	{
		redirect(base_url() . 'index.php/eco_stays/stays');
	}
	public function dashboard(): void
	{

		redirect(base_url() . 'index.php/eco_stays/stays');
		
	}
}