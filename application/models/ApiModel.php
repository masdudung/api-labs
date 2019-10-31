<?php

class ApiModel extends CI_Model {

    # List Table yang dibutuhkan
    private $table1 = 'transaksi';
    private $table2 = 'transaksi_sample';
    private $table3 = 'transaksi_parameter';
    private $table4 = 'customer';
    private $table5 = 'parameter';
    private $table6 = 'metode';

    # khusus pathology
    private $divisi = 3;
    
    # Mengambil semua transaksi pada tanggal tertentu
    public function get_trans_by_tgl($tanggal_transaki)
    {
        # field yg tampil
        $field = "$this->table1.id_transaksi AS transID,";
        $field .= "$this->table1.jumlah_sample AS sampleCount,";
        $field .= "$this->table1.tanggal_transaksi AS transDate";
        
        $data = $this->db
            ->select($field)
            ->from($this->table1)
            ->where(
                array(
                    'id_divisi'=> $this->divisi,
                    'DATE(tanggal_transaksi)'=> $tanggal_transaki,
                )
            )
            ->order_by('tanggal_transaksi', 'DESC')
            ->get()->result();
        
        return $data;
    }

    public function get_transdetail_by_id($id_transaksi)
    {
        # field yg tampil dari tabel transaksi
        $field1 = "id_transaksi AS transID, ";
        $field1 .= "tanggal_transaksi AS transaction_date, ";
        $field1 .= "tanggal_selesai AS completion_date, ";
        $field1 .= "tanggal_bayar AS payment_date, ";
        $field1 .= "id_customer AS customerID, ";
        $field1 .= "keterangan_customer AS customer_notes, ";
        $field1 .= "id_sample AS sampleID, ";
        $field1 .= "id_petugas_sampling AS sampling_officerID, ";
        $field1 .= "nama_petugas_sampling AS sampling_officerName, ";
        $field1 .= "jumlah_sample AS samples_count, ";
        $field1 .= "CASE WHEN wadah_sample = 0 THEN 'Plastik' ELSE 'Gelas' END AS samples_container, ";
        $field1 .= "CASE WHEN pengambilan_sample = 0 THEN 'Diantar' ELSE 'Diambil' END AS sampling, ";
        $field1 .= "CASE WHEN lunas = 0 THEN 'Belum lunas' ELSE 'Lunas' END AS payment_status, ";
        $field1 .= "dokter_rujukan AS referralDoctor";
        

        # field yg tampil dari tabel customer
        $field2 = "CASE WHEN type_customer = 0 THEN 'Institusi' ELSE 'Perorangan' END AS customer_type , ";
        $field2 .= "user_id AS uID, ";
        $field2 .= "nama_customer AS customer_name, ";
        $field2 .= "telp_customer AS phone, ";
        $field2 .= "email_customer AS email, ";
        $field2 .= "provinsi AS province, ";
        $field2 .= "kabupaten AS districts, ";
        $field2 .= "kecamatan AS sub-district, ";
        $field2 .= "desa AS village, ";
        $field2 .= "alamat_customer AS address, ";
        $field2 .= "bidang_usaha AS business_field, ";
        $field2 .= "nama_pic AS pic_name, ";
        $field2 .= "alamat_pic AS pic_address, ";
        $field2 .= "tanggal_register AS register_date, ";
        $field2 .= "tanggal_lahir AS dateofbirth";

        # field yg tampil dari tabel transaksi sample
        $field3 = "id AS sampleID, ";
        $field3 .= "kode_sample AS sampleCode, ";

        # field yg tampil dari tabel transaksi parameter
        $field4 = "id AS itemID, ";
        $field4 .= "id_transaksi_sample AS sampleID, ";
        $field4 .= "$this->table5.id_parameter AS paramID, ";
        $field4 .= "$this->table5.nama_parameter AS paramName, ";
        $field4 .= "$this->table6.id_metode AS methodID, ";
        $field4 .= "$this->table6.nama_metode AS methodName, ";

        # select transaksi
        $transaksi = $this->db
            ->select($field1)
            ->from($this->table1)
            ->where(
                array(
                    'id_transaksi'=> $id_transaksi,
                )
            )
            ->get()->result();
        
        # select customer berdasarkan transaksi diatas
        $customer = [];
        if(count($transaksi)>0)
        {
            $customer = $this->db
                ->select($field2)
                ->from($this->table4)
                ->where(
                    array(
                        'id_customer'=> $transaksi[0]->customerID,
                    )
                )
                ->get()->result();
        }

        $sample = [];
        if(count($transaksi)>0)
        {
            $sample = $this->db
                ->select($field3)
                ->from($this->table2)
                ->where(
                    array(
                        'id_transaksi'=> $id_transaksi,
                    )
                )
                ->get()->result();
            $query_sampleID = $this->db
                ->select('id_transaksi_sample')
                ->from($this->table2)
                ->where(
                    array(
                        'id_transaksi'=> $id_transaksi,
                    )
                )
                ->get_compiled_select();
        }

        $parameter = [];
        if(count($sample)>0)
        {
            $table5_join = "(SELECT id_parameter, nama_parameter FROM $this->table5) AS $this->table5";
            $table6_join = "(SELECT id_metode, nama_metode FROM $this->table6) AS $this->table6";

            $parameter = $this->db
                ->select($field4)
                ->from($this->table3)
                ->join($table5_join, "$this->table5.id_parameter = $this->table3.id_parameter")
                ->join($table6_join, "$this->table6.id_metode = $this->table3.id_metode")
                ->where("`id_transaksi_sample` IN ($query_sampleID)", NULL, FALSE)
                ->get()->result();
        }
        
        if(count($parameter)>0)
        {
            
            foreach ($sample as $key1 => $item1) 
            {
                $sample[$key1]->{'listParam'} = array();
                foreach ($parameter as $key2 => $item2) 
                {
                    # code...
                    if($item1->sampleID == $item2->sampleID)
                    {
                        array_push($sample[$key1]->{'listParam'}, $item2);
                    }
                }

            }
        }
        
        return array(
            'customer' => $customer,
            'trans' => $transaksi,
            'sample' => $sample,
        );
    }

    public function update($_data)
    {
        $rows = 0;

        # start DB transaction
        $this->db->trans_begin();

            $data = array_chunk($_data, 1);
            foreach ($data as $key => $item) {
                # code...
                $this->db->update_batch($this->table3, $item, 'id');
                $rows = $rows + $this->db->affected_rows();
            }
            $lala = array('sts'=>TRUE, 'jml'=> $rows);
        
        if ($this->db->trans_status() === FALSE) {
            # Something went wrong.
            $this->db->trans_rollback();
            return array('sts'=>FALSE);
        } else {
            # Everything is Perfect. 
            # Committing data to the database.
            if ($rows =! count($_data)) {
                $this->db->trans_rollback();
                return false;
            }

            $this->db->trans_commit();
            return $lala;
        }

        # Complete DB transaction
        $this->db->trans_off();
    }

}
