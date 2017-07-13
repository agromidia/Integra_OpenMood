<?php
require_once '../config.php';

global $DB;
$DB->set_debug(true);

function dataExpirada($userid,$courseid,$dataInicio,$dataFinal)
{
    global $DB, $CFG;

    $dataAtual = strtotime('NOW');
    $sql = "UPDATE mdl_user_enrolments
               SET timestart=:dataInicio,timeend=:dataFinal, timemodified=:dataAtual
             WHERE userid=:userid
               AND enrolid
                IN (SELECT id FROM mdl_enrol WHERE courseid=:courseid)";
    $params= array('dataInicio' => $dataInicio,'dataFinal' => $dataFinal, 'dataAtual' => $dataAtual,'userid'=> $userid,'courseid'=> $courseid );;
    return $DB->execute($sql,$params);
 }

$validData = $DB->get_recordset_sql("SELECT u.id AS userid,e.enrol,e.name AS enrolname,ue.timestart,ue.timeend,ue.timecreated,mc.shortname,e.courseid,ue.userid
                                       FROM mdl_user_enrolments ue
                                 INNER JOIN mdl_enrol e ON ue.enrolid=e.id
                                 INNER JOIN mdl_user u ON u.id=ue.userid
                                 INNER JOIN mdl_course mc ON e.courseid=mc.id
                                      WHERE ue.timeend=0 AND ue.timestart=0 and ue.timecreated <= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 90 day))");

foreach ($validData AS $row)
{
    $dataInicio = $row->timecreated;
    $userid = $row->userid;
    $courseid = $row->courseid;

    if($dataInicio != 0)
    {
        $dataFinal = strtotime('+92 day',$dataInicio);
        dataExpirada($userid,$courseid,$dataInicio,$dataFinal);
    }

}

$validData->close();











// CRONTAB
// */15 * * * * /usr/local/bin/php -f /home/ead/public_html/cursos/integra_OpenMood/integra_OpenMood.php >> /var/log/integra_OpenMood_sucesso.log 2>> /var/log/integra_OpenMood_erros.log && /usr/local/bin/php /home/ead/public_html/cursos/enrol/database/cli/sync.php -v >> /var/log/Sync_Mood_sucesso.log 2>> /var/log/Sync_Mood_erros.log

// SELECT distinct c.id,c.fullname,r.name AS profile,mue.timestart,mue.timeend
// FROM mdl_role_assignments rs
// INNER JOIN mdl_user_enrolments mue ON rs.userid=mue.userid
// INNER JOIN mdl_context e ON rs.contextid=e.id
// INNER JOIN mdl_course c ON c.id=e.instanceid
// INNER JOIN mdl_role r ON r.id=rs.roleid
// WHERE e.contextlevel=50 AND rs.userid=1153
