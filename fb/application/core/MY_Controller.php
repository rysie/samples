<?php

if (!defined("BASEPATH"))
    exit("No direct script access allowed");

class MY_Controller extends CI_Controller {

    function __construct() {
        parent::__construct();

        /* skompiluj scss -> css */
        $this->sass->compile("css/main.scss", "css/main.css");

        // combine and minify frontend JS
        $jsOutFile = "js/__compiled.js";
        $jsFiles = array("js/frontend_funkcje.js");
        $jsMaxDate = 0;
        foreach ($jsFiles as $jsFile) {
            if ($jsMaxDate < filemtime($jsFile))
                $jsMaxDate = filemtime($jsFile);
        }
        if (!is_file($jsOutFile) || ($jsMaxDate > filemtime($jsOutFile))) {
            $this->load->driver("minify");
            $jsOut = $this->minify->combine_files($jsFiles);
            $fh = fopen($jsOutFile, "w") or die("Minify: can't open file for writing");
            chmod($jsOutFile, 0666);
            fwrite($fh, $jsOut);
            fclose($fh);
        }

        // init out array for TAL
        $this->out = array();
        $this->out["meta"] = "";

        // allow robots access by default
        $this->tal->robots = false;

        // set hostname for views
        $this->tal->hostname = $this->config->item("base_url");

        // set service name
        $this->out["serviceName"] = "";
        $this->out["adminEmail"] = "";
        $this->subpath = "focus-amb/";
        $this->tal->subpath = $this->subpath;

        // set current URI
        $this->tal->current_uri = current_url();

        // set email addresses for receiving orders
        $this->my_email = "";

        $this->appId = "";
        $this->secret = "";
        $this->profileId = "";
        $this->profileUrl = "https://www.facebook.com/HoteleFocus";
        $this->out["appId"] = $this->appId;
        $this->out["profileUrl"] = $this->profileUrl;
        $this->load->library("facebook", array("appId" => $this->appId, "secret" => $this->secret));
    }

    function authUser() {
        if (!$this->facebook->getUser()) {
            $params = array(
                "scope" => "email, user_likes",
                "canvas" => 1,
                "fbconnect" => 0,
                "redirect_uri" => "https://apps.facebook.com/{$this->appId}/",
                //"redirect_url" => "https://www.facebook.com/HoteleFocus/app_657969220937574"
            );
            $login_url = $this->facebook->getLoginUrl($params);
            //die("{$login_url}");
            die("<script type=\"text/javascript\">top.location.href = \"{$login_url}\";</script>");
        } else {
            try {
                $fb_user = $this->facebook->api("/me");
            } catch (Exception $e) {
                $params = array(
                    "canvas" => 1,
                    "fbconnect" => 0,
                    "redirect_uri" => "https://apps.facebook.com/{$this->appId}/",
                    //"redirect_url" => "https://www.facebook.com/HoteleFocus/app_657969220937574"
                );
                $login_url = $this->facebook->getLoginUrl($params);
                //die("{$login_url}");
                die("<script type=\"text/javascript\">top.location.href = \"{$login_url}\";</script>");
            }
        }
        return $fb_user;
    }

    function userLikesUs() {
        $this->authUser();
        $profileId = $this->profileId;
        $fb_user_likes = $this->facebook->api("/me/likes/{$profileId}");

        if (array_key_exists("data", $fb_user_likes) && sizeof($fb_user_likes["data"]) && $fb_user_likes["data"][0]["id"] === $profileId)
            return true;

        return false;
    }

}
