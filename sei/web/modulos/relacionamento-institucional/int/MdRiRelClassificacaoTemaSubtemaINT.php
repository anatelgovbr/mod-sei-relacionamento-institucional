<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelClassificacaoTemaSubtemaINT extends InfraINT
    {

        public static function validarExclusaoRelClassSubt($idClassTema, $idSubtema)
        {
            $objRelCadClassTemaDTO = new MdRiRelCadastroClassificacaoTemaDTO();
            $objRelCadClassTemaRN  = new MdRiRelCadastroClassificacaoTemaRN();
            $objRelCadClassTemaDTO->setNumIdSubtema($idSubtema);
            $objRelCadClassTemaDTO->setNumIdClassificacaoTema($idClassTema);
            $count = $objRelCadClassTemaRN->contar($objRelCadClassTemaDTO);

            $xml = '<Dados>';
            if($count > 0)
            {
                $xml .= '<Msg>';
                $xml .= 'A exclus�o do Tema n�o � permitida pois, j� existem processos de relacionamento institucional vinculados.';
                $xml .= '</Msg>';
            }

            $xml.= '</Dados>';

            return $xml;
        }

       

    }