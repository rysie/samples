<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Contentstatic extends MY_Admin {

    function __construct() {
        parent::__construct();
        $this->authAdmin();

        $CI = &get_instance();
        $this->myClass = $CI->router->class;

        $this->table = "aktualnosci";
        $this->load->model("admin/Gallery_model");
        $this->load->model("admin/News_model");
        $this->tal->js = array("/js/admin_{$this->myClass}.js");
        
        $this->out = array();

        $this->out["message"] = "Proszę wybrać treść do edycji.";
    }

    function index() {
        $this->tal->contentList = $this->Contentstatic_model->getAllItems();
        $this->tal->display("admin/contentstatic.html");
    }

    function edit($id) {
        $this->tal->contentList = $this->Contentstatic_model->getAllItems();
        $this->tal->item = $this->Contentstatic_model->getItem($id);
        $this->tal->galleries = $this->Gallery_model->getGalleries();
        $this->tal->display("admin/contentstatic.html");
    }

    function update($id) {
        $tab = $_POST;
        $tab["id"] = $id;
        $this->Contentstatic_model->updateItem($tab);
        $this->tal->contentList = $this->Contentstatic_model->getAllItems();        
        $this->tal->message = "Treść została zaktualizowana.";
        $this->tal->display("admin/contentstatic.html");
    }

}

?>