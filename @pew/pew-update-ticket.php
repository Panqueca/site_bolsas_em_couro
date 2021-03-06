<?php
session_start();

require_once "@valida-sessao.php";
require_once "pew-system-config.php";
$tabela_usuarios = $pew_db->tabela_usuarios_administrativos;

$ticketID = isset($_POST["ticket_id"]) ? addslashes($_POST["ticket_id"]) : null;
$mensagem = isset($_POST["message_body"]) ? addslashes($_POST["message_body"]) : null;
$dataAtual = date("Y-m-d h:i:s");
$dirImagens = "../ticket/ticket_images/";

$queryTotal = mysqli_query($conexao, "select count(id) as total from tickets_register where id = '$ticketID'");
$infoTotal = mysqli_fetch_array($queryTotal);

$nomeAtendente = ucwords($_SESSION["pew_session"]["usuario"]);

if($infoTotal["total"] > 0){

    function get_last_insert_id(){
        global $conexao;

        $qLastID = mysqli_query($conexao, "select last_insert_id()");
        $infoLastID = mysqli_fetch_array($qLastID);
        return $infoLastID["last_insert_id()"];
    }

    $query = mysqli_query($conexao, "select id_cliente, ref from tickets_register where id = '$ticketID'");
    $infoTicket = mysqli_fetch_array($query);

    mysqli_query($conexao, "update tickets_register set status = 2 where id = '$ticketID'");

    mysqli_query($conexao, "insert into tickets_messages (ticket_id, name, message, type, data_controle) values ('$ticketID', '$nomeAtendente', '$mensagem', 1, '$dataAtual')");

    $messageID = get_last_insert_id();

    if(isset($_FILES["imagens"])){
        foreach($_FILES["imagens"]["tmp_name"] as $index => $tmp_name){
            if($tmp_name != ""){
                $file_name = $_FILES["imagens"]["name"][$index];
                $file_tmp = $_FILES["imagens"]["tmp_name"][$index];
                $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $final_name = md5(time().$index).".".$file_ext;
                move_uploaded_file($file_tmp, $dirImagens.$final_name);

                mysqli_query($conexao, "insert into tickets_images (ticket_id, message_id, image) values ('$ticketID', '$messageID', '$final_name')");
            }
        }
    }

    echo "<script>window.location.href = 'pew-edita-ticket.php?id_ticket=$ticketID';</script>";

}else{
    echo "<script>window.location.href = 'pew-tickets.php?msg=Ocorreu um erro ao enviar a resposta';</script>";
}