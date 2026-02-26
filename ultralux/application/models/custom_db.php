<?php
declare(strict_types=1);

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Library with generic database functions
 *
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A <balu.provab@gmail.com>
 * @version    V2
 */
class Custom_Db extends CI_Model
{
    /**
     * Get records from a table using a basic select
     */
    public function single_table_records(
        string $table,
        string $cols = '*',
        array $condition = [],
        int $offset = 0,
        int $limit = 100000000,
        array $order_by = []
    ): array {
        if (!empty($table)) {
            foreach ($order_by as $k => $v) {
                $this->db->order_by($k, $v);
            }

            $query = $this->db->select($cols)->get_where($table, $condition, $limit, $offset);

            if ($query->num_rows() > 0) {
                return ['status' => QUERY_SUCCESS, 'data' => $query->result_array()];
            }

            return ['status' => QUERY_FAILURE];
        }

        redirect('general/redirect_login?op=R');
    }

    /**
     * Get records from multiple tables using CROSS JOIN
     */
    public function multiple_table_cross_records(
        array $tables = [],
        string $cols = '*',
        array $joincondition = [],
        array $condition = [],
        int $offset = 0,
        int $limit = 1000,
        array $order_by = []
    ): array {
        if (!empty($tables) && !empty($joincondition)) {
            foreach ($order_by as $k => $v) {
                $this->db->order_by($k, $v);
            }

            for ($i = 1; $i < count($tables); $i++) {
                foreach ($joincondition as $ck => $cv) {
                    $this->db->join($tables[$i], "$ck = $cv");
                }
            }

            $query = $this->db->select($cols)->get_where($tables[0], $condition, $limit, $offset);
            return ['status' => QUERY_SUCCESS, 'data' => $query->result_array()];
        }

        redirect('general/redirect_login?op=R');
    }

    /**
     * Insert record into database
     */
    public function insert_record(string $table_name, array $data): array
    {
        $this->db->insert($table_name, $data);
        if ($this->db->affected_rows() > 0) {
            return ['status' => QUERY_SUCCESS, 'insert_id' => $this->db->insert_id()];
        }

        redirect('general/redirect_login?op=C');
    }

    /**
     * Update record in the database
     */
    public function update_record(string $table_name, array $data, array $condition): int
    {
        if (!empty($data) && !empty($condition)) {
            $this->db->update($table_name, $data, $condition);
            return $this->db->affected_rows() > 0 ? QUERY_SUCCESS : QUERY_FAILURE;
        }

        redirect('general/redirect_login?op=U');
    }

    /**
     * Delete record from the database
     */
    public function delete_record(string $table_name, array $condition): int
    {
        if (!empty($condition)) {
            $this->db->delete($table_name, $condition);
            return QUERY_SUCCESS;
        }

        redirect('general/redirect_login?op=D');
    }

    /**
     * Generate a static DB response for test/debug
     */
    public function generate_static_response(string $data): int
    {
        $insert = $this->insert_record('test', ['test' => $data]);
        return (int)$insert['insert_id'];
    }

    /**
     * Form SQL condition string from array
     */
    public function get_custom_condition(array $cond): string
    {
        if (empty($cond)) {
            return '';
        }

        $sql = array_reduce($cond, function ($carry, $v) {
            return $carry . "{$v[0]} {$v[1]} {$v[2]} AND ";
        }, ' AND ');

        return rtrim($sql, ' AND ');
    }

    /**
     * Get phone code list from the country table
     */
    public function get_phone_code_list(): array
    {
        $query = 'SELECT origin, country_code, name 
                  FROM api_country_list 
                  WHERE country_code != 0 
                  GROUP BY country_code 
                  ORDER BY country_code';

        return $this->db->query($query)->result_array();
    }
}
