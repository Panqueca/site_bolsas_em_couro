<?php
    $post_fields = array("titulo", "subtitulo", "status", "descricao_curta", "descricao_longa", "url_video");
    $file_fields = array("imagem", "thumbnail");
    $invalid_fileds = array();
    $gravar = true;
    $i = 0;

    foreach($post_fields as $post_name){
        /*Validação se todos campos foram enviados*/
        if(!isset($_POST[$post_name])){
            $gravar = false;
            $i++;
            $invalid_fileds[$i] = $post_name;
        }
    }
    foreach($file_fields as $file_name){
        /*Validação se todos campos foram enviados*/
        if(!isset($_FILES[$file_name])){
            $gravar = false;
            $i++;
            $invalid_fileds[$i] = $file_name;
        }
    }

    if($gravar){
        require_once "pew-system-config.php";
        require_once "@classe-system-functions.php";
        
        $tabela_dica = $pew_custom_db->tabela_dicas;
        
        $titulo = $_POST["titulo"];
        $subtitulo = $_POST["subtitulo"];
        $descricaoCurta = $_POST["descricao_curta"];
        $descricaoLonga = $_POST["descricao_longa"];
        $imagem = $_FILES["imagem"]["name"];
        $thumb = $_FILES["thumbnail"]["name"];
        $video = $_POST["url_video"];
        
        $refDicas = $pew_functions->url_format($titulo);
        $status = (int)$_POST["status"] == 1 ? 1 : 0;
        
        $nomeImagem = $pew_functions->url_format($titulo);
        $data = date("Y-m-d h:i:s");
        $dirImagens = "../imagens/dicas/";
         
        if($imagem != ""){
            $refImg = substr(md5(uniqid()), 0, 4);
            $ext = pathinfo($imagem, PATHINFO_EXTENSION);
            $imagem = $nomeImagem."-dica-ref$refImg.".$ext;
            move_uploaded_file($_FILES["imagem"]["tmp_name"], $dirImagens.$imagem);
        }
        if($thumb != ""){
            echo "kk";
            $refImg = substr(md5(uniqid()), 0, 4);
            $ext = pathinfo($thumb, PATHINFO_EXTENSION);
            $thumb = $nomeImagem."-dicathumb-ref$refImg.".$ext;
            move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $dirImagens.$thumb);
        }

        mysqli_query($conexao, "insert into $tabela_dica (titulo, subtitulo, ref, descricao_curta, descricao_longa, imagem, thumb, video, data_controle, status) values ('$titulo', '$subtitulo', '$refDicas', '$descricaoCurta', '$descricaoLonga','$nomeImagem', '$thumb', '$video','$data', '$status')");
        
        header("location: pew-dicas.php");
        echo "true";
        print_r($post_fields);
    }else{
        echo "false";
    }
?>