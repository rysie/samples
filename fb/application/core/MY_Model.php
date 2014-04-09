<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function tableIsEmpty($table, $checkIfEmpty = true) {
        if (!$this->db->table_exists($table)) {
            return true;
        }

        if ($checkIfEmpty) {
            $res = $this->db->get($table);
            if ($res->num_rows() > 0)
                return false; else
                return true;
        }
        return false;
    }

    function createTableStructure($table, $fillWithSample = true) {
        $this->load->helper('file');
        $file = "sql/" . $table . ".sql";
        if (!file_exists($file)) {
            print("<p>Cannot find SQL file: {$file}</p>");
            return false;
        }

        print("<p>Creating table {$table} from file {$file}...</p>");
        $queries = explode(";", read_file($file));
        foreach ($queries as $q) {
            $query = trim($q);
            if (strlen($query) > 10)
                $this->db->query($query);
        }
    }

}