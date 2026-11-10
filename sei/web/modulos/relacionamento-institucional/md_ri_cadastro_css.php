.color-input {
  background-color: transparent;
}

.color-input:hover {
  background-color: #b0b0b0;
}

#divNumeroOrgaoDemandante {
  padding-top: 20px
}

#divTipoProcessoOrgaoDemandante {
  padding-top: 20px
}

#divTableOrgaoDemandante {
  padding-top: 10px
}

.row div .mb-3 {
  margin: 0px !important;
  padding: 0px !important
}

.infraFieldset {
  height: 100%
}

#divTxtCompEstado {
  margin-top:15px
}

.infraImgModulo {
  width: 20px;
  height:20px;
  margin-top:5px
}

#divSelMunicipio{
  padding-top:9px
}

.divIcones div {
  padding-left:5px;
}
.row div {
  padding-top: 2px
}

#txtNumero {
  width: 250px;
}

#btnValidar, #btnAdicionarOrgaoDemandante, #btnAdicionarLocal, #btnAdicionarTipoControle {
 margin-left:5px;
}

#txtNumeroSei, #txtTipo, #txtNumeroOrgaoDemandante, #selTipoProcessoOrgaoDemandante {
width: 260px;
}

.selInfoDemanda {
 width: calc(100% - 30px);
}

/* Localidade (UF/Cidade/Local) na mesma linha */
.md-ri-localidade-campos{
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: flex-end;
}

.md-ri-localidade-campos .md-ri-linha-camp {
  display: block;
}

.md-ri-localidade-input {
  width: 100%;
}

.md-ri-localidade-input select,
.md-ri-localidade-input input {
  width: 100%;
}

.md-ri-localidade-uf {
  flex: 0 0 72px;
}

.md-ri-localidade-cidade {
  flex: 1 1 260px;
  min-width: 220px;
}

.md-ri-localidade-local {
  flex: 1 1 260px;
  min-width: 220px;
}

.md-ri-localidade-acoes {
  display: flex;
  align-items: flex-end;
}

.md-ri-localidade-acoes #btnAdicionarLocal {
  margin-top: 0;
}

#divTipoControle {
 margin-left:45px;
}


.md-ri-aviso-gp{
  margin-top: 10px;
  padding: 10px 12px;
  border: 1px solid #d4b106;
  background: #fffbe6;
  color: #614700;
  border-radius: 4px;
}

/* Modal do Controle sobre a Demanda usa infraSparklingModal */

/* Fieldset filho nao deve herdar height:100% */
#fldInformacaoDemanda #fldLocalidade {
	height: auto !important;
	min-height: 0 !important;
	width: 100%;
}


.md-ri-tcd-nome {
  display: inline-block;
  padding: 10px 14px;
  border: 2px solid #3b82c4;
  background: #e9f2ff;
  border-radius: 4px;
  font-weight: bold;
  font-size: 20px;
  line-height: 1.2;
  letter-spacing: 0.2px;
  color: #0b2f5b;
  box-shadow: 0 1px 0 rgba(0,0,0,0.06);
}

.md-ri-overlay-aguarde {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255,255,255,0.72);
  z-index: 9999;
}

.md-ri-overlay-aguarde .md-ri-overlay-aguarde-texto {
  position: absolute;
  top: 40%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-weight: bold;
  padding: 10px 14px;
  background: rgba(0,0,0,0.65);
  color: #ffffff;
  border-radius: 4px;
}

/* InfoAdd - Documento SEI (Validação inline) */
.md-ri-docsei-msg {
  margin-top: 4px;
  font-size: 12px;
}

.md-ri-docsei-msg--erro{
  color: #b02a37;
}

.md-ri-docsei-msg--aguarde{
  color: #6c757d;
}
