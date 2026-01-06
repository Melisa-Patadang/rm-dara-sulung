<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notif extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Notif_model', 'notif');
        $this->load->library('session'); 
    }

    private function get_last_seen_id()
    {
        $last = (int)$this->session->userdata('notif_last_seen_id');
        $max  = (int)$this->notif->get_max_transaksi_id();
        if ($last > $max) {
            $last = 0;
            $this->session->set_userdata('notif_last_seen_id', $last);
        }
        if ($last <= 0) {
            $last = $max > 0 ? $max : 0;
            $this->session->set_userdata('notif_last_seen_id', $last);
        }

        return $last;
    }

    public function count()
    {
        $last_seen_id = $this->get_last_seen_id();
        $count = $this->notif->count_new_transaksi($last_seen_id);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['count' => (int)$count]));
    }

    public function list()
    {
        $last_seen_id = $this->get_last_seen_id();
        $items = $this->notif->latest_transaksi(8, $last_seen_id);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['items' => $items]));
    }

    public function seen()
    {
        $max_id = (int)$this->notif->get_max_transaksi_id();
        $this->session->set_userdata('notif_last_seen_id', $max_id);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['ok' => true, 'last_seen_id' => $max_id]));
    }
    public function reset_notif()
    {
        $this->session->unset_userdata('notif_last_seen_id');
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['ok' => true, 'message' => 'notif_last_seen_id direset']));
    }
}