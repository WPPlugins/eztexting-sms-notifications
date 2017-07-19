<?php

require_once 'class-ezsmsn-response.php';

class EZSMSN_Request {

    /**
     * make api call to ez gateway
     *
     * @param string $url
     * @param array $data
     * @return EZSMSN_Response
     */
    public function send($url, $data) {
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        curl_close($curl);

        $json = json_decode($response);
        $json = $json->Response;
        return new EZSMSN_Response($json);
    }
}

?>
