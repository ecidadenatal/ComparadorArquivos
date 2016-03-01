<?

function escreveXML($sPath, $iRow, $sConteudo) {
      //SE O ARQUIVO AINDA NÃO EXISTIR
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
            $oDomXml->preserveWhiteSpace = false; 
            $oDomXml->formatOutput       = true;
            $oDomXml->load('files.xml');
            $oNoModifications   = $oDomXml->getElementsByTagName("modifications");
            $aModifications     = $oDomXml->getElementsByTagName("file");
            //Se já existir modification no arquivo, só acrescenta
            $lPathExist = false;
            foreach ($aModifications as $oModification) {
              
                  $lNoExistente = false;
                  $sPathXML     = $oModification->getAttribute("path");

                  if ($sPathXML == $sPath) {
                        $lPathExist = true;
                        foreach ($oModification->childNodes as $oDadosModification) {
                              foreach ($oDadosModification->childNodes as $modification) {
                                    if ($modification->nodeName == "row" && $modification->nodeValue == $iRow) {
                                      //NESSE MOMENTO SERÁ MESMO PATH E MESMA LINHA, PORTANTO, JÁ EXISTE
                                      $lNoExistente = true;
                                      echo "Já existe uma modificação na linha $iRow do arquivo $sPath. \n";
                                    }       
                              }
                        }   
                        if ($lNoExistente == false) {
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
      }
}

function comparaArquivos() {
      $oDomXml  = new DOMDocument();
      $oDomXml->preserveWhiteSpace = false; 
      $oDomXml->formatOutput       = true;
      $oDomXml->load(utf8_encode('files.xml'));
      $oNoModifications   = $oDomXml->getElementsByTagName("modifications");
      $aModifications     = $oDomXml->getElementsByTagName("file");

      foreach ($aModifications as $oModification) {
            
            $sFilePath = $oModification->getAttribute("path");
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
						                                    $iRowAtual = $iRow+1;
                                                echo "Diferença na linha ".$iRowAtual." do arquivo ".$sFilePath.".\n";
                                          }
                                          $iRow++;
                                    }
                              }
                        }
                  }
            }
      }
}

//TESTES

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
comparaArquivos();
?>
