<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Form extends MY_Admin {

    function __construct() {
        parent::__construct();
        $this->authAdmin();

        $CI = &get_instance();
        $this->myClass = $CI->router->class;

        $this->table = "aktualnosci";
        $this->load->model("admin/Tree_model");
        $this->load->model("admin/Form_model");

        $this->tal->css = array("smoothness/smoothness.css");
        $this->tal->js = array("/js/admin_{$this->myClass}.js");
        $this->out = array();

        $this->out["message"] = "";
    }

    function index() {
        
    }

    function view($table, $id) {
        $form = $this->Form_model->getItem("form_" . $table, $id);
        $this->tal->form = $form;
        $this->tal->display("admin/form_{$table}.html");
    }

    function delete($table, $id) {
        $this->load->helper("Text");
        $form = $this->Form_model->getItem("form_" . $table, $id);
        if (!$form) {
            $year = date("Y");
            $this->out["message"] = "Nie można usunąć tego wpisu (być może już został wcześniej skasowany).";
        } else {
            $this->Form_model->deleteItem("form_" . $table, $id);
            $this->out["message"] = "Wpis został usunięty.";
            $year = substr($form["datetime"], 0, 4);
        }

        $forms = $this->Form_model->getFormsYear("form_{$table}", $year);
        if (is_array($forms))
            foreach ($forms as &$form) {
                if (array_key_exists("wiadomosc", $form))
                    $form["wiadomosc_short"] = word_limiter($form["wiadomosc"], 20);
            }

        $this->tal->year = $year;
        $this->tal->forms = $forms;
        $this->tal->years = $this->Form_model->getYears("form_{$table}");

        $this->tal->out = $this->out;
        $this->tal->display("admin/form_{$table}.html");
    }

    function kontakt($id = false) {
        $this->load->helper("Text");
        if (is_numeric($this->uri->segment(4)))
            $year = $this->uri->segment(4);
        else
            $year = date("Y");

        $this->tal->year = $year;
        $forms = $this->Form_model->getFormsYear("form_kontakt", $year);
        if (is_array($forms))
            foreach ($forms as &$form) {
                $form["wiadomosc_short"] = word_limiter($form["wiadomosc"], 20);
            }

        $this->tal->forms = $forms;
        $this->tal->years = $this->Form_model->getYears("form_kontakt");

        $this->tal->display("admin/form_kontakt.html");
    }

    function zamowienia($id = false) {
        $this->load->helper("Text");
        if (is_numeric($this->uri->segment(4)))
            $year = $this->uri->segment(4);
        else
            $year = date("Y");

        $this->tal->year = $year;
        $forms = $this->Form_model->getFormsYear("products_orders", $year);

        $this->tal->forms = $forms;
        $this->tal->years = $this->Form_model->getYears("products_orders");

        $this->tal->display("admin/orders_list.html");
    }

    function zamowienie_nr($id) {
        $this->load->model("frontend/Products_model");

        if ($id)
            $order = $this->Products_model->getOrder($id);

        $total = 0;
        $totalWeight = 0;
        if ($order) {
            foreach ($order["cart"] as &$i) {
                $i["price"] = formatuj_kwote($i["price"]);
                $i["subtotal"] = formatuj_kwote($i["subtotal"]);
                $totalWeight += $i["weight"] * $i["qty"];
                $total += $i["subtotal"];
                $i["weight_formatted"] = formatuj_wage($i["weight"]);
                $i["total_weight"] = formatuj_wage($i["weight"] * $i["qty"]);
            }

            $this->out["cart"] = $order["cart"];
            $this->out["order"] = $order;
            $this->out["cart_total"] = formatuj_kwote($total);
            $this->out["cart_total_vat"] = formatuj_kwote($total * 1.23);
            $this->out["cart_total_weight"] = formatuj_wage($totalWeight);
            $this->out["cart_shipping_cost"] = $this->Products_model->getShippingCost($this->out["cart_total_weight"]["g"]);
            $this->out["cart_total_with_shipping"] = formatuj_kwote($this->out["cart_total"] + $this->out["cart_shipping_cost"]["cost"]);
            $this->out["cart_total_with_shipping_vat"] = formatuj_kwote($this->out["cart_total_with_shipping"] * 1.23);
        }

        $this->tal->out = $this->out;
        $this->tal->display("admin/orders_list.html");
    }

    function usun_zamowienie($id) {
        $this->load->model("frontend/Products_model");

        if ($id)
            $order = $this->Products_model->getOrder($id);


        if (!$order) {
            $year = date("Y");
            $this->out["message"] = "Nie można usunąć tego zamówienia (być może już zostało wcześniej skasowane).";
        } else {
            $this->Form_model->deleteItem("products_orders", $id);
            $this->out["message"] = "Zamówienie zostało usunięte";
            $year = substr($order["datetime"], 0, 4);
        }

        $forms = $this->Form_model->getFormsYear("products_orders", $year);
        $this->tal->years = $this->Form_model->getYears("products_orders");
        $this->tal->year = $year;
        $this->tal->forms = $forms;
        $this->tal->display("admin/orders_list.html");
    }

}

