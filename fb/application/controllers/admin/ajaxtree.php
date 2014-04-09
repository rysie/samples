<?php

class AjaxTree extends MY_Admin {

    function __construct() {
        parent :: __construct();
        $this->authAdmin();

        if ($this->uri->segment(4))
            $this->table = $this->uri->segment(4);
        else
            die("Ajax: table not specified.");


        $this->load->model("admin/Tree_model");
    }

    function ajax_getTree() {
        if ($this->uri->segment(5) && (is_numeric($this->uri->segment(5))))
            $root_id = $this->uri->segment(5);
        else
            $root_id = 0;
        print(json_encode($this->Tree_model->getTree($this->table, $root_id)));
    }

    function ajax_getItem() {
        $id = $this->input->post("id");
        $view = $this->input->post("view");
        $this->tal->ajax = $this->Tree_model->getItem($this->table, $id);
        print $this->tal->display("admin/{$view}", false);
    }

    function ajax_toggleItem() {
        $id = $this->input->post("id");
        print($this->Tree_model->toggleItem($this->table, $id));
    }
    
    function ajax_toggleDiscount() {
        $id = $this->input->post("id");
        print($this->Tree_model->toggleDiscount($this->table, $id));
    }    

    function ajax_createItem() {
        $parent_id = $this->input->post("parent_id");
        print($this->Tree_model->createItem($this->table, $parent_id));
    }

    function ajax_deleteItem() {
        $id = $this->input->post("id");
        $toDel = array_flatten($this->Tree_model->getTree($this->table, $id), "id");
        array_push($toDel, $id);
        foreach ($toDel as $item)
            $this->Tree_model->deleteItem($this->table, $item);
    }

    function ajax_moveItem() {
        $this->Tree_model->moveNode($this->table, $this->input->post("old_parent"), $this->input->post("new_parent"), $this->input->post("id"), $this->input->post("position"));
    }

}

?>