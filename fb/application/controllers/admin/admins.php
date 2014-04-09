<?php

class Admins extends MY_Admin {

    function __construct() {
        parent :: __construct();
        $this->authAdmin();
        $CI = &get_instance();
        $this->myClass = $CI->router->class;

        $this->table = "admins";

        $this->tal->js = array("/js/jquery.validate.js", "/js/jquery.validate.messages_pl.js",
            "/js/jquery.validate.password.js", "/js/admin_{$this->myClass}.js");

        $this->tal->css = array("jquery.validate.password.css");
    }

    function index() {
        $this->tal->adminList = $this->Admins_model->getAdmins();
        $this->tal->display("admin/admins.html");
    }

    function edit($id) {
        $this->tal->admin = $this->Admins_model->getAdmin(array("id_admins" => $id));
        $this->tal->display("admin/admins.html");
    }

    function addAdmin() {
        $this->tal->addAdmin = true;
        $this->tal->display("admin/admins.html");
    }

    function create() {
        $tab = $_POST;
        $tab["password"] = sha1($tab["password"]);
        unset($tab["old_password"]);
        unset($tab["password_confirm"]);
        unset($tab["old_login"]);
            
        if (isset($tab["receive_messages"]))
            $tab["receive_messages"] = 1; else
            $tab["receive_messages"] = 0;

        $tab["type"] = 1;

        $this->tal->message = "Dodano nowego użytkownika.";
        $this->Admins_model->createAdmin($tab);
        $this->tal->adminList = $this->Admins_model->getAdmins();
        $this->tal->display("admin/admins.html");
    }

    function delete($id) {
        $this->Admins_model->deleteAdmin($id);
        $this->tal->message = "Wpis został usunięty.";
        $this->tal->adminList = $this->Admins_model->getAdmins();
        $this->tal->display("admin/admins.html");
    }

    function update() {
        $tab = $_POST;
        if (strlen($tab["password"]) > 0)
            $tab["password"] = sha1($tab["password"]);

        if ($tab["password"] == "")
            $tab["password"] = $tab["old_password"];

        unset($tab["old_password"]);
        unset($tab["password_confirm"]);
        unset($tab["old_login"]);

        if (isset($tab["receive_messages"]))
            $tab["receive_messages"] = 1; else
            $tab["receive_messages"] = 0;

        $this->tal->message = "Zaktualizowano dane administratora.";
        $this->Admins_model->updateAdmin($tab);
        $this->tal->adminList = $this->Admins_model->getAdmins();
        $this->tal->display("admin/admins.html");
    }

    function checkAdmin() {
        if ($_POST["login"] == $_POST["old_login"]) {
            echo "true";
            return true;
        }
        echo $this->Admins_model->checkAdmin($_POST["login"]);
    }

}

