<?php

class Content extends MY_Controller {

    function __construct() {
        parent :: __construct();
    }

    function index() {
        $this->load->library('Opauth/Opauth', $this->config->item('opauth_config'), false);
        $this->opauth->run();
        
        
        if ($this->uri->segment(3) == '') {
            $ci_config = $this->config->item('opauth_config');
            $arr_strategies = array_keys($ci_config['Strategy']);

            echo("Please, select an Oauth provider:<br />");
            echo("<ul>");
            foreach ($arr_strategies AS $strategy) {
                echo("<li><a href='" . base_url() . "auth/login/" . strtolower($strategy) . "'>Login with " . $strategy . "</li>");
            }
            echo("</ul>");
        } else {
            //Run login
            $this->load->library('Opauth/Opauth', $this->config->item('opauth_config'), false);
            $this->opauth->run();
        }
    }

    function index1() {
        $this->load->library("facebook", array("appId" => "537261893054891", "secret" => "e017e9bff01c534340491525e5e21fbe"));
        $user = $this->facebook->getUser();

        $params = array(
            'scope' => 'user_likes'
        );

        $params = array(
            'fbconnect' => 0,
            'canvas' => 1,
            'req_perms' => 'publish_stream,email',
                // For a full list of permissions: http://developers.facebook.com/docs/authentication/permissions
        );
        $loginUrl = $this->facebook->getLoginUrl($params);

        print_arr($loginUrl);

        $login_url = $this->facebook->getLoginUrl($params);

        if ($user_id) {
            // We have a user ID, so probably a logged in user.
            // If not, we'll get an exception, which we handle below.
            try {

                $user_profile = $this->facebook->api('/me', 'POST');
                echo "Name: " . $user_profile['name'];
            } catch (FacebookApiException $e) {
                // If the user is logged out, you can have a 
                // user ID even though the access token is invalid.
                // In this case, we'll get an exception, so we'll
                // just ask the user to login again here.
                echo 'Please <a href="' . $login_url . '">login.</a>';
                error_log($e->getType());
                error_log($e->getMessage());
            }
        } else {
            // No user, print a link for the user to login
            echo 'Please <a href="' . $login_url . '">login.</a>';
        }
    }

    function index0() {
        $this->load->library("facebook", array("appId" => "537261893054891", "secret" => "e017e9bff01c534340491525e5e21fbe"));
        $login_url = $this->facebook->getLoginUrl();
        echo 'Please <a href="' . $login_url . '">login.</a>';
        //  67400590475

        $isFan = $this->facebook->api(array(
            "method" => "fql.query",
            "query" => "SELECT uid FROM page_fan WHERE page_id = '67400590475' AND uid = me()"
        ));

        print_arr($isFan);

        $likes = $this->facebook->api("/me/likes");
        print_arr($likes);



        //$ret = $this->facebook->api(array("method" => "fql_query", "query" => "SELECT status FROM user WHERE uid = 100000004116032"));
        //$ret = $this->facebook->api(array("method" => "fql_query", "query" => "SELECT uid, page_id FROM page_fan WHERE uid=me() AND page_id=182107488488244"));
        //$ret = $this->facebook->api(array("method" => "fql_query", "query" => "SELECT uid, page_id FROM page_fan WHERE uid=100000004116032"));
        //print_arr($ret);

        $response = $this->facebook->api("/me");
        print_arr($response);


        //print_arr($access_token = $this->facebook->getAccessToken());
        //print_arr($this->input->get());

        $user_id = $this->facebook->getUser();

        if ($user_id) {

            // We have a user ID, so probably a logged in user.
            // If not, we'll get an exception, which we handle below.
            try {

                $user_profile = $this->facebook->api('/me', 'POST');
                echo "Name: " . $user_profile['name'];
            } catch (FacebookApiException $e) {
                // If the user is logged out, you can have a 
                // user ID even though the access token is invalid.
                // In this case, we'll get an exception, so we'll
                // just ask the user to login again here.
                $login_url = $this->facebook->getLoginUrl();
                echo 'Please <a href="' . $login_url . '">login.</a>';
                error_log($e->getType());
                error_log($e->getMessage());
            }
        } else {
            // No user, print a link for the user to login
            $login_url = $this->facebook->getLoginUrl();
            echo 'Please <a href="' . $login_url . '">login.</a>';
        }
    }

    function _output() {
        $this->tal->out = $this->out;
        //$this->tal->display("frontend/content.html");
    }

}
