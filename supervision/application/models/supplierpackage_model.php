<?php
/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */
class Supplierpackage_Model extends CI_Model {
	public function __construct() {
		parent::__construct ();
	}
	public function package_view_data_types(): CI_DB_result {
		return $this->db->get ( 'package_types' );
	}
	 public function get_countries(): array {
		$this->db->limit ( 1000 );
		$this->db->order_by ( "name", "asc" );
		$qur = $this->db->get ( "country" );
		return $qur->result ();
	}
	public function package_type_data(): CI_DB_result {
		return $this->db->get ( 'package_types' );
	}
	  public function add_new_package(array $newpackage): int {
		$this->db->insert ( 'package', $newpackage );
		return $this->db->insert_id ();
	}
    public function update_code_package(array $packcode, string $package): bool{
		$this->db->where ( 'package_id', $package );
		return $this->db->update ( 'package', $packcode );
	}
	public function itinerary(array $itinerary): int {
		$this->db->insert ( 'package_itinerary', $itinerary );
		return $this->db->insert_id ();
	}
	public function que_ans(array $que_ans): int {
		$this->db->insert ( 'package_que_ans', $que_ans );
		return $this->db->insert_id ();
	}
	public function pricing_policy(array $pricingpolicy): int {
		$this->db->insert ( 'package_pricing_policy', $pricingpolicy );
		return $this->db->insert_id ();
	}
	public function cancellation_penality(array $cancellation): int {
		$this->db->insert ( 'package_cancellation', $cancellation );
		return $this->db->insert_id ();
	}
	public function deals(array $deals): int {
		$this->db->insert ( 'package_deals', $deals );
		return $this->db->insert_id ();
	}
   public function travel_images(array $traveller): int {
		$this->db->insert ( 'package_traveller_photos', $traveller );
		return $this->db->insert_id ();
	}
	public function without_price(): array {
		$this->db->where ( 'supplier_id', $this->session->userdata ( 'sup_id' ) );
		$this->db->where ( "price_includes", '0' );
		$this->db->where ( "deals", '0' );
		$q = $this->db->get ( "package" );
		if ($q->num_rows () > 0) {
			return $q->result ();
		}
		return array ();
	}
	public function get_supplier(): array {
		$this->db->where ( 'supplier_id', $this->session->userdata ( 'sup_id' ) );
		$this->db->where ( "deals", '1' );
		$this->db->where ( "price_includes", '0' );
		$q = $this->db->get ( "package" );
		if ($q->num_rows () > 0) {
			return $q->result ();
		}
		return array ();
	}
	public function update_status_answer(int $id, int|string $status): string {
		$data = array (
				'status' => $status 
		);
		// $where = "package_id = " . $package_id;
		$where = "id = " . $id;
		// $where = "qid = " . $qid;
		if ($this->db->update ( 'package_answers', $data, $where )) {
			return $status;
		} else {
			return '0';
		}
	}
	public function update_status(int $id, int $status): int {
		$data = array (
				'status' => $status 
		);
		$where = "package_id = " . $id;
		if ($this->db->update ( 'package', $data, $where )) {
			return $status;
		} else {
			return '0';
		}
	}
    public function update_top_destination(int $id, int $status): int {
		$data = array (
				'top_destination' => $status 
		);
		$where = "package_id = " . $id;
		if ($this->db->update ( 'package', $data, $where )) {
			return $status;
		} else {
			return '0';
		}
	}
	public function update_enquiry_status(int $id, int $status): int {
		$data = array (
				'enquiry_status' => $status
		);
		$where = "id = " . $id;
		if ($this->db->update ( 'package_enquiry', $data, $where )) {
			return $status;
		} else {
			return '0';
		}
	}
	public function update_homepage_status(int $package, int $home_page): bool{
		$data = array (
				'home_page' => $home_page 
		);
		
		$where = "package_id = " . $package;
		// $where = "img_id = " . $img_id;
		if ($this->db->update ( 'package', $data, $where )) {
			return true;
		} else {
			return false;
		}
	}
	public function get_package_id(int $package_id): null{
		$this->db->select ( "*" );
		$this->db->from ( "package" );
		$this->db->join ( 'package_cancellation', 'package_cancellation.package_id = package.package_id' );
		$this->db->join ( 'package_pricing_policy', 'package_pricing_policy.package_id = package.package_id' );
		$this->db->where ( 'package.package_id', $package_id );
		return $this->db->get ()->row ();
	}
	public function get_price(int $package_id): object|bool{
		$this->db->from ( 'package_duration' );
		$this->db->where ( 'package_id', $package_id );
		$query = $this->db->get ();
		if ($query->num_rows > 0) {
			
			return $query->row ();
		}
		return false;
	}
    public function get_country_city_list(): array|bool {
		$this->db->select ( '*' )->from ( 'country' );
		$query = $this->db->get ();
		
		if ($query->num_rows > 0) {
			
			return $query->result ();
		}
		return false;
	}
	public function get_itinerary_id(int $package_id): array|bool {
		$this->db->from ( 'package_itinerary' );
		$this->db->where ( 'package_id', $package_id );
		$query = $this->db->get ();
		if ($query->num_rows > 0) {
			
			return $query->result ();
		}
		return false;
	}
	public function get_que_ans(int $package_id): array|bool {
		$this->db->from ( 'package_que_ans' );
		$this->db->where ( 'package_id', $package_id );
		$query = $this->db->get ();
		
		if ($query->num_rows > 0) {
			
			return $query->result ();
		}
		return false;
	}
	public function with_price(): array {
		$q = $this->db->get ( "package" );
		if ($q->num_rows () > 0) {
			return $q->result ();
		}
		return array ();
	}
    public function get_country_name(int $id): ?object {
		$this->db->select ( 'name' );
		$this->db->where ( 'country_id', $id );
		return $this->db->get ( 'country' )->row ();
	}
	public function enquiries(): array|bool {
		$this->db->from ( 'package_enquiry' );
		$this->db->join ( 'package', 'package.package_id=package_enquiry.package_id' );
		$this->db->order_by ( 'id', "desc" );
		$query = $this->db->get ();
		if ($query->num_rows > 0) {
			return $query->result ();
		}
		return false;
	}
	public function get_crs_city_list(string $value): array {
		$this->db->where ( 'country', $value );
		return $this->db->get ( 'crs_city' )->result ();
	}
	public function get_tour_list(int $value): array
 {
		$this->db->where ( 'package_types_id', $value );
		return $this->db->get ( 'package_types' )->result ();
	}
	public function update_edit_package(int $package_id, array $data): bool{
		$where = "package_id = " . $package_id;
		if ($this->db->update ( 'package', $data, $where )) {
			return true;
		} else {
			return false;
		}
	}
    public function update_edit_policy(int $package_id, array $policy): bool {
		$where = "package_id = " . $package_id;
		if ($this->db->update ( 'package_pricing_policy', $policy, $where )) {
			return true;
		} else {
			return false;
		}
	}
	public function update_edit_can(int $package_id, array $can): bool  {
		$where = "package_id = " . $package_id;
		if ($this->db->update ( 'package_cancellation', $can, $where )) {
			return true;
		} else {
			return false;
		}
	}
	public function update_edit_dea(int $package_id, array $dea): bool  {
		$where = "package_id = " . $package_id;
		if ($this->db->update ( 'package_deals', $dea, $where )) {
			return true;
		} else {
			return false;
		}
	}
	public function update_edit_pri(int $package_id, array $pri): bool {
		$where = "package_id = " . $package_id;
		if ($this->db->update ( 'package_duration', $pri, $where )) {
			return true;
		} else {
			return false;
		}
	}
public function get_image(int $package_id):false {
		$this->db->from ( 'package_traveller_photos' );
		$this->db->where ( 'package_id', $package_id );
		$query = $this->db->get ();
		if ($query->num_rows > 0) {
			
			return $query->result ();
		}
		return false;
	}
    public function update_itinerary(int $package, int $itinerary_id, array $data): bool {
		$where = "package_id = " . $package;
		$where = "iti_id = " . $itinerary_id;
		if ($this->db->update ( 'package_itinerary', $data, $where )) {
			return true;
		} else {
			return false;
		}
	}
	public function delete_traveller_img(int $pack_id, int $img_id): void {
		$this->db->where ( 'package_id', $img_id );
		$this->db->where ( 'img_id', $pack_id );
		$this->db->delete ( 'package_traveller_photos' );
	}
	public function view_enqur(int $package_id): array  {
		$this->db->from ( 'package_enquiry' );
		$this->db->where ( 'package_id', $package_id );
		$query = $this->db->get ();
		if ($query->num_rows > 0) {
			
			return $query->result ();
		}
	}
	public function delete_enquiry(int $id): void {
		$this->db->where ( 'id', $id );
		$this->db->delete ( 'package_enquiry' );
	}
	public function delete_package_type(int $id): void {
		$this->db->where ( 'package_types_id', $id );
		$this->db->delete ( 'package_types' );
	}
	public function delete_package(int $id): void{
		$this->db->where ( 'package_id', $id );
		$this->db->delete ( 'package' );
	}
	public function get_pack_id(int $id): array {
		$this->db->select ( '*' );
		$this->db->where ( 'package_types_id', $id );
		return $this->db->get ( 'package_types' )->result ();
	}
	public function update_package_type(array $add_package_data, int $id): void {
		$this->db->where ( 'package_types_id', $id );
		$this->db->update ( 'package_types', $add_package_data );
	}

	public function b2b_package_report(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array|int
	{
		$condition_str = '';
		if (valid_array($condition)) {
			foreach ($condition as $cond) {
				$condition_str .= ' AND ' . $cond[0] . ' ' . $cond[1] . ' ' . $cond[2];
			}
		}

		if ($count) {
			$query = 'SELECT COUNT(PE.id) AS total_records
					  FROM package_enquiry PE
					  LEFT JOIN package P ON PE.package_id = P.package_id
					  WHERE 1=1 ' . $condition_str;
			$data = $this->db->query($query)->row_array();
			return (int)($data['total_records'] ?? 0);
		}

		$query = 'SELECT PE.*, P.title AS package_name
				  FROM package_enquiry PE
				  LEFT JOIN package P ON PE.package_id = P.package_id
				  WHERE 1=1 ' . $condition_str . '
				  ORDER BY PE.date DESC, PE.id DESC
				  LIMIT ' . (int)$offset . ', ' . (int)$limit;
		return $this->db->query($query)->result_array();
	}

	public function b2c_package_report(array $condition = [], bool $count = false, int $offset = 0, int $limit = 100000000000): array|int
	{
		return $this->b2b_package_report($condition, $count, $offset, $limit);
	}
}