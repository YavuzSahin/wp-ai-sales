<?php


class FacebookConversionsAPI {

    private $pixel_id;
    private $access_token;
    private $api_url;

    // constructor to set up initial values
    public function __construct(){
        $this->pixel_id = '4148989822085327'; // replace with your actual Pixel ID
        $this->access_token = 'EAAP1unmiKA0BO6IVeENZAIDZAWP8F1x9Mj7XMEtfoLyRQI5dvAiR0iGfJiu6XPss9twYHkSH6Fo3viV9ITDFsCpE8xShxMxvbt9FbEZCcyz0qpnKqlJgNZACmxZBksPKm1fsl0vcMGvorXEzFK4VAe5L0WR4iik92bUQSwMh26UQhihwIiZByXGkjmddFIPPpBswZDZD'; // replace with your actual access token
        $this->api_url = "https://graph.facebook.com/v22.0/{$this->pixel_id}/events";
    }

    // method to send conversion data to Facebook
    public function sendEvent($event_name, $user_data = [], $custom_data = [], $event_id = null) {
        // determine if the request is over HTTPS or HTTP
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        // build the full URL of the current page
        $event_source_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        // collect Facebook cookies that are important for tracking
        $user_data['fbc'] = $_COOKIE["_fbc"];
        $user_data['fbp'] = $_COOKIE["_fbp"];

        // collect the client's IP address and user agent
        $user_data['client_ip_address'] = $_SERVER['REMOTE_ADDR'];
        $user_data['client_user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        // Generate event_id if not provided (you might want to use your own ID generation)
        if (!$event_id) {
            $event_id = uniqid('ev_', true);
        }

        // prepare event data to be sent to Facebook
        $event_data = [
            'event_name' => $event_name, // name of the event (e.g., 'Lead')
            'event_id' => $event_id, // Add the event_id here
            'event_time' => time(), // current timestamp
            'event_source_url' => $event_source_url, // URL where the event occurred
            'user_data' => $user_data, // hashed and non-hashed user data
            'custom_data' => $custom_data, // any additional custom data for the event
            'action_source' => 'website' // specifies that the event came from the website
        ];

        // prepare the full payload including the access token
        $payload = [
            'data' => [$event_data],
            'access_token' => $this->access_token
        ];

        // send the payload to Facebook using the makeRequest method
        return $this->makeRequest($payload);
    }

    // private method to handle the CURL request to Facebook
    private function makeRequest($payload) {
        $payload_encode = json_encode($payload); // encode the payload as JSON

        // initialize CURL with the API URL
        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_POST, true); // set CURL to use POST method
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_encode); // attach the payload
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return the response as a string
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // set the content type to JSON

        // execute the CURL request and get the response
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // get the HTTP status code
        curl_close($ch); // close the CURL session

        // check if the response was successful
        if ($http_code !== 200 || json_decode($response)->error) {
            return 'Error : ' . json_encode($response); // return error if the response is not OK
        } else {
            return true; // return true if the event was tracked successfully
        }
    }

    // method to hash user data as per Facebook's requirements
    public function hashUserData($data) {
        $hashed_data = [];

        // hash each piece of user data using SHA-256
        foreach ($data as $key => $value) {
            $hashed_data[$key] = hash('sha256', strtolower(trim($value))); // trim, lowercase, and hash the data
        }

        return $hashed_data; // return the hashed data
    }
}