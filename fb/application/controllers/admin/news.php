<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class News extends MY_Admin {

    function __construct() {
        parent::__construct();
        $this->authAdmin();

        $CI = &get_instance();
        $this->myClass = $CI->router->class;

        $this->table = "aktualnosci";
        $this->load->model("admin/Tree_model");
        $this->load->model("admin/Gallery_model");
        $this->load->model("admin/News_model");
        
        $this->tal->css = array("smoothness/smoothness.css");
        $this->tal->js = array("/js/jquery-ui-1.8.9.full.min.js", "/js/jquery.ui.datepicker-pl.js", "/js/admin_{$this->myClass}.js");
        $this->out = array();

        $this->out["message"] = "Proszę wybrać aktualność do edycji.";
    }

    function index() {
        if (is_numeric($this->uri->segment(4)))
            $year = $this->uri->segment(4);
        else
            $year = date("Y");

        $this->tal->year = $year;
        $this->tal->newsList = $this->News_model->getNewsYear($year);
        $this->tal->years = $this->News_model->getYears();
        $this->tal->display("admin/news.html");
    }

    function edit($id) {
        $this->tal->news = $this->News_model->getItem($id);
        $this->tal->galleries = $this->Gallery_model->getGalleries();
        $this->tal->display("admin/news.html");
    }

    function create() {
        $galleries = false;
        $id = $this->News_model->createEmptyItem();
        $this->tal->news = $this->News_model->getItem($id);
        $this->tal->galleries = $this->Gallery_model->getGalleries();
        $this->tal->display("admin/news.html");
    }

    function update($id) {
        $tab = $_POST;
        $tab["id"] = $id;
        $this->News_model->updateItem($tab);
        $news = $this->News_model->getItem($id);
        $year = substr($news["datetime"], 0, 4);
        $this->tal->year = $year;
        $this->tal->years = $this->News_model->getYears();
        $this->tal->newsList = $this->News_model->getNewsYear($year);
        $this->tal->message = "Treść została zaktualizowana.";
        $this->tal->display("admin/news.html");
    }

    function delete($id) {
        $news = $this->News_model->getItem($id);
        $year = substr($news["datetime"], 0, 4);
        $this->News_model->deleteItem($id);
        redirect("/admin/news/index/{$year}");
    }

}

?>