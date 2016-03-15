<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2013  DBselller Servicos de Informatica             
 *                            www.dbseller.com.br                     
 *                         e-cidade@dbseller.com.br                   
 *                                                                    
 *  Este programa e software livre; voce pode redistribui-lo e/ou     
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme  
 *  publicada pela Free Software Foundation; tanto a versao 2 da      
 *  Licenca como (a seu criterio) qualquer versao mais nova.          
 *                                                                    
 *  Este programa e distribuido na expectativa de ser util, mas SEM   
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de              
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM           
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais  
 *  detalhes.                                                         
 *                                                                    
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU     
 *  junto com este programa; se nao, escreva para a Free Software     
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA          
 *  02111-1307, USA.                                                  
 *  
 *  Copia da licenca no diretorio licenca/licenca_en.txt 
 *                                licenca/licenca_pt.txt 
 */
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

require_once ('libs/db_conn.php');
require_once ('libs/db_stdlib.php');
require_once ('libs/db_utils.php');
require_once ("libs/db_app.utils.php");
require_once ('libs/db_conecta.php');
require_once ('libs/JSON.php');
require_once ('libs/db_utils.php');
require_once ('dbforms/db_funcoes.php');

db_app::import("exceptions.*");

$oJson               = new services_json();

$oParam              = $oJson->decode(str_replace("\\","",$_POST["json"]));
$oRetorno            = new stdClass();
$aRetorno            = array();
$oRetorno->status    = 1;
$oRetorno->message   = '';

/**
 * $iRetorno = 0  retornar um objeto.
 * $iRetorno = 1  retorna o array para o widget do autocomplete
 */
$iRetorno            = 0;

switch ($oParam->exec) {

    case "escreveXML":

        $sPath     = $oParam->caminhoArquivo;
        $iRow      = $oParam->linhaArquivo;
        $sConteudo = $oParam->conteudoArquivo;
      	//SE O ARQUIVO AINDA NÃO EXISTIR
        try {

          	if (!file_exists('files.xml')) {
          	//ESCREVE CABEÇALHO
                $oXmlWriter = new XMLWriter();
                $oXmlWriter->openMemory();
                $oXmlWriter->setIndent(true);
                $oXmlWriter->startDocument('1.0', 'ISO-8859-1');//'UTF-8');
                $oXmlWriter->endDtd();
                $oXmlWriter->startElement("modifications");
                      //ESCREVE O ARQUIVO
                      $oXmlWriter->startElement("file");
                      $oXmlWriter->writeAttribute("path", $sPath);
                            //ESCREVE A MODIFICAÇÃO
                            $oXmlWriter->startElement("modification");
                            //ESCREVE A LINHA
                            $oXmlWriter->startElement("row");
                            $oXmlWriter->writeCData($iRow);
                            $oXmlWriter->endElement();
                            //ESCREVE O CONTEUDO
                            $oXmlWriter->startElement("content");
                            $oXmlWriter->writeCData($sConteudo);
                            $oXmlWriter->endElement();
                            //FECHA A MODIFICACAO
                            $oXmlWriter->endElement();
                      //FECHA O ARQUIVO
                      $oXmlWriter->endElement();
                $oXmlWriter->endElement();
                $strBuffer  = $oXmlWriter->outputMemory();
                $rsXMl      = fopen('files.xml', 'w');
                fputs($rsXMl, utf8_decode($strBuffer));
                fclose($rsXMl); 

          	} else {
                $oDomXml  = new DOMDocument();
                $oLog      = fopen('tmp/logModification.txt', "w");
                $oDomXml->preserveWhiteSpace = false; 
                $oDomXml->formatOutput       = true;
                $oDomXml->load('files.xml');
                $oNoModifications   = $oDomXml->getElementsByTagName("modifications");
                $aModifications     = $oDomXml->getElementsByTagName("file");
                //Se já existir modification no arquivo, só acrescenta
                $lPathExist = false;
                foreach ($aModifications as $oModification) {
                  
                      	$lNodeExist   = false;
                      	$sPathXML     = $oModification->getAttribute("path");
                      	if ($sPathXML == $sPath) {
                            $lPathExist = true;
                            foreach ($oModification->childNodes as $oDadosModification) {
                                  foreach ($oDadosModification->childNodes as $modification) {
                                        if ($modification->nodeName == "row" && $modification->nodeValue == $iRow) {
                                          //NESSE MOMENTO SERÁ MESMO PATH E MESMA LINHA, PORTANTO, JÁ EXISTE
                                          	$lNodeExist = true;
                                            $data  = date('d/m/Y h:i:s a', time());
                                          	$sLog = "[$data] - Já existe uma modificação na linha $iRow do arquivo $sPath. \n";
                                            fwrite($oLog, $sLog);
                                            $oRetorno->status = 3;
                                        }       
                                  }
                            }   
                            if ($lNodeExist == false) {
                                $oNewModification = $oDomXml->createElement("modification");
                        
                                $oRow = $oDomXml->createElement("row");
                                $oRow->appendChild($oDomXml->createCDATASection(utf8_encode($iRow)));
                                $oNewModification->appendChild($oRow);
                                  
                                $oContent = $oDomXml->createElement("content");
                                $oContent->appendChild($oDomXml->createCDATASection(utf8_encode($sConteudo)));
                                $oNewModification->appendChild($oContent);
                                $oModification->appendChild($oNewModification);
                                  
                                $oDomXml->save('files.xml');
                            }
                      	}
                }
                if ($lPathExist == false) {
                    $oNewFile = $oDomXml->createElement("file");
                    $oFileAttribute = $oDomXml->createAttribute("path");
                    $oFileAttribute->value = $sPath;
                    $oNewFile->appendChild($oFileAttribute);
                    $oNewModification = $oDomXml->createElement("modification");
                    
                    $oRow = $oDomXml->createElement("row");
                    $oRow->appendChild($oDomXml->createCDATASection($iRow));
                    $oNewModification->appendChild($oRow);
                    
                    $oContent = $oDomXml->createElement("content");
                    $oContent->appendChild($oDomXml->createCDATASection($sConteudo));
                    $oNewModification->appendChild($oContent);
                    
                    $oNewFile->appendChild($oNewModification);
                    
                    $oNoModifications->item(0)->appendChild($oNewFile);
                    $oDomXml->save(utf8_decode('files.xml'));                  
                }

                fclose($oLog);
          	}

        } catch (Exception $eErro) {
          $oRetorno->message = $eErro->getMessage();
          $oRetorno->status = 2;
        }

    break;

    case "comparaArquivos":

        try {

            $oDomXml  = new DOMDocument();
            $oDomXml->preserveWhiteSpace = false; 
            $oDomXml->formatOutput       = true;
            $oDomXml->load(utf8_encode('files.xml'));
            $oNoModifications   = $oDomXml->getElementsByTagName("modifications");
            $aModifications     = $oDomXml->getElementsByTagName("file");

            foreach ($aModifications as $oModification) {
                    
                $sFilePath = $oModification->getAttribute("path");
                $oLog      = fopen('tmp/logComparacaoModification.txt', "w");

                if (file_exists($sFilePath)) {

                    foreach($oModification->childNodes as $oDadosModification) {
                          
                        foreach ($oDadosModification->childNodes as $modification) {
                              
                            if ($modification->nodeName == "row") {
                                //Como o primeiro elemento de uma array é o 0, a linha inicial será sempre 1 anterior à cadastrada
                                $iRow = $modification->nodeValue-1;
                            }
                            $aFile = file($sFilePath);
                            if ($modification->nodeName == "content") {
                                $aContent = explode("\n", $modification->nodeValue);
                                for ($i=0; $i < sizeof($aContent); $i++) { 
                                    if (strcmp(trim($aContent[$i]), trim($aFile[$iRow])) != 0) {
                                       $data  = date('d/m/Y h:i:s a', time());
                                       $iRowAtual = $iRow+1;
                                       $sLog  = "[$data] - Diferença na linha ".$iRowAtual." do arquivo ".$sFilePath.".\n";
                                       fwrite($oLog, $sLog);
                                       $oRetorno->status = 3;
                                    }
                                    $iRow++;
                                }
                            }
                        }
                    }
                } else {

                    $data  = date('d/m/Y h:i:s a', time());
                    $oRetorno->status = 3;
                    $sErro = "[$data] - Arquivo $sFilePath não encontrado.";
                    fwrite($oLog, $sErro);
                }

                fclose($oLog);
            }

        } catch (Exception $eErro) {
          $oRetorno->message = $eErro->getMessage();
          $oRetorno->status = 2;
        }

    break;
}

$oRetorno->message = urlencode($oRetorno->message);
if ($iRetorno == 1) {
  echo($oJson->encode($aRetorno));
} else {
  echo($oJson->encode($oRetorno));
}

//TESTES
/*
escreveXML("forms/db_frmliquidasemordem.php", 150, "              <tr>
                <td>
                  <label class=\"bold\" for=\"e03_numeroprocesso\">Processo Administrativo:</label>
                </td>
                <td colspan=\"3\">
                  <?php db_input('e03_numeroprocesso', 13, '', true, 'text', $db_opcao, null, null, null, null, 15); ?>
                </td>
              </tr>
              <!--[PLUGIN] CONTRATO PADRS -->

              <!--[Extensao OrdenadorDespesa] inclusao_ordenador-->

            </table>");
escreveXML("forms/db_frmliquidasemordem.php", 164, "            <fieldset class=\"separator\">
              <legend>Nota</legend>

              <table width=\"60%\">
                <!--[Extensao ContratosPADRS] serie nota -->
                <tr>
                  <td nowrap>
                    <label class=\"bold\" for=\"e69_numnota\">Número da Nota:</label>
                  </td>");
//escreveXML("", -, "");
//escreveXML("", -, "");
//escreveXML("", -, "");
//escreveXML("", -, "");
comparaArquivos();*/
?>
