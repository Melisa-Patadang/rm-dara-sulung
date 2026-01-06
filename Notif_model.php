<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notif_model extends CI_Model {

    public function get_max_transaksi_id()
    {
        $row = $this->db
            ->select_max('id_transaksi', 'max_id')
            ->get('transaksi_penjualan')
            ->row_array();

        return isset($row['max_id']) ? (int)$row['max_id'] : 0;
    }

    public function count_new_transaksi($last_seen_id)
    {
        return $this->db
            ->from('transaksi_penjualan')
            ->where('id_transaksi >', (int)$last_seen_id)
            ->count_all_results();
    }

   public function latest_transaksi($limit = 8, $last_seen_id = 0)
{
    $rows = $this->db
        ->select('id_transaksi, id_pesanan, id_user, tanggal_transaksi, total_transaksi')
        ->from('transaksi_penjualan')
        ->where('id_transaksi >', (int)$last_seen_id) 
        ->order_by('id_transaksi', 'DESC')
        ->limit((int)$limit)
        ->get()
        ->result_array();

    $items = [];
    foreach ($rows as $r) {
        $items[] = [
            'id'     => (int)$r['id_transaksi'],
            'title'  => 'Transaksi #' . (int)$r['id_transaksi'],
            'desc'   => 'Total: Rp ' . number_format((float)$r['total_transaksi'], 0, ',', '.'),
            'time'   => date('d/m/Y H:i', strtotime($r['tanggal_transaksi'])),
            'is_new' => 1
        ];
    }

    return $items;
}


}
