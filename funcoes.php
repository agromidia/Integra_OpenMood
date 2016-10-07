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
    $inserirAlunoCurso = $con->query("INSERT IGNORE INTO moodle.mdl_user_enrolments (status,enrolid,userid,timestart,timeend,timecreated,timemodified)
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
    $efetua_matricula = $con->query("INSERT IGNORE INTO moodle.mdl_role_assignments (roleid,contextid,userid,timemodified)
                                       VALUES (5,'$result_contexid','$last_idUser','$timestamp_datainicio')");

    if ($efetua_matricula === TRUE) {
        echo " Aluno Matriculado no Curso ";
    } else {
        echo "<br>Erro: ", $con->error;
    }
}

//function emailAlunoESuporte($email,$firstname,$lastname,$nometratado,$nomecurso,$datainicio,$datafim,$passuser,$mail=array())
//{
//    $mail=NULL;
        
    /*$clone_email = clone $mail; 
    
    vd($clone_email);
    
    $clone_email->addAddress($email); 
    $clone_email->Subject = 'Olá '.$firstname.', aqui está sua conta do Aulas a Distância'; 
    $clone_email->Body    = '<p><img src="https://aulasadistancia.com.br/site/templates/ol_chranet/images/logo/logoEAD.png" border="0"></p> 
    '.cumprimento().' '.$firstname.', <br /><br /> 

    &Eacute; com muita satisfa&ccedil;&atilde;o que informamos que voc&ecirc; foi cadastrado(a) na plataforma de cursos do Aulas a Dist&acirc;ncia, logo a baixo est&atilde;o os dados para o seu acesso.<br><br> 

    ----------------------------------<br /><br /> 
    Curso: <strong>'.$nomecurso.'<br /> 
    </strong>Prazo: <strong>'.$datainicio.'</strong>&nbsp;a&nbsp;<strong>'.$datafim.'<br /> 
    </strong>URL: <a title="Aulas a Dist&acirc;ncia" href="https://aulasadistancia.com.br/site/" target="_blank">https://aulasadistancia.com.br/site/</a> <br /> 
    clique em <em>"&Aacute;rea do Aluno"</em> e peencha com os dados a seguir<br /><br /> 
    Dados de acesso.<br /> 
    e-Mail: <strong>'.$email.'<br /> 
    </strong>Senha: <strong>'.$passuser.'</strong> <br /><br /> 
    ----------------------------------<br /><br /> 

    - A impress&atilde;o do certificado e o download dos materiais do curso devem ser realizados dentro do prazo acima.<br /> 
    - A gera&ccedil;&atilde;o do certificado fora do prazo adquirido somente acontecer&aacute; mediante pagamento de uma taxa.<br /> 
    - Para altera&ccedil;&otilde;es do seu nome entre no curso com os dados informados acima e localize a se&ccedil;&atilde;o Administra&ccedil;&atilde;o.<br /> 
    - Clique em <em>"Minhas configura&ccedil;&otilde;es de perfil"</em> e em seguida clique em <em>"Modificar Perfil"</em>.<br /> 
    - Fa&ccedil;a as altera&ccedil;&otilde;es desejadas e clique no bot&atilde;o <em>"Atualizar perfil"</em> no fim da p&aacute;gina.<br /><br /> 

    Qualquer d&uacute;vida entre em contato com o suporte via chat online na tela inicial da plataforma ou pelo e-mail suporte@dietpro.com.br <br /><br /> 
    Bons estudos!!!<br/><br/> 
    Atenciosamente.<br /><br />
    Alan Pereira<br/>
    relaciomanento com Cliente<br/>
    relacionamento.cliente@assistemas.com.br<br/>
    (31) 3891-9898'; 

    if(!$clone_email->send())
    {
        echo 'Mailer Error: ' . $clone_email->ErrorInfo;
        exit;
    } 
    echo 'Message has been sent <br>';*/ 
//}

function emailSuporte()
{
   /* $clone_email = clone $mail; 
    
    $clone_email->addAddress($email); 
    $clone_email->Subject = 'O aluno '.$firstname.', não acessou o curso'; 
    $clone_email->Body    = ' 
    O aluno '.$firstname.', ainda não acesou o curso <br /><br /> 
    Curso: <strong>'.$nomecurso.'</strong><br /> 
    Prazo: <strong>'.$datainicio.'</strong>&nbsp;a&nbsp;<strong>'.$datafim.'</strong><br /> 
    e-Mail: <strong>'.$email.'</strong><br />'; 
    if(!$clone_email->send()) {echo 'Mailer Error: ' . $clone_email->ErrorInfo; exit;} 
    echo 'Message has been sent <br>';*/
}

