<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kelolamenu extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
    }
    
    public function index()
    {
    $data = $this->Menu_model->getPaginated();

    $this->load->view('header');
    $this->load->view('sidebar');
    $this->load->view('topnav');
    $this->load->view('kelolamenu', $data);
    $this->load->view('footer');
    }

    public function tambahmenu()
    {
        $this->load->view('header');
        $this->load->view('sidebar');
        $this->load->view('topnav');
        $this->load->view('tambahmenu');
        $this->load->view('footer');
    }

    public function store()
    {
        $this->Menu_model->storeMenu();
        redirect('kelolamenu');
    }

    public function edit($id)
    {
        $data['menu'] = $this->Menu_model->getById($id);

        $this->load->view('header');
        $this->load->view('sidebar');
        $this->load->view('topnav');
        $this->load->view('editmenu', $data);
        $this->load->view('footer');
    }

    public function update()
    {
        $this->Menu_model->updateMenuFull();
        redirect('kelolamenu');
    }

    public function delete($id)
    {
        $this->Menu_model->deleteMenuFull($id);
        redirect('kelolamenu');
    }
}
