<?php

require 'tsul_ssl.php';
require 'verifica.php';

function inverteData($data)
{
	if (count(explode('/', $data)) > 1) {
		return implode('-', array_reverse(explode('/', $data)));
	} elseif (count(explode('-', $data)) > 1) {
		return implode('/', array_reverse(explode('-', $data)));
	}
}

$destino = $_GET['destino'];
$motivoalta = 'Finalizado pelo controle de Permanencia';
$atendimento = $_GET['atendimento'];
$hospital = $_GET['hospital'];
$setor = $_GET['setor'];
$data = date('Y-m-d', strtotime('-1days'));
$data_controle = date('Y-m-d');
$hora = date('H:i');
$clinica = $_GET['clinica'];

// if ($destino == 'alta') {
//     $destino = '01';
// } else if ($destino == 'alta / encam. ambul.') {
//     $destino = '02';
// } else if ($destino == 'em observacao / medicacao') {
//     $destino = '07';
// } else if ($destino == 'exames / reavaliacao') {
//     $destino = '10';
// } else if ($destino == 'permanecia') {
//     $destino = '03';
// } else if ($destino == 'transf. outra upa') {
//     $destino = '04';
// } else if ($destino == 'transf. intern. hospitalar') {
//     $destino = '05';
// } else if ($destino == 'obito') {
//     $destino = '06';
// } else if ($destino == 'nao respondeu chamado') {
//     $destino = '09';
// } else if ($destino == 'alta evasao') {
//     $destino = '11';
// } else if ($destino == 'alta pedido') {
//     $destino = '12';
// } else if ($destino == 'alta / policia') {
//     $destino = '14';
// } else if ($destino == 'alta / penitenciaria') {
//     $destino = '15';
// } else if ($destino == 'alta / pos medicamento') {
//     $destino = '16';
// }

include 'conexao.php';
$stmt = "select count(*) as qtd, destino_encaminhamento from destino_paciente where atendimento_id = $atendimento group by 2";
$sth = pg_query($stmt) or die($stmt);
$row = pg_fetch_object($sth);

if ($row->qtd == 0) {
	include 'conexao.php';
	$stmt = "insert into destino_paciente (atendimento_id, destino_encaminhamento, motivo,data, hora, data_controle,usuario) 
					values ($atendimento, $destino, '$motivoalta', '$data', '$hora','$data_controle','$usuario')";
	$sth = pg_query($stmt) or die($stmt);
} elseif ($row->destino_encaminhamento == 3 or $row->destino_encaminhamento == 20 or $row->destino_encaminhamento == 7 or $row->destino_encaminhamento == 10 or $row->destino_encaminhamento == 21) {
	$stmt = "update destino_paciente set destino_encaminhamento = '$destino', motivo= '$motivoalta', data = '$data', hora = '$hora', setor = null, hospital = null, clinica = null, data_controle = '$data_controle', usuario='$usuario'
    where atendimento_id = '$atendimento'";
	$sth = pg_query($stmt) or die($stmt);
} else {
	echo "<script>Swal.fire('Paciente já finalizado pelo médico!!!')</script>";
}

include 'conexao.php';
$stmt = "select count(*) as qtd from controle_permanencia where atendimento_id = $atendimento";
$sth = pg_query($stmt) or die($stmt);
$row = pg_fetch_object($sth);

if ($row->qtd == 0) {
	include 'conexao.php';
	$stmt = "insert into controle_permanencia(atendimento_id, data, hora) values($atendimento, '$data', '$hora')";
	$sth = pg_query($stmt) or die($stmt);
} else {
	include 'conexao.php';
	$stmt = "update controle_permanencia set data = '$data', hora = '$hora' where atendimento_id = $atendimento";
	$sth = pg_query($stmt) or die($stmt);
}
?>
<table id="data_table" class="table">
	<thead>
		<tr>
			<th>Prontuário</th>
			<th>Paciente</th>
			<th>DT. Entrada</th>
			<th>DT. Saída</th>
			<th>Hora</th>
			<th>Destino</th>
			<th>Dias de Permanência</th>
			<th>Ação</th>
		</tr>
	</thead>
	<tbody>
		<?php
		include 'conexao.php';
		$sql = "SELECT a.destino_id, b.paciente_id, c.nome, b.dat_cad as data_entrada, a.data as data_saida, a.hora, a.destino_encaminhamento as destino  FROM destino_paciente a INNER JOIN atendimentos b ON a.atendimento_id = b.transacao INNER JOIN pessoas c ON b.paciente_id = c.pessoa_id INNER JOIN controle_permanencia d ON d.atendimento_id = a.atendimento_id WHERE motivo = 'Finalizado pelo controle de Permanencia' AND data_controle = '" . date('Y-m-d') . "' ORDER BY d.controle_permanecia_id desc";
		$result = pg_query($sql) or die($sql);
		while ($row = pg_fetch_object($result)) {
			?>
		<tr>
			<td><?= $row->paciente_id; ?>
			</td>
			<td><?= ts_decodifica($row->nome); ?>
			</td>
			<td><?= inverteData(substr($row->data_entrada, 0, 10)); ?>
			</td>
			<td><input type="text" id="data_saida"
					value="<?= inverteData($row->data_saida); ?>"
					OnKeyPress="formatar('##/##/####', this)"
					onblur="altera_data(this.value,<?= $row->destino_id; ?>)">
			</td>
			<td><input type="text" id="data_saida"
					value="<?= $row->hora; ?>"
					OnKeyPress="formatar('##:##', this)"
					onblur="altera_hora(this.value,<?= $row->destino_id; ?>)">
			</td>
			<?php
				if ($row->destino == '01') {
					echo '<td>ALTA</td>';
				} elseif ($row->destino == '02') {
					echo '<td>ALTA / ENCAM. AMBUL.</td>';
				} elseif ($row->destino == '07') {
					echo '<td>EM OBSERVAÇÃO / MEDICAÇÃO</td>';
				} elseif ($row->destino == '10') {
					echo '<td>EXAMES / REAVALIACAO</td>';
				} elseif ($row->destino == '03') {
					echo '<td>PERMANÊCIA</td>';
				} elseif ($row->destino == '04') {
					echo '<td>TRANSF. OUTRA UPA</td>';
				} elseif ($row->destino == '05') {
					echo '<td>TRANSF. INTERN. HOSPITALAR</td>';
				} elseif ($row->destino == '06') {
					echo '<td>ÓBITO</td>';
				} elseif ($row->destino == '09') {
					echo '<td>NAO RESPONDEU CHAMADO</td>';
				} elseif ($row->destino == '11') {
					echo '<td>ALTA EVASÃO</td>';
				} elseif ($row->destino == '12') {
					echo '<td>ALTA PEDIDO</td>';
				} elseif ($row->destino == '14') {
					echo '<td>ALTA / POLICIA</td>';
				} elseif ($row->destino == '15') {
					echo '<td>ALTA / PENITENCIÁRIA</td>';
				} elseif ($row->destino == '16') {
					echo '<td>ALTA / PÓS MEDICAMENTO</td>';
				} elseif ($row->destino == '20') {
					echo '<td>ALTA VIA SISTEMA</td>';
				} elseif ($row->destino == '21') {
					echo '<td>TRANSFERENCIA</td>';
				}
			$d1 = strtotime($row->data_saida);
			$d2 = strtotime(substr($row->data_entrada, 0, 10));
			$dataFinal = ($d2 - $d1) / 86400;
			if ($dataFinal < 0) {
				$dataFinal *= -1;
			} ?>
			<td><?= $dataFinal; ?>
			</td>
			<td><button class="btn btn-raised btn-danger btn-min-width mr-1 mb-1"
					onclick="cancelar_permanencia(<?= $row->destino_id; ?>)">Cancelar</button>
			</td>
		</tr>
		<?php
		} ?>
	</tbody>
</table>