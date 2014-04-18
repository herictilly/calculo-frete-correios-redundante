<? //
////header("Content-Type: application/xml; charset=ISO-8859-1");   
//
//
////Código
////Serviço
////41106
////PAC sem contrato
////40010
////SEDEX sem contrato
////40045
////SEDEX a Cobrar, sem contrato
////40215
////SEDEX 10, sem contrato
////40290
////SEDEX Hoje, sem contrato
////40096
////SEDEX com contrato
////40436
////SEDEX com contrato
////40444
////SEDEX com contrato
////81019
////e-SEDEX, com contrato
////41068
//
//
//
//
//
//
$sCepOrigem = 'SEU_CEP';

$sCepDestino= $_GET['cep'];
$vlCompra = $_GET['valor'];

if($sCepDestino==''){
	$sCepDestino=$_SESSION['cep'];
}

	$sCepDestino = str_replace('-','',$sCepDestino);
	if($_GET['peso']){
		$nVlPeso=$_GET['peso']/1000;
	}else{
		$nVlPeso = $pesoTotal/1000;
	}

	$cep = trim($sCepDestino);
	$avaliaCep = ereg("^[0-9]{8}$", $cep);
		if($avaliaCep != true){
			if($cep){
			echo "Erro: Cep inválido ex: \"nnnnn-nnn\" ";
			}
		}else{	
	
	
		$url="http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?";

		$url = $url . "sCepOrigem=SEU_CEP&sCepDestino=".$sCepDestino."&nVlPeso=".$nVlPeso."&nCdFormato=1&nVlComprimento=20&nVlAltura=20&nVlLargura=20&sCdMaoPropria=n&nVlValorDeclarado=0&sCdAvisoRecebimento=n&nCdServico=40010,81019,41068,40096&nVlDiametro=0&StrRetorno=xml&nCdEmpresa=XXXX&sDsSenha=YYYYY";


error_log($url,0);
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $url);
								curl_setopt($ch, CURLOPT_VERBOSE, 1);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));						
								curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
								curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
								curl_setopt($ch, CURLOPT_POST, 0);
							
								// Go for it!!!
								$result = curl_exec($ch);
								// Look at the returned header
								$resultArray = curl_getinfo($ch);
						
								// close curl
								curl_close($ch);
	
								if($resultArray['http_code'] == "200"){
									//echo "<br />OK! <br />";
								} else {
									//echo "eek! yegads! error" . $resultArray;
									$erro='s';
								}
	
	
			$xml = @simplexml_load_string($result);
			if($xml){
				foreach($xml->cServico as $tfrete){
					
					if($tfrete->Codigo==41068){
						$valordofretePAC = $tfrete->Valor;
						$prazodofretePAC = $tfrete->PrazoEntrega;
					}
					if($tfrete->Codigo==40096){
						$valordofreteSEDEX = $tfrete->Valor;
						$prazodofreteSEDEX = $tfrete->PrazoEntrega;
					}
					if($tfrete->Codigo==81019){
						$valordofreteESEDEX = $tfrete->Valor;
						$prazodofreteESEDEX = $tfrete->PrazoEntrega;
					}
					
					
					$erro = $tfrete->MsgErro;
				}
			}
	
	//Se falhar os correios usar pagSeguro	
	if($erro!=''){  
			$nVlPeso=str_replace('.',',',$nVlPeso);
			$url = "https://pagseguro.uol.com.br/desenvolvedor/simulador_de_frete_calcular.jhtml?postalCodeFrom={$sCepOrigem}&weight={$nVlPeso}&value={$Valor}&postalCodeTo={$sCepDestino}";
	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));						
			curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_POST, 0);
		
			// Go for it!!!
			$result = curl_exec($ch);
			// Look at the returned header
			$resultArray = curl_getinfo($ch);

			curl_close($ch);	

			
			if($resultArray['http_code'] == "200"){
				//echo "<br />OK! <br />";
			}else {
				//echo "eek! yegads! error" . $resultArray;
				
				//manda o frete padrao
				$valordofretePAC = 15.0;
				$prazodofretePAC = 10;
				
				$valordofreteSEDEX = 25.0;
				$prazodofreteSEDEX = 3;
			}
		//echo $url;
			// close curl
					
			
			$resultF = explode('|',$result);
	//        $valores=array();
			if($resultF[0]=='ok'){
	//         // $valores['Sedex']=$result[3];
	//         // $valores['PAC']=$result[4];
			 $valordofretePAC = $resultF[4];
			 $valordofreteSEDEX = $resultF[3];
			 $valordofreteESEDEX='';
			 $erro='';
			 $prazodofreteESEDEX=2;
			 $prazodofreteSEDEX=3;
			 $prazodofretePAC=8;
			}else{
			  $erro= $resultF[1] . ' - ' .  $nVlPeso;
			  
			  
			  $valordofretePAC = '15.00';
				$prazodofretePAC = 7;
				
				$valordofreteSEDEX = '30.00';
				$prazodofreteSEDEX = 3;
			}        
	
	}

$avisoFretegratis=''; 
$fg=false;
if($vlCompra>150){
	$avisoFretegratis='<strong>Frete Grátis!</strong> Envio por ';
	$valordofretePAC = '0.00';
	$fg=true;
	
}	
					
	if($_GET['ret']){
				if($erro==''){
					if(isset($valordofretePAC)){?>
					<strong>PAC:</strong> R$<?=$valordofretePAC?> <br />Tempo de chegada ao destino após envio: <?=$prazodofretePAC?> dia(s)<br /><br />
					<strong>Sedex:</strong> R$<?=$valordofreteSEDEX?> <br />Tempo de chegada ao destino após envio: <?=$prazodofreteSEDEX?> dia(s)<br /><br />				
			<?
					if($valordofreteESEDEX>0){?><strong>e-Sedex:</strong> R$<?=$valordofreteESEDEX?> <br />Tempo de chegada ao destino após envio: <?=$prazodofreteESEDEX?> dia(s) <br /><br />				<? }
					}else{
					?><small>Erro 1 no c&aacute;lculo. Por favor revise o CEP informado.</small><?	
					}
					
					if($valordofreteSEDEX10>0){		
						//echo '<br />' . $valordofreteSEDEX10 . '<br />' . $prazodofreteSEDEX10; 
						?><strong>Expressa Urgente:</strong> R$<?=number_format(($valordofreteSEDEX + 40),2, ',', '');?> <br />Será confeccionado em até 1 dia útil após o pagamento e o tempo de chegada ao destino após envio: <?=$prazodofreteSEDEX10?> dia(s)<br /><br /><?
					}
				}else{
					//echo 'Erro: '.$erro;
					?><strong>PAC:</strong> R$<?=$valordofretePAC?> <br />Tempo de chegada ao destino após envio: <?=$prazodofretePAC?> dia(s)<br /><br />
					<strong>Sedex:</strong> R$<?=$valordofreteSEDEX?> <br />Tempo de chegada ao destino após envio: <?=$prazodofreteSEDEX?> dia(s)<br /><br /><?
				}
	}
	$checkDef = 'checked="checked"';
	if($fg){
		$checkDef='';
	}
		
	if($_GET['check']){
				if($erro==''){
					if(isset($valordofretePAC)){
						?>
						<input name="tipo_frete" type="radio" value="EN" id="pac" onclick="atualizaFrete('Pac')" <? if($fg){ ?>checked="checked"<? } ?>/>
						<label for="pac"><?=$avisoFretegratis?>Normal - <strong>R$ <span id="valorPac"><?=$valordofretePAC?></span></strong> # <?=$prazodofretePAC?> dia(s) p/ entrega.</label><br /><br />
						
						<?
						if($valordofreteESEDEX>0){
						?><input name="tipo_frete" type="radio" id="esedex" value="SD" <? if(!$fg){ ?>checked="checked" <? } ?>onclick="atualizaFrete('eSedex')" />
						<label  for="esedex">e-Sedex - <strong>R$ <span id="valoreSedex"><?=$valordofreteESEDEX?></span></strong> <?=$prazodofreteESEDEX?> dia(s) p/ entrega.</label>
						<br /><br /><?					
						$checkDef='';
						}
						

					?>										
						<input name="tipo_frete" type="radio" id="sedex" value="SD" <?=$checkDef?> onclick="atualizaFrete('Sedex')" />
						<label  for="sedex">Sedex - <strong>R$ <span id="valorSedex"><?=$valordofreteSEDEX?></span></strong> <?=$prazodofreteSEDEX?> dia(s) p/ entrega.</label>
						
					<?
					if($valordofreteSEDEX10>0){
					?><br /><br /><br /><input name="tipo_frete" type="radio" id="expressa" value="SD" onclick="atualizaFrete('expressa')" />
					<label  for="expressa" style="font-weight:bold">Expressa Urgente - <strong>R$ <span id="valorExpressa"><?=number_format(($valordofreteSEDEX + 40),2, ',', '');?></span></strong> Fabricado em até 1 dia útil após a confirmação do pagamento e enviado em <?=$prazodofreteSEDEX?> dia(s) p/ entrega.</label>
					<br /><br /><?					
					
					}
					
					
					?>
						<p style="color:#900; font-weight:bold">Atenção: Prazo de entrega aproximado após a criação (até 3 dias úteis)  e postagem dos produtos. Se houver dúvidas não deixe de entrar em contato.</p><?
					}else{
						?><small>Erro 2 no c&aacute;lculo. Por favor revise o CEP informado.</small><?	
					}
				}else{
					//echo 'Erro: '.$erro;
					//Erro - valor médio
					//
					    error_log("Frete Falhou e Generico assume", 0);

					?><br />
						<input name="tipo_frete" type="radio" value="EN" id="pac" onclick="atualizaFrete('Pac')" <? if($fg){ ?>checked="checked"<? } ?>/>
						<label for="pac" style="text-align:right"><?=$avisoFretegratis?>Encomenda Normal <br /> <strong>R$ <span id="valorPac"><?=$valordofretePAC?></span></strong> # <?=$prazodofretePAC?> dia(s) p/ entrega.</label><br /><br />
						
						<?
						
						?><input name="tipo_frete" type="radio" id="sedex" value="SD" <? if(!$fg){ ?>checked="checked" <? } ?>onclick="atualizaFrete('Sedex')" />
						<label  for="sedex">Sedex - <strong>R$ <span id="valorSedex"><?=$valordofreteSEDEX?></span></strong> <?=$prazodofreteSEDEX?> dia(s) p/ entrega.</label>
						<br /><br /><?
				}
	}
}?>
