<?php
require 'tsul_ssl.php';
function inverteData($data)
{
	if (count(explode('/', $data)) > 1) {
		return implode('-', array_reverse(explode('/', $data)));
	} elseif (count(explode('-', $data)) > 1) {
		return implode('/', array_reverse(explode('-', $data)));
	}
}
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$data = $_GET['data'];
	$modalidade = $_GET['modalidade'];
}
?>
<style>
    hr {
        color: #12A1A6;
        background-color: #12A1A6;
        margin-top: 0px;
        margin-bottom: 0px;
        height: 4px;
        width: 340px;
        margin-left: 0px;
        border-top-width: 0px;
    }

    .blink {
        animation-duration: 0.85s;
        animation-name: blink;
        animation-iteration-count: infinite;
        animation-direction: alternate;
        animation-timing-function: ease-in-out;
    }

    @keyframes blink {
        from {
            color: white;
        }

        to {
            color: darkred;
        }
    }
</style>
<table class="table table-condensed dataTable width-full" data-plugin="dataTable">
    <thead>
        <tr>
            <th width='8%'>Data</th>
            <th width='5%'>AN</th>
            <th width='5%'>ID Pac</th>
            <th width='33%'>Nome</th>
            <th>Data de Nascimento</th>
            <th width='25%'>Solicitante</th>
            <th width='32%'>Descricao</th>
            <th width='5%'>Situação</th>
            <th width='2%'>Ação</th>

        </tr>
    </thead>
    <tfoot>
        <tr>
            <th width='8%'>Data</th>
            <th width='5%'>AN</th>
            <th width='5%'>ID Pac</th>
            <th width='33%'>Nome</th>
            <th>Data de Nascimento</th>
            <th width='25%'>Solicitante</th>
            <th width='32%'>Descricao</th>
            <th width='5%'>Situação</th>
            <th width='2%'>Ação</th>
        </tr>
    </tfoot>
    <tbody>
        <?php
		include 'conexao.php';
		$stmt = "select a.transacao, a.exame_nro, a.exame_id, a.pessoa_id, s.nome as solicitante, a.situacao, a.contraste, b.transacao, a.med_analise, b.dat_cad as cadastro, b.dt_solicitacao, b.dt_realizacao, b.convenio_id, a.pedido,
					c.nome, d.sigla as convenio, a.exame_id, e.descricao as desc_exames, f.sigla as modalidade,c.dt_nasc, z.coronavirus from itenspedidos a left join pedidos b on 
					b.transacao=a.transacao left join pessoas c on b.paciente_id=c.pessoa_id  left join pessoas s on b.solicitante_id=s.pessoa_id left join convenios d on b.convenio_id=d.convenio_id
					left join procedimentos e on a.exame_id=e.procedimento_id left join modalidades f on e.modalidade_id=f.modalidade_id left join atendimentos z on a.atendimento_id=z.transacao 
					WHERE f.modalidade_id = '$modalidade' AND b.dat_cad = '$data' order by a.transacao DESC";

		$sth = pg_query($stmt) or die($stmt);
		while ($row = pg_fetch_object($sth)) {
			if ($row->situacao == 'Realizado') {
				$classe = "class='bg-info'";
			} else {
				$classe = "class='bg-danger'";
			}

			echo '<tr ' . $classe . '>';
			if ($row->coronavirus == 1) {
				echo "<td class='blink'><font size='2'>" . inverteData(substr($row->cadastro, 0, 10)) . '</font></td>';
				echo "<td class='blink'><font size='2'>" . $row->exame_nro . '</font></td>';
				echo "<td class='blink'><font size='2'>" . $row->pessoa_id . '</font></td>';
				echo "<td class='blink'><font size='2'>" . ts_decodifica($row->nome) . '</font></td>';
				echo "<td class='blink'><font size='2'>" . date('d/m/Y', strtotime($row->dt_nasc)) . '</font></td>';
				echo "<td class='blink'><font size='2'>" . ts_decodifica($row->solicitante) . '</font></td>';
				echo "<td class='blink'><font size='2'>" . $row->desc_exames . '</font></td>';
				echo "<td class='blink'><font size='2'>" . $row->situacao . '</font></td>';
				echo "<td class='blink'><a href=\"alterastatuspedido.php?id=" . $row->exame_nro . "\"><i class=\"fas fa-radiation-alt\" style='color:white' title=\"Marcar como realizado\"></i>";
			} else {
				echo "<td><font size='2'>" . inverteData(substr($row->cadastro, 0, 10)) . '</font></td>';
				echo "<td><font size='2'>" . $row->exame_nro . '</font></td>';
				echo "<td><font size='2'>" . $row->pessoa_id . '</font></td>';
				echo "<td><font size='2'>" . ts_decodifica($row->nome) . '</font></td>';
				echo "<td><font size='2'>" . date('d/m/Y', strtotime($row->dt_nasc)) . '</font></td>';
				echo "<td><font size='2'>" . ts_decodifica($row->solicitante) . '</font></td>';
				echo "<td><font size='2'>" . $row->desc_exames . '</font></td>';
				echo "<td><font size='2'>" . $row->situacao . '</font></td>';
				echo '<td><a href="alterastatuspedido.php?id=' . $row->exame_nro . "\"><i class=\"fas fa-radiation-alt\" style='color:white' title=\"Marcar como realizado\"></i>";
			}
			echo '</tr>';
		}
		?>
    </tbody>
</table>