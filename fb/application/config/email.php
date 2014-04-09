<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

$config["useragent"] = "";
$config["charset"] = "utf-8";
$config["wordwrap"] = TRUE;
$config["protocol"] = "sendmail";
$config['mailpath'] = '/usr/sbin/sendmail';
$config["smtp_host"] = "localhost";
$config["smtp_port"] = 25;
$config["smtp_user"] = "sklep@meridian.umbrella.pl";
$config["smtp_pass"] = "";
$config["mailtype"] = "html";
$config['newline'] = "\r\n";
$config['crlf']    = "\n"; 
