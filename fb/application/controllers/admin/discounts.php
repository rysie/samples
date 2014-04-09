<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Discounts extends MY_Admin {

    function __construct() {
        parent::__construct();
        $this->authAdmin();

        $CI = &get_instance();
        $this->myClass = $CI->router->class;

        $this->table = "discounts";
        $this->load->model("admin/Discounts_model");
        
        $this->tal->css = array("smoothness/smoothness.css");
        $this->tal->js = array("/js/admin_{$this->myClass}.js");
        $this->out = array();

        $this->out["message"] = "Proszę wybrać rabat do edycji.";
    }

    function index() {
        $this->tal->discountsList = $this->Discounts_model->getDiscounts();
        $this->tal->display("admin/discounts.html");
    }

    function edit($id) {
        $this->tal->discount = $this->Discounts_model->getItem($id);
        $this->tal->display("admin/discounts.html");
    }

    function create() {
        $id = $this->Discounts_model->createEmptyItem();
        $this->tal->discount = $this->Discounts_model->getItem($id);
        $this->tal->display("admin/discounts.html");
    }

    function update($id) {
        $tab = $_POST;
        $tab["id"] = $id;
        $this->Discounts_model->updateItem($tab);
        $discount = $this->Discounts_model->getItem($id);
        $this->tal->discountsList = $this->Discounts_model->getDiscounts();
        $this->tal->message = "Wartość została zaktualizowana.";
        $this->tal->display("admin/discounts.html");
    }

    function delete($id) {
        $this->Discounts_model->deleteItem($id);
        redirect("/strefa_partnera/admin/discounts/index");
    }

}

?>