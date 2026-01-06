<?php
    class Menu_model extends CI_Model {

    private $path = './uploads/menu/';
    private $table = 'menu';

    
   public function getPaginated()
    {
    $this->load->library('pagination');

    $total = $this->db->count_all($this->table);

    $config['base_url'] = base_url('kelolamenu/index');
    $config['total_rows'] = $total;
    $config['per_page'] = 5;

    $this->pagination->initialize($config);

    $start = $this->uri->segment(3, 0);

    $data['menu'] = $this->db
        ->limit($config['per_page'], $start)
        ->get($this->table)
        ->result_array();
    $data['pagination'] = $this->pagination->create_links();
    $data['start'] = $start;

    return $data;
    }

    
    public function getById($id)
    {
        return $this->db->where('id_menu', $id)->get('menu')->row_array();
    }

    private function upload_gambar()
    {
        $config['upload_path']    = $this->path;
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['max_size']      = 2048;

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('gambar')) {
            return $this->upload->data('file_name');
        }
        return null;
    }

    private function deleteFile($file)
    {
        if (!empty($file) && file_exists($this->path . $file)) {
            unlink($this->path . $file);
        }
    }

    public function storeMenu()
    {
        $gambar = $this->upload_gambar();

        $data = [
            'nama_menu' => $this->input->post('nama_menu'),
            'harga'     => $this->input->post('harga'),
            'kategori'  => $this->input->post('kategori'),
            'gambar'    => $gambar
        ];

        $this->db->insert('menu', $data);
    }

    public function updateMenuFull()
    {
        $id = $this->input->post('id_menu');
        $old = $this->getById($id);

        $gambar_baru = $this->upload_gambar();
        if ($gambar_baru) {
            $this->deleteFile($old['gambar']);
        } else {
            $gambar_baru = $old['gambar'];
        }

        $data = [
            'nama_menu' => $this->input->post('nama_menu'),
            'harga'     => $this->input->post('harga'),
            'kategori'  => $this->input->post('kategori'),
            'gambar'    => $gambar_baru
        ];

        $this->db->where('id_menu', $id)->update('menu', $data);
    }

    public function deleteMenuFull($id)
    {
        $old = $this->getById($id);
        $this->deleteFile($old['gambar']);
        $this->db->where('id_menu', $id)->delete('menu');
    }
}