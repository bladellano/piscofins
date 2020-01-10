<?php 

header ('Content-type: text/html; charset=UTF-8');

include("class/EfdPiscofins.php");

$rotina  = $argv[0];
$cnpj    = $argv[1];
$origem  = $argv[2];
$destino = $argv[3];

$oSpedEfd  = new EfdPiscofins($cnpj,$origem,$destino);

if($argc != 4 || strlen($cnpj) != 14)
	die("Parametros insuficientes!"); 

/* ENCONTRA OS ARQUIVOS COM CNPJ SOLICITADO */
$diretorio = dir($origem."/");
$aArquivos = array();
while($arquivo = $diretorio->read()){
	if($oSpedEfd->comparandoNomeArquivoCnpj($cnpj,$arquivo))
		$aArquivos[] = $oSpedEfd->arquivoParaArray($origem."/".$arquivo);

}
$diretorio -> close();

if(count($aArquivos) ==0) 
	die("Nenhum arquivo encontrado!");

/* JUNTA TODAS AS LINHAS DOS ARQUIVOS ENCONTRADOS PARA O CNPJ */
$aFinalDados = array();
foreach($aArquivos as $ar){
	foreach($ar as $values){
		$aFinalDados[] = $values;
	}
}

/* ARRAY PRINCIPAL P/ PROCESSAR O RESTANTE PARA BAIXO */
$arrayFiltrado = $oSpedEfd->criandoDadosFiltrados($aFinalDados);

/* AGRUPA POR DATA AS NOTAS FISCAIS COM OS ITENS */
$notas = $oSpedEfd->criandoNfe($arrayFiltrado);

/* JUNTANDO ARRAYS */
$aJuntadaNfe = array_merge(
	array("Principal"=> $oSpedEfd->criandoUnidadeNegocio($arrayFiltrado)),
	array("Nfe"=> $oSpedEfd->agrupandoNfePorData($notas['notas_fiscais']))
);


$pasta_arquivo_destino = $destino.'/'.$cnpj.'/efd-piscofins/';

/* CRIA PASTA SE NÃO EXISTIR */
if(!is_dir($destino))
	mkdir($pasta_arquivo_destino, 0777, true);

$saida = json_encode( $aJuntadaNfe,JSON_UNESCAPED_UNICODE );

file_put_contents($pasta_arquivo_destino."$cnpj"."_compras_vendas.json", $saida);
echo "Arquivo processado com sucesso!";

?>