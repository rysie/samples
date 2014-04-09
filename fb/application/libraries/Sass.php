<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require 'sass/SassParser.php';

class Sass extends SassParser {

    function compileAll($directory) {
        try {
            $this->load->helper('file');
            $allFiles = get_filenames($directory);
            print_r($allFiles);
            $this->compile();
        } catch (exception $ex) {
            exit('SASS fatal error:<br />' . $ex);
        }
    }

    function compile($input, $output) {
        try {
            if (!is_file($output) || filemtime($input) > filemtime($output)) {
                $sass = new SassParser(array("cache" => "true",
                            "style" => "compressed",
                            'extensions' => array('compass' => array()),
                            "vendor_properties" => true));
                $css = $sass->toCss($input);

                $fh = fopen($output, 'w') or die("SASS: can't open file for writing");
                chmod($output, 0666);
                fwrite($fh, $css);
                fclose($fh);
            }
        } catch (exception $ex) {
            exit('SASS fatal error:<br />' . $ex->getMessage());
        }
    }

}
