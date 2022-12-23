<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerVerification;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Mail\DynamicEmail;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
   
    public function index(Request $request)
    {
        
        
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        for ($i = 0; $i < ob_get_level(); $i++) {
            ob_end_flush();
        }
        ob_implicit_flush(1);
        set_time_limit(0);
        $chandles = [];
        $start_time = microtime(true);
        $start_date = date('Y-m-d', strtotime('2012-01-01'));
        $curl = [];
        $dates = [];
        $mh = curl_multi_init();
        $i = 0;
        $period = new \DatePeriod(
            new \DateTime('2012-01-01'),
            new \DateInterval('P1D'),
            new \DateTime('2022-01-01')
       );
       foreach ($period as $key => $value) {
            $dates[]=$value->format('Y-m-d'); 
        }
        $call_request = 100;
        // if (count($dates) < $call_request) {
        //     $call_request = count($dates); // if ids are less then window size, set window size as of ids size
        // }
      
        //loop through ids to process | limited by window size   
        // echo $call_request;exit;
        for ($i = 0; $i < $call_request; ++$i) {
            $curl= curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://id.nadra.gov.pk/bioVerify/process.php',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => 'gzip,deflate',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TCP_FASTOPEN => 1,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    '6_letters_code' => 'tjgyyt',
                    'isSubmited' => true,
                    'identity_no' => '37407-0420874-7',
                    'issue_date' => $dates[$i],
                ),
                CURLOPT_HTTPHEADER => array(
                    'Cookie: PHPSESSID=575dee6fd735377a16d63eb4b36b3f1e'
                ),
            ));
            curl_multi_add_handle($mh, $curl);
            $chandles[] = $curl;
        }
        
        $prevRunning = null;
        do {
            $status = $this->curl_multi_exec_full($mh, $running);
            if($running < $prevRunning){
                while ($read = curl_multi_info_read($mh, $msgs_in_queue)) {
        
                    $info = curl_getinfo($read['handle']);
        
                    if($read['result'] !== CURLE_OK){
                        // print "Error: ".$info['url'].PHP_EOL;
                    }
        
                    if($read['result'] === CURLE_OK){
                      
                        $response = curl_multi_getcontent($read['handle']).PHP_EOL;
                        echo $response;
                        $rep = json_decode($response);
                        if($rep){
                            if($rep->resStatus=='success'){
                                break;
                            }
                        }
                    }
                }
            }
        
            if ($running > 0) {
                $this->curl_multi_wait($mh);
            }
        
            $prevRunning = $running;
        
        } while ($running > 0 && $status == CURLM_OK);
        foreach($chandles as $ch){
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);
        $time_end = microtime(true);
        $execution_time = ($time_end - $start_time);
        
        echo '<b>Total Execution Time:</b> ' . ($execution_time * 1000) . 'Milliseconds';
    }

    public function curl_multi_exec_full($mh, &$still_running) {
        do {
            $state = curl_multi_exec($mh, $still_running);
        } while ($still_running > 0 && $state === CURLM_CALL_MULTI_PERFORM && curl_multi_select($mh, 0.1));
        return $state;
    }
    public function curl_multi_wait($mh, $minTime = 0.001, $maxTime = 1){
        $umin = $minTime*1000000;
    
        $start_time = microtime(true);
        $num_descriptors = curl_multi_select($mh, $maxTime);
        if($num_descriptors === -1){
            usleep($umin);
        }
    
        $timespan = (microtime(true) - $start_time);
        if($timespan < $umin){
            usleep($umin - $timespan);
        }
    }
    

}
