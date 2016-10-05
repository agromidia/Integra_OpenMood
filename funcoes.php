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

// enroll student to course (roleid = 5 is student role)
function enroll_to_course($courseid, $userid, $roleid=5, $extendbase=3, $extendperiod=0)  {
    global $DB;

    $instance = $DB->get_record('enrol', array('courseid'=>$courseid, 'enrol'=>'manual'), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
    $today = time();
    $today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);

    if(!$enrol_manual = enrol_get_plugin('manual')) { throw new coding_exception('Can not instantiate enrol_manual'); }
    switch($extendbase) {
        case 2:
            $timestart = $course->startdate;
            break;
        case 3:
        default:
            $timestart = $today;
            break;
    }
    if ($extendperiod <= 0) { $timeend = strtotime('+32 days'); }   // extendperiod are seconds
    else { $timeend = $timestart + $extendperiod; }
    $enrolled = $enrol_manual->enrol_user($instance, $userid, $roleid, $timestart, $timeend);
    add_to_log($course->id, 'course', 'enrol', '../enrol/users.php?id='.$course->id, $course->id);

    return $enrolled;
}

function vd($data)
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}