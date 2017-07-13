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

/* Retorna as palavaras minúsculas, a primeira de um nome composto e acrescenta um underline e o ano.
*  ex: Marcelo Caetano -> marcelo_16
*  Esse tratamento é para gerar uma senha padrão para todos os usuários
*/
function tratanome($nome)
{
    $trataetapa1 = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(rtrim($nome)));
    $trataetapa2 = current(str_word_count($trataetapa1,2));
    $tratafinal = strtolower($trataetapa2);

    return $tratafinal;
}

// busca os usuários da tabela v_OpenMood.
$query_view = $conn->query("SELECT * FROM v_OpenMood");

while ($row=$query_view->fetch_assoc()) {

    $user_moodle = $row['email'];
    $name = $row['firstname'];
    $lname = $row['lastname'];
    $email = $row['email'];
    $id_course = $row['courseid_mood'];
    $idnumber = $row['sku_idnumber'];
    $duration = 32; // Em dias
    $nometratado = tratanome($name);
    $pass_moodle = password_hash(($nometratado."_".date("y")), PASSWORD_DEFAULT);

    $sql_email = $conn->query("SELECT email FROM moodle.mdl_user WHERE email='$email'");
    $result_query = $sql_email->num_rows;

    if ($result_query === 0)
    {
        $sql = "INSERT INTO moodle.mdl_user (auth, confirmed, mnethostid, username, password, firstname, lastname, email,country,lang)
            VALUES ('manual', 1, 1, '$user_moodle', '$pass_moodle', '$name', '$lname', '$email','BR','pt_br')";

        if ($conn->query($sql) === TRUE)
        {
            echo "OK, Cadastrado. ";
        } else {
            echo "Erro ao cadastrar aluno. ", $conn->error;
        }

        $sql = "SELECT * FROM moodle.mdl_user WHERE email='$email'";
        $result = $conn->query($sql);
        if ($row = $result->fetch_assoc())
        {
            $id = $row['id']; // Id do último usuário criado, necessário para registro no curso
        }

        // procura enrol id
        $sql = "SELECT id FROM moodle.mdl_enrol WHERE courseid=$id_course AND enrol='manual'";
        $result = $conn->query($sql);
        if (!$result)
        {
            echo "Problema em encontrar o enrol id. ", $conn->error;
        }
        if ($row = $result->fetch_assoc())
        {
            $idenrol = $row["id"];
        }

        // procura o context id
        $sql = "SELECT id FROM moodle.mdl_context WHERE contextlevel=50 AND instanceid=$id_course";
        $result = $conn->query($sql);
        if(!$result)
        {
            echo "Problema em encontrar o context id ", $conn->error;
        }
        if($row = $result->fetch_assoc())
        {
            $idcontext = $row["id"];
        }

        $sql_user_courso = $conn->query("SELECT me.courseid, mue.userid
                                           FROM mdl_user_enrolments mue
                                     INNER JOIN mdl_user mu ON mu.id = mue.userid
                                     INNER JOIN mdl_enrol me ON mue.enrolid = me.id
                                          WHERE me.courseid = 46 AND mue.userid = 1316");

        $result_sql_user_course = $sql_user_courso->num_rows;

        if ($result_sql_user_course === 0) {

                $time = time();
                $ntime = $time + 60*60*24*$duration; // Quanto tempo vai durar matriculadas $duration = dias, isso pode ser 0 para ilimitado.
                $sql = "INSERT INTO moodle.mdl_user_enrolments (status, enrolid, userid, timestart, timeend, timecreated, timemodified)
                             VALUES (0, $idenrol, $id, $time, $ntime, $time, $time)";

                if ($conn->query($sql) === TRUE) {
                    echo "Cadastrado No Curso.";
                } else {
                    echo "Erro:", $conn->error;
                }

                $sql = "INSERT INTO moodle.mdl_role_assignments (roleid, contextid, userid, timemodified)
                             VALUES (5, $idcontext, $id, $time)"; // Roleid = 5, significa student.

                if ($conn->query($sql) === TRUE) {
                     echo "Cadastro Ativo.";
                } else {
                    echo "Erro:", $conn->error;
                }

        } else {
            echo "ERRO", $conn->error;
            {
                $this->foo = $foo;
            };
        }

    } else {
        echo "Já Cadastrados.";
    }

};
