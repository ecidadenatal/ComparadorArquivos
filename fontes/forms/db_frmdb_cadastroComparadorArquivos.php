<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2009  DBselller Servicos de Informatica             
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
include("dbforms/db_classesgenericas.php");
$cliframe_alterar_excluir = new cl_iframe_alterar_excluir;
  db_app::load("scripts.js, prototype.js, strings.js, datagrid.widget.js, AjaxRequest.js");
  db_app::load("estilos.css, grid.style.css");
?>
<form name="form1" method="post" action="">
<br>
<br>
<br>
<center>
  <fieldset style="width: 700px">
    <legend><strong>Inserir bloco de código do modification</strong></legend>
      <table border="0">
        <tr>
          <td nowrap title="caminhoArquivo">
            <label for="sCaminhoArquivo">Caminho do arquivo:</label> 
          </td>
          <td colspan="3"> 
      	  <?
            db_input('sCaminhoArquivo',58, 3,true,'text',1,"");
      	  ?>
          </td>
        </tr>
        <tr>
          <td nowrap title="numLinhaArquivo">
            <label for="iLinhaInicio">Nº da Linha:</label>
          </td>
          <td colspan="3"> 
          <?
            db_input('iLinhaInicio',10, 1,true,'text',1,"");
          ?>
          </td>
        </tr>
        <tr>
          <td nowrap title="conteudoArquivo">
            <label for="sConteudo">Conteúdo:</label>
          </td>
          <td colspan="3"> 
      	  <?
            db_textarea('sConteudo',15,56, 5,true,'text',$db_opcao,"");
      	  ?>
          </td>
        </tr>
        </tr>
        <tr>
          <td colspan="4" align="center">
            <input name="<?=($db_opcao==1?"incluir":($db_opcao==2||$db_opcao==22?"alterar":"excluir"))?>" onclick="<?=($db_opcao==1?"js_inserirModification();":($db_opcao==2||$db_opcao==22?"js_alterarModification();":"js_excluirModification();"))?>" type="button" id="db_opcao" value="<?=($db_opcao==1?"Incluir":($db_opcao==2||$db_opcao==22?"Alterar":"Excluir"))?>" <?=($db_botao==false?"disabled":"")?>  >
            
            <input name="novo" type="button" id="cancelar" value="Novo" onclick="js_cancelar();">
          </td>
        </tr>
      </table>
      <!--<table>
        <tr>
          <td valign="top"  align="center">  
          <?/*
          $dbwhere = " db51_layouttxt = ".@$db51_layouttxt;
          if(isset($db51_codigo) && trim($db51_codigo) != ""){
            $dbwhere .= " and db51_codigo <> ".$db51_codigo;
          }
          $chavepri= array("db51_codigo"=>@$db51_codigo);
          $cliframe_alterar_excluir->chavepri=$chavepri;
          $cliframe_alterar_excluir->sql     = $cldb_layoutlinha->sql_query_file(null,
                                                                              "
                                                                               db51_codigo,
                 							         db51_descr,
      																 db51_obs,
                 							         case db51_tipolinha when 1 then '".$x[1]."'
                 							                             when 2 then '".$x[2]."'
                 							                             when 3 then '".$x[3]."'
                 							                             when 4 then '".$x[4]."'
                 							                             when 5 then '".$x[5]."'
                 							         end as db51_tipolinha,
                                                                               db51_tamlinha
                 							        ",
                 							        "db51_tipolinha",
                 							        $dbwhere);
          $val = 1;
          if($db_opcao==3){
            $val = 4;
          }
          $cliframe_alterar_excluir->opcoes = $val;
          $cliframe_alterar_excluir->campos  ="db51_descr,db51_tipolinha,db51_tamlinha,db51_obs";
          $cliframe_alterar_excluir->legenda="ITENS LANÇADOS";
          $cliframe_alterar_excluir->iframe_height ="160";
          $cliframe_alterar_excluir->iframe_width ="700";

          $cliframe_alterar_excluir->iframe_alterar_excluir(1);
          */?>
          </td>
        </tr>
      </table>-->
  </fieldset>
<br>
<input name="comparar" type="button" id="comparar" value="Comparar Arquivos" onclick="js_compararModification();">
</center>
</form>
<script>
function js_cancelar(){
  location.href = 'db_cadastroComparadorArquivos_001.php';
}

function js_inserirModification() {
  
  js_divCarregando("Aguarde, inserindo os dados no XML...", "msgBox");
  
  var sCaminhoArquivo  = document.getElementById("sCaminhoArquivo").value;
  var iLinhaArquivo    = document.getElementById("iLinhaInicio").value;
  var sConteudoArquivo = document.getElementById("sConteudo").value;

  if (sCaminhoArquivo == '' || iLinhaArquivo == '' || sConteudoArquivo == '') {
    alert("Preencha todos os campos");
    return false;
  }

  var oParam               = new Object();
  oParam.exec              = 'escreveXML';
  oParam.caminhoArquivo    = sCaminhoArquivo;
  oParam.linhaArquivo      = iLinhaArquivo;
  oParam.conteudoArquivo   = sConteudoArquivo;

  new Ajax.Request('db_comparadorDeArquivosModification.php',
                  {method: 'post',
                   parameters: 'json='+Object.toJSON(oParam),
                   onComplete: js_retornoModification
                  });

}

function js_retornoModification(oAjax) {
  js_removeObj("msgBox");
  var oRetorno = eval("("+oAjax.responseText+")");
  if (oRetorno.status == 1) {

    alert("Dados inseridos");
    location.href = 'db_cadastroComparadorArquivos_001.php';
    return false;
  
  } else if (oRetorno.status == 3) {
  
    alert("A linha não pôde ser inserida. Verifique o log (tmp/logModification.txt).");
    location.href = 'db_cadastroComparadorArquivos_001.php';
    return false;
  
  } else {

    alert("Houve algum problema");
    return false;
  
  }
}

function js_compararModification() {
  
  js_divCarregando("Aguarde, comparando os dados do XML...", "msgBox");

  var oParam               = new Object();
  oParam.exec              = 'comparaArquivos';

  new Ajax.Request('db_comparadorDeArquivosModification.php',
                  {method: 'post',
                   parameters: 'json='+Object.toJSON(oParam),
                   onComplete: js_retornoComparar
                  });

}

function js_retornoComparar(oAjax) {
  js_removeObj("msgBox");
  var oRetorno = eval("("+oAjax.responseText+")");
  if (oRetorno.status == 1) {

    alert("Dados comparados. Nenhuma diferença encontrada.");
    location.href = 'db_cadastroComparadorArquivos_001.php';
    return false;
  
  } else if (oRetorno.status == 3) {
  
    alert("Dados comparados. Algumas diferenças foram encontradas. Verifique o log (tmp/logComparacaoModification.txt).");
    location.href = 'db_cadastroComparadorArquivos_001.php';
    return false;
  
  } else {

    alert("Houve algum problema. Verifique a existência do arquivo .XML e se ele contém dados.");
    return false;
  
  }
}
  
function js_alterarModification() {
  alert("alterar");
}

function js_excluirModification() {
  alert("excluir");
}

</script>