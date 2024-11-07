<?php
    class Database {
        function connect(){
            //establish connection
            $conn = new mysqli('localhost', 'root', '', 'samson_pharmacy');
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            return $conn;
        }

        function sql($sql) {
            $conn = $this->connect();
            $result = $conn->query($sql);
            $conn->close();
            return $result;
        }

        
    }

?>