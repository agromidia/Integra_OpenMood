<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

header("Content-type: text/html; charset=utf-8");

require_once 'logMsg.php';
require_once 'conexao.php';
require_once 'phpmailerConf.php';
require_once 'funcoes.php';

// BUSCA OS DADOS DO OPENCART NA VIEW
$query_view = $con->query("SELECT * FROM v_OpenMood") or die($mysqli->errno .' - '. $mysqli->error);

while ($row = $query_view->fetch_object())
{
    $firstname = $row->firstname;
    $lastname = $row->lastname;
    $email = $row->email;
    $username = $email;
    $password = password_hash(strtolower($firstname."_".date("y")), PASSWORD_DEFAULT);
    $nometratado = tratanome($firstname);
    $passuser = $nometratado."_".date("y");
    $description = "Inscrito automaticamente pelo sistema.";
    $nomecurso = utf8_encode($row->namecourse_open);
    $idnumber = $row->sku_idnumber;
    $idUsuarioMoodle = $row->userid_mood;
    $courseid = $row->courseid_mood;

    setlocale(LC_ALL, "pt_BR.utf8");
    $datainicio =  strftime("%d de %B");
    $datafim = strftime("%d de %B de %Y", strtotime('+32 days'));

    $timestamp_datainicio = strtotime('NOW');
    $timestamp_datafinal = strtotime('+32 days');

    // VERIFICA SE OS USUÁIOS ESTÃO CADASTRADOS NA TABELA mdl_user DO MOODLE E SE CORRESPONDEM OS EMAILS DA view v_OpenMood.
    $sql_linha = $con->query("SELECT email FROM moodle.mdl_user WHERE email='$email'") or die($mysqli->errno .' - '. $mysqli->error);
    $sql_linha_result = $sql_linha->num_rows;

    // VERIFICA SE O ALUNO ESTA MATRICULADO NO CURSO
    $verifica_inscricao = $con->query("SELECT me.courseid, mue.userid FROM mdl_user_enrolments mue
                                   INNER JOIN mdl_user mu ON mu.id = mue.userid
                                   INNER JOIN mdl_enrol me ON mue.enrolid = me.id
                                        WHERE me.courseid='$courseid' AND mue.userid='$idUsuarioMoodle' AND mu.email='$email'");

    $result_verifica_inscricao = $verifica_inscricao->num_rows;

    if ($result_verifica_inscricao === 0)
    {
            echo "<br>E-mail não existe. ".$email."<br /><br />";

            // SE NAO ESTIVER, CADASTRA O ALUNO
            // Registra Aluno Novo
            cadastraAluno($username,$password,$firstname,$lastname,$email,$description);

            $last_idUser = $con->insert_id;

            // SE O CADASTRO FOR BEM SUCEDIDO, CADASTRA E EFETUA MATRICULA DO ALUNO NO CURSO
            // Recupera a chave da modalidade da matrícula do curso
            $result_courseid = $con->query("SELECT id FROM moodle.mdl_enrol WHERE courseid = '$courseid' AND enrol='manual'");
            $row = $result_courseid->fetch_assoc();
            $mdl_enrol_id = $row['id'];

            cadastraAlunoCurso($mdl_enrol_id,$last_idUser,$timestamp_datainicio,$timestamp_datafinal,$timestamp_datainicio,$timestamp_datainicio);

            // Recupera o contexto do curso.
            $result_contextCurso = $con->query("SELECT id FROM moodle.mdl_context WHERE instanceid = '$courseid' AND contextlevel=50");
            $row_context = $result_contextCurso->fetch_assoc();
            $result_contexid = $row_context['id'];

            efetuaMatriculaAluno($result_contexid,$last_idUser,$timestamp_datainicio);

            // ENVIA EMAIL PARA O ALUNO E O COORDENADOR
            $clone_email = clone $mail;

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
            relaciomanento com Cliente<br/>
            relacionamento.cliente@assistemas.com.br<br/>
            (31) 3891-9898';

            if(!$clone_email->send())
            {
                echo 'Mailer Erro: ' . $clone_email->ErrorInfo;
                exit;
            }
            echo 'Mensagem enviada com sucesso <br>';

    }
    else // SE O ALUNO JA ESTIVER MATRICULADO MAS NAO ESTIVER EM NENHUM CURSO, FAZ A VERIFICAÇÃO POR CURSO E PERÍODO VIGENTE
    {
        echo "<br />E-mail " .$email. " existe | ID Moodle " .$idUsuarioMoodle. " | ID Curso: ".$courseid." -> ";

        // Recupera a chave da modalidade da matrícula do curso
        $result_courseid = $con->query("SELECT id FROM moodle.mdl_enrol WHERE courseid = '$courseid' AND enrol='manual'");
        $row = $result_courseid->fetch_assoc();
        $mdl_enrol_id = $row['id'];

        // Recupera o contexto do curso.
        $result_contextCurso = $con->query("SELECT id FROM moodle.mdl_context WHERE instanceid = '$courseid' AND contextlevel=50");
        $row_context = $result_contextCurso->fetch_assoc();
        $result_contexid = $row_context['id'];

        // VERIFICA SE O ALUNO ESTA MATRICULADO NO CURSO
        $verifica_inscricao = $con->query("SELECT me.courseid, mue.userid FROM mdl_user_enrolments mue
                                            INNER JOIN mdl_user mu ON mu.id = mue.userid
                                            INNER JOIN mdl_enrol me ON mue.enrolid = me.id
                                            WHERE me.courseid ='$courseid' AND mue.userid ='$idUsuarioMoodle'");

        $result_verifica_inscricao = $verifica_inscricao->num_rows;

        // VERIFICA O STATUR DO ALUNO NO CURSO
        $verifica_conclusao = $con->query("SELECT COUNT(ue.timeend) AS countrecord
                                            FROM mdl_user_enrolments ue
                                            INNER JOIN mdl_enrol e ON ue.enrolid=e.id
                                            INNER JOIN mdl_user u ON u.id=ue.userid
                                            WHERE e.courseid='$courseid' AND u.id='$idUsuarioMoodle' AND ue.timeend > NOW() AND ue.timeend != 0");

        $row_verifica_conclusao = $verifica_conclusao->fetch_assoc();
        $result_verifica_conclusao = $row_verifica_conclusao['countrecord'];

        // VERIFICA SE O ALUNO AINDA ESTA COM O CURSO VIGENTE
        $verifica_validade = $con->query("SELECT timeend
                                            FROM mdl_user_enrolments ue
                                            INNER JOIN mdl_enrol e ON ue.enrolid=e.id
                                            INNER JOIN mdl_user u ON u.id=ue.userid
                                            WHERE e.courseid='$courseid' AND u.id='$idUsuarioMoodle' AND ue.timeend < NOW() AND ue.timeend != 0");

        $row_verifica_validade = $verifica_validade->fetch_assoc();
        $row_validade = $row_verifica_validade['timeend'];

        if ($result_verifica_inscricao > 0) // CONDIÇÃO SE ESTIVER MATRICULADO
        {
            echo " | Inscrição já realizada. ";

            if ($row_validade > (strtotime(date("Y-m-d H:i:s"))) ) // CONDIÇÃO SE ESTIVER NO PERÍODO VIGENTE - SE CONCLUIU OU NÃO
            {
                echo " | Aluno matriculado, não concluiu o curso ou período não venceu ";
            }
            else
            {
                echo " | O prazo do curso venceu.";

                emailSuporte();

            }
        }
        else // SE O EMAIL EXISTIR E NÃO ESTIVER MATRICULADO EM NENHUM CURSO
        {
            echo " | Inscrição não realizada. ";

            // INSERE O ALUNO NO CURSO E EFETUA A MATRICULA
            // Recupera a chave da modalidade da matrícula do curso
            $result_courseid = $con->query("SELECT id FROM moodle.mdl_enrol WHERE courseid = '$courseid' AND enrol='manual'");
            $row = $result_courseid->fetch_assoc();
            $mdl_enrol_id = $row['id'];

            cadastraAlunoCurso($mdl_enrol_id,$idUsuarioMoodle,$timestamp_datainicio,$timestamp_datafinal,$timestamp_datainicio,$timestamp_datainicio);

            // Recupera o contexto do curso.
            $result_contextCurso = $con->query("SELECT id FROM moodle.mdl_context WHERE instanceid = '$courseid' AND contextlevel=50");
            $row_context = $result_contextCurso->fetch_assoc();
            $result_contexid = $row_context['id'];

            efetuaMatriculaAluno($result_contexid,$idUsuarioMoodle,$timestamp_datainicio);

            // emailAlunoESuporte($email,$firstname,$lastname,$nometratado,$nomecurso,$datainicio,$datafim,$passuser);

        }

         // // verifica se o e-mail existe e verifica se esta associado ao curso
         // $sql_confereSku = $con->query("SELECT u.firstaccess
         //                                FROM mdl_user_enrolments ue
         //                                INNER JOIN mdl_enrol e ON ue.enrolid=e.id
         //                                INNER JOIN mdl_user u ON u.id=ue.userid
         //                                WHERE e.courseid='$courseid' AND u.id='$idUsuarioMoodle'");
         // $sql_confereSku_result = $sql_confereSku->num_rows;

         // // Se trouxer 1 entra na condição e avisa ao suporte
         // if ($sql_confereSku_result === 0 )
         // {
         //     echo " | Curso não acessado.";
         // }
         // else
         // {
         //     echo " | Iniciou o Curso";
         // }

    }
}
mysqli_free_result($query_view);
