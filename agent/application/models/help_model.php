<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Help_Model extends CI_Model
{
    /**
     * Fetch Help Center links grouped by menu and sub-menu IDs.
     *
     * @return array
     */
    public function fetchHelpLinks(): array
    {
        $result = $this->db->get('crs_help_links')->result();
        $linkArray = [];

        foreach ($result as $row) {
            $linkArray[] = $this->getHelpLinks((int)$row->menu_id, (int)$row->sub_menu_id);
        }

        return $linkArray;
    }

    /**
     * Get help links for given menu and sub-menu ID.
     *
     * @param int $menu_id
     * @param int $sub_menu_id
     * @return array
     */
    public function getHelpLinks(int $menu_id, int $sub_menu_id): array
    {
        return $this->db
            ->select('*')
            ->from('crs_sub_menus')
            ->where('menu_id', $menu_id)
            ->where('sub_menu_id', $sub_menu_id)
            ->get()
            ->result();
    }
}
