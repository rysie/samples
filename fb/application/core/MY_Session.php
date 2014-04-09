<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * Created by PhpDesigner7.
 * User: Ray
 * Date: 7/31/2011
 * Time: 12:20:15 AM
 * To change this template use File | Settings | File Templates.
 */

/**
 * ------------------------------------------------------------------------
 * CI Session Class Extension for AJAX calls.
 * ------------------------------------------------------------------------
 *
 * ====- Save as application/libraries/MY_Session.php -====
 */
class MY_Session extends CI_Session {
    // --------------------------------------------------------------------

    /**
     * sess_update()
     *
     * Do not update an existing session on ajax or xajax calls
     *
     * @access    public
     * @return    void
     */
    public function sess_update() {
        $CI = get_instance();

        if (!$CI->input->is_ajax_request()) {
            parent::sess_update();
        }
    }

    // --------------------------------------------------------------------

    /**
     * sess_destroy()
     *
     * Clear's out the user_data array on sess::destroy.
     *
     * @access    public
     * @return    void
     */
    public function sess_destroy() {
        $this->userdata = array();

        parent::sess_destroy();
    }

}

// ------------------------------------------------------------------------
/* End of file MY_Session.php */
/* Location: ./application/libraries/MY_Session.php */  