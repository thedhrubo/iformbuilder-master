<?php

class iformbuilder_api {

    public function __construct() {
        $this->profile = '479916';
        $this->page_id = '3715842';
        $this->server = 'https://app.iformbuilder.com';
        $this->client = 'b8a874436d9a9f93834ffd6fc57b80b3b3d9f290';
        $this->secret = '1460e20d9063b81ec5923fc97e5956d86ba02af2';
        $this->endpoint = 'https://app.iformbuilder.com/exzact/api/oauth/token';
        $this->exp = 600;
        $this->ch = NULL;
        $this->token = NULL;
        $this->issued = time();
    }

    private function encodeAssertion($client_key, $client_secret) {//*
        $iat = time();
        $payload = array(
            "iss" => $client_key,
            "aud" => $this->endpoint,
            "exp" => $iat + $this->exp,
            "iat" => $iat
        );

        return $this->encoder($payload, $client_secret);
    }

    private function encoder($payload, $client_secret) {//*
        return $this->encode($payload, $client_secret);
    }

    /**
     * api OAuth endpoint
     *
     * @param string $url
     *
     * @return Boolean
     */
    private function isValid($url) { //*
        return strpos($url, "exzact/api/oauth/token") !== false;
    }

    /**
     * Validate Endpoint
     *
     * @throws \Exception
     */
    private function validateEndpoint() { // *
        if (empty($this->endpoint) || !$this->isValid($this->endpoint)) {
            throw new \Exception('Invalid url: Valid format https://SERVER_NAME.iformbuilder.com/exzact/api/oauth/token');
        }
    }

    /**
     * Format Params
     *
     * @return string
     */
    private function getParams() {//*
        return $this->jwtFlowParameters();
    }

    private function jwtFlowParameters() {//*
        return array("grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
            "assertion" => $this->encodeAssertion($this->client, $this->secret));
    }

    /**
     * @param RequestHandler $iForm
     *
     * @return string
     * @throws \Exception
     */
    public function getToken() {//*
        try {
            $this->validateEndpoint();
            $params = $this->getParams();
            $result = $this->check($this->create($this->endpoint)
                            ->with(http_build_query($params)));
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        return $result;
    }

    private function loadToken() {//*
        //59 minutes
        if ($this->issued + 3500 < time()) {
            $this->setToken();
        }

        return $this->token;
    }

    private function setToken() {//*
        $this->token = $this->getToken($this);
        $this->issued = time();
    }

    public function create($url, $params = array()) {//*
        $this->setupCreate($url);
        if (!empty($params)) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($params));

            return $this->execute();
        }

        return $this;
    }

    public function init() {//*
        if (gettype($this->ch) !== 'resource')
            $this->ch = curl_init();
    }

    /**
     * @param $url
     */
    private function setupCreate($url) {//*
        $this->init();
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_POST, true);
        $this->baseCurl($url);
    }

    /**
     * @param array $params passed to method
     *
     * @throws \Exception
     * @return string
     */
    public function with($params) {//*
        if (!$this->ch)
            throw new \Exception('Invalid use of method.  Must declare request type before passing parameters');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);

        return $this->execute();
    }

    /**
     * Execute call
     *
     * @param null $header
     *
     * @return mixed
     * @throws \Exception
     */
    public function execute($header = null) { //*
        try {
            $response = $this->handle($header);
        } catch (\Exception $e) {
            $response = $e->getMessage();
        }

        return $response;
    }

    private function handle($header) {//*
        $requestCount = 0;
        do {
            list ($httpStatus, $response) = $this->request($header);
            $isRequestTimedOut = $httpStatus < 100 || $httpStatus == 503 || $httpStatus == 504; //api is busing | rate limiting
            $requestCount ++;

            if ($isRequestTimedOut)
                sleep(1);
        } while ($isRequestTimedOut && $requestCount < 5);

        $errorMsg = (is_array($response) && isset($response['body'])) ? $response['body'] : $response;
        if ($httpStatus < 200 || $httpStatus >= 400)
            throw new \Exception($errorMsg, $httpStatus);

        return $response;
    }

    /**
     * @param          $url
     * @param callable $setupOperation
     */
    private function baseCurl($url, Callable $setupOperation = null) {//*
//        $this->init();
        if (!is_null($setupOperation))
            $setupOperation();

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);

        $this->authorizeCall();
    }

    private function authorizeCall() {//*
        if (!is_null($this->token)) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->loadToken(),
            ));
        }
    }

    /**
     * Send Curl request
     *
     * @param null $header
     *
     * @return array
     */
    private function request($header = null) {//*
        $response = curl_exec($this->ch);
        if (!is_null($header)) {
            $result = array('header' => '', 'body' => '');
            $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
            $result['header'] = substr($response, 0, $header_size);
            $result['body'] = substr($response, $header_size);
        } else {
            $result = $response;
        }

        $errorCode = curl_errno($this->ch);
        $httpStatus = ($errorCode) ? $errorCode : curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        curl_close($this->ch);

        return array($httpStatus, $result);
    }

    /**
     * Check results
     *
     * @param $results
     *
     * @return string token || error msg
     */
    private function check($results) {//*
        $token = json_decode($results, true);

        return isset($token['access_token']) ? $token['access_token'] : $token['error'];
    }

    /**
     * Converts and signs a PHP object or array into a JWT string.
     *
     * @param object|array $payload PHP object or array
     * @param string       $key     The secret key
     * @param string       $algo    The signing algorithm. Supported
     *                              algorithms are 'HS256', 'HS384' and 'HS512'
     *
     * @return string      A signed JWT
     * @uses jsonEncode
     * @uses urlsafeB64Encode
     */
    function encode($payload, $key, $algo = 'HS256') {//*
        $header = array('typ' => 'JWT', 'alg' => $algo);

        $header_en = $this->base64url_encode($this->jsonEncode($header));
        $payload_en = $this->base64url_encode($this->jsonEncode($payload));

        $segments = array();
        $segments[] = $this->base64url_encode($this->jsonEncode($header));
        $segments[] = $this->base64url_encode($this->jsonEncode($payload));
        $signing_input = implode('.', $segments);

        $signature = $this->sign($signing_input, $key, $algo);
        $segments[] = $this->base64url_encode($signature);
        return implode('.', $segments);
    }

    /**
     * Sign a string with a given key and algorithm.
     *
     * @param string $msg    The message to sign
     * @param string $key    The secret key
     * @param string $method The signing algorithm. Supported
     *                       algorithms are 'HS256', 'HS384' and 'HS512'
     *
     * @return string          An encrypted message
     * @throws DomainException Unsupported algorithm was specified
     */
    function sign($msg, $key, $method = 'HS256') {//*
        $methods = array(
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        );
        if (empty($methods[$method])) {
            throw new DomainException('Algorithm not supported');
        }
        return hash_hmac($methods[$method], $msg, $key, true);
    }

    /**
     * Encode a PHP object into a JSON string.
     *
     * @param object|array $input A PHP object or array
     *
     * @return string          JSON representation of the PHP object or array
     * @throws DomainException Provided object could not be encoded to valid JSON
     */
    function jsonEncode($input) {//*
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            $this->_handleJsonError($errno);
        } else if ($json === 'null' && $input !== null) {
            throw new DomainException('Null result with non-null input');
        }
        return $json;
    }

    /**
     * Encode a string with URL-safe Base64.
     *
     * @param string $input The string you want encoded
     *
     * @return string The base64 encode of what you passed in
     */
    function base64url_encode($data) {//*
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Helper method to create a JSON error.
     *
     * @param int $errno An error number from json_last_error()
     *
     * @return void
     */
    function _handleJsonError($errno) { //*
        $messages = array(
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
        );
        throw new DomainException(
        isset($messages[$errno]) ? $messages[$errno] : 'Unknown JSON error: ' . $errno
        );
    }

    function saveData() {
        $data = json_decode(file_get_contents("php://input"));
        $this->token = $this->getToken();
        $url = 'https://app.iformbuilder.com/exzact/api/v60/profiles/' . $this->profile . '/pages/' . $this->page_id . '/records';
        $fields['fields'] = array();

        foreach ($data as $k => $v) {
            $temp = array();
            $temp['element_name'] = $k;
            if ($k == 'birth_date')
                $temp['value'] = date('Y-m-d', strtotime($v));
            else if ($k == 'subscribe')
                $temp['value'] = $v == 'on' ? '1' : '0';
            else
                $temp['value'] = $v;

            $fields['fields'][] = $temp;
        }
        $response = $this->sendRequest($url, $this->jsonEncode($fields), true);
        $newID = json_decode($response);
        if (is_int($newID->id) == true)
            return json_encode(array('msg' => 'success', 'id' => $newID->id));
        else
            return json_encode(array('msg' => $newID));
    }

    function getData(){
        $this->token = $this->getToken();
        $url = 'https://app.iformbuilder.com/exzact/api/v60/profiles/' . $this->profile . '/pages/' . $this->page_id . '/feed?FORMAT=JSON';
         return $this->sendRequest($url, '', false);
    }
    
    function sendRequest($url, $fields, $isPost = true) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        }

        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $this->token"
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
     function sendRequest_update($url, $fields, $isPost = true) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        }

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $this->token"
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
     function updateData() {
        $data = json_decode(file_get_contents("php://input"));
        $this->token = $this->getToken();
        $url = 'https://app.iformbuilder.com/exzact/api/v60/profiles/' . $this->profile . '/pages/' . $this->page_id . '/records/'.$data->id;
        $fields['fields'] = array();
            unset($data->id);
        foreach ($data as $k => $v) {
            $temp = array();
            $temp['element_name'] = $k;
            if ($k == 'birth_date')
                $temp['value'] = date('Y-m-d', strtotime($v));
            else if ($k == 'subscribe')
                $temp['value'] = $v == 'true' ? '1' : '0';
            else
                $temp['value'] = $v;

            $fields['fields'][] = $temp;
        }
        $response = $this->sendRequest_update($url, $this->jsonEncode($fields), true);
        $newID = json_decode($response);
         
        if (is_int($newID->id) == true)
            return json_encode(array('msg' => 'success', 'id' => $newID->id));
        else
            return json_encode(array('msg' => $newID));
    }
}