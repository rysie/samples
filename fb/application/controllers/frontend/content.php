<?php

class Content extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->out["user"] = $this->authUser();
        $this->out["likesUs"] = $this->userLikesUs();
        
        $date_start = strtotime("2010-03-18 00:00:00");
        $date_stop = strtotime("2020-06-16 23:59:59");

        if (time() < $date_start || time() > $date_stop) {
            $this->out["time"] = date("Y-m-d H:i:s");
            $this->out["time_start"] = date("d.m.Y", $date_start);
            $this->out["time_stop"] = date("d.m.Y", $date_stop);
            if (time() < $date_start)
                $this->out["reason"] = "too_early";
            if (time() > $date_stop)
                $this->out["reason"] = "too_late";

            $this->tal->out = $this->out;
            $this->tal->display("frontend/time.html");
        }
    }

    private function existsInDB() {
        $this->db->where("uid", $this->out["user"]["id"]);
        //$this->db->join("meta", "meta.id_users = users.id_users");
        $result = $this->db->get("users");
        $udb = db_row_result($result);

        if ($udb)
            redirect("/alreadyVoted");
    }

    public function index() {
        $this->tal->out = $this->out;
        $this->tal->display("frontend/start.html");
    }

    public function info() {
        if (!$this->out["likesUs"])
            redirect("/");

        $this->existsInDB();

        $this->tal->out = $this->out;
        $this->tal->display("frontend/info.html");
    }

    public function go() {
        if (!$this->out["likesUs"])
            redirect("/");

        $this->existsInDB();

        $this->out["form_errors"] = false;
        $this->load->library("Form_validation");


        $this->form_validation->set_rules("imie", "<b>imię</b>", "trim|required");
        $this->form_validation->set_rules("nazwisko", "<b>nazwisko</b>", "trim|required");
        $this->form_validation->set_rules("telefon", "<b>Telefon</b>", "trim|required");
        $this->form_validation->set_rules("email", "<b>E-mail</b>", "trim|required|valid_email");
        $tmp = $this->input->post('email');
        $this->form_validation->set_rules("email2", "<b>Powtórz e-mail</b>", "trim|required|valid_email|callback_same_value[$tmp]");
        $this->form_validation->set_rules("cel", "<b>Cel podróży</b>", "required");
        $this->form_validation->set_rules("zakwaterowanie", "<b>Zakwaterowanie</b>", "required");
        $tmp = $this->input->post('zakwaterowanie');
        $this->form_validation->set_rules("zakwaterowanie_inne", "<b>Zakwaterowanie</b>", "trim|callback_zakwaterowanie_inne[$tmp]");
        $this->form_validation->set_rules("ostatnie_hotele", "<b>Podaj nazwy hoteli...</b>", "trim|required");
        $this->form_validation->set_rules("ile_razy_turystycznie", "<b>Ile razy wyjeżdżasz turystycznie</b>", "trim|required");
        $this->form_validation->set_rules("ile_razy_sluzbowo", "<b>Ile razy wyjeżdżasz służbowo</b>", "trim|required");
        $this->form_validation->set_rules("skad_wiesz", "<b>Skąd wiesz o sieci Focus</b>", "trim|required");
        $this->form_validation->set_rules("czy_nocowales", "<b>Czy nocowałeś w sieci Focus</b>", "trim|required");
        $tmp = $this->input->post('czy_nocowales');
        $this->form_validation->set_rules("czy_nocowales_gdzie", "<b>Czy nocowałeś w sieci Focus</b>", "trim|callback_czy_nocowales_gdzie[$tmp]");
        $this->form_validation->set_rules("gdzie_testowac", "<b>Gdzie chciałbyś przetestować hotel</b>", "required");
        $this->form_validation->set_rules("czy_aktywny", "<b>Czy jesteś aktywny w Internecie</b>", "trim|required");
        $tmp = $this->input->post('czy_aktywny');
        $this->form_validation->set_rules("czy_aktywny_gdzie", "<b>Czy jesteś aktywny w Internecie</b>", "trim|callback_czy_aktywny_gdzie[$tmp]");
        $this->form_validation->set_rules("zainteresowania", "<b>Zainteresowania</b>", "trim|required");
        $this->form_validation->set_rules("dlaczego_ambasador", "<b>Dlaczego byłbyś dobrym ambasadorem</b>", "trim|required");
        $this->form_validation->set_rules("rok_urodzenia", "<b>Rok urodzenia</b>", "trim|required");
        $this->form_validation->set_rules("plec", "<b>Płeć</b>", "trim|required");
        $this->form_validation->set_rules("wyksztalcenie", "<b>Wykształcenie</b>", "trim|required");
        $this->form_validation->set_rules("wojewodztwo", "<b>Województwo</b>", "trim|required");
        $this->form_validation->set_rules("adres", "<b>Adres</b>", "trim|required");
        $this->form_validation->set_rules("zgoda", "<b>Zgoda na przetwarzanie danych</b>", "trim|required");

        $validation = $this->form_validation->run();

        $fields = array("imie", "nazwisko", "telefon", "email", "email2", "cel",
            "zakwaterowanie", "zakwaterowanie_inne", "ostatnie_hotele", "ile_razy_turystycznie", "ile_razy_sluzbowo",
            "skad_wiesz", "czy_nocowales", "czy_nocowales_gdzie", "gdzie_testowac", "czy_aktywny", "czy_aktywny_gdzie",
            "zainteresowania", "miejsca_aktywnosci", "blog", "dlaczego_ambasador",
            "rok_urodzenia", "plec", "wyksztalcenie", "wojewodztwo", "adres", "zgoda");


        // validation failed or never run
        if ($validation === false) {
            foreach ($fields as $field) {
                $this->out["form"][$field] = form_prep($this->input->post($field));
                $this->out["errors"][$field] = form_error($field);
            }

            $this->out["has_errors"] = validation_errors();
            
            if (!$this->input->post("email"))
                $this->out["form"]["email"] = $this->out["user"]["email"];

            if (!$this->input->post("imie"))
                $this->out["form"]["imie"] = $this->out["user"]["first_name"];

            if (!$this->input->post("nazwisko"))
                $this->out["form"]["nazwisko"] = $this->out["user"]["last_name"];
            
            
            $this->tal->out = $this->out;
            $this->tal->display("frontend/question.html");
        } else {
            $save = $this->input->post();
            unset($save["email2"]);
            $save = array_merge($save, array(
                "uid" => $this->out["user"]["id"],
                //"uid" => random_string("alnum", 10),
                "datetime" => date("Y-m-d H:i:s"),
                "count" => 1,
                "profil_fb" => $this->out["user"]["link"]
            ));
            $this->db->set($save);
            $this->db->insert("users");

            $this->tal->out = $this->out;
            $this->tal->display("frontend/thankyou.html");
        }
    }

    public function alreadyVoted() {
        if (!$this->out["likesUs"])
            redirect("/");


        $this->tal->out = $this->out;
        $this->tal->display("frontend/alreadyVoted.html");
    }

    public function addAnswer() {
        die("haha");
        if (!$this->out["likesUs"])
            redirect("/");

        $this->existsInDB();

        $post = $this->input->post();

        //print_arr($post);

        if (
                !array_key_exists("agr_1", $post) ||
                !array_key_exists("agr_2", $post) ||
                strlen(trim($post["firstname"])) < 2 ||
                strlen(trim($post["surname"])) < 2 ||
                strlen(trim($post["phone"])) < 8 ||
                strlen(trim($post["hotel"])) < 3 ||
                strlen(trim($post["description"])) < 3
        ) {
            redirect("/go");
        }


        $this->db->set("uid", $this->out["user"]["id"]);
        $this->db->set("firstname", $post["firstname"]);
        $this->db->set("surname", $post["surname"]);
        $this->db->set("email", $this->out["user"]["email"]);
        $this->db->set("count", 1);
        $this->db->insert("users");
        $id = $this->db->insert_id();

        $this->db->set("id_users", $id);
        $this->db->set("hotel", $this->input->post("hotel"));
        $this->db->set("description", $this->input->post("description"));
        if (array_key_exists("agr_3", $post) && $post["agr_3"])
            $this->db->set("marketing_agreement", 1);
        $this->db->insert("meta");


        $this->tal->out = $this->out;
        $this->tal->display("frontend/thankyou.html");
    }

    public function back() {
        die("<script type=\"text/javascript\">top.location.href = \"{$this->profileUrl}\";</script>");
    }

    /**
     * ---------------------------------------------------
     * callback validators
     * 
     */
    public function same_value($v1, $v2) {
        if ($v1 !== $v2)
            $this->form_validation->set_message('same_value', 'Pola muszą być takie same.');

        return ($v1 === $v2);
    }

    public function zakwaterowanie_inne($v1, $v2) {
        if ($v2 == "inne" && strlen($v1) < 2) {
            $this->form_validation->set_message('zakwaterowanie_inne', "Wpisz zakwaterowanie, jakie wybierasz.");
            return false;
        }
        return true;
    }

    public function czy_nocowales_gdzie($v1, $v2) {
        if ($v2 == "tak" && strlen($v1) < 2) {
            $this->form_validation->set_message("czy_nocowales_gdzie", "Wpisz, w którym hotelu nocowałeś.");
            return false;
        }
        return true;
    }

    public function czy_aktywny_gdzie($v1, $v2) {
        if ($v2 == "tak" && strlen($v1) < 2) {
            $this->form_validation->set_message("czy_aktywny_gdzie", "Wpisz, gdzie jesteś aktywny.");
            return false;
        }
        return true;
    }

    public function select($v1) {
        if (!strlen($v1)) {
            $this->form_validation->set_message('select', '');
            return false;
        }
        return true;
    }

}
