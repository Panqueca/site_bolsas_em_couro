<?php
    require_once "@classe-carrinho-compras.php";
    $cls_carrinho = new Carrinho();
    $carrinho = $cls_carrinho->get_carrinho();

    /*echo "<pre>";
        print_r($carrinho);
    echo "</pre>";*/

?>

<script>
    $(document).ready(function(){
        
        // PAGINA INTERNA DO PRODUTO
        var botaoComprar = $("#addProdutoCarrinho");
        var adicionandoCarrinho = false;
        if(typeof botaoComprar != "undefined"){
            var idProduto = botaoComprar.attr("carrinho-id-produto");
            botaoComprar.off().on("click", function(){
                var objQuantidade = $(".quantidade-produto");
                var quantidade = 1;
                if(objQuantidade.val() == 0 || objQuantidade.val() == "" && typeof objQuantidade != "undefined"){
                    objQuantidade.val("1");
                    quantidade = 1;
                }else if(typeof objQuantidade == "undefined"){
                    quantidade = 1;
                }else{
                    quantidade = objQuantidade.val();
                }
                if(idProduto != "undefined" && idProduto > 0){
                    adicionandoCarrinho = true;
                    var quantidade = objQuantidade.val();
                    $.ajax({
                        type: "POST",
                        url: "@classe-carrinho-compras.php",
                        data: {acao_carrinho: "adicionar_produto", id_produto: idProduto, quantidade: quantidade},
                        error: function(){
                            notificacaoPadrao("Ocorreu um erro ao adicionar o produto ao carrinho");
                            adicionandoCarrinho = false;
                        },
                        success: function(resposta){
                            console.log(resposta)
                            if(resposta == "true"){
                                notificacaoPadrao("<i class='fas fa-plus'></i> Produto adicionado", "success");
                            }else if(resposta == "sem_estoque"){
                                notificacaoPadrao("<i class='fas fa-exclamation-circle'></i> Produto sem estoque");
                            }else{
                                notificacaoPadrao("Ocorreu um erro ao adicionar o produto ao carrinho");
                            }
                        }
                        
                    });
                }else{
                    notificacaoPadrao("Ocorreu um erro ao adicionar o produto ao carrinho");
                }
            });
        }
        // END PAGINA INTERNA DO PRODUTO
        
        // CARRINHO HEADER
        var headerCart = $(".header-cart");
        var carregandoCarrinho = false;
        var displayCart = $(".cart-display");
        headerCart.off().on("mouseenter", function(){
            displayCart.html("<h4 class='cart-title' style='color: #666;'><i class='fas fa-spinner fa-spin icone-loading'></i> Carregando</h4>");
             if(!carregandoCarrinho){
                 carregandoCarrinho = true;
                 console.log("Atualizando carrinho");
                 $.ajax({
                    type: "POST",
                    url: "@classe-carrinho-compras.php",
                    data: {acao_carrinho: "get_header_carrinho"},
                    error: function(){
                        notificacaoPadrao("Ocorreu um erro ao carregar o carrinho");
                        carregandoCarrinho = false;
                    },
                    success: function(resposta){
                        displayCart.html(resposta);
                        carregandoCarrinho = false;
                    }
                });
             }
        });
        
        setInterval(function(){
            var cartItem = $(".cart-item");
            cartItem.each(function(){
                var item = $(this);
                var botaoRemover = item.children(".remove-button");
                botaoRemover.off().on("click", function(){
                    var idProduto = botaoRemover.attr("carrinho-id-produto");
                    function remover(){
                        var dados = {
                            acao_carrinho: "remover_produto",
                            id_produto: idProduto,
                        }
                        $.ajax({
                            type: "POST",
                            url: "@classe-carrinho-compras.php",
                            data: dados,
                            error: function(){
                                notificacaoPadrao("Ocorreu um erro ao remover o produto");
                                adicionandoCarrinho = false;
                            },
                            success: function(resposta){
                                if(resposta == "true"){
                                    notificacaoPadrao("<i class='fas fa-times'></i> Produto removido", "success");
                                }else{
                                    notificacaoPadrao("Ocorreu um erro ao remover o produto");
                                }
                            }

                        });
                    }

                    mensagemConfirma("Tem certeza que deseja remover este produto?", remover);
                });
            });
        }, 500);
        // END CARRINHO HEADER
    });
</script>