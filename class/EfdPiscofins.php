<?php 

class EfdPiscofins{

	public $cnpj;
	public $origem;
	public $destino;
	private $aCodigos = array("0140","0150","0190","0200","A100","A170");

	function __construct($cnpj,$origem,$destino){
		$this->cnpj    = $cnpj;
		$this->origem  = $origem;
		$this->destino = $destino;
	}
	
	function __destruct() {
		echo utf8_encode("\nFIM!");
	}

	public function arquivoParaArray($str){
		$file = fopen($str, "r");
		$newArray = array();
		while ($getRowCsv = fgetcsv($file, 10000, "\n")) {	
			$newArray[] = $getRowCsv[0];
		}	
		fclose($file);	
		return $newArray;
	}

	public function comparandoNomeArquivoCnpj($re,$str){
		preg_match_all("/$re/m", $str, $matches);
		if(count(current($matches)) > 0) return true;
		return false;
	}

	public function criandoDadosFiltrados($data){	
		$arrayFiltrado = [];
		foreach ($data as $key => $value) {
			$row =  explode("|",$value);
			array_shift($row);
			if(in_array($row[0], $this->aCodigos)){
				$arrayFiltrado[] = $row;
			}
		}
		return $arrayFiltrado;
	}


	public function criandoUnidadeNegocio($data){ /* Param: $arrayFiltrado */
		$arrayExecutoraIndividual = array();
		foreach ($data as $key => $partValue) {
			if($partValue[0] == "0140"){
				$arrayUnidadeNegocio = array();		
				$arrayUnidadeNegocio['cnpj'] = $partValue[3];        
				$arrayUnidadeNegocio['nome'] = $partValue[2];
				$arrayExecutoraIndividual['unidade_negocio'][] = $arrayUnidadeNegocio;    
			}
			if($partValue[0] == "0150"){
				$clientesFornecedores = array();
				$countLastIndex = count($arrayExecutoraIndividual['unidade_negocio']);
				$lastIndex = $countLastIndex - 1;
				$clientesFornecedores['cod_id_part'] = $partValue[1];        
				$clientesFornecedores['nome']        = $partValue[2];        
				$clientesFornecedores['cnpj']        = $partValue[4];
				$clientesFornecedores['endereco']    = $partValue[9];
				$clientesFornecedores['cod_mun']     = $partValue[7];
				$clientesFornecedores['end']         = $partValue[9];
				$clientesFornecedores['bairro']      = $partValue[12];
				$clientesFornecedores['compl']       = $partValue[11];
				$arrayExecutoraIndividual['unidade_negocio'][$lastIndex]['clientes_fornecedores'][] = $clientesFornecedores;
			}
			if($partValue[0] == "0200"){
				$produtosServicos = array();
				$countLastIndex = count($arrayExecutoraIndividual['unidade_negocio']);
				$lastIndex = $countLastIndex - 1;		
				$produtosServicos['cod_item']   = $partValue[1];        
				$produtosServicos['descr_item'] = $partValue[2];        
				$produtosServicos['unid_inv']   = $partValue[5];
				$produtosServicos['tipo_item']  = $partValue[6];
				$produtosServicos['cod_gen']    = $partValue[9];
				$produtosServicos['cod_lst']    = $partValue[10];
				$produtosServicos['aliq_icms']  = $partValue[11];

				$arrayExecutoraIndividual['unidade_negocio'][$lastIndex]['clientes_fornecedores'][0]['produtos_servicos'][] = $produtosServicos;
			}
		}
		return $arrayExecutoraIndividual;
	}


	public function criandoNfe($data){ /* Param:  $arrayFiltrado */
		$arrayNfe = array();
		foreach ($data as $key => $partValue) {

			if($partValue[0] == "A100"){

				$notaFiscal = array();

				$notaFiscal['ind_oper'] = $partValue[1];        
				$notaFiscal['ind_emit'] = $partValue[2];       
				$notaFiscal['cod_part'] = $partValue[3];       
				$notaFiscal['cod_sit']  = $partValue[4];       
				$notaFiscal['num_doc']  = $partValue[7];       
				$notaFiscal['dt_doc']   = $partValue[9];       
				$notaFiscal['vl_doc']   = $partValue[11];       

				$arrayNfe['notas_fiscais'][] = $notaFiscal;    
			}

			if($partValue[0] == "A170"){
				$itemNotaFiscal = array();
				$countLastIndex = count($arrayNfe['notas_fiscais']);
				$lastIndex = $countLastIndex - 1;
				$itemNotaFiscal['num_item']   = $partValue[1];        
				$itemNotaFiscal['cod_item']   = $partValue[2];        
				$itemNotaFiscal['desc_compl'] = $partValue[3];
				$itemNotaFiscal['vl_item']    = $partValue[4];

				$arrayNfe['notas_fiscais'][$lastIndex]['itens_notas_fiscais'][] = $itemNotaFiscal;
			}
		}
		return $arrayNfe;
	}


	public function agrupandoNfePorData($data){
		$output = array(); 
		foreach ($data as $row) {
			if(!isset($output[$row['dt_doc']])) {
				$output[$row['dt_doc']] = array($row['itens_notas_fiscais']);
			} else {
				array_push($output[$row['dt_doc']],$row['itens_notas_fiscais']);
			}
			$arrayFinal[$row['dt_doc']] = [
				'num_doc'             => $row['num_doc'],
				'cod_sit'             => $row['cod_sit'],
				'vl_doc'              => $row['vl_doc'],
				'itens_notas_fiscais' => $output[$row['dt_doc']]
			];
		}
		return $arrayFinal;
	}

}