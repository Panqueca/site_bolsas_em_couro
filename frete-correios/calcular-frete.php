<?php

require_once "classe-sistema-empacotamento.php";
$functions = new Empacotamento();

function frete($produtos = null, $codigo_correios = "41106", $cep_destino = null, $declarar_valor = false, $url_api = null){
    require_once "classe-sistema-empacotamento.php";
    require_once "calculo-caixas.php";
    
    // EMPACOTAR PRODUTOS
    $empacotamento = new Empacotamento();
    foreach($produtos as $infoProduto){
        $id =  $infoProduto["id"];
        $titulo =  $infoProduto["titulo"];
        $preco =  $infoProduto["preco"];
        $comprimento =  $infoProduto["comprimento"];
        $largura =  $infoProduto["largura"];
        $altura =  $infoProduto["altura"];
        $peso =  $infoProduto["peso"];
        $quantidade = isset($infoProduto["quantidade"]) && $infoProduto["quantidade"] > 0 ? $infoProduto["quantidade"] : 1;
        
        $empacotamento->add_produto($id, $titulo, $preco, $comprimento, $largura, $altura, $peso, $quantidade);
    }


    $carrinho = $empacotamento->configurar();
    
    $caixas = calcular_caixas($carrinho);
    
    if(!function_exists("is_json")){
        function is_json($string){
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }
    }

    function calcular_frete($servicoCorreios, $cepDestino, $declararValor, $url_api_transportadora){
        global $caixas, $functions;
        $frete_caixas = array();
        $ctrlFrete = 0;
        foreach($caixas as $infoCaixa){
            if($infoCaixa != false){
                $alturaCaixa = $infoCaixa->altura;
                $larguraCaixa = $infoCaixa->largura;
                $comprimentoCaixa = $infoCaixa->comprimento;
                $quantidadeItens = $infoCaixa->qtd_itens;
                $pesoCaixa = $infoCaixa->peso;
                $volumeCaixa = $infoCaixa->volume;
                $volumeItens = $infoCaixa->volume_itens;
                $valorItens = $infoCaixa->valor_mercadoria;
                $valorMercadoria = $declararValor == true ? $valorItens : 0;

                $dadosFrete = [
                    'cep_destino' => $cepDestino,
                    'codigo_servico' => $servicoCorreios,
                    'comprimento' => $comprimentoCaixa,
                    'largura' => $larguraCaixa,
                    'altura' => $alturaCaixa,
                    'peso' => $pesoCaixa,
                    'valor_mercadoria' => $valorMercadoria,
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url_api_transportadora);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $dadosFrete);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($ch);
                
                if($response != "false" && is_json($response)){
                    
                    $json_response = json_decode($response);
                    
                    $split_response = explode(",", $response);
                    $frete_caixas[$ctrlFrete] = array();
                    
                    $frete_caixas[$ctrlFrete]["Valor"] = $json_response->Valor;
                    $frete_caixas[$ctrlFrete]["PrazoEntrega"] = $json_response->PrazoEntrega;

                    $ctrlFrete++;
                }else{
                    return false;
                }

                curl_close($ch);
            }
        }

        $freteFinal = 0;
        $prazoFinal = 0;
        if(count($frete_caixas) > 0){
            foreach($frete_caixas as $arrayCaixa){
                $valorFrete = $arrayCaixa["Valor"];
                $prazoFinal = $arrayCaixa["PrazoEntrega"] > $prazoFinal ? $arrayCaixa["PrazoEntrega"] : $prazoFinal;
                //print_r($arrayCaixa);
                $freteFinal += $valorFrete;
            }
        }
        
        
        $textPrazo = $prazoFinal == 1 ? "dia" : "dias";
        
        $finalReturn = '{"valor": '.$freteFinal.', "prazo": "'.$prazoFinal.' '.$textPrazo.'"}';

        return $finalReturn;
    }

    $frete = calcular_frete($codigo_correios, $cep_destino, $declarar_valor, $url_api);
    return $frete;
}