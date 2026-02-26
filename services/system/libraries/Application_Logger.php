<?php
/**
 * Provab APPLICATION Class
 * Handle APPLICATION CUSTOM Details
 */
class Application_Logger
{
	public function __construct()
	{
	}

	public function registration(string $username, string $details = '', int $user_id = 0, array $action_query_string = [], array $attr = []): void
	{
		$details = $details ?: "$username Has Registered With Us";
		$this->log_time_line('EID001', $details, $action_query_string, $attr, $user_id);
	}

	public function profile_update(string $username, string $details = '', array $action_query_string = [], array $attr = [], int $user_id = 0): void
	{
		$details = $details ?: "$username Updated Profile Details";
		$this->log_time_line('EID002', $details, $action_query_string, $attr, $user_id);
	}

	public function change_password(string $username, string $details = ''): void
	{
		$details = $details ?: "$username Changed Password";
		$this->log_time_line('EID003', $details);
	}

	public function login(string $username, int $user_origin, array $action_query_string, string $details = ''): void
	{
		$details = $details ?: "$username Login To System";
		$this->log_time_line('EID005', $details, $action_query_string, [], $user_origin);
	}

	public function logout(string $username, int $user_origin, array $action_query_string, string $details = ''): void
	{
		$details = $details ?: "$username Logout Of System";
		$this->log_time_line('EID006', $details, $action_query_string, [], $user_origin);
	}

	public function email_subscription(string $username): void
	{
		$details = "$username Registered For News Letter";
		$this->log_time_line('EID004', $details);
	}

	public function balance_status(string $details): void
	{
		$this->log_time_line('EID007', $details);
	}

	public function transaction_status(string $details, array $action_query_string): void
	{
		$this->log_time_line('EID008', $details, $action_query_string);
	}

	public function account_status(string $details, array $action_query_string): void
	{
		$this->log_time_line('EID009', $details, $action_query_string);
	}

	public function api_status(string $details): void
	{
		$this->log_time_line('EID010', $details);
	}

	protected function log_time_line(
		string $event_origin,
		string $event_details,
		array $action_query_string = [],
		array $attr = [],
		int $user_id = 0
	): void {
		// Short-circuit return for disabled logging
		return;

		$details = @unserialize(file_get_contents('http://ip-api.com/php'));

		$CI = get_instance();
		$data = [
			'domain_origin' => get_domain_auth_id(),
			'event_origin' => $event_origin,
			'event_description' => $event_details,
			'location' => $details['regionName'] . ', ' . $details['timezone'],
			'internal_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
			'external_ip' => $details['query'] ?? '',
			'city' => $details['city'] ?? '',
			'country' => $details['country'] ?? '',
			'country_code' => $details['countryCode'] ?? '',
			'lat' => $details['lat'] ?? '',
			'lon' => $details['lon'] ?? '',
			'created_by_id' => $user_id ?: intval($GLOBALS['CI']->entity_user_id ?? 0),
			'created_datetime' => date('Y-m-d H:i:s'),
			'action_query_string' => valid_array($action_query_string)
				? json_encode(['q_params' => array_merge($action_query_string, ['q_search_type' => 'wildcard'])])
				: null,
			'attributes' => json_encode(array_merge(
				['isp' => $details['isp'] ?? '', 'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''],
				$attr
			)),
		];

		$CI->custom_db->insert_record('timeline', $data);
	}

	public function get_events(int $start, int $event_limit, array $cond = []): array
	{
		$CI = get_instance();
		$c_filter = '';

		if (is_domain_user()) {
			$c_filter .= ' AND TL.domain_origin = ' . get_domain_auth_id();
		}
		if (valid_array($cond)) {
			$c_filter .= $CI->custom_db->get_custom_condition($cond);
		}
		if (is_app_user()) {
			$c_filter .= ' AND TL.created_by_id = ' . intval($CI->entity_user_id ?? 0);
		}

		$query = 'SELECT TL.*, TLE.event_title, TLE.event_icon
				  FROM timeline TL
				  JOIN timeline_master_event TLE ON TL.event_origin = TLE.origin
				  WHERE 1=1 ' . $c_filter . '
				  ORDER BY TL.origin DESC
				  LIMIT ' . $start . ',' . $event_limit;

		return $CI->db->query($query)->result_array();
	}

	public function day_summary(array $cond = []): array
	{
		if (!is_logged_in_user()) {
			return [];
		}

		$CI = get_instance();
		$c_filter = '';

		if (is_domain_user()) {
			$c_filter .= ' AND TL.domain_origin = ' . get_domain_auth_id();
		}
		if (is_app_user()) {
			$c_filter .= ' AND TL.created_by_id = ' . intval($CI->entity_user_id ?? 0);
		}
		if (valid_array($cond)) {
			$c_filter .= $CI->custom_db->get_custom_condition($cond);
		}

		$query = 'SELECT COUNT(*) AS total, TL.origin AS event_origin, TLE.*
				  FROM timeline_master_event TLE
				  LEFT JOIN timeline TL ON TLE.origin = TL.event_origin
				  WHERE 1=1 ' . $c_filter . '
				  GROUP BY TLE.origin
				  ORDER BY TLE.event_title';

		return $CI->db->query($query)->result_array();
	}
}
