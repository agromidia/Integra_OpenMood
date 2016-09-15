<?php

require_once 'logMsg.php';
require_once 'conexao.php';
require_once 'phpmailerConf.php';
require_once 'funcoes.php';

$query_view = "SELECT * FROM v_OpenMood";
$result_select_view = $con->query($query_view);

while ($row = $result_select_view->fetch_array())
{
    $firstname = $row['firstname'];
    $lastname = $row['lastname'];
    $email = $row['email'];
    $username = $email;
    $password = MD5(strtolower($row['firstname']."_".date("y")));
    $nometratado = tratanome($row['firstname']);
    $passuser = $nometratado."_".date("y");
    $confirmed = "1";
    $description = "Inscrito automaticamente pelo sistema.";
    $mnethostid = "1";
    $lang = "pt_br";
    $nomecurso = utf8_encode($row['namecourse_open']);
    $idnumber = $row['sku_idnumber'];
    $idUsuarioMoodle = $row['userid_mood'];
    $courseid = $row['courseid_mood'];

    setlocale(LC_ALL, "pt_BR.utf8");
    $datainicio =  strftime("%d de %B");
    $datafim = strftime("%d de %B de %Y", strtotime('+32 days'));

    $timestamp_datainicio = strtotime('NOW');
    $timestamp_datafinal = strtotime('+32 days');

    $sql_linha = $con->query("SELECT email FROM mdl_user WHERE email='{$email}'");
    $sql_linha_result = $sql_linha->num_rows;

    if ($sql_linha_result > 0)
    {
        echo "<br>E-mail " .$email. " existe | SKU: ".$idnumber. " | ID Moodle " .$idUsuarioMoodle. " | ID Curso: ".$courseid." -> ";


        // == início ==
        // Recupera a chave da modalidade da matrícula do curso
        $result_courseid = $con->query("SELECT id FROM moodle.mdl_enrol WHERE courseid=$courseid AND enrol='manual'") or die ($con->error);
        $row = $result_courseid->fetch_assoc();
        $mdl_enrol_id = $row['id'];
        // Recupera o contexto do curso.
        $result_contextCurso = $con->query("SELECT id FROM moodle.mdl_context WHERE instanceid=$courseid AND contextlevel=50") or die ($con->error);
        $row_context = $result_contextCurso->fetch_assoc();
        $result_contexid = $row_context['id'];
        // Verifica se o aluno está matricilado no curso.
        $verifica_inscricao = $con->query("SELECT enrolid FROM moodle.mdl_user_enrolments mmue WHERE mmue.userid=$idUsuarioMoodle AND mmue.enrolid=$mdl_enrol_id");
        $result_verifica_inscricao = $verifica_inscricao->num_rows;
        // Verifica se o aluno concluiu o curso
        $verifica_conclusao = $con->query("SELECT u.id, u.firstname,u.lastname, u.email,c.timecompleted FROM moodle.mdl_course_completions c INNER JOIN moodle.mdl_user u ON c.userid=u.id WHERE  c.timecompleted > 0  AND c.course=$courseid") or die ($con->error);
        $row_verifica_conclusao = $verifica_conclusao->fetch_assoc();
        $result_verifica_conclusao = $row_verifica_conclusao['0'];

            if ($result_verifica_inscricao > 0)
            {
                echo "Inscri&ccedil;&atilde;o j&aacute; realizada.";

                    if ($result_verifica_conclusao == 0)
                    {

                       echo " | Aluno Matriculado, mas n&atilde;o concluiu o curso";

                    }
                    else
                    {
                        var_dump($result_verifica_conclusao);
                        # code...
                    }
            }
            else
            {

                echo " | Inscri&ccedil;&atilde;o n&atilde;o realizada.";
                // // Inscreve o aluno na tabela mdl_user_enrolments
                // $inserirAlunoCurso = $con->query("INSERT INTO moodle.mdl_user_enrolments (status,enrolid,userid,timestart,timeend,timecreated,timemodified) VALUES (0,$mdl_enrol_id,$idUsuarioMoodle,$timestamp_datainicio,$timestamp_datafinal,0,0) or die ($con->error);

                // // Efetua a matricula no curso
                // $efetua_matricula = $con->query("INSERT INTO moodle.mdl_role_assignments (roleid,contextid,userid,timemodified) VALUES (5,$result_contexid,$idUsuarioMoodle,0)") or die ($con->error);


            }

        // verifica se o e-mail existe e verifica se esta associado ao curso
        $sql_confereSku = $con->query("SELECT mue.userid AS useridUserEnrol from moodle.mdl_user_enrolments mue where mue.userid=$idUsuarioMoodle LIMIT 1") or die ($con->error);
        $sql_confereSku_result = $sql_confereSku->num_rows;
        // Se trouxer 1 entra na condição e avisa ao suporte
        if (!$sql_confereSku_result > 0)
        {
            // $clone_email = clone $mail;
            // $clone_email->addAddress($email);
            // $clone_email->Subject = 'O aluno '.$firstname.', não acessou o curso';
            // $clone_email->Body    = '
            // O aluno '.$firstname.', ainda não acesou o curso <br /><br />
            // Curso: <strong>'.$nomecurso.'</strong><br />
            // Prazo: <strong>'.$datainicio.'</strong>&nbsp;a&nbsp;<strong>'.$datafim.'</strong><br />
            // e-Mail: <strong>'.$email.'</strong><br />';

            // var_dump($clone_email);
            // logMsg( "E-mail existe. ".$email." e não foi Acessado" );

            // if(!$clone_email->send()) {echo 'Mailer Error: ' . $clone_email->ErrorInfo; exit;}
            // echo 'Message has been sent <br>';
            echo "Curso não acessado.";
        }

    } else {
        echo "<br>E-mail não existe. ".$email."<br /><br />";
        $result_insert = $con->query("INSERT INTO moodle.mdl_user (firstname,lastname,email,username,password,confirmed,description,mnethostid,lang) VALUES ('{$firstname}','{$lastname}','{$email}','{$username}','{$password}','{$confirmed}','{$description}','{$mnethostid}','{$lang}')") or die ("<br> Nao foi inserido");

        $clone_email = clone $mail;
        $clone_email->addAddress($email);
        $clone_email->Subject = 'Olá '.$firstname.', aqui está sua conta do Aulas a Distância';
        $clone_email->Body    = '<p><img src="https://aulasadistancia.com.br/site/templates/ol_chranet/images/logo/logoEAD.png" border="0"></p>
        '. cumprimento() .' '.$firstname.', <br /><br />

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
        Atenciosamente.<br /><br />';

        if(!$clone_email->send()) {echo 'Mailer Error: ' . $clone_email->ErrorInfo; exit;}

        echo 'Message has been sent <br>';
        // var_dump($result_insert);
        // print_r($mail);
    }
}

mysqli_free_result($result_select_view);

