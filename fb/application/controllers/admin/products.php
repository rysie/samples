<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Products extends MY_Admin {

    function __construct() {
        parent::__construct();
        $this->authAdmin();

        $CI = &get_instance();
        $this->myClass = $CI->router->class;

        $this->table = "products_tree";
        $this->load->model("admin/Tree_model");
        $this->load->model("admin/Products_model");

        $this->tal->js = array("/js/jquery-ui-1.8.9.full.min.js", "/js/admin_{$this->myClass}.js");

        $this->out = array();
        $this->out["message"] = "Proszę wybrać produkt do edycji.";
    }

    function index() {
        $this->out["action"] = false;
    }

    
    function generate() {
        $this->out["action"] = "generate";
        $this->tal->out = $this->out;
        $this->load->helper("Dompdf");
        $productsTree = $this->Tree_model->getAllChildren("products_tree", 1);
        $this->tal->hostname = $this->config->item("base_url");
        $this->tal->productsTree = $productsTree;
        
        $content = $this->tal->display("admin/cennik.html", true);
        file_put_contents("cenniki/test.html", $content);
        $cnt = file_get_contents("cenniki/test.html", $content);
        pdf_create($cnt, "cenniki/test.pdf");
    }
    
    function errors($subaction = false, $id = false) {
        $this->out["action"] = "errors";
        $this->out["errors"] = $this->Products_model->getErrors();
        if ($subaction) {
            $this->out["subaction"] = $subaction;
            switch ($subaction) {
                case "weight":
                    unset($this->out["message"]);
                    $this->out["product"] = $this->Products_model->getItem($id);
                    break;
                case "prices":
                    unset($this->out["message"]);
                    $this->out["product"] = $this->Products_model->getItem($id);
                    break;
            }
        }
    }

    function errors_update($subaction = false) {
        if (!$subaction)
            $this->out["message"] = "Wystąpił błąd - brak zdefiniowanej akcji.";
        else {
            $ok = false;
            $tab = $this->input->post();
            $id = $tab["id"];
            unset($tab["id"]);

            switch ($subaction) {
                case "weight":
                    $ok = $this->Tree_model->updateItemAttributes($this->table, $id, $tab);
                    break;
                case "prices":
                    $prices = $this->input->post("prices");
                    if ($prices)
                        $ok = $this->Products_model->updatePrices($id, $prices);
                    break;
            }
            if ($ok)
                $this->out["message"] = "Produkt został zaktualizowany.";
            else
                $this->out["message"] = "Wystąpił błąd w trakcie aktualizacji produktu.";
            $this->out["action"] = "errors";
            $this->out["errors"] = $this->Products_model->getErrors();
        }
    }

    function promocje($id = false) {
        $promos = $this->Products_model->getPromotions();
        if ($id) {
            $this->out["product"] = $this->Products_model->getItem($id);
            unset($this->out["message"]);
        }

        $this->out["promocje"] = $promos;
        $this->out["action"] = "promocje";
    }

    function promocje_update() {
        $promos = $this->Products_model->getPromotions();
        $this->out["promocje"] = $promos;
        $this->out["action"] = "promocje";

        $prices = $this->input->post("prices");
        $id = $this->input->post("id");

        if (!$id || !$prices) {
            $this->out["message"] = "Wystąpił błąd - brak produktu";
        } else {
            $this->Products_model->updatePrices($id, $prices);
            $promos = $this->Products_model->getPromotions();
            $this->out["promocje"] = $promos;
            $this->out["message"] = "Produkt został zaktualizowany.";
        }
    }

    
    function nowosci($id = false) {
        $nowosci = $this->Products_model->getMarkedAsNew();
        if ($id) {
            $this->out["product"] = $this->Products_model->getItem($id);
            unset($this->out["message"]);
        }

        $this->out["nowosci"] = $nowosci;
        $this->out["action"] = "nowosci";
    }

    function nowosci_update() {
        $nowosci = $this->Products_model->getMarkedAsNew();
        $this->out["nowosci"] = $nowosci;
        $this->out["action"] = "nowosci";

        $tab = $this->input->post();
        
        if ((key_exists("new", $tab)) && ($tab["new"]))
            $tab["new"] = 1; else
            $tab["new"] = 0;
        
        $id = $this->input->post("id");

        if (!$id || !$tab) {
            $this->out["message"] = "Wystąpił błąd - brak produktu";
        } else {
            $this->Tree_model->updateItemAttributes($this->table, $id, $tab);
            $nowosci = $this->Products_model->getMarkedAsNew();
            $this->out["nowosci"] = $nowosci;
            $this->out["message"] = "Produkt został zaktualizowany.";
        }
    }    
    
    function _output() {
        $this->tal->out = $this->out;
        $this->tal->display("admin/products.html");
    }

}

