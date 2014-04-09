<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class ProductsTree extends MY_Admin {

    function __construct() {
        parent::__construct();
        $this->authAdmin();

        $CI = &get_instance();
        $this->myClass = $CI->router->class;

        $this->table = "products_tree";
        $this->load->model("admin/Tree_model");
        $this->load->model("admin/Products_model");
        $this->Tree_model->initialize($this->table);
        $this->Tree_model->initialize($this->table . "_attributes");

        $this->tal->js = array("/js/jquery-ui-1.8.9.full.min.js", "/js/admin_{$this->myClass}.js");

        $this->out = array();
        $this->out["root_id"] = 0;
        $this->out["message"] = "Proszę wybrać produkt do edycji.";
    }

    function index($root_id = 0) {
        $this->out["root_id"] = $root_id;
        $this->tal->out = $this->out;
        $this->tal->display("admin/productstree.html");
    }

    function edit($id = false) {
        $product = $this->Products_model->getItem($id);
        $this->tal->ajax = array("product" => $product, "root_id" => $this->input->post('root_id'));
        $this->tal->ssid = $this->session->userdata('session_id');
        print $this->tal->display("admin/productstree.html", false);
    }

    function update($root_id = 0) {
        $this->out["root_id"] = $root_id;
        $tab = $this->input->post();

        if (!$tab)
            redirect("/admin/productstree");

        $tab["price"] = formatuj_kwote($tab["price"]);
        $tab["price_tax"] = formatuj_kwote($tab["price_tax"]);

        $id = $tab["id"];
        unset($tab["id"]);

        if (array_key_exists("photos", $tab)) {
            $photos = $tab["photos"];
            unset($tab["photos"]);

            $i = 1;
            $ph = array();
            foreach ($photos as $k => $p) {
                $ph["id"] = $p["id_item"];
                $ph["title"] = $p["title"];
                $ph["position"] = $i++;
                $this->Products_model->updatePhoto($ph);
            }
        }

        if (array_key_exists("docs", $tab)) {
            $photos = $tab["docs"];
            unset($tab["docs"]);

            $i = 1;
            $ph = array();
            foreach ($docs as $k => $p) {
                $ph["id"] = $p["id_item"];
                $ph["title"] = $p["title"];
                $ph["position"] = $i++;
                $this->Products_model->updateDoc($ph);
            }
        }

        if ($_FILES) {
            $this->load->helper("thumbnail_helper");
            if (!file_exists("./products/${id}")) {
                mkdir("./products/${id}");
                chmod("./products/${id}", 0777);
            }
            if (array_key_exists("new_photo", $_FILES) && $_FILES["new_photo"]["error"] == 0) {
                $config["upload_path"] = "./products/{$id}/";
                $config["allowed_types"] = "jpg|gif|png";
                $this->load->library("upload", $config);

                if (!$this->upload->do_upload("new_photo")) {
                    print ($this->upload->display_errors());
                } else {
                    $uploaded = $this->upload->data();
                    thumbnail_resize("./products/{$id}/{$uploaded["file_name"]}", 950, 713, "both", false);
                    thumbnail_resize("./products/{$id}/{$uploaded["file_name"]}", 300, 200, "both", "thumbsq");
                    thumbnail_generate("./products/{$id}/{$uploaded["file_name"]}", 150);
                    $newPhotoId = $this->Products_model->addPhoto($id, array("file" => $uploaded["file_name"]));
                }
            }

            if (array_key_exists("new_doc", $_FILES) && $_FILES["new_doc"]["error"] == 0) {
                $config["upload_path"] = "./products/{$id}/";
                $config["allowed_types"] = "doc|pdf|docx|zip|rar";
                $this->load->library("upload", $config);

                if (!$this->upload->do_upload("new_doc")) {
                    print ($this->upload->display_errors());
                } else {
                    $uploaded = $this->upload->data();
                    $newDocId = $this->Products_model->addDoc($id, array("file" => $uploaded["file_name"]));
                }
            }
        }

        if ($this->Tree_model->updateItemAttributes($this->table, $id, $tab)) {
            $this->out["message"] = "Produkt został zaktualizowany.";
        } else {
            $this->out["message"] = "Wystąpił błąd. Nie zaktualizowano produktu.";
        }

        $this->tal->out = $this->out;
        $this->tal->display("admin/productstree.html");
    }

    function ajax_deleteSinglePhoto() {
        $id = $this->input->post("id");
        $item = $this->Products_model->getPhoto($id);

        if ($item) {
            if (file_exists("products/{$item["id_product"]}/{$item["file"]}"))
                unlink("products/{$item["id_product"]}/{$item["file"]}");
            if (file_exists("products/{$item["id_product"]}/thumbs/{$item["file"]}"))
                unlink("products/{$item["id_product"]}/thumbs/{$item["file"]}");
            if (file_exists("products/{$item["id_product"]}/thumbsq/{$item["file"]}"))
                unlink("products/{$item["id_product"]}/thumbsq/{$item["file"]}");
            $this->Products_model->deleteSinglePhoto($id);
            $this->tal->message = "Usunięto zdjęcie.";
        }
    }

    function ajax_deleteSingleDoc() {
        $id = $this->input->post("id");
        $item = $this->Products_model->getDoc($id);

        if ($item) {
            if (file_exists("products/{$item["id_product"]}/{$item["file"]}"))
                unlink("products/{$item["id_product"]}/{$item["file"]}");
            $this->Products_model->deleteSingleDoc($id);
            $this->tal->message = "Usunięto załącznik.";
        }
    }

}
