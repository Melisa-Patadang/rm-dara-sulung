<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dapur_model extends CI_Model {

  public function count_by_status(string $status): int
  {
    return (int)$this->db
      ->from('pesanan')
      ->where('status_pesanan', $status)
      ->count_all_results();
  }

  public function get_pesanan_dapur(array $hidden_ids = []): array
  {
    $this->db
      ->select('id_pesanan, tanggal_pesan, total_harga, status_pesanan')
      ->from('pesanan')
      ->where_in('status_pesanan', ['diproses','siap_ambil']);

    $hidden_ids = array_values(array_filter(array_map('intval', $hidden_ids), fn($v) => $v > 0));
    if (!empty($hidden_ids)) {
      $this->db->where_not_in('id_pesanan', $hidden_ids);
    }

    $orders = $this->db
      ->order_by('tanggal_pesan', 'DESC')
      ->get()
      ->result_array();

    if (empty($orders)) return [];

    $ids = array_column($orders, 'id_pesanan');

    $details = $this->db
      ->select('dp.id_pesanan, m.nama_menu, dp.jumlah')
      ->from('detail_pesanan dp')
      ->join('menu m', 'm.id_menu = dp.id_menu', 'left')
      ->where_in('dp.id_pesanan', $ids)
      ->order_by('dp.id_pesanan', 'ASC')
      ->get()
      ->result_array();

    $map = [];
    foreach ($details as $d) {
      $pid = (int)$d['id_pesanan'];
      if (!isset($map[$pid])) $map[$pid] = [];
      $map[$pid][] = [
        'nama_menu' => (string)($d['nama_menu'] ?? '-'),
        'jumlah'    => (int)($d['jumlah'] ?? 0),
      ];
    }

    foreach ($orders as &$o) {
      $pid = (int)$o['id_pesanan'];
      $o['status'] = (string)$o['status_pesanan'];
      $o['items']  = $map[$pid] ?? [];
    }
    unset($o);

    return $orders;
  }

  public function update_status(int $id_pesanan, string $status_baru): bool
  {
    $this->db->where('id_pesanan', $id_pesanan)
             ->update('pesanan', ['status_pesanan' => $status_baru]);

    return $this->db->affected_rows() > 0;
  }

  public function get_max_pesanan_id(): int
{
  $row = $this->db->select_max('id_pesanan', 'mx')
    ->from('pesanan')
    ->get()->row();

  return (int)($row->mx ?? 0);
}

public function count_new_pesanan_for_dapur(int $last_seen_id): int
{
  return (int)$this->db->from('pesanan')
    ->where('id_pesanan >', $last_seen_id)
    ->where_in('status_pesanan', ['diproses']) 
    ->count_all_results();
}


  public function log_status(int $id_pesanan, string $status): bool
{
  $this->db->insert('status_pesanan', [
    'id_pesanan'   => $id_pesanan,
    'status'       => $status,
    'waktu_update' => date('Y-m-d H:i:s')
  ]);
  return $this->db->affected_rows() > 0;
}

}
