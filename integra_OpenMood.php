<?php
// define('CLI_SCRIPT', true);
require_once '../config.php';
require_once 'logMsg.php';

$svrname    = $CFG->dbhost;
$database   = $CFG->dbname;
$username   = $CFG->dbuser;
$password   = $CFG->dbpass;

$con = new mysqli($svrname,$username,$password,$database);
if ($con->connect_errno) { printf($con->connect_errno); exit();}

require_once '../lib/phpmailer/PHPMailerAutoload.php';
$mail = new PHPMailer;
$mail->SMTPDebug = 3;
$mail->isSMTP();
$mail->isHTML(true);
$mail->CharSet = 'UTF-8';
$mail->SMTPAuth = true;
$mail->Host = 'smtp.nutrimidia.com.br';
$mail->Username = 'no-reply@nutrimidia.com.br';
$mail->Password = 'KhN0x6j0';
$mail->SMTPSecure = 'tls';
$mail->port = 587;
$mail->From = 'postmaster.assistemas@gmail.com';
$mail->FromName = 'Inscrição do curso no Aulas a Distância.';
$mail->AddCC('postmaster.assistemas@gmail.com', 'Postmaster AS Sistemas');

if (date("H") >= 7 && date("H") <= 12) {
    $tratamento = "Bom Dia";
} elseif(date("H") > 12 && date("H") < 18) {
    $tratamento = "Bom tarde";
} elseif(date("H") >=  18) {
    $tratamento = "Boa noite";
}

$query_view = "SELECT * FROM v_OpenMood";
$result_select_view = $con->query($query_view);

function tratanome($nome)
{
	$trataetapa1 = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(rtrim($nome)));
	$trataetapa2 = current(str_word_count($trataetapa1,2));
	$tratafinal = strtolower($trataetapa2);

	return $tratafinal;
}

while ($row = $result_select_view->fetch_array()) {
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
    $nomecurso = utf8_encode($row['name']);
    $idnumber = $row['sku'];
    $idUsuarioMoodle = $row['id_user_moodle'];

    setlocale(LC_ALL, "pt_BR.utf8");
    $datainicio =  strftime("%d de %B");
    $datafim = strftime("%d de %B de %Y", strtotime('+32 days'));

    $sql_linha = $con->query("SELECT email FROM mdl_user WHERE email = '{$email}'");
    $sql_linha_result = $sql_linha->num_rows;

    if ($sql_linha_result > 0)
    {
        echo "<br>E-mail existe. ".$email." ".$idnumber." ".$idUsuarioMoodle;
        // verifica se o e-mail existe e verifica se esta associado ao curso
        $sql_confereSku = $con->query("SELECT mue.userid AS useridUserEnrol from moodle.mdl_user_enrolments mue where mue.userid = $idUsuarioMoodle LIMIT 1");
        $sql_confereSku_result = $sql_confereSku->num_rows;
        // Se trouxer 1 entra na condição e avisa ao suporte
        if (!$sql_confereSku_result > 0)
        {
            $clone_email = clone $mail;
            $clone_email->addAddress($email);
            $clone_email->Subject = 'O aluno '.$firstname.', não acessou o curso';
            $clone_email->Body    = '
            O aluno '.$firstname.', ainda não acesou o curso <br /><br />
            Curso: <strong>'.$nomecurso.'</strong><br />
            Prazo: <strong>'.$datainicio.'</strong>&nbsp;a&nbsp;<strong>'.$datafim.'</strong><br />
            e-Mail: <strong>'.$email.'</strong><br />';
            var_dump($clone_email);
            logMsg( "E-mail existe. ".$email." e não foi Acessado" );
            if(!$clone_email->send()) {echo 'Mailer Error: ' . $clone_email->ErrorInfo; exit;}
            echo 'Message has been sent <br>';
        }
    }
    else {
        echo "<br>E-mail não existe. ".$email."<br /><br />";
        $result_insert = $con->query("INSERT INTO mdl_user (firstname,lastname,email,username,password,confirmed,description,mnethostid,lang) VALUES ('{$firstname}','{$lastname}','{$email}','{$username}','{$password}','{$confirmed}','{$description}','{$mnethostid}','{$lang}')") or die ("<br> Nao foi inserido");
        $clone_email = clone $mail;
        $clone_email->addAddress($email);
        $clone_email->Subject = 'Olá '.$firstname.', aqui está sua conta do Aulas a Distância';
        $clone_email->Body    = '<p><img src="https://aulasadistancia.com.br/site/templates/ol_chranet/images/logo/logoEAD.png" border="0"></p>
        '.$tratamento.' '.$firstname.', <br /><br />

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
