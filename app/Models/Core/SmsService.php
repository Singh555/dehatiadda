<?php
namespace App\Models\Core;
use Log;
use App\Models\Core\SmsTxnHistory;
//use DB;

class SmsService {
    /*
     * Schedule New SMS
     */
    public static function scheduleNewSMS($mobile_no, $message_text, $message_type, $sent_type)
    {
        try
        {
            $smsTxnHistory = new SmsTxnHistory;
            $smsTxnHistory->message_text = $message_text;
            $smsTxnHistory->mobile_no = $mobile_no;
            $smsTxnHistory->message_type = $message_type;
            $smsTxnHistory->sent_type = $sent_type;
            $smsTxnHistory->created_by = 'AUTO';
            //DB::beginTransaction();
            if($smsTxnHistory->save()){
                Log::debug(__CLASS__." :: ".__FUNCTION__." send type found as $sent_type");
                return self::sendBulkSMS($smsTxnHistory->id, $mobile_no, $message_text, $sent_type);
            }
            session()->put("error","SMS Sending Failed");
            return false;
        } catch (Exception $ex) {
            Log::error(__CLASS__."::".__FUNCTION__." Exception :: ".$ex->getMessage());
            session()->put("error","Exception While Sending SMS. Please try again");
            return false;
        }
    }
    /*
     * Send Bulk SMS
     */
    private static function sendBulkSMS($id, $mobile_no, $message_text, $sent_type)
    {
        if(empty($mobile_no))
        {
            Log::error(__CLASS__." :: ".__FUNCTION__." Mobile numbers should not be empty !!!");
            return false;
        }
        if(empty($message_text))
        {
            Log::error(__CLASS__." :: ".__FUNCTION__." Message text should not be empty !!!");
            return false;
        }
        $url_link= config('app.sms_url_link');
        // Account details
	$apiKey = urlencode(config('app.api_key'));
	
	// Message details
	$sender = urlencode(config('app.sender_id'));
	$message = rawurlencode($message_text);
 
        // Prepare data for POST request
	$post_fields = array('apikey' => $apiKey, 'numbers' => $mobile_no, "sender" => $sender, "message" => $message);
        Log::info($post_fields);
        $sendSMSResp = self::callUrl($url_link, $post_fields);
        $jsonDecodeResp = json_decode($sendSMSResp, TRUE);
        $sent_message_id="";
        if(json_last_error() == JSON_ERROR_NONE)
        {
            $error_message = '';
            //Log::error(__FUNCTION__." json error message ".$jsonDecodeResp['errors']);
            if(isset($jsonDecodeResp['status']) && $jsonDecodeResp['status'] =='failure'){
                if(isset($jsonDecodeResp['errors']) && count($jsonDecodeResp['errors']) > 0){
                for($i=0;$i<count($jsonDecodeResp['errors']);$i++){
                    if(isset($jsonDecodeResp['errors'][$i]['message'])){
                    $error_message .= $jsonDecodeResp['errors'][$i]['message'];
                    }
                    
                }
                
              }
            }
            $sent_response = $error_message;
            Log::error(__FUNCTION__." json error message2 ".$sent_response);
            
            if(isset($jsonDecodeResp['batch_id']) and !empty($jsonDecodeResp['batch_id']))
            {
                Log::info(__FUNCTION__." json job ID ".$jsonDecodeResp['batch_id']);
                $sent_message_id = $jsonDecodeResp['batch_id'];
            }
        }
        else
        {
            Log::debug(__FUNCTION__." Send SMS Response = $sendSMSResp");
            $sent_response = $sendSMSResp;
        }
        if(json_last_error() == JSON_ERROR_NONE and $jsonDecodeResp['status'] == "success")
        {
            Log::info($jsonDecodeResp['message']);
            return self::updateSMSHistoryById($id, $sent_response, $sent_message_id, $sent_type);
        }
        
        self::updateSMSHistoryById($id, $sent_response, $sent_message_id, $sent_type);
        return false;
    }
    /*
     * Add call third party URL for sending SMS
     */
    protected static function callUrl($url, $data) {
        // Send the POST request with cURL
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	
        Log::debug($response);
        
	// Process your response here
	return $response;
    }
    protected static function callUrl2($url)
    {
        $headers = array();
        $headers[] = 'X-Authorization: '.config('app.api_key');

        $options = array (CURLOPT_RETURNTRANSFER => true, // return web page
        CURLOPT_HEADER => $headers, // don't return headers
        CURLOPT_FOLLOWLOCATION => false, // follow redirects
        CURLOPT_ENCODING => "", // handle compressed
        CURLOPT_USERAGENT => "test", // who am i
        CURLOPT_AUTOREFERER => true, // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
        CURLOPT_TIMEOUT => 120, // timeout on response
        CURLOPT_MAXREDIRS => 10 ); // stop after 10 redirects

        $ch = curl_init ( $url );
        curl_setopt_array ( $ch, $options );
        $content = curl_exec ( $ch );
        $err = curl_errno ( $ch );
        $errmsg = curl_error ( $ch );
        $header = curl_getinfo ( $ch );
        $httpCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );

        curl_close ( $ch );

        $header ['errno'] = $err;
        $header ['errmsg'] = $errmsg;
        $header ['content'] = $content;
        return $header ['content'];
    }
    protected static function updateSMSHistoryById($id, $sent_response, $sent_message_id, $sent_type)
    {
        try
        {
            $smsTxnHistory = SmsTxnHistory::find($id);
            $retry_count = ($smsTxnHistory->retry_count+1);
            $smsTxnHistory->retry_count = $retry_count;
            $smsTxnHistory->sent_date = now();
            $smsTxnHistory->message_id = $sent_message_id;
            $smsTxnHistory->sent_status = $sent_response;
            $smsTxnHistory->updated_by = 'AUTO';
            if($smsTxnHistory->save()){
                if($sent_type === "MANUAL")
                {
                    //DB::commit();
                }
                return true;
            }
            return false;
        } 
        catch (PDOException $ex) {
            Log::debug(__CLASS__." :: ".__FUNCTION__." ".$ex->getMessage());
            //DB::rollback();
            return false;
        }
    }
}
