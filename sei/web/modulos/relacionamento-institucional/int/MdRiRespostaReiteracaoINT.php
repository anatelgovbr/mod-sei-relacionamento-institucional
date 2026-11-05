<?php

    /**
     * @since  11/10/2016
     * @author Jaqueline Mendes <jaqueline.mendes@castgroup.com.br>
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRespostaReiteracaoINT extends InfraINT
    {

		public static function verificaRespostaMerito($idRelReitDoc){
			$objRespReitRN  = new MdRiRespostaReiteracaoRN();
    		$objRespReitDTO = new MdRiRespostaReiteracaoDTO();
			$objRespReitDTO->setNumIdReiteracaoDocRI($idRelReitDoc);
			$objRespReitDTO->setStrSinMerito('N');
			$count = $objRespReitRN->contar($objRespReitDTO);
			$xml = '';

			if($count > 0){

				$msg = "Não é possível remover, pois a Solicitação foi respondida (Resposta sem Mérito).\n \nCaso seja de fato necessário remover a Solicitação, antes deve remover as Respostas à Solicitação correspondente na tela de \"Controle de Demandas - Respostas.\"";
				$xml = '<Dados>';
				$xml .= '<RespostaMerito>'. 'S' .'</RespostaMerito>';
				$xml .= '<Msg>'.$msg.'</Msg>';
				$xml .= '</Dados>';

			}else {
				$xml = '<Dados>';
				$xml .= '<RespostaMerito>' .'N'.'</RespostaMerito>';
				$xml .= '</Dados>';
			}

			return $xml;
		}

    }