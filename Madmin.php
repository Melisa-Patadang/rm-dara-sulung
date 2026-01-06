<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Madmin extends CI_Model {

    function cek_login($username, $password, $role)
    {
        $password = sha1($password);

        $this->db->where('username', $username);
        $this->db->where('password', $password);
        $this->db->where('role', $role);

        return $this->db->get('admin')->row();
    }

    function get_role() 
    {
        $query = $this->db->query("SHOW COLUMNS FROM admin LIKE 'role'");
        $row = $query->row();

        preg_match("/^enum\('(.*)'\)$/", $row->Type, $matches);

        return explode("','", $matches[1]);
    }

   public function getTwoMenu()
    {
    return $this->db->limit(2)->get('menu')->result_array();
}

    
}
