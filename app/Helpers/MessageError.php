<?php

function sqlError(array $error)
{
    $return = "";
    if ($error[1] === 1062) :
        $return = explode(" ", $error[2]);
        $return = "O {$return[5]} informado já existe!";
    elseif ($error[1] === 1048 || $error[1] === 1364) :
        $return = str_replace("Column", "O campo ", $error[2]);
        $return = str_replace("cannot be null", " não pode ser vazio!", $return);
        $return = str_replace("Field", "O campo ", $return);
        $return = str_replace("doesn't have a default value", " não pode ser vazio!", $return);
    elseif ($error[1] === 1366) :
        $return = explode(" ", $error[2]);
        $valor = str_replace("integer", "inteiro", $return[1]);
        $return = "É preciso que o campo {$return[6]} seja um valor do tipo {$valor}";
    elseif ($error[1] === 1054) :
        $return = explode(" ", $error[2]);
        $return = "O campo {$return[2]} não existe no banco de dados";
    elseif ($error[1] === 1265) :
        $return = explode(" ", $error[2]);
        $return = "O valor informado não é um valor válido. ({$return[4]})";
    endif;
    return [
        'error' => true,
        'message' => $return,
    ];
}
