<?php
/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */


class Custom_Db extends CI_Model
{
    public function single_table_records(string $table, string $cols = '*', array $condition = [],int $offset = 0, int $limit = 100000000, array $order_by = []): array|string {
        if (empty($table)) {
            redirect('general/redirect_login?op=R');
        }

        foreach ($order_by as $k => $v) {
            $this->db->order_by($k, $v);
        }

        $tmp_data = $this->db->select($cols)->get_where($table, $condition, $limit, $offset);
        if ($tmp_data->num_rows() > 0) {
            return [
                'status' => QUERY_SUCCESS,
                'data' => $tmp_data->result_array()
            ];
        }

        return ['status' => QUERY_FAILURE];
    }

    public function multiple_table_cross_records(array $tables = [], string $cols = '*',array $joincondition = [], array $condition = [], int $offset = 0, int $limit = 1000, array $order_by = []): array|string {
        if (!valid_array($tables) || !valid_array($joincondition)) {
            redirect('general/redirect_login?op=R');
        }

        foreach ($order_by as $k => $v) {
            $this->db->order_by($k, $v);
        }
        $count_val = count($tables);
        for ($i = 1; $i < $count_val; $i++) {
            foreach ($joincondition as $ck => $cv) {
                $this->db->join($tables[$i], "$ck=$cv");
            }
        }

        $tmp_data = $this->db->select($cols)->get_where($tables[0], $condition, $limit, $offset)->result_array();
        return [
            'status' => QUERY_SUCCESS,
            'data' => $tmp_data
        ];
    }

    public function insert_record(string $table_name, array $data): array|string
    {
        $this->db->insert($table_name, $data);
        if ($this->db->affected_rows() > 0) {
            return [
                'status' => QUERY_SUCCESS,
                'insert_id' => $this->db->insert_id()
            ];
        }

        redirect('general/redirect_login?op=C');
    }

    public function update_record(string $table_name = '', array $data = [], array $condition = []): string
    {
        if (!valid_array($data) || !valid_array($condition)) {
            redirect('general/redirect_login?op=U');
        }

        $this->db->update($table_name, $data, $condition);
        return ($this->db->affected_rows() > 0) ? QUERY_SUCCESS : QUERY_FAILURE;
    }

    public function delete_record(string $table_name = '', array $condition = []): string
    {
        if (!valid_array($condition)) {
            redirect('general/redirect_login?op=D');
        }

        $this->db->delete($table_name, $condition);
        return QUERY_SUCCESS;
    }

    public function generate_static_response(string $data): int|string
    {
        $insert_id = $this->insert_record('test', ['test' => $data]);
        return $insert_id['insert_id'] ?? 0;
    }

    public function get_custom_condition(array $cond): string
    {
        $sql = ' AND ';
        foreach ($cond as $v) {
            $sql .= "$v[0] $v[1] $v[2] AND ";
        }
        return rtrim($sql, ' AND ');
    }

    public function get_result_by_query(string $query): array|bool
    {
        $result = $this->db->query($query);
        return ($result->num_rows() > 0) ? $result->result() : false;
    }

    public function get_phone_code_list(): array
    {
        $query = 'SELECT origin,country_code,name FROM api_country_list WHERE country_code!=0 GROUP BY (country_code) ORDER BY name ASC';
        return $this->db->query($query)->result_array();
    }
}
