<?php
    $jsonData = json_decode(file_get_contents('php://input'), true);
    if($jsonData != null){
        $codigoCorreios = isset($jsonData["codigo_correios"]) ? $jsonData["codigo_correios"] : null;
        $cepDestino = isset($jsonData["cep_destino"]) ? $jsonData["cep_destino"] : null;
        $produtos = isset($jsonData["produtos"]) ? $jsonData["produtos"] : null;
        $declararValor = isset($jsonData["declarar_valor"]) ? $jsonData["declarar_valor"] : null;
        
        
        if($cepDestino != null && $produtos != null){
            $_POST["cep_destino"] = $cepDestino;
            $_POST["produtos"] = $produtos;
        }
        
        if($codigoCorreios != null) $_POST["codigo_correios"] = $codigoCorreios;
        if($declararValor != null) $_POST["declarar_valor"] = $declararValor;
        
    }

    $post_fields = array("cep_destino", "produtos");
    $invalid_fileds = array();
    $calcular = true;
    $i = 0;
    foreach($post_fields as $post_name){
        if(!isset($_POST[$post_name])){
            $calcular = false;
            $i++;
            $invalid_fileds[$i] = $post_name;
        }
    }

    if($calcular){
        require_once "@classe-paginas.php";
        require_once "calcular-frete.php";
        
        if(!function_exists("is_json")){
            function is_json($string){
                json_decode($string);
                return (json_last_error() == JSON_ERROR_NONE);
            }
        }
        
        
        $cls_paginas = new Paginas();
        
        $baseSite = "https://".$cls_paginas->base_path;
        $pastaCorreios = "frete-correios/ws-correios.php";

        $codigoCorreios = isset($_POST["codigo_correios"]) ? $_POST["codigo_correios"] : "41106";
        
        $cepDestino = str_replace("-", "", $_POST["cep_destino"]);
        
        $declararValor = isset($_POST["declarar_valor"]) ? $_POST["declarar_valor"] : false;

        if(is_json($_POST["produtos"]) && isset($_POST["no_console"])){
            $_POST["produtos"] = json_decode($_POST["produtos"], true);
            $arrayProdutos = array();
            $ctrlProdutos = 0;
            foreach($_POST["produtos"]["itens"] as $info){
                $arrayProdutos[$ctrlProdutos] = $info;
                $ctrlProdutos++;
            }
            $_POST["produtos"] = $arrayProdutos;
        }
        
        $produtos = is_array($_POST["produtos"]) ? $_POST["produtos"] : array();
        
        
        /*
        $produtos[0] = array();
        $produtos[0]["id"] = "#idProduto";
        $produtos[0]["titulo"] = "Titulo produto";
        $produtos[0]["preco"] = "35.00";
        $produtos[0]["comprimento"] = "12";
        $produtos[0]["largura"] = "40";
        $produtos[0]["altura"] = "35";
        $produtos[0]["peso"] = ".3";
        */
        
        $url_correios_api = $baseSite."/".$pastaCorreios;
        
        $infoFrete = frete($produtos, $codigoCorreios, $cepDestino, $declararValor, $url_correios_api);
        if($infoFrete != false && $produtos != false){
            //echo $infoFrete; // RETORNO EM JSON
        }else{
            echo "false";
        }
        
    }else{
        //print_r($invalid_fileds);
        echo "false";
    }
