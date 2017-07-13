<?php

class db{

    private $user = "root";
    private $pass = "Admin01agroserver";
    private $host = "localhost";
    private $database = "moodle";

    private function Connect(){
        $con = new PDO("mysql:host=127.0.0.1;dbname=$this->database", $this->user, $this->pass);
        return $con;
    }

    public function RunQuery($sql){
        $stm = $this->Connect()->prepare($sql);
        return $stm->execute();
    }

    public function RunSelect($sql){
        $stm = $this->Connect()->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }

}
