<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Admin extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->out = array();
        /* skompiluj scss -> css */
        $this->sass->compile("css/admin.scss", "css/admin.css");

        define("USER_ADMIN", 1);
        define("USER_NORMAL", 2);
        define("USER_BLOCKED", 4);
        define("USER_SUPERADMIN", 128);

        $this->tal->hostname = $this->config->item("base_url");
        $this->out["serviceName"] = "Meridian";
        $this->out["adminEmail"] = "sklep@meridian.umbrella.pl";
        $this->subpath = "strefa_partnera/";
        $this->tal->subpath = "strefa_partnera/";
    }

    function authAdmin() {
        $this->load->library("session");
        $this->load->helper("url");
        $this->load->model("admin/Admins_model");
        if ($this->Admins_model->loggedIn()) {
            return $this->Admins_model->getAdmin(array("id_admins" => $this->session->userdata("id_admins")));
        } else {
            redirect(base_url() . "admin/welcome/login");
        }
    }

}
