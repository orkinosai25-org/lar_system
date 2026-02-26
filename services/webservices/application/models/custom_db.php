<?php

Class Custom_Db extends CI_Model
{
    	/**
	 * get records from table using basic select
	 *
	 * @param $table	 Name of table from which records has to be fetched
	 * @param $cols		 group of columns to be selected
	 * @param $condition condition to be used to the records
	 * @param $offset	 offset to be used while fetching records
	 * @param $limit	 number of records to be fetched
	 * @param $order_by	 order in which the records has to be fetched
     * */

function single_table_records(string $table, string|array $cols = '*', array $condition = [],
    int $offset = 0, int $limit = 100000000, array $order_by = []): array {

    if (empty($table) || !is_string($table)) {
        redirect('general/redirect_login?op=R');
    }

    if (!empty($order_by)) {
        foreach ($order_by as $k => $v) {
            $this->db->order_by($k, $v);
        }
    }

    if (empty($condition)) {
        $condition = [];
    }

    $tmp_data = $this->db->select($cols)
                         ->get_where($table, $condition, $limit, $offset);

    if ($tmp_data->num_rows() > 0) {
        return [
            'status' => QUERY_SUCCESS,
            'data' => $tmp_data->result_array()
        ];
    }

    return ['status' => QUERY_FAILURE];
}


	/**
	 * get records from different tables using cross join
	 *
	 * @param array $tables        array having list of tables to be joined
	 * @param string $cols	       group of columns to be selected
	 * @param array $joincondition join condition to be used to join tables
	 * @param array $condition     condition to be used to the records
	 * @param number $offset       offset to be used while fetching records
	 * @param number $limit        number of records to be fetched
	 * @param array $order_by      order in which the records has to be fetched
	 * Ex. multiple_table_cross_records(array('modules','api'), 'api_id', array('modules.pk' => 'api.module_fk'), array('modules.status' => ACTIVE, 'api.status' => ACTIVE));
	 */
    function multiple_table_cross_records(array $tables = [], string|array $cols = '*',array $joincondition = [],
    array $condition = [], int $offset = 0, int $limit = 1000, array $order_by = []): array {
        if (empty($tables) || empty($joincondition)) {
            redirect('general/redirect_login?op=R');
        }

        if (!empty($order_by)) {
            foreach ($order_by as $k => $v) {
                $this->db->order_by($k, $v);
            }
        }

        for ($i = 1, $tableCount = count($tables); $i < $tableCount; $i++) {
            foreach ($joincondition as $ck => $cv) {
                $this->db->join($tables[$i], $ck . "=" . $cv);
            }
        }

        $tmp_data = $this->db->select($cols)
                            ->get_where($tables[0], $condition, $limit, $offset)
                            ->result_array();

        return [
            'status' => !empty($tmp_data) ? QUERY_SUCCESS : QUERY_FAILURE,
            'data' => $tmp_data
        ];
    }

	/*
	 *this will insert the data into database and create new record
	 *
	 *@param string $table_name name of the table to which the data has to be inserted
	 *@param array  $data       data which has to be inserted into database
	 *
	 *@return array has status of insertion and insert id
	 */

    function insert_record(string $table_name, array $data): array
    {
        $this->db->insert($table_name, $data);
        $num_inserts = $this->db->affected_rows();

        if (intval($num_inserts) > 0) {
            return [
                'status' => QUERY_SUCCESS,
                'insert_id' => $this->db->insert_id()
            ];
        }

        redirect('general/redirect_login?op=C');
    }

    /*
	 *this will insert the data into database and create new record
	 *
	 *@param string $table_name name of the table to which the data has to be inserted
	 *@param array  $data       data which has to be inserted into database
	 *
	 *@return array has status of insertion and insert id
	 */

    function update_record(string $table_name, array $data, array $condition): string
    {
        if (!valid_array($data) || !valid_array($condition)) {
            redirect('general/redirect_login?op=U');
        }

        $this->db->update($table_name, $data, $condition);

        if ($this->db->affected_rows() > 0) {
            return QUERY_SUCCESS;
        }

        return QUERY_FAILURE;
    }


    /*
	 *this will delete data from database
	 *
	 *@param string $table_name name of the table to which the data has to be inserted
	 *@param array  $condition  condition for deleting data
	 *
	 *@return array has status of insertion and insert id
	 */

    function delete_record(string $table_name, array $condition): string
    {
        if (!valid_array($condition)) {
            redirect('general/redirect_login?op=D');
        }

        $this->db->delete($table_name, $condition);
        return QUERY_SUCCESS;
    }

    function generate_static_response(string $data, string $desc = ''): int
    {
        $insert_id = $this->custom_db->insert_record('test', [
            'test' => $data,
            'description' => $desc
        ]);

        return $insert_id['insert_id'] ?? 0;
    }

    function get_static_response(int $origin = 0): string
    {
        $data = $this->custom_db->single_table_records(
            'provab_api_response_history',
            'response',
            ['origin' => $origin]
        );

        return $data['data'][0]['response'] ?? '';
    }

    /**
	 * form sql condition for the ip array
	 * @param $cond Condition is array of array with each array having 3 params('col', 'comparision', 'value')
	 */

     function get_custom_condition(array $cond): string
    {
        $sql = ' AND ';

        if (!empty($cond)) {
            foreach ($cond as $v) {
                if (is_array($v) && count($v) == 3) {
                    $sql .= $v[0] . ' ' . $v[1] . ' ' . $v[2] . ' AND ';
                }
            }
        }

        return rtrim($sql, ' AND ');
    }

    function get_custom_query(string $query): array
    {
        $result_array = $this->db->query($query)->result_array();

        if ($result_array) {
            return [
                'status' => QUERY_SUCCESS,
                'data' => $result_array
            ];
        }

        return [
            'status' => QUERY_FAILURE,
            'data' => []
        ];
    }

}