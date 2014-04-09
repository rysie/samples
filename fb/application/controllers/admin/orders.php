<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Orders extends MY_Admin {

    function __construct() {
        parent::__construct();
        $this->authAdmin();

        $CI = &get_instance();
        $this->myClass = $CI->router->class;

        $this->table = "orders_finished";
        $this->load->model("admin/Tree_model");
        $this->load->model("admin/Orders_model");

        $this->tal->css = array("smoothness/smoothness.css");
        $this->tal->js = array("js/admin_{$this->myClass}.js");
        $this->out = array();

        $this->out["message"] = "";
    }

    function index($year = false) {
        $result = $this->db->query("SELECT DISTINCT substr(datetime,1,4)
            FROM `orders_finished`
            ORDER BY datetime DESC
            ");
        $years = db_result($result);

        $this->tal->years = $years;

        if (!$year)
            $year = date("Y");
        $start = $year . "-01-01 00:00:00";
        $end = $year . "-12-31 23:59:59";
        $result = $this->db->query("SELECT order_number, datetime FROM orders_finished n
            WHERE n.datetime BETWEEN '{$start}' AND '{$end}'
            GROUP BY n.order_number
            ORDER BY n.datetime DESC
            ");

        $out = db_result($result);

        if ($out) {
            foreach ($out as $k => $o) {
                $this->db->where("order_number", $o["order_number"]);
                $ress = $this->db->get("orders_finished");
                $dets = db_result($ress);
                $total = 0;
                $total_tax = 0;
                $total_discount = 0;
                $total_discount_tax = 0;
                foreach ($dets as $di => $det) {
                    $total += $det["price"] * $det["qty"];
                    $total_tax += $det["price_tax"] * $det["qty"];
                    $dets[$di]["total"] = formatuj_kwote($det["price"] * $det["qty"]);
                    $dets[$di]["total_tax"] = formatuj_kwote($det["price_tax"] * $det["qty"]);
                    $dets[$di]["discount"] = $det["discount"] . "%";
                    $dets[$di]["discount_amount"] = formatuj_kwote($det["discount"] * $dets[$di]["total"] / 100);
                    $dets[$di]["discount_amount_tax"] = formatuj_kwote($det["discount"] * $dets[$di]["total_tax"] / 100);
                    $dets[$di]["after_discount"] = formatuj_kwote($dets[$di]["total"] - $det["discount"] * $dets[$di]["total"] / 100);
                    $dets[$di]["after_discount_tax"] = formatuj_kwote($dets[$di]["total_tax"] - $det["discount"] * $dets[$di]["total_tax"] / 100);
                    $total_discount += $dets[$di]["discount_amount"];
                    $total_discount_tax += $dets[$di]["discount_amount_tax"];
                    $address_send = (array) json_decode($det["address_send"]);
                    $address_invoice = (array) json_decode($det["address_invoice"]);
                }
                $totals = array("total" => formatuj_kwote($total), "total_tax" => formatuj_kwote($total_tax),
                    "total_discount" => formatuj_kwote($total_discount), "total_discount_tax" => formatuj_kwote($total_discount_tax),
                    "to_pay" => formatuj_kwote($total - $total_discount), "to_pay_tax" => formatuj_kwote($total_tax - $total_discount_tax));
                $out[$k]["address"] = array("send" => $address_send, "invoice" => $address_invoice);
                $out[$k]["details"] = $dets;
                $out[$k]["totals"] = $totals;
            }
        }

        $this->tal->orders_list = $out;
        $this->tal->year = $year;
        $this->tal->out = $this->out;
        $this->tal->display("admin/orders.html");
    }

    function details($id = false) {
        if (!$id)
            redirect("/admin/orders");

        $this->db->where("order_number", $id);
        $ress = $this->db->get("orders_finished");
        $dets = db_result($ress);

        if (!$dets)
            redirect("/admin/orders");

        $total = 0;
        $total_tax = 0;
        $total_discount = 0;
        $total_discount_tax = 0;
        foreach ($dets as $di => $det) {
            $datetime = $det["datetime"];
            $total += $det["price"] * $det["qty"];
            $total_tax += $det["price_tax"] * $det["qty"];
            $dets[$di]["total"] = formatuj_kwote($det["price"] * $det["qty"]);
            $dets[$di]["total_tax"] = formatuj_kwote($det["price_tax"] * $det["qty"]);
            $dets[$di]["discount"] = $det["discount"] . "%";
            $dets[$di]["discount_amount"] = formatuj_kwote($det["discount"] * $dets[$di]["total"] / 100);
            $dets[$di]["discount_amount_tax"] = formatuj_kwote($det["discount"] * $dets[$di]["total_tax"] / 100);
            $dets[$di]["after_discount"] = formatuj_kwote($dets[$di]["total"] - $det["discount"] * $dets[$di]["total"] / 100);
            $dets[$di]["after_discount_tax"] = formatuj_kwote($dets[$di]["total_tax"] - $det["discount"] * $dets[$di]["total_tax"] / 100);
            $total_discount += $dets[$di]["discount_amount"];
            $total_discount_tax += $dets[$di]["discount_amount_tax"];
            $address_send = (array) json_decode($det["address_send"]);
            $address_invoice = (array) json_decode($det["address_invoice"]);
        }
        $totals = array("total" => formatuj_kwote($total), "total_tax" => formatuj_kwote($total_tax),
            "total_discount" => formatuj_kwote($total_discount), "total_discount_tax" => formatuj_kwote($total_discount_tax),
            "to_pay" => formatuj_kwote($total - $total_discount), "to_pay_tax" => formatuj_kwote($total_tax - $total_discount_tax));
        $out["address"] = array("send" => $address_send, "invoice" => $address_invoice);
        $out["details"] = $dets;
        $out["totals"] = $totals;
        $out["message"] = $dets[0]["message"];

        $this->db->where("id_users", $out["address"]["send"]["id_users"]);
        $result = $this->db->get("users");
        $user = db_row_result($result);
        $out["user_email"] = $user["email"];
        $out["datetime"] = $datetime;


        $this->tal->single_order = $out;
        $this->tal->display("admin/orders.html");
    }

    function usun_zamowienie($id = false) {
        if (!$id)
            redirect("/admin/orders");

        $this->db->where("order_number", $id);
        $this->db->delete("orders_finished");
        
        
        redirect("/admin/orders");
    }

}
