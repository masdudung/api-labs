<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transdetail extends CI_Controller 
{

    private $app_id;
    private $app_key;

	public function __construct()
	{
        # load model dan constructor
        parent::__construct();
        $this->load->model('ApiModel');
        
        # valid $app_id & $app_key
        $this->app_id = '1829174060561133';
        $this->app_key = 'EAAaogEPvNnYBANtIWUtHUE8P';
	}

	public function index()
	{	
		# set header
		http_response_code(200);
        header('Content-Type: application/json');

        # set message
        $result = array(
            'error'     => true,
            'message'   => 'Parameter tidak lengkap'
        );

        # set app_id & app_key
        $app_id = '';
        $app_key = '';
        
        # cek token & tanggal null atau terisi
        $token = $this->input->get('token', true);
        $id_transaksi = $this->input->get('id', true);
        if($token==null || $id_transaksi==null)
        {
            echo json_encode($result);
            exit();
        }

        # token adalah base64 dari $app_id:$app_key, maka dicek apakah token valid atau tidak
        if (base64_decode($token, true) === false) 
        {
            $result['message'] = 'Token Tidak valid';
            echo json_encode($result);
            exit();
        }
        
        # jika token base64 valid, maka di explode, kemudian dicek apakah app id terdaftar atau tidak
        $token = base64_decode($token);
        $token = explode(":", $token);
        if(count($token)==2){
            $app_id = $token[0];
            $app_key = $token[1];
        }
        
        # cek $app_key & $app_key
        if($app_id !== $this->app_id || $app_key !== $this->app_key ){
            $result['message'] = 'Token Tidak valid';
            echo json_encode($result);
            exit();
        }

        # panggil transaksi hari ini
        $result['error'] = false;
		$result['message'] = $this->ApiModel->get_transdetail_by_id($id_transaksi);
		echo json_encode($result);
    }
    
}
