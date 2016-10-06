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
                                    VALUES ('manual',1,1,'$username','$password','$firstname','$lastname','$email','$description','BR','pt_br')");
    if ($result_insert === TRUE)
    {
        echo " Usuario Registrado ";
    }
    else
    {
        echo "<br>Erro: ". $con->error;
    }
}

function cadastraAlunoCurso($mdl_enrol_id,$last_idUser,$timestamp_datainicio,$timestamp_datafinal,$timestamp_datainicio,$timestamp_datainicio)
{
    global $con;
        // Inscreve o aluno na tabela mdl_user_enrolments
        $inserirAlunoCurso = $con->query("INSERT INTO moodle.mdl_user_enrolments (status,enrolid,userid,timestart,timeend,timecreated,timemodified)
                                           VALUES (0,'$mdl_enrol_id','$last_idUser','$timestamp_datainicio','$timestamp_datafinal','$timestamp_datainicio','$timestamp_datainicio')");

        if ($inserirAlunoCurso === TRUE) {
            echo " Aluno Cadastrado no Curso ";
        } else {
            echo "<br>Erro: ", $con->error;
        }
}


function efetuaMatriculaAluno($result_contexid,$last_idUser,$timestamp_datainicio)
{
    global $con;

        // Efetua a matricula no curso
        $efetua_matricula = $con->query("INSERT INTO moodle.mdl_role_assignments (roleid,contextid,userid,timemodified)
                                           VALUES (5,'$result_contexid','$last_idUser','$timestamp_datainicio')");

        if ($efetua_matricula === TRUE) {
            echo " Aluno Matriculado no Curso ";
        } else {
            echo "<br>Erro: ", $con->error;
        }
}