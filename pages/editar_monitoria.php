<?php
$nameCSS = 'criar_monitoria';
$titlePage = 'Criar Nova Monitoria';

require_once __DIR__ . '/header.php';

require_once __DIR__ . '/../src/controllers/editar_monitorias_dados.php';
?>

<div class="container">
    <div class="header" style="background: <?php echo $cor_disciplina; ?>;">
        <h1>Editar Monitoria</h1>
        <p>Edite os dados da sua monitoria para disponibilizá-la aos alunos</p>
    </div>

    <form class="form-container" method="POST" id="form-monitoria" action="../src/controllers/editar_monitoria_backend.php?id=<?php echo $_GET['id']; ?>">
        <div class="form-grid">
            <div class="form-main">
                <div class="info-box">
                    <p>📚 Preencha todos os campos obrigatórios para criar sua monitoria. Os alunos poderão se inscrever assim que a monitoria for publicada.</p>
                </div>

                <div class="form-group">
                    <label>Matéria <span>*</span></label>
                    <input type="text" disabled name="materia" value="<?php echo $disciplina_base; ?>" placeholder="Ex: Cálculo I, Programação, Física..." required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Data <span>*</span></label>
                        <input type="date" name="data" id="inputData" value="<?php echo $resultado_monitoria_dados['Data']; ?>" required>
                        <span id="iconeStatus" class="status" style="font-size: 20px; margin-left: 6px;"></span>
                        <div class="erro-msg" id="erroData"></div>
                    </div>

                    <div class="form-group">
                        <label>Horário de Início <span>*</span></label>
                        <select name="horario_inicio" id="inputHorario" required>
                            <option value="" selected default>Escolha um horário:</option>
                            <?php foreach ($horarios_monitoria as $horario): ?>
                                <?php if ($horario === $hora_formatada): ?>
                                    <option value="<?php echo $hora_formatada; ?>" selected><?php echo $hora_formatada; ?></option>
                                <?php else: ?>
                                    <option value="<?php echo $horario; ?>"><?php echo $horario; ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <span id="iconeStatus2" class="status2" style="font-size: 20px; margin-left: 6px;"></span>
                        <div class="erro-msg" id="erroHorario"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Localização <span>*</span></label>
                    <select name="sala" id="inputSala" required>
                        <option value="" default selected>Escolha uma localização</option>
                        <?php foreach ($lista_Salas as $sala): ?>
                            <?php if ($sala === $resultado_monitoria_dados['Localizacao']): ?>
                                <option value="<?php echo $sala; ?>" selected>Sala <?php echo $sala; ?></option>
                            <?php else: ?>
                                <option value="<?php echo $sala; ?>">Sala <?php echo $sala; ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <span id="iconeStatus3" class="status3" style="font-size: 20px; margin-left: 6px;"></span>
                    <div class="erro-msg" id="erroSala"></div>
                </div>

                <div class="form-group">
                    <label>Capacidade de Alunos <span>*</span></label>
                    <input type="number" name="capacidade" id="inputCapacidade" min="1" max="40" value="<?php echo $resultado_monitoria_dados['Capacidade_Alunos']; ?>" placeholder="Número máximo de alunos" required>
                    <div class="erro-msg" id="erroCapacidade"></div>
                </div>
            </div>

            <div class="conteudos-section">
                <h3>Conteúdos Abordados</h3>
                <div class="conteudo-input-wrapper">
                    <input type="text" id="conteudo-input" placeholder="Digite um conteúdo e pressione Enter">
                    <button type="button" class="btn-add" onclick="adicionarConteudo()">Adicionar</button>
                </div>

                <div class="conteudos-list" id="conteudos-list">
                    <div class="empty-state">
                        Nenhum conteúdo adicionado ainda
                    </div>
                </div>

                <input type="hidden" name="conteudos" id="conteudos-hidden">
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="window.history.back()">Cancelar</button>
            <button type="submit" name="criar_monitoria" class="btn-submit">Editar Monitoria</button>
        </div>
    </form>
</div>

<script>
// Quando o formulário for enviado
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#form-monitoria');
    const dataInput = document.querySelector('#inputData'); // ID do seu input de data

    form.addEventListener('submit', function(e) {

        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0); // zera horário para comparação correta

        const dataEscolhida = new Date(dataInput.value);

        if (dataEscolhida < hoje) {
            e.preventDefault(); // impede envio
            alert("A data da monitoria não pode ser anterior ao dia atual!");
            dataInput.focus();
        }
    });
});
</script>



<script>
    function adicionarConteudo() {
        const input = document.getElementById('conteudo-input');
        const conteudo = input.value.trim();

        if (conteudo === '') return;

        conteudos.push(conteudo);
        atualizarListaConteudos();
        input.value = '';
        input.focus();
    }

    function removerConteudo(index) {
        conteudos.splice(index, 1);
        atualizarListaConteudos();
    }

    function atualizarListaConteudos() {
        const lista = document.getElementById('conteudos-list');
        const hiddenInput = document.getElementById('conteudos-hidden');

        if (conteudos.length === 0) {
            lista.innerHTML = '<div class="empty-state">Nenhum conteúdo adicionado ainda</div>';
        } else {
            lista.innerHTML = conteudos.map((conteudo, index) => `
                    <div class="conteudo-item">
                        <span>${conteudo}</span>
                        <button type="button" class="btn-remove" onclick="removerConteudo(${index})">Remover</button>
                    </div>
                `).join('');
        }

        hiddenInput.value = JSON.stringify(conteudos);
    }

    document.getElementById('conteudo-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            adicionarConteudo();
        }
    });

    document.getElementById('form-monitoria').addEventListener('submit', function(e) {
        if (conteudos.length === 0) {
            e.preventDefault();
            alert('Por favor, adicione pelo menos um conteúdo para a monitoria.');
            return false;
        }
    });
</script>


<script>
    let conteudos = <?= json_encode($conteudosArray); ?>;

    console.log(conteudos);
    atualizarListaConteudos();
</script>

<script>
    function mostrarErro(id, mensagem) {
        const el = document.getElementById(id);
        el.innerText = mensagem;
        el.style.display = "block";
    }

    function limparErro(id) {
        const el = document.getElementById(id);
        el.innerText = "";
        el.style.display = "none";
    }

    document.getElementById("inputData").addEventListener("change", function() {
        const valor = this.value;

        if (!valor) {
            mostrarErro("erroData", "A data é obrigatória.");
        } else {
            limparErro("erroData");
        }
    });

    document.getElementById("inputHorario").addEventListener("change", function() {
        const valor = this.value;

        if (!valor) {
            mostrarErro("erroHorario", "Selecione um horário válido.");
        } else {
            limparErro("erroHorario");
        }
    });

    document.getElementById("inputSala").addEventListener("change", function() {
        const valor = this.value;

        if (!valor) {
            mostrarErro("erroSala", "Você deve escolher uma sala.");
        } else {
            limparErro("erroSala");
        }
    });

    document.getElementById("inputCapacidade").addEventListener("input", function() {
        const n = Number(this.value);

        if (n < 1 || n > 100 || isNaN(n)) {
            mostrarErro("erroCapacidade", "A capacidade deve ser entre 1 e 100.");
        } else {
            limparErro("erroCapacidade");
        }
    });


    document.getElementById("form-monitoria").addEventListener("submit", function(e) {

        let bloqueia = false;

        if (!inputData.value) {
            mostrarErro("erroData", "A data é obrigatória.");
            bloqueia = true;
        }

        if (!inputHorario.value) {
            mostrarErro("erroHorario", "Selecione um horário válido.");
            bloqueia = true;
        }

        if (!inputSala.value) {
            mostrarErro("erroSala", "Você deve escolher uma sala.");
            bloqueia = true;
        }

        const cap = Number(inputCapacidade.value);
        if (cap < 1 || cap > 100 || isNaN(cap)) {
            mostrarErro("erroCapacidade", "A capacidade deve ser entre 1 e 100.");
            bloqueia = true;
        }

        if (conteudos.length === 0) {
            alert("Por favor, adicione pelo menos um conteúdo.");
            bloqueia = true;
        }

        if (bloqueia) e.preventDefault();
    });
</script>

<script>
    const idMonitoriaAtual = "<?= $_GET['id']; ?>";
    const dataAtual = "<?= $resultado_monitoria_dados['Data']; ?>";
    const horarioAtual = "<?= $hora_formatada; ?>";
    const salaAtual = "<?= $resultado_monitoria_dados['Localizacao']; ?>";
</script>

<?php

$scripts = ["editar_monitoriaJs/editar_verificar"];
require_once __DIR__ . '/footer.php';
?>