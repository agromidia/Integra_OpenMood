<?php

//define('CLI_SCRIPT', true);
require_once '/var/www/html/moodle/config.php';

$svrname    = $CFG->dbhost;
$database   = $CFG->dbname;
$username   = $CFG->dbuser;
$password   = $CFG->dbpass;

$con = new mysqli($svrname,$username,$password,$database);
if ($con->connect_errno)
{
    printf($con->connect_errno);
    exit();
}
