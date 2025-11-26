<?php
$pageTitle = "Página de Chat";
$nameCSS = "chat";
include_once "header.php";

require_once __DIR__ . '/../src/utils/con_db.php';
?>


<main>  
    <div class="barra-de-pesquisa">
        <input type="text" id="searchInput" placeholder="Pesquisar contatos...">
    </div>

    <div class="lista-nomes">
    </div>

    <div class="fundo">
        <div class="cabecalho-fundo">
            <div class="icone-cabecalho" style="background-image: url('../public/img/fotosPerfil/perfilPadrao.png'); background-size: cover; background-position: center; background-repeat: no-repeat; border-radius: 50%;"></div>
            <div class="nome-titulo-monitor">
                <h2>Selecione um contato</h2>
                <p class="titulo-monitor-cabecalho">Escolha um monitor ou aluno para conversar</p>
            </div>
        </div>

        <div class="mensagens-container">
            <p id="texto">Nenhuma conversa selecionada</p>
        </div>

        <div class="input-container">
            <form class="form-input" id="chatForm">
                <label>
                    <img class="icone-file" width="20px" height="20px" src="../public/img/formsComponents/anexos.png" alt="ícone anexo">
                    <input type="file" name="file" id="fileInput">
                </label>

                <input type="text" id="textInput" name="text" placeholder="Mensagem">

                <button type="submit" class="botao-enviar" id="sendBtn">
                    <img src="../public/img/formsComponents/botao-enviar.png" width="25px" height="25px" alt="Enviar-mensagem">
                </button>
            </form>
        </div>
    </div>
</main>

<script>
    window.CurrentUserId = <?php echo $_SESSION["usuario_id"]; ?>;
</script>

<?php
$scripts = ['chatJS/chat'];
include_once "footer.php";
?>
