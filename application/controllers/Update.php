<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Update extends CI_Controller 
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
        $token = $this->input->post('token', true);
        $data = $this->input->post('data', true);
        if($token==null || $data==null)
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

        if(base64_decode($data, true) == false)
        {
            echo json_encode($result);
            exit();
        }

        $data = base64_decode($data);
        $data = json_decode($data);
        
        $error = 0;
        $newData = array();
        if(is_array($data))
        {
            foreach ($data as $key => $item) {
                # code...
                $newItem = array('id'=>null, 'hasil'=>'', 'waktu_uji'=>'');
                
                    #cek apa id ada
                    if( isset($item->itemID) )
                    {
                        $newItem['id'] = $item->itemID;
                    }

                    #cek inputan hasil
                    if( isset($item->result) )
                    {
                        $newItem['hasil'] = $item->result;
                    }
                    
                    #cek inputan waktu uji
                    if( isset($item->testTime) )
                    {
                        $newItem['waktu_uji'] = $item->testTime;
                    }

                if($newItem['id']===null)
                {
                    $error = $error + 1;
                }else {
                    array_push($newData, $newItem);
                }
            }
        }
        else
        {
            echo json_encode($result);
            exit();
        }

        if($error!=0)
        {
            echo json_encode($result);
            exit();
        }

        $update = $this->ApiModel->update($newData);
        if($update['sts']==true)
        {
            $result['error'] = false;
            $result['message'] = $update['jml']. " record berhasil diupdate.";
        }
		echo json_encode($result);
    }

    public function lala()
    {
        $babi = array(
            array('itemID'=>'2611', 'result'=>'10', 'testTime'=>'10.00 - 10.30'),
            array('itemID'=>'2612', 'result'=>'20', 'testTime'=>'10.00 - 12.30'),
            array('itemID'=>'2613', 'result'=>'15', 'testTime'=>'10.00 - 11.30'),
            array('itemID'=>'2614', 'result'=>'25', 'testTime'=>'10.00 - 1.30'),
            array('itemID'=>'2615', 'result'=>'30', 'testTime'=>'10.00 - 14.30')
        );

        $babi1 = array('itemID'=>'2615', 'result'=>'25 lala');
        
        echo base64_encode( json_encode($babi) );
    }
}