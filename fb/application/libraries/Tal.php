<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//Path to PHPTAL library, i used 1.2.0
require 'phptal/PHPTAL.php';

/**
 * Wrapper for PHPTAL tempalte engine
 */
class Tal extends PHPTAL {

    /**
     * Default templates and compiled templates path
     */
    var $templates = 'views/';
    var $templates_c = 'cache/';

    /**
     * @param string  (template name or path)
     * @param boolean (set TRUE to return page content)
     * @param boolean (set FALSE if you don't want to use default templates path)
     *
     * @result mixed (depends on second parameter)
     *
     * This method returns or echoes parsed tempalte content
     */
    function display($filename, $return=false, $usepath=true) {
        try {
            $this->templates = APPPATH . "views/";
            $this->templates_c = APPPATH . "cache/";
            $path = (!$usepath) ? $filename : $this->templates . $filename;
            $this->addPreFilter(new PHPTAL_PreFilter_StripComments());
            //$this->addPreFilter(new PHPTAL_PreFilter_Normalize());
            $this->addPreFilter(new PHPTAL_PreFilter_Compress());
            $this->setTemplate($path);
            $this->setOutputMode(PHPTAL::HTML5);
            $this->setPhpCodeDestination($this->templates_c);
            if (!$return) {
                echo $this->execute();
            } else {
                return $this->execute();
            }
        } catch (exception $ex) {
            exit('PHPTAL fatal error: <b>' . $ex->getMessage()."</b><br/>"." --> ".$ex->getFile().$ex->getLine()."<br/>");
            
        }
    }

}

/* End of file tal.php */
/* Location: ./system/application/libraries/tal.php */