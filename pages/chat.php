<?php
$titlePage = "Página de Chat";
$nameCSS = "chat";
include_once "header.php";

require_once __DIR__ . '/../src/utils/con_db.php';

$sql = "SELECT 
            a.Registro_Academico, 
            a.Nome, 
            a.Foto_Perfil, 
            m.disciplina_monitorada AS disciplina
        FROM Aluno a
        LEFT JOIN Monitora m 
            ON a.Registro_Academico = m.Registro_Academico
        WHERE a.Registro_Academico != :registro";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':registro', $_SESSION['registro']);
$stmt->execute();

$contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<main>  
    <div class="barra-de-pesquisa">
        <input type="text" id="searchInput" placeholder="Pesquisar contatos...">
    </div>

    <div class="lista-nomes">
        <?php foreach ($contatos as $c): ?>
            <div class="caixa-nome" data-id="<?= $c['Registro_Academico'] ?>">
                <img src="../public/img/fotosPerfil/perfilPadrao.png">
                <span><?= $c['Nome'] ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="fundo">
        <div class="cabecalho-fundo">
            <img class="icone-cabecalho" src="../public/img/fotosPerfil/perfilPadrao.png" alt="icone">
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
                    
                <div class="input-wrapper">
                    <div id="filePreview"></div>
                    <input type="text" id="textInput" name="text" placeholder="Mensagem">
                </div>
                <button type="submit" class="botao-enviar" id="sendBtn">
                    <img src="../public/img/formsComponents/botao-enviar.png" width="25px" height="25px" alt="Enviar-mensagem">
                </button>
            </form>
        </div>
    </div>
</main>

<script>
    window.CurrentUserId = <?php echo $_SESSION["registro"]; ?>;
</script>

<?php
$scripts = ['chatJS/chat'];
include_once "footer.php";
?>
