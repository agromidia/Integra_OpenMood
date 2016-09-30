<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once '/var/www/html/moodle/config.php';

$servername = $CFG->dbhost;
$dbname     = $CFG->dbname;
$username   = $CFG->dbuser;
$password   = $CFG->dbpass;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_errno)
{
    printf($conn->connect_errno);
    exit();
}

// busca os usuarios da tabela v_OpenMood.
$query_view = $conn->query("SELECT * FROM v_OpenMood");

while ($row=$query_view->fetch_assoc()) {

    $user_moodle = $row['email'];
    $pass_moodle = password_hash(($row['firstname']."_".date("y")), PASSWORD_DEFAULT);
    $name = $row['firstname'];
    $lname = $row['lastname'];
    $email = $row['email']; // Este tem que ser verificada por você, como nós estamos inserindo-o diretamente
    $id_course = $row['courseid_mood']; // Id que você colocou no moodle Administração, não o real id
    $idnumber = $row['sku_idnumber'];
    $duration = '32';

    $sql_email = $conn->query("SELECT email FROM moodle.mdl_user WHERE email='$email'");
    $result_query = $sql_email->num_rows;

    if ($result_query === 0) 
    {
        $sql = "INSERT INTO moodle.mdl_user (auth, confirmed, mnethostid, username, password, firstname, lastname, email,country,lang)
            VALUES ('manual', 1, 1, '$user_moodle', '$pass_moodle', '$name', '$lname', '$email','BR','pt_br')";
        // auth = 'manual', confirmed = 1, mnethostid = 1 Always. the others are your variables

        if ($conn->query($sql) === TRUE)
        {
            echo "OK, Cadastrado.";
        } else {
            echo "Erro ao cadastrar aluno.";
        }

        $sql = "SELECT * FROM moodle.mdl_user WHERE email='$email'";
        $result = $conn->query($sql);
        if ($row = $result->fetch_assoc())
        {
            $id = $row['id']; // Id of newly created user. we're using that for to register him on the course
        }

        // // You have to use this if your idnumber for the course is the one you put into moodle (thats not the real id)
        // $sql = "SELECT id FROM moodle.mdl_course WHERE idnumber=$idnumber";
        // $result = $conn->query($sql);
        // if (!$result)
        // {
        //     echo "Curso não exite";
        // }
        // if ($row = $result->fetch_assoc())
        // {
        //     $idcourse = $row["id"];
        // } else {
        //     var_dump($row);
        // }

        // I need now the "enrol" id, so I do this:
        $sql = "SELECT id FROM moodle.mdl_enrol WHERE courseid=$id_course AND enrol='manual'";
        $result = $conn->query($sql);
        if (!$result)
        {
            echo "";
        }
        if ($row = $result->fetch_assoc())
        {
            $idenrol = $row["id"];
        }

        // Lastly I need the context
        $sql = "SELECT id FROM moodle.mdl_context WHERE contextlevel=50 AND instanceid=$id_course";
        $result = $conn->query($sql);
        if(!$result)
        {
            echo "Again, weird error, shouldnt happen to you";
        }
        if($row = $result->fetch_assoc())
        {
            $idcontext = $row["id"];
        }

        ///We were just getting variables from moodle. Here is were the enrolment begins:
        $time = time();
        $ntime = $time + 60*60*24*$duration; //How long will it last enroled $duration = days, this can be 0 for unlimited.
        $sql = "INSERT INTO moodle.mdl_user_enrolments (status, enrolid, userid, timestart, timeend, timecreated, timemodified)
        VALUES (0, '$idenrol', '$id', '$time', '$ntime', '$time', '$time')";
        if ($conn->query($sql) === TRUE) {
            echo "Cadastrado No Curso.";
        } else {
            echo "Erro:", $conn->error;
        }

        $sql = "INSERT INTO moodle.mdl_role_assignments (roleid, contextid, userid, timemodified)
        VALUES (5, '$idcontext', '$id', '$time')"; //Roleid = 5, means student.
        if ($conn->query($sql) === TRUE) {
             echo "Cadastro Ativo.";
        } else {
            echo "Erro:", $conn->error;
        }
    }
    else
    {
        echo "Já Cadastrados.";
    }

};
