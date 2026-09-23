<?php
function traduz_prioridade($codigo)
{
    $prioridade = '';
        switch ($codigo) {
        case 1:
            $prioridade = 'Baixa';
            break;
        case 2:
            $prioridade = 'Média';
            break;
        case 3:
            $prioridade = 'Alta';
            break;
        }
        
        return $prioridade;
}

function conversor($data_br) 
{
    if (empty($data_br)) return null;
    $partes = explode('/', $data_br);
    if (count($partes) == 3) {
        return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
    }
    return $data_br;
}

function traduz_data_para_exibir($data_americana) 
{
    if (empty($data_americana) || $data_americana == '0000-00-00') return '';
    $partes = explode('-', $data_americana);
    if (count($partes) == 3) {
        return $partes[2] . '/' . $partes[1] . '/' . $partes[0];
    }
    return $data_americana;
}

function traduz_concluida($valor) {
    return ($valor == 1) ? 'Sim' : 'Não';
}