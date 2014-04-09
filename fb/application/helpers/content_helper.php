<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


/**
 * ContentHelpers
 *
 * @package	CMS
 * @subpackage	Helpers
 * @category	Helpers
 * @author	Marcin Maciński
 */
// ------------------------------------------------------------------------
if (!function_exists('print_arr')) {

    /**
     * print_arr - przyjazne print_r
     * @access	public
     * @param	array
     * @return	string
     */
    function print_arr($variabile, $text = '', $return = FALSE) {
        if ($text <> '')
            $text = " $text=";
        $type = gettype($variabile);
        switch ($type) {
            case "array":
            case "object":
                #$out="<p><pre>$text".var_export($variabile,TRUE)."</pre></p>";
                $out = "<p><pre>$text" . print_r($variabile, TRUE) . "</pre></p>";
                break;
            case "NULL":
                $str = "<p>NULL</p>";
            case "string":
            default:
                if (!isset($str))
                    $str = strval($variabile);
                $out = "<p><pre>#" . $type . "[" . strlen($str) . "]:$text" . ($str) . "</pre></p>";
                break;
        }
        if (!$return)
            echo $out;
        else
            return $out;
    }

}

if (!function_exists('array_rotate')) {

    /**
     * array_rotate - obraca tablicę o N pozycji (1 element -> 2, 2 -> 3...)
     * np. array_rotate($array, $n)
     *
     * @access	public
     * @param	array
     * @param	integer
     * @return	array
     */
    function array_rotate($ar, $n) {
        for ($i = 0; $i < $n; $i++) {
            array_unshift($ar, array_pop($ar));
        }
        return $ar;
    }

}



if (!function_exists('array_flatten')) {

    /**
     * array_flatten - spłaszcza wielowymiarową tablicę i zwraca tylko ID
     * np. array_flatten($tree) - zwraca tablicę zawierającą ID
     *
     * @access	public
     * @param	array
     * @param   string
     * @return	array
     
    function array_flatten($array, $key = "id") {
        $return = array();
        array_walk_recursive($array, function($a, $k) use (&$return) {
            if ($k == "id")
                $return[] = $a;
        });
        return $return;
    }
*/
}

if (!function_exists('db_result')) {

    /**
     * db_result - zwraca wynik zapytania z bazy danych (array - jeśli są jakiekolwiek wyniki; false lub array() - jeśli nie ma nic)
     *
     * @access	public
     * @param	database array object
     * @param   string
     * @return	array|boolean   zależnie od parametrów
     */
    function db_result($result, $ret = "") {
        if ($result && ($result->num_rows() > 0))
            return $result->result_array();
        else
            switch ($ret) {
                case "": return false;
                    break;
                case "array": return array();
                    break;
            }
    }

}



if (!function_exists('db_row_result')) {

    /**
     * db_row_result - zwraca wynik zapytania z bazy danych na podstawie 1 wyniku (array - jeśli są jakiekolwiek wyniki; false - jeśli nie ma nic)
     *
     * @access	public
     * @param	database row object
     * @return	array|boolean   zależnie od parametrów
     */
    function db_row_result($result) {
        if ($result && ($result->num_rows() > 0))
            return $result->row_array();
        else
            return false;
    }

}


if (!function_exists('create_links')) {

    /**
     * create_links - tworzy dodatkowe pole o indeksie 'seo' w tablicy wielu treści, pole to będzie zawierać przyjazny link
     *
     * @access	public
     * @param	array
     * @param   string
     * @return	array
     */
    function create_links($tab, $pole = "title") {
        if (is_array($tab)) {
            foreach ($tab as &$row) {
                if (isset($row[$pole]))
                    $row["seo"] = url_title(
                            str_replace
                                    (
                                    array("Ą", "ą", "Ś", "ś", "Ł", "ł", "Ż", "ż", "Ź", "ź", "Ć", "ć", "Ń", "ń", "Ę", "ę", "Ó", "ó", "%",
                        "ä", "Ä", "é", "ö", "Ö", "ü", "Ü", "ß", ".", ","), array("A", "a", "S", "s", "L", "l", "Z", "z", "Z", "z", "C", "c", "N", "n", "E", "e", "O", "o", "procent",
                        "ae", "Ae", "e", "oe", "Oe", "ue", "Ue", "ss", "", ""), $row[$pole])
                    );
            }
            unset($row);
            return $tab;
        } else
            return false;
    }

}


if (!function_exists('row_create_link')) {

    /**
     * row_create_link - tworzy dodatkowe pole o indeksie 'seo' w treści podanej jako tablica, pole to będzie zawierać przyjazny link
     *
     * @access	public
     * @param	array
     * @return	array
     */
    function row_create_link($row, $pole = "title") {
        if (isset($row[$pole])) {
            $row["seo"] = url_title(str_replace(
                            array("Ą", "ą", "Ś", "ś", "Ł", "ł", "Ż", "ż", "Ź", "ź", "Ć", "ć", "Ń", "ń", "Ę", "ę", "Ó", "ó", "%",
                "ä", "Ä", "é", "ö", "Ö", "ü", "Ü", "ß", ".", ","), array("A", "a", "S", "s", "L", "l", "Z", "z", "Z", "z", "C", "c", "N", "n", "E", "e", "O", "o", "procent",
                "ae", "Ae", "e", "oe", "Oe", "ue", "Ue", "ss", "", ""), $row[$pole])
            );
            return $row;
        } else
            return false;
    }

}

if (!function_exists('create_unique_link')) {

    /**
     * create_unique_link - tworzy unikalną nazwę ('seo')
     *
     * @access	public
     * @param	string
     * @param	array
     * @return	string
     */
    function create_unique_link($title, $SEOs) {
        $tempSEO = url_title(str_replace(
                        array("Ą", "ą", "Ś", "ś", "Ł", "ł", "Ż", "ż", "Ź", "ź", "Ć", "ć", "Ń", "ń", "Ę", "ę", "Ó", "ó", "%", "ä", "Ä", "é", "ö", "Ö", "ü", "Ü", "ß", ".", ",", "!"), array("A", "a", "S", "s", "L", "l", "Z", "z", "Z", "z", "C", "c", "N", "n", "E", "e", "O", "o", "procent", "ae", "Ae", "e", "oe", "Oe", "ue", "Ue", "ss", "", "", ""), $title));
        $newSEO = $tempSEO;

        if (in_array($tempSEO, $SEOs)) {
            for ($i = 0; $i < 10000; $i++) {
                $newSEO = $tempSEO . "-" . $i;
                if (!in_array($newSEO, $SEOs))
                    break;
            }
        }
        return $newSEO;
    }

}


if (!function_exists('antyspam')) {

    /**
     * antyspam - rozpoznaje zawarte w treści adresy mailowe, koduje je i dodaje tag <span>
     *
     * @access	public
     * @param	array
     * @return	array
     */
    function antyspam($tresc, $pole = "content") {
        if (!array_key_exists($pole, $tresc))
            return $tresc;

        $content = $tresc[$pole];
        $pattern = "/[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+/";
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[0] as $match) {
                $ciag = "";
                for ($i = 0; $i < strlen($match); $i++) {
                    $ciag .= chr(ord($match[$i]) + 2);
                }
                $content = str_replace($match, "<span class=\"antyspam\">{$ciag}</span>", $content);
            }
        }
        $tresc[$pole] = $content;
        return $tresc;
    }

}

if (!function_exists('odmien_liczebnik')) {

    /**
     * odmien_liczebnik - odmienia podany liczebnik (<liczba>, <odmiana_1>, <odmiana_2>, <odmiana_3)
     *
     * @access	public
     * @param	integer
     * @param   string
     * @param   string
     * @param   string
     * @return	string
     */
    function odmien_liczebnik($wartosc, $odmiana1, $odmiana2, $odmiana3) {
        $koncowka1 = substr((string) $wartosc, -1);
        $koncowka2 = substr((string) $wartosc, -2);

        if ($wartosc == 1)
            return $odmiana1;
        if (in_array($koncowka2, array('12', '13', '14')))
            return $odmiana3;
        if (in_array($koncowka1, array('2', '3', '4')))
            return $odmiana2;
        return $odmiana3;
    }

}


if (!function_exists('formatuj_date')) {

    /**
     * formatuj_date - tworzy przyjazną datę
     *
     * @access	public
     * @param   date object
     * @return	array
     */
    function formatuj_date($data, $klasa = "") {
        $out = array();
        if (!$data)
            return false;

        $data = substr($data, 0, 10);
        $tab = explode("-", $data);
        if ((strlen($tab[1])) < 2)
            $tab[1] = "0" . $tab[1];
        if ($tab[2][0] == "0")
            $tab[2] = substr($tab[2], 1, 2);

        $dniTygodnia = array("0" => "niedziela", "1" => "poniedziałek", "2" => "wtorek", "3" => "środa", "4" => "czwartek",
            "5" => "piątek", "6" => "sobota");

        $dow = date("w", mktime(0, 0, 0, (int) $tab[1], (int) $tab[2], (int) $tab[0]));


        $miesiace_odm = array("01" => "stycznia", "02" => "lutego", "03" => "marca", "04" => "kwietnia", "05" => "maja", "06" => "czerwca",
            "07" => "lipca", "08" => "sierpnia", "09" => "września", "10" => "października", "11" => "listopada", "12" => "grudnia");
        $miesiace = array("01" => "styczeń", "02" => "luty", "03" => "marzec", "04" => "kwiecień", "05" => "maj", "06" => "czerwiec",
            "07" => "lipiec", "08" => "sierpień", "09" => "wrzesień", "10" => "październik", "11" => "listopad", "12" => "grudzień");

        $out["dow"] = $dniTygodnia[$dow];
        $out["dzien"] = $tab[2];
        $out["miesiac_odm"] = $miesiace_odm[$tab[1]];
        $out["miesiac"] = $miesiace[$tab[1]];
        $out["miesiac_num"] = $tab[1];
        $out["rok"] = $tab[0];
        $out["fulldate"] = $out["dow"] . ", " . $out["dzien"] . " " . $out["miesiac_odm"] . " " . $out["rok"] . " r.";

        return $out;
    }

}


if (!function_exists('formatuj_kwote')) {

    /**
     * formatuj_kwote - formatuje liczbę do ciągu xxxx.xx zł
     *
     * @access	public
     * @param   string
     * @return	string
     */
    function formatuj_kwote($str) {
        $str = str_replace(',', ".", $str);
        $out = floatval($str);

        $val = round((float) $out, 2);
        
        if (strpos($val, ".") === false)
            $val .= ".00";
        list($a, $b) = explode('.', $val);
        if (strlen($b) < 2)
            $b = str_pad($b, 2, '0', STR_PAD_RIGHT);
        $out = 2 ? "$a.$b" : $a;
        return $out;
    }

}

if (!function_exists('url_title_pl')) {

    /**
     * Formatuje stringa do postaci używanej w urlach
     *
     * @param	string
     * @return	string
     */
    function url_title_pl($title) {
        return url_title(
                str_replace(
                        array("Ą", "ą", "Ś", "ś", "Ł", "ł", "Ż", "ż", "Ź", "ź", "Ć", "ć", "Ń", "ń", "Ę", "ę", "Ó", "ó", "%", "ä", "Ä", "é", "ö", "Ö", "ü", "Ü", "ß", ".", ",", "!", " "), array("A", "a", "S", "s", "L", "l", "Z", "z", "Z", "z", "C", "c", "N", "n", "E", "e", "O", "o", "procent", "ae", "Ae", "e", "oe", "Oe", "ue", "Ue", "ss", ".", "_", "", "_"), $title
                ), 'dash', TRUE
        );
    }

}