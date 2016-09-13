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