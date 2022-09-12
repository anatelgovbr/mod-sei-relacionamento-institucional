<?
/**
 * @since  30/05/2017
 * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
 */

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiCalculoDataRN extends InfraRN
{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	protected function inicializarObjInfraIBanco()
	{
		return BancoSEI::getInstance();
	}
	
	private function _removerTimeDate(&$strData){
		$countDate  = strlen($strData);
		$isDateTime = $countDate > 10 ? true : false;
		if($isDateTime){
			$arrData = explode(" ",$strData);
			$strData = $arrData[0];
		}
	}
	
	/* função responsável por calcular somatoria de data considerando ou nao dias uteis 
	 * @param array de 3 elementos: 
	 * [0] - quantidade de dias a somar, 
	 * [1] - data a partir da qual somar, 
	 * [2] - true/false se é para levar em consideraçao dias uteis
	 * */
	
	public function somarDiaUtilConectado( $param )
	{
		
		$numQtde = $param[0];
		$strData = $param[1];
		$isDiaUtil = $param[2];
		
		//forma de calculo considerando dia util
		if( $isDiaUtil ) {
		
			$strDataFinal = InfraData::calcularData(($numQtde + 365), InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ADIANTE, $strData);
			
			$this->_removerTimeDate($strData);
			$arrParamDatas = array( $strData, $strDataFinal );
			$arrFeriados  = $this->recuperarFeriados( $arrParamDatas );
					
			$count = 0;
			while ($count < $numQtde) {
				
				$strData = InfraData::calcularData(1, InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ADIANTE, $strData);
				
				if (InfraData::obterDescricaoDiaSemana($strData) != 'sábado' &&
						InfraData::obterDescricaoDiaSemana($strData) != 'domingo' &&
						!in_array($strData, $arrFeriados)
						) {
							$count++;
						}
						
			}
		
		} 
		
		//forma de calculo sem considerar dias uteis
		else {
			
			$strData = null;
		}
		
		return $strData;
	}
		
	/* Função para listar feriados cadastrados no SEI, entre uma data inicial e uma data final */
	public function recuperarFeriadosConectado( $arrParamDatas )
	{
		
		$strDataInicial = $arrParamDatas[0]; 
		$strDataFinal = $arrParamDatas[1];
		
		$numIdOrgao   = SessaoSEI::getInstance()->getNumIdOrgaoUnidadeAtual();
		$arrFeriados  = array();
		
		$objFeriadoRN = new FeriadoRN();
		$objFeriadoDTO = new FeriadoDTO();
		$objFeriadoDTO->retDtaFeriado();
		$objFeriadoDTO->retStrDescricao();
		
		if($numIdOrgao != ''){
			$objFeriadoDTO->adicionarCriterio(array('IdOrgao','IdOrgao','IdOrgao'),
					array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),
					array(null,0,$numIdOrgao),
					array(InfraDTO::$OPER_LOGICO_OR,InfraDTO::$OPER_LOGICO_OR));
		}else{
			$objFeriadoDTO->adicionarCriterio(array('IdOrgao','IdOrgao'),
					array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),
					array(null,0),
					array(InfraDTO::$OPER_LOGICO_OR));
		}
				
		$objFeriadoDTO->adicionarCriterio(array('Feriado', 'Feriado'),
				array(InfraDTO::$OPER_MAIOR_IGUAL, InfraDTO::$OPER_MENOR_IGUAL),
				array($strDataInicial, $strDataFinal),
				array(InfraDTO::$OPER_LOGICO_AND));
		
		$objFeriadoDTO->setOrdDtaFeriado(InfraDTO::$TIPO_ORDENACAO_ASC);
		
		$count = $objFeriadoRN->contar($objFeriadoDTO);
		$arrObjFeriadoDTO = $objFeriadoRN->listar($objFeriadoDTO);
		
		if($count > 0)
		{
			$arrFeriados = InfraArray::converterArrInfraDTO($arrObjFeriadoDTO, 'Feriado');
		}
		
		return $arrFeriados;
	}

}