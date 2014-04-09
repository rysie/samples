<?php

class Users extends MY_Admin {

    function __construct() {
        parent :: __construct();
        $this->authAdmin();
        $CI = &get_instance();
        $this->myClass = $CI->router->class;
        $this->load->model("admin/User_model");
        $this->table = "users";

        $this->tal->js = array("/js/jquery.validate.js", "/js/jquery.validate.messages_pl.js",
            "/js/jquery.validate.password.js", "/js/admin_{$this->myClass}.js");

        $this->tal->css = array("jquery.validate.password.css");
    }

    function index($start = 0, $sort = "id_users") {
        $this->load->library("pagination");

        $per_page = 100;
        $config["base_url"] = "/admin/users/index/";
        $config["uri_segment"] = 4;
        $config["total_rows"] = $this->User_model->countUsers();
        $config["per_page"] = $per_page;
        $this->pagination->initialize($config);

        $this->out["paginacja"] = $this->pagination->create_links();

        $this->out["current_page"] = $start;
        $this->tal->out = $this->out;
        $this->tal->userList = $this->User_model->getUsersList($start, $per_page, $sort);
        $this->tal->display("admin/users.html");
    }

    function edit($id) {
        $this->out["business_types"] = array(
            array("id" => 2, "type" => "sklep stacjonarny zielarski"),
            array("id" => 3, "type" => "sklep stacjonarny medyczny"),
            array("id" => 4, "type" => "sklep internetowy"),
            array("id" => 5, "type" => "dalsza odsprzedaż towaru"),
            array("id" => 1, "type" => "inne")
        );
        $default_data = array(
            "company" => "",
            "name" => "",
            "address" => "",
            "locality" => "",
            "zip" => "",
            "country" => "Polska",
            "nip" => "",
            "regon" => "",
            "phone" => "",
            "business_type" => 1);


        $this->tal->user = $this->User_model->getUser(array("id_users" => $id));
        $userdata_send = $this->User_model->getUserData($id, 1);
        $userdata_invoice = $this->User_model->getUserData($id, 2);

        if (!$userdata_send)
            $userdata_send = $default_data;
        if (!$userdata_invoice)
            $userdata_invoice = $default_data;

        $this->out["user_data"]["send"] = $userdata_send;
        $this->out["user_data"]["invoice"] = $userdata_invoice;
        $this->tal->out = $this->out;
        $this->tal->display("admin/users.html");
    }

    function activate($id) {
        if (!$id)
            redirect("/admin/users");
        $this->db->where("id_users", $id);
        $this->db->set("status", 3);
        $this->db->set("admin_token", NULL);
        $this->db->update("users");

        $this->db->where("id_users", $id);
        $result = $this->db->get("users");
        $user = db_row_result($result);
        
        $this->tal->out = $this->out;
        $tresc = $this->tal->display("frontend/emailUser_aktywacja.html", true);
        $this->load->library("Email");
        $this->email->from($this->out["adminEmail"], $this->out["serviceName"]);
        $this->email->to($user["email"]);
        $this->email->subject("Konto w serwisie " . $this->out["serviceName"] . " jest aktywne");
        $this->email->message($tresc);
        if (!$this->email->send()) {
            
        }

        redirect("/admin/users");
    }

    function delete($id) {
        if (!$id)
            redirect("/admin/users");
        $this->User_model->deleteUser($id);
        redirect("/admin/users");
//        $this->tal->message = "Wpis został usunięty.";
//        $this->tal->userList = $this->User_model->getUsers();
//        $this->tal->display("admin/users.html");
    }

    function edit_credentials($id = false) {
        if (!$id)
            redirect("/admin/users");
        $this->db->where("id_users", $id);
        $result = $this->db->get("users");

        $this->tal->credentials = db_row_result($result);
        $this->tal->display("admin/users.html");
    }

    function update_credentials($id = false) {
        $this->out["message"] = false;
        $creds = $this->input->post();
        if (!$id || !$creds)
            redirect("/admin/users");
        if (strlen($creds ["password"] && strlen($creds ["password"]) < 6))
            $this->out["message"] = "Hasło jest za krótkie";

        if (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $creds["email"]))
            $this->out["message"] = "Niepoprawny email";

        if ($this->out["message"]) {
            $this->db->where("id_users", $id);
            $result = $this->db->get("users");

            $this->tal->credentials = db_row_result($result);
            $this->tal->out = $this->out;
            $this->tal->display("admin/users.html");
        } else {
            $this->db->where("id_users", $id);
            if (strlen($creds["password"]) > 1)
                $this->db->set("password", sha1("this_is_salt" . $creds["password"]));
            $this->db->set("email", $creds["email"]);
            $this->db->set("status", $creds["status"]);
            $this->db->set("admin_token", NULL);
//            if (array_key_exists("enabled", $creds) && $creds["enabled"] == "on")
//                $this->db->set("status", 2);
//            else
//                $this->db->set("status", 1);
            $this->db->update("users");
            redirect("/admin/users");
        }
    }

    function update($id = false) {
        if (!
                $id)
            redirect("/admin/users");
        $tab = $this->input->post();

        $data_send = $this->input->post("data_send");
        $data_invoice = $this->input->post("data_invoice");

        $this->db->where("id_users", $id);
        $this->db->where("data_type", 1);
        $result = $this->db->get("users_data");

        $this->db->set("company", $data_send['company']);
        $this->db->set("name", $data_send['name']);
        $this->db->set("address", $data_send['address']);
        $this->db->set("zip", $data_send['zip']);
        $this->db->set("locality", $data_send['locality']);
        $this->db->set("country", $data_send['country']);
        $this->db->set("nip", null);
        $this->db->set("regon", null);
        $this->db->set("phone", $data_send["phone"]);

        if (db_row_result($result)) {
            $this->db->where("data_type", 1);
            $this->db->where("id_users", $id);
            $this->db->update("users_data");
        } else {
            $this->db->set("data_type", 1);
            $this->db->set("id_users", $id);
            $this->db->insert("users_data");
        }


        $this->db->where("id_users", $id);
        $this->db->where("data_type", 2);
        $result = $this->db->get("users_data");

        $this->db->set("company", $data_invoice['company']);
        $this->db->set("name", $data_invoice['name']);
        $this->db->set("address", $data_invoice['address']);
        $this->db->set("zip", $data_invoice['zip']);
        $this->db->set("locality", $data_invoice['locality']);
        $this->db->set("country", $data_invoice['country']);
        $this->db->set("nip", $data_invoice['nip']);
        $this->db->set("regon", $data_invoice['regon']);
        $this->db->set("business_type", $data_invoice['business_type']);

        if (db_row_result($result)) {
            $this->db->where("data_type", 2);
            $this->db->where("id_users", $id);
            $this->db->update("users_data");
        } else {
            $this->db->set("data_type", 2);
            $this->db->set("id_users", $id);
            $this->db->insert("users_data");
        }

        redirect(
                "/admin/users");
    }

    function checkUser() {
        if ($_POST["login"] == $_POST["old_login"]) {
            echo "true";

            return true;
        }
        echo $this->User_model->checkUser($_POST["login"]);
    }

}
