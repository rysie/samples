<?php

class Gallery extends MY_Admin {

    function __construct() {
        parent :: __construct();

        $this->load->model("admin/Tree_model");
        $this->load->model("admin/Gallery_model");
        $this->table = "gallery_tree";
        $this->Tree_model->initialize($this->table);
        $this->Tree_model->initialize($this->table . "_attributes");
        $this->Tree_model->initialize("gallery_items", false);

        $this->out = array();
        $this->tal->css = array("swfupload.css");
        $this->tal->js = array("/js/swfupload.js", "/js/swfupload.queue.js", "/js/swfupload.handlers.js",
            "/js/swfupload.fileprogress.js", "/js/jquery-ui-1.8.9.full.min.js", "/js/admin_gallery.js");

        if ((!array_key_exists("swfuploadauth", $_POST)) || ($_POST["swfuploadauth"] != "auth" ))
            $this->authAdmin();
    }

    function index() {
        $this->tal->out = $this->out;
        $this->tal->display("admin/gallery.html");
    }

    function ajax_deleteGallery() {
        /* removes GALLERY and ALL its children */
        $this->load->helper("file_helper");
        $id = $this->input->post("id");
        $toDel = array_flatten($this->Tree_model->getTree($this->table, $id), "id");
        array_push($toDel, $id);
        foreach ($toDel as $item) {
            if (file_exists("gallery/{$item}")) {
                delete_files("gallery/{$item}/", TRUE);
                rmdir("gallery/{$item}");
            }
            $this->Tree_model->deleteItem($this->table, $item);
        }
        print("<h2>Galeria została usunięta.</h2>Kliknij <a href='/admin/gallery'>tutaj</a>, aby powrócić do listy.");
    }

    function ajax_deleteSinglePhoto() {
        $id = $this->input->post("id");
        $item = $this->Gallery_model->getItem($id);

        if ($item) {
            if (file_exists("gallery/{$item["id_gallery"]}/{$item["file"]}"))
                unlink("gallery/{$item["id_gallery"]}/{$item["file"]}");
            if (file_exists("gallery/{$item["id_gallery"]}/thumbs/{$item["file"]}"))
                unlink("gallery/{$item["id_gallery"]}/thumbs/{$item["file"]}");
            $this->Gallery_model->deleteSinglePhoto($id);
            $this->tal->message = "Usunięto zdjęcie.";
        }
        $this->tal->out = $this->out;
        $this->tal->gallery = $this->Gallery_model->getGallery($item["id_gallery"]);
        $this->tal->display("admin/gallery.html");
    }

    function editGallery($id) {
        $this->out["sessionId"] = $this->session->userdata('session_id');
        $this->tal->gallery = $this->Gallery_model->getGallery($id);
        $this->tal->out = $this->out;
        $this->tal->display("admin/gallery.html");
    }

    function updateGallery() {
        $this->tal->out = $this->out;
        $id_gallery = $_POST["id_gallery"];
        $this->load->helper("thumbnail_helper");
        if (!file_exists("gallery/{$id_gallery}"))
            mkdir("gallery/{$id_gallery}");

        $i = 1;
        if (array_key_exists("id_item", $_POST))
            foreach ($_POST["id_item"] as $k => $p) {
                $out["id"] = $p;
                $out["title"] = $_POST["title"][$k];
                $out["position"] = $i++;
                $this->Gallery_model->updateItem($out);
            }
        $this->Gallery_model->updateGallery($_POST["id_gallery"], $_POST["gallery_title"]);

        $this->tal->message = "Zaktualizowano galerię.";
        $this->out["sessionId"] = $this->session->userdata('session_id');
        $this->tal->gallery = $this->Gallery_model->getGallery($_POST["id_gallery"]);
        $this->tal->out = $this->out;
        $this->tal->display("admin/gallery.html");
    }

    function upload() {
        $this->tal->out = $this->out;

        $id = $_POST["id"];
        if (!$id)
            return false;
        $this->load->helper("thumbnail_helper");

        if (!file_exists("./gallery/${id}")) {
            mkdir("./gallery/${id}");
            chmod("./gallery/${id}", 0777);
        }


        if ($_FILES["Filedata"]["error"] == 0) {
            $this->load->helper("Text");
            $config["file_name"] = url_title(convert_accented_characters(
                            iconv("cp1250", "utf-8", $_FILES["Filedata"]["file_name"]), '_'));
            $config["upload_path"] = "./gallery/{$id}/";
            $config["allowed_types"] = "*";
            $this->load->library("upload", $config);

            if (!$this->upload->do_upload("Filedata")) {
                print ($this->upload->display_errors());
            } else {
                $uploaded = $this->upload->data();
                //$uploaded["file_name"] = url_title(convert_accented_characters($_FILES["Filedata"]["file_name"]), '_');
                thumbnail_resize("./gallery/{$id}/{$uploaded["file_name"]}", 950, 713, "both");
                thumbnail_generate("./gallery/{$id}/{$uploaded["file_name"]}", 100);
                $id = $this->Gallery_model->insertItem($id, array("file" => $uploaded["file_name"]));
            }
            print("Plik został dodany.");
        }
    }

}

?>