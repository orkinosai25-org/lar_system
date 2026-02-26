<?php
require_once 'abstract_management_model.php';

/**
 * @package    Provab Application
 * @subpackage Travel Portal
 */
class Private_Management_Model extends Abstract_Management_Model
{
    private array $airline_markup = [];
    private array $hotel_markup = [];
    private array $bus_markup = [];
    private array $sightseeing_markup = [];
    private array $transferv1_markup = [];

    public function __construct()
    {
        parent::__construct('level_1');
    }

    public function get_markup(string $module_name, string $version = FLIGHT_VERSION_1, string $OperatorCode = '', string $is_domestic = ''): array
    {
        return match ($module_name) {
            'b2c_flight' => $this->airline_markup($version, $OperatorCode, $is_domestic),
            'b2c_hotel' => $this->hotel_markup(),
            'b2c_bus' => $this->bus_markup(),
            'b2c_sightseeing' => $this->sightseeing_markup(),
            'b2c_viator_transfer' => $this->transferv1_markup(),
            default => ['value' => 0, 'type' => ''],
        };
    }

    private function airline_markup(string $version = FLIGHT_VERSION_1, string $opcode = '', string $is_domestic = ''): array
    {
        if (empty($this->airline_markup)) {
            $response = [];
            if ($opcode !== '') {
                $response['airline_wise_markup_list'] = $this->specific_ailrine_wise_markup('b2c_flight', $version, $opcode);
            }
            $response['specific_markup_list'] = $this->specific_domain_markup('b2c_flight', $version, $is_domestic);
            $response['generic_markup_list'] = $this->generic_domain_markup('b2c_flight', $version, $is_domestic);
            return $response;
        }

        return $this->airline_markup;
    }

    private function hotel_markup(): array
    {
		$response = [];
        if (empty($this->hotel_markup)) {
            $response['specific_markup_list'] = $this->specific_domain_markup('b2c_hotel');
            if (!valid_array($response['specific_markup_list'])) {
                $response['generic_markup_list'] = $this->generic_domain_markup('b2c_hotel');
            }
            $this->hotel_markup = $response;
        }
        return $this->hotel_markup;
    }


    private function generic_domain_markup(string $module_type, string $version = FLIGHT_VERSION_1, string $is_domestic = ''): array
    {
        $query = ''; // initialized to avoid "undefined variable"
        if($version == FLIGHT_VERSION_2){
			if(($is_domestic == 1) && ($module_type == 'b2c_flight')){
				$query = 'SELECT ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type,
				ML.markup_currency AS markup_currency,ML.booking_source_fk,BS.source_id
				FROM markup_list AS ML
				LEFT JOIN booking_source BS on BS.origin=ML.booking_source_fk
				where ML.value != "" and ML.module_type = "'.$module_type.'" and
				ML.markup_level = "'.$this->markup_level.'" and ML.type="generic" and ML.domain_list_fk=0
				order by ML.booking_source_fk desc';
				// echo $query;exit;
			}
			else if($module_type == 'b2c_flight'){
				$query = 'SELECT ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.int_value as value, ML.int_value_type as value_type,
				ML.markup_currency AS markup_currency,ML.booking_source_fk,BS.source_id
				FROM markup_list AS ML
				LEFT JOIN booking_source BS on BS.origin=ML.booking_source_fk
				where ML.int_value != "" and ML.module_type = "'.$module_type.'" and
				ML.markup_level = "'.$this->markup_level.'" and ML.type="generic" and ML.domain_list_fk=0
				order by ML.booking_source_fk desc';
				
			}
			else{
				$query = 'SELECT ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type,
				ML.markup_currency AS markup_currency,ML.booking_source_fk,BS.source_id
				FROM markup_list AS ML
				LEFT JOIN booking_source BS on BS.origin=ML.booking_source_fk
				where ML.value != "" and ML.module_type = "'.$module_type.'" and
				ML.markup_level = "'.$this->markup_level.'" and ML.type="generic" and ML.domain_list_fk=0
				order by ML.booking_source_fk desc';
				// echo $query;exit;
			}

			
		} else{
			 if(strtolower($module_type) == 'b2c_flight'){//For Older Version
				$booking_source_fk = $this->custom_db->single_table_records('booking_source', 'origin', array('source_id' => trim(TBO_FLIGHT_BOOKING_SOURCE)));
				$booking_source_fk = $booking_source_fk['data'][0]['origin'];
				
				$booking_source_filter = ' AND ((ML.booking_source_fk=0 OR ML.booking_source_fk is NULL) OR ML.booking_source_fk='.$booking_source_fk.') ';
			} else {
				$booking_source_filter = '';
			}
			$query = 'SELECT ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type,
			ML.markup_currency AS markup_currency,ML.booking_source_fk
			FROM markup_list AS ML where ML.value != "" and ML.module_type = "'.$module_type.'" and
			ML.markup_level = "'.$this->markup_level.'" and ML.type="generic" and ML.domain_list_fk=0 '.$booking_source_filter.'
			order by ML.booking_source_fk desc limit 1';
		}
        $generic_data_list = $this->db->query($query)->result_array();
        return $generic_data_list;
    }

    private function specific_domain_markup(string $module_type, string $version = FLIGHT_VERSION_1, string $is_domestic = ''): array
    {
        $query = ''; // initialized to avoid "undefined variable"
        if($version == FLIGHT_VERSION_2){
			if($is_domestic == 1 && $module_type == 'b2c_flight'){
				$query = 'SELECT
				ML.origin AS markup_origin, ML.value, ML.value_type,  ML.markup_currency AS markup_currency,BS.source_id
				FROM domain_list AS DL JOIN markup_list AS ML
				JOIN booking_source BS on BS.origin=ML.booking_source_fk
				JOIN domain_api_map DAM on BS.origin=DAM.booking_source_fk and DL.origin=DAM.domain_list_fk
				where ML.value != "" and
				ML.module_type = "'.$module_type.'" and ML.markup_level = "'.$this->markup_level.'" and DL.origin=ML.domain_list_fk and ML.type="specific"
				and ML.domain_list_fk != 0 and ML.reference_id='.get_domain_auth_id().' 
				and ML.domain_list_fk = '.get_domain_auth_id().'
				order by DL.created_datetime DESC';
				// echo $query;exit;
			}
			else if($module_type == 'b2c_flight'){
				$query = 'SELECT
				ML.origin AS markup_origin, ML.int_value as value, ML.int_value_type as value_type,  ML.markup_currency AS markup_currency,BS.source_id
				FROM domain_list AS DL JOIN markup_list AS ML
				JOIN booking_source BS on BS.origin=ML.booking_source_fk
				JOIN domain_api_map DAM on BS.origin=DAM.booking_source_fk and DL.origin=DAM.domain_list_fk
				where ML.int_value != "" and
				ML.module_type = "'.$module_type.'" and ML.markup_level = "'.$this->markup_level.'" and DL.origin=ML.domain_list_fk and ML.type="specific"
				and ML.domain_list_fk != 0 and ML.reference_id='.get_domain_auth_id().' 
				and ML.domain_list_fk = '.get_domain_auth_id().'
				order by DL.created_datetime DESC';
				// echo $query;exit;
			}
			else{
				$query = 'SELECT
				ML.origin AS markup_origin, ML.value, ML.value_type,  ML.markup_currency AS markup_currency,BS.source_id
				FROM domain_list AS DL JOIN markup_list AS ML
				JOIN booking_source BS on BS.origin=ML.booking_source_fk
				JOIN domain_api_map DAM on BS.origin=DAM.booking_source_fk and DL.origin=DAM.domain_list_fk
				where ML.value != "" and
				ML.module_type = "'.$module_type.'" and ML.markup_level = "'.$this->markup_level.'" and DL.origin=ML.domain_list_fk and ML.type="specific"
				and ML.domain_list_fk != 0 and ML.reference_id='.get_domain_auth_id().' 
				and ML.domain_list_fk = '.get_domain_auth_id().'
				order by DL.created_datetime DESC';
			}
			
			
		}  else {
			if(strtolower($module_type) == 'b2c_flight'){//For Older Version
				$booking_source_fk = $this->custom_db->single_table_records('booking_source', 'origin', array('source_id' => trim(TBO_FLIGHT_BOOKING_SOURCE)));
				$booking_source_fk = $booking_source_fk['data'][0]['origin'];
			
				$booking_source_filter = ' AND ML.booking_source_fk='.$booking_source_fk.' ';
			} else {
				$booking_source_filter = '';
			}
			$query = 'SELECT
			ML.origin AS markup_origin, ML.value, ML.value_type,  ML.markup_currency AS markup_currency,BS.source_id
			FROM domain_list AS DL JOIN markup_list AS ML
			LEFT JOIN booking_source BS on BS.origin=ML.booking_source_fk 
			where ML.value != "" and
			ML.module_type = "'.$module_type.'" and ML.markup_level = "'.$this->markup_level.'" and DL.origin=ML.domain_list_fk and ML.type="specific"
			and ML.domain_list_fk != 0 and ML.reference_id='.get_domain_auth_id().' 
			and ML.domain_list_fk = '.get_domain_auth_id().''.$booking_source_filter.'
			order by DL.created_datetime DESC';
		}
        $specific_data_list = $this->db->query($query)->result_array();
        return $specific_data_list;
    }

    private function specific_ailrine_wise_markup(string $module_type='', string $version = FLIGHT_VERSION_1, string $opcode=''): array
    {
        $query = ''; // initialized to avoid "undefined variable"
        if($version == FLIGHT_VERSION_2){
			$query = 'SELECT ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type,
			ML.markup_currency AS markup_currency,ML.booking_source_fk,BS.source_id
			FROM markup_list_airline AS ML
                        LEFT JOIN airline_list AS AL on AL.origin=ML.reference_id
			LEFT JOIN booking_source BS on BS.origin=ML.booking_source_fk
			where ML.value != "" and ML.module_type = "'.$module_type.'" and
			ML.markup_level = "'.$this->markup_level.'" and ML.type="specific" and ML.domain_list_fk='.get_domain_auth_id().' and AL.code="'.$opcode.'"
			order by ML.booking_source_fk desc';
		} else{
			 if(strtolower($module_type) == 'b2c_flight'){//For Older Version
				$booking_source_fk = $this->custom_db->single_table_records('booking_source', 'origin', array('source_id' => trim(TBO_FLIGHT_BOOKING_SOURCE)));
				$booking_source_fk = $booking_source_fk['data'][0]['origin'];
				
				$booking_source_filter = ' AND ((ML.booking_source_fk=0 OR ML.booking_source_fk is NULL) OR ML.booking_source_fk='.$booking_source_fk.') ';
			} else {
				$booking_source_filter = '';
			}
			$query = 'SELECT ML.origin AS markup_origin, ML.type AS markup_type, ML.reference_id, ML.value, ML.value_type,
			ML.markup_currency AS markup_currency,ML.booking_source_fk
			FROM markup_list_airline AS ML 
                        LEFT JOIN airline_list AS AL on AL.origin=ML.reference_id
                        where ML.value != "" and ML.module_type = "'.$module_type.'" and
			ML.markup_level = "'.$this->markup_level.'" and ML.type="specific" and ML.domain_list_fk='.get_domain_auth_id().' and AL.code="'.$opcode.'"
			order by ML.booking_source_fk desc';
		}
        $generic_data_list = $this->db->query($query)->result_array();
        return $generic_data_list;
    }

    public function update_domain_balance(int $domain_origin, float $amount): float
    {
        $current_balance = 0;
        $cond = ['origin' => $domain_origin];
        $details = $this->custom_db->single_table_records('domain_list', 'balance', $cond);

        if ($details['status'] === true) {
            $details['data'][0]['balance'] = $current_balance = ($details['data'][0]['balance'] + $amount);
            $this->custom_db->update_record('domain_list', $details['data'][0], $cond);
        }

        return $current_balance;
    }
}
