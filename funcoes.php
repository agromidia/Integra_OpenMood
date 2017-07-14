<?php
function tratanome($nome)
{
    $trataetapa1 = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(rtrim($nome)));
    $trataetapa2 = current(str_word_count($trataetapa1,2));
    $tratafinal = strtolower($trataetapa2);

    return $tratafinal;
}

function cumprimento()
{
    if (date("H") >= 7 && date("H") <= 12) {
        $tratamento = "Bom Dia";
    } elseif(date("H") > 12 && date("H") < 18) {
        $tratamento = "Bom tarde";
    } elseif(date("H") >=  18) {
        $tratamento = "Boa noite";
    }

    return $tratamento;
}

function vd($data)
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

function cadastraAluno($username,$password,$firstname,$lastname,$email,$description)
{
    global $con;

    $result_insert = $con->query("INSERT INTO moodle.mdl_user (auth,confirmed,mnethostid,username,password,firstname,lastname,email,description,country,lang)
                                  	   VALUES ('manual',1,1,'$username','$password','$firstname','$lastname','$email','$description','BR','pt_br')") or die($mysqli->errno .' - '. $mysqli->error);
    if ($result_insert === TRUE)
    {
        echo " Usuario Registrado ";
    }
    else
    {
        echo "<br>Erro: ". $con->error;
    }
}

function cadastraAlunoCurso($email,$timestamp_datainicio,$timestamp_datafinal,$timestamp_datainicio,$timestamp_datainicio,$courseid)
{
    global $con;

    $idUsuario = $con->query("SELECT id FROM moodle.mdl_user WHERE email='$email'") or die($mysqli->errno .' - '. $mysqli->error);
    $i = $idUsuario->fetch_assoc();
    $idUser = $i['id'];

    // SE O CADASTRO FOR BEM SUCEDIDO, CADASTRA E EFETUA MATRICULA DO ALUNO NO CURSO
    // Recupera a chave da modalidade da matrícula do curso
    $result_courseid = $con->query("SELECT id FROM moodle.mdl_enrol WHERE courseid = '$courseid' AND enrol='manual'") or die($mysqli->errno .' - '. $mysqli->error);
    $row = $result_courseid->fetch_assoc();
    $mdl_enrol_id = $row['id'];

    // Inscreve o aluno na tabela mdl_user_enrolments
    $inserirAlunoCurso = $con->query("INSERT IGNORE INTO moodle.mdl_user_enrolments (status,enrolid,userid,timestart,timeend,timecreated,timemodified)
                                      VALUES (0,'$mdl_enrol_id','$idUser','$timestamp_datainicio','$timestamp_datafinal','$timestamp_datainicio','$timestamp_datainicio')") or die($mysqli->errno .' - '. $mysqli->error);

    if ($inserirAlunoCurso === TRUE) {
        echo " Aluno Cadastrado no Curso ";
    } else {
        echo "<br>Erro: ", $con->error;
    }
}

function efetuaMatriculaAluno($email,$timestamp_datainicio,$courseid)
{
    global $con;

    $idUsuario = $con->query("SELECT id FROM moodle.mdl_user WHERE email='$email'") or die($mysqli->errno .' - '. $mysqli->error);
    $i = $idUsuario->fetch_assoc();
    $idUser = $i['id'];

    // Recupera o contexto do curso.
    $result_contextCurso = $con->query("SELECT id FROM moodle.mdl_context WHERE instanceid = '$courseid' AND contextlevel=50") or die($mysqli->errno .' - '. $mysqli->error);
    $row_context = $result_contextCurso->fetch_assoc();
    $result_contexid = $row_context['id'];

    // Efetua a matricula no curso
    $efetua_matricula = $con->query("INSERT IGNORE INTO moodle.mdl_role_assignments (roleid,contextid,userid,timemodified)
                                     VALUES (5,'$result_contexid','$idUser','$timestamp_datainicio')") or die($mysqli->errno .' - '. $mysqli->error);

    if ($efetua_matricula === TRUE) {
        echo " Aluno Matriculado no Curso ";
    } else {
        echo "<br>Erro: ", $con->error;
    }
}

function emailSuporte()
{
    $clone_email = clone $mail;

    $clone_email->addAddress($email);
    $clone_email->Subject = 'O aluno '.$firstname.', não acessou o curso';
    $clone_email->Body    = '
    O aluno '.$firstname.', ainda não acesou o curso <br /><br />
    Curso: <strong>'.$nomecurso.'</strong><br />
    Prazo: <strong>'.$datainicio.'</strong>&nbsp;a&nbsp;<strong>'.$datafim.'</strong><br />
    e-Mail: <strong>'.$email.'</strong><br />';
    if(!$clone_email->send()) 
    {
        echo 'Mailer Error: ' . $clone_email->ErrorInfo;
        exit;
    }
    
    echo 'Message has been sent <br>';
}

