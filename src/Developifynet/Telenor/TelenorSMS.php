<?php

namespace Developifynet\Telenor;

use Illuminate\Support\Traits\Macroable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class TelenorSMS
{
    use Macroable;

    // Username for SMS APIs
    private $_username = null;
    // Password for SMS APIs
    private $_password = null;
    // Receiver List which will get SMS
    private $_tos = null;
    // Text for Receiver List which will get SMS
    private $_text = null;
    // SMS Mast (optional)
    private $_mask = null;
    // Unicode for other than English (optional)
    private $_unicode = null;

    // Test Mode enable/disable
    private $_test_mode = 0;

    // Url where authentication will be done
    private $_auth_url = 'https://telenorcsms.com.pk:27677/corporate_sms2/api/auth.jsp';

    // Url where after authentication SMS will be sent.
    private $_sendsms_url = 'https://telenorcsms.com.pk:27677/corporate_sms2/api/sendsms.jsp';

    /*
     * Get Session ID after authentication
     * @param: void
     * @return: array|mixed
     */
    private function getSessionID() {

        $client = new Client();

        $status = true;
        $error_msg = '';
        $session_id = '';

        try {
            $response = $client->request('GET', $this->_auth_url, ['query' => array(
                'msisdn' => $this->_username,
                'password' => $this->_password,
            )]);

            if($response->getStatusCode() == 200) {
                $responseBody = $response->getBody();
                if($responseBody) {
                    try {
                        $responseJSON = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode(simplexml_load_string($responseBody, "SimpleXMLElement", LIBXML_NOCDATA)), true);

                        if(
                            (isset($responseJSON['response']) && $responseJSON['response'] == 'OK') &&
                            (isset($responseJSON['data']) && $responseJSON['data'])
                        ) {
                            $session_id = $responseJSON['data'];
                        } else {
                            $status = false;
                            $error_msg = 'Invalid credentials provided';
                        }
                    } catch (\Exception $e) {
                        $status = false;
                        $error_msg = 'Service is temporarily unavailable. Your patience is requested.';
                    }
                } else {
                    $status = false;
                    $error_msg = 'Unable to connect with server.';
                }
            } else {
                $status = false;
                $error_msg = $response->getReasonPhrase();
            }
        } catch (GuzzleException $e) {
            $status = false;
            $error_msg = $e->getMessage();
        }

        return array(
            'status' => $status,
            'session_id' => $session_id,
            'error_msg' => $error_msg
        );
    }

    /*
     * Send Quick SMS based on provided Data
     * @param: void
     * @return: array|mixed
     */
    private function sendQuickSMS($SessonID) {

        $client = new \GuzzleHttp\Client();

        $query = array(
            'session_id' => $SessonID,
            'to' => $this->_tos,
            'text' => $this->_text,
        );
        if($this->_mask) {
            $query['mask'] = $this->_mask;
        }

        // SMS Response Data
        $sms_data = '';

        try {
            $response = $client->request('GET', $this->_sendsms_url, ['query' => $query]);

            $status = true;
            $error_msg = '';

            if($response->getStatusCode() == 200) {
                $responseBody = $response->getBody();
                if($responseBody) {
                    try {
                        $responseJSON = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode(simplexml_load_string($responseBody, "SimpleXMLElement", LIBXML_NOCDATA)), true);
                        if(
                            (isset($responseJSON['response']) && $responseJSON['response'] == 'OK') &&
                            (isset($responseJSON['data']) && $responseJSON['data'])
                        ) {
                            $sms_data = $responseJSON['data'];
                        } else {
                            $status = false;
                            $error_msg = $this->tranlateError($responseJSON['data']);
                        }
                    } catch (\Exception $e) {
                        $status = false;
                        $error_msg = 'Service is temporarily unavailable. Your patience is requested.';
                    }
                } else {
                    $status = false;
                    $error_msg = 'Unable to connect with server.';
                }
            } else {
                $status = false;
                $error_msg = $response->getReasonPhrase();
            }
        } catch (ConnectException $e) {
            $status = false;
            $error_msg = $e->getMessage();
        }

        return array(
            'status' => $status,
            'sms_data' => $sms_data,
            'error_msg' => $error_msg
        );
    }

    /*
     * Send SMS based on provided Data
     * @param: array|mixed
     * @return: array|mixed
     */
    public function SendSMS($SMSData) {
        // Error handling variables
        $status = 1;
        $error_msg = array();

        if(!isset($SMSData['username']) || !$SMSData['username']) {
            $status = false;
            $error_msg[] = 'Username is required';
        } else {
            $this->_username = $SMSData['username'];
        }

        if(!isset($SMSData['password']) || !$SMSData['password']) {
            $status = false;
            $error_msg[] = 'Password is required';
        } else {
            $this->_password = $SMSData['password'];
        }

        if(!isset($SMSData['to']) || !$SMSData['to']) {
            $status = false;
            $error_msg[] = 'To is required';
        } else {
            $this->_tos = is_array($SMSData['to']) ? implode(',', $SMSData['to']) : $SMSData['to'];
        }

        if(!isset($SMSData['text']) || !$SMSData['text']) {
            $status = false;
            $error_msg[] = 'Text is required';
        } else {
            $this->_text = htmlentities($SMSData['text']);
        }

        if(!isset($SMSData['test_mode'])) {
            $status = false;
            $error_msg[] = 'Test Mode value is required';
        } else {
            $this->_test_mode = $SMSData['test_mode'];
        }

        if(isset($SMSData['mask']) && $SMSData['mask']) {
            $this->_mask = $SMSData['mask'];
        }

        // Verify Test Mode, If enable then send response immedately
        if($this->_test_mode) {
            return array(
                'status' => true,
                'sms_data' => 'Test Mode is enabled',
                'error_msg' => '',
            );
        }

        if(!$status) {
            // One or more information is needed
            return array(
                'status' => $status,
                'sms_data' => '',
                'error_msg' => implode(', ', $error_msg),
            );
        } else {
            // All vaidation is complete now authenticate
            $authResponse = $this->getSessionID();
            // Authentication is complete now send Quick SMS
            if($authResponse['status']) {
                // Send Quick SMS based on retrieved Session ID
                return $quickSMSResponse = $this->sendQuickSMS($authResponse['session_id']);
            } else {
                return array(
                    'status' => false,
                    'sms_data' => '',
                    'error_msg' => $authResponse['error_msg'],
                );
            }
        }
    }
    /*
     * Error Translation for SMS Gateway
     *
     * @param: string
     * @return: string
     */
    private function tranlateError($ErrorCode) {
        $errorCodes = array(
            'Error 200' => 'Failed login. Username and password do not match.',
            'Error 201' => 'Unknown MSISDN, Please Check Format i.e. 92345xxxxxxx',
            'Error 100' => 'Out of credit.',
            'Error 101' => 'Field or input parameter missing',
            'Error 102' => 'Invalid session ID or the session has expired. Login again.',
            'Error 103' => 'Invalid Mask',
            'Error 104' => 'Invalid operator ID',
            'Error 211' => 'Unknown Message ID.',
            'Error 300' => 'Account has been blocked/suspended',
            'Error 400' => 'Duplicate list name.',
            'Error 401' => 'List name is missing.',
            'Error 411' => 'Invalid MSISDN in the list.',
            'Error 412' => 'List ID is missing.',
            'Error 413' => 'No MSISDNs in the list.',
            'Error 414' => 'List could not be updated. Unknown error.',
            'Error 415' => 'Invalid List ID.',
            'Error 500' => 'Duplicate campaign name.',
            'Error 501' => 'Campaign name is missing.',
            'Error 502' => 'SMS text is missing.',
            'Error 503' => 'No list selected or one of the list IDs is invalid.',
            'Error 504' => 'Invalid schedule time for campaign.',
            'Error 506' => 'Cannot send message at the specified time. Please specify a different time.',
            'Error 507' => 'Campaign could not be saved. Unknown Error',
            'Error 600' => 'Campaign ID is missing',
            'Error 700' => 'File ID is missing',
            'Error 701' => 'File not available or not ready',
            'Error 702' => 'Invalid value for max retries',
            'Error 703' => 'Invalid value for Call ID',
            'Error 704' => 'Invalid Mask for IVR',
            'Error 301' => 'Incoming SMS feature is not available for current user',
            'Error 302' => 'In valid action attribute value',
            'Error 303' => 'User has entered date and is not valid date',
            'Error 304' => 'API throughput limit reached for TPS Control mode',
            'Error 305' => 'User SMS/recipients exceeds than allowed throughput',
        );

        if($ErrorCode && array_key_exists($ErrorCode, $errorCodes)) {
            return $errorCodes[$ErrorCode];
        } else {
            return 'No error code match.';
        }
    }
}

