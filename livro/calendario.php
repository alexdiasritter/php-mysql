<?php
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

    function linha($semana)
    {
        $hoje = date("j");
        echo "<tr>";
        for ($i = 0; $i <= 6; $i++) {
            if (isset($semana[$i])) {
                if ($semana[$i] == $hoje) {
                    echo "<td><strong>{$semana[$i]}</strong></td>";
                } elseif ($i == 0) {
                    echo "<td><span style='color: red'>{$semana[$i]}</span></td>";
                } else {
                    echo "<td>{$semana[$i]}</td>";
                }
            } else {
                echo "<td></td>";
            }
        }
        echo "</tr>";
    }

    function calendario()
    {
        $dia = 1;
        $semana = array();

        while ($dia <= 31) {
            array_push($semana, $dia);

            if (count($semana) == 7) {
                linha($semana);
                $semana = array();
            }

            $dia++;
        }
    linha($semana);
    }
?>

<table border="1">
    <tr>
        <th>Dom</th>
        <th>Seg</th>
        <th>Ter</th>
        <th>Qua</th>
        <th>Qui</th>
        <th>Sex</th>
        <th>Sáb</th>
    </tr>
    <?php calendario(); ?>
</table>
