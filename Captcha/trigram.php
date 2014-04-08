<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Trigram {

    /**
     * Generates unique trigram for User->login
     *
     * @param	integer desired length of trigram
     * @param   boolean unique or not
     * @return	string
     */
    function generate($pwl = 10, $unique = false) {
        if ($unique) {
            $CI = &get_instance();
            $res = false;
            while (!is_array($res) || sizeof($res) > 0) {
                $trigram = $this->generateGenericTrigram($pwl);
                $res = $CI->em->getRepository("Entity\User")->findBy(array("login" => "{$trigram}"));
            }
        } else {
            $trigram = $this->generateGenericTrigram($pwl);
        }
        return $trigram;
    }

    /**
     * Generates generic trigram based on Polish language letter probabilities
     *
     * @param	integer desired length of trigram
     * @return	string
     */
    function generateGenericTrigram($pwl = 10) {
        // include probabilities list
        require("probabilities.php");
        $done = false;

        while ($done == false) {
            $output = "";
            $ranno = rand(1, $trigram_sigma);
            $sum = 0;
            for ($c1 = 0; $c1 < 26; $c1++) {
                for ($c2 = 0; $c2 < 26; $c2++) {
                    for ($c3 = 0; $c3 < 26; $c3++) {
                        $sum += $trigram_tris[$c1][$c2][$c3];
                        if ($sum > $ranno) {
                            $output .= $trigram_alphabet[$c1];
                            $output .= $trigram_alphabet[$c2];
                            $output .= $trigram_alphabet[$c3];
                            $c1 = 26; 
                            $c2 = 26;
                            $c3 = 26;
                        } 
                    } 
                } 
            } 

            if ((strlen($output) == 3) && ($output[0] != "y"))
                $done = true;
        }

        
        for ($nchar = 3; $nchar < $pwl; $nchar++) {
            $c1 = strpos($trigram_alphabet, $output[$nchar - 2]);
            $c2 = strpos($trigram_alphabet, $output[$nchar - 1]);
            $sum = 0;
            for ($c3 = 0; $c3 < 26; $c3++)
                $sum += $trigram_tris[$c1][$c2][$c3];
            if ($sum == 0) {
                return false;
            }
            $ranno = rand(0, $sum - 1);
            $sum = 0;
            for ($c3 = 0; $c3 < 26; $c3++) {
                $sum += $trigram_tris[$c1][$c2][$c3];
                if ($sum > $ranno) {
                    $output .= $trigram_alphabet[$c3];
                    $c3 = 26; 
                } 
            }
        } 

        return $output;
    }

}
