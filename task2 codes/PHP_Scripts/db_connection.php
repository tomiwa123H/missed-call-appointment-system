<?php
function get_database_connection() {
    //  MySQL server info
    $servername = "ccsw-mysql-exams1.mysql.database.azure.com";
    $db_user   = "0020041303_25_1634_User1"; 
    $db_pass   = "cmADT2Qu39Ac";            
    $db_name   = "0020041303_25_1634_DB1";
    $port      = 3306;

    // Create connection
    $conn = new mysqli($servername, $db_user, $db_pass, $db_name, $port);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set charset to avoid encoding issues
    $conn->set_charset("utf8mb4");

    return $conn;
}
?>