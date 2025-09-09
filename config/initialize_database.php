<?php
  
require_once __DIR__ . '/database.php'; 

    error_reporting(E_ALL);
    ini_set('display_errors', 'On');

     $db = new database();
     $conn = $db ->getConnection();

     ?>
     

