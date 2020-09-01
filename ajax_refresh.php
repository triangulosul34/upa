<?php

include "Config.php";

function inverteData($data)
{
	if (count(explode("/", $data)) > 1) {
		return implode("-", array_reverse(explode("/", $data)));
	} elseif (count(explode("-", $data)) > 1) {
		return implode("/", array_reverse(explode("-", $data)));
	}
}


function connect()
{
	return new PDO('pgsql:host=localhost;dbname=' . BANCO_DADOS . '', 'postgres', 'tsul2020##');
}
$pdo = connect();
$keyword = '%' . $_POST['keyword'] . '%';
$sql = "SELECT pessoa_id, cpf, num_carteira_convenio as cns, nome, nome_mae, dt_nasc, sexo, telefone, celular, cep, endereco, numero, complemento, bairro, cidade, estado FROM pessoas WHERE nome LIKE '%$keyword%' ORDER BY nome ASC LIMIT 10 ";
$query = $pdo->prepare($sql);
$query->bindParam(':keyword', $keyword, PDO::PARAM_STR);
$query->execute();
$list = $query->fetchAll();
foreach ($list as $rs) {
	if ($rs['sexo'] == "M") {
		$sexo = "masculino";
	} else {
		$sexo = "feminino";
	}

	$cpf = preg_replace("/[^0-9]/", "", $rs['cpf']);
	// put in bold the written text
	$country_name = str_replace($_POST['keyword'], '<b>' . $_POST['keyword'] . '</b>', $rs['nome']) . " - " . inverteData($rs['dt_nasc']) . " - " . $cpf;
	// add new option
	echo '<li onclick="set_item(\'' . str_replace("'", "\'", $rs['nome']) . '\',\'' . $rs['pessoa_id'] . '\',\'' . inverteData($rs['dt_nasc']) . '\',\'' . $sexo . '\',\'' . $rs['telefone'] . '\',\'' . $rs['celular'] . '\',\'' . $rs['cep'] . '\',\'' . utf8_decode($rs['rua']) . '\',\'' . $rs['numero'] . '\',\'' . utf8_decode($rs['bairro']) . '\',\'' . $rs['cidade'] . '\',\'' . $rs['estado'] . '\',\'' . $cpf . '\',\'' . $rs['nome_mae'] . '\')">' . $country_name . '</li>';
}