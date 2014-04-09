<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class ContentTree extends MY_Admin {

    function __construct() {
        parent::__construct();
        $this->authAdmin();

        $CI = &get_instance();
        $this->myClass = $CI->router->class;

        $this->table = "content_tree";
        $this->load->model("admin/Tree_model");
        $this->Tree_model->initialize($this->table);
        $this->Tree_model->initialize($this->table . "_attributes");

        $this->tal->js = array("/js/admin_{$this->myClass}.js");
        $this->out = array();

        $this->out["message"] = "Proszę wybrać treść do edycji.";
    }

    function index() {
        $this->tal->out = $this->out;
        $this->tal->display("admin/contenttree.html");
    }

    function update() {
        $tab = $this->input->post();
        
        /*if ((key_exists("tabs", $tab)) && ($tab["tabs"]))
            $tab["tabs"] = 1; else
            $tab["tabs"] = 0;*/
            
        $id = $tab["id"];
        unset($tab["id"]);
        if ($this->Tree_model->updateItemAttributes($this->table, $id, $tab)) {
            $this->out["message"] = "Treść została zaktualizowana.";
        } else {
            $this->out["message"] = "Wystąpił błąd. Nie zaktualizowano treści.";
        }
        $this->tal->out = $this->out;
        $this->tal->display("admin/contenttree.html");
    }

}

?>