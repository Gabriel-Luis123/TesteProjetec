<?php
$faqs = [
    [
        'pergunta' => 'Como faço para agendar uma monitoria?',
        'resposta' => 'Para agendar uma monitoria, você deve entrar em contato com um dos
                            monitores da disciplina desejada, verificando a disponibilidade dele e informando a matéria
                            da qual você tem dúvidas.'
    ],
    [
        'pergunta' => 'Quais disciplinas estão disponíveis para monitoria?',
        'resposta' => 'É necessário verificar na página de monitores se há um monitor da
                            disciplina desejada'
    ],
    [
        'pergunta' => 'Qual conteúdo será abordado na monitoria?',
        'resposta' => 'O conteúdo abordado na monitoria aparece no espaço referente àquela
                            monitoria, porém, você pode pedir ao monitor para explicar outras matérias na sala.'
    ],
    [
        'pergunta' => 'Quem são os monitores e como entrar em contato com eles?',
        'resposta' => 'Você pode encontrar os monitores e suas respectivas informações na
                            página “Monitores” do nosso site.'
    ],
    [
        'pergunta' => 'Posso reagendar ou desmarcar uma monitoria?',
        'resposta' => 'Sim, é possível reagendar ou remarcar uma monitoria, basta entrar
                            em contato com o monitor.'
    ],
    [
        'pergunta' => 'Onde acontecem as monitorias?',
        'resposta' => 'As monitorias acontecem em uma sala determinada pelo monitor, essa
                            informação está disponível junto com as outras informações da monitoria desejada, na agenda.'
    ],
    [
        'pergunta' => 'Como sei se minha vaga na monitoria foi confirmada?',
        'resposta' => 'Você confirma a sua presença na monitoria por meio do botão "inscrever-se", após isso sua vaga foi confirmada'
    ],
    [
        'pergunta' => 'Como recuperar sua senha?',
        'resposta' => 'Para recuperar a sua senha, basta clicar no botão “esqueci a minha senha” abaixo do login.'
    ],
    [
        'pergunta' => 'Como entrar em contato com o monitor?',
        'resposta' => 'Para entrar em contato com o monitor, você pode utilizar o chat do nosso site ou então, utilizar o e-mail do monitor, que está disponível na página “Monitores”.'
    ],[
        'pergunta' => 'Como acessar os anexos da monitoria que participei?',
        'resposta' => 'Os anexos da monitoria estão disponíveis junto com as outras informações da monitoria desejada, na agenda.'
    ],[
        'pergunta' => 'Como denunciar alguma mensagem inapropriada que recebi no chat?',
        'resposta' => 'Envie uma mensagem para nosso email presente no footer do site.'
    ]
];

$titlePage = 'FAQs - Sistema de Monitoria';
$nameCSS = 'FAQ';
require_once __DIR__ . '/header.php';

?>
    <header class="cabecalho-1">
        <h1>Perguntas Frequentes</h1>
        <p>Encontre respostas para as dúvidas mais comuns sobre o sistema de monitoria</p>
    </header>

    <main>
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($faqs); ?></div>
                <div class="stat-label">Perguntas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">100%</div>
                <div class="stat-label">Respostas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">24/7</div>
                <div class="stat-label">Disponível</div>
            </div>
        </div>

        <div class="search-section">
            <div class="search-container">
                <span class="search-icon">🔍</span>
                <input 
                    type="text" 
                    class="search-input" 
                    id="searchInput" 
                    placeholder="Digite sua pergunta ou palavra-chave..."
                    aria-label="Buscar FAQs"
                >
            </div>
        </div>

        <div class="faq-container" id="faqContainer">
            <?php foreach ($faqs as $index => $faq): ?>
            <div class="faq-item" data-index="<?php echo $index; ?>">
                <button 
                    class="faq-question" 
                    onclick="toggleFAQ(this)"
                    aria-expanded="false"
                    aria-controls="answer-<?php echo $index; ?>"
                >
                    <div style="display: flex; align-items: center; flex: 1;">
                        <span class="faq-number"><?php echo $index + 1; ?></span>
                        <span><?php echo htmlspecialchars($faq['pergunta']); ?></span>
                    </div>
                    <div class="faq-icon">+</div>
                </button>
                <div 
                    class="faq-answer" 
                    id="answer-<?php echo $index; ?>"
                    role="region"
                >
                    <div class="faq-answer-text">
                        <?php echo htmlspecialchars($faq['resposta']); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="no-results" id="noResults" style="display: none;">
            <div class="no-results-icon">🔎</div>
            <p class="no-results-text">Nenhuma pergunta encontrada com esse termo. Tente uma busca diferente.</p>
        </div>
    </main>

    <script>
        function toggleFAQ(button) {
            const faqItem = button.closest('.faq-item');
            const answer = faqItem.querySelector('.faq-answer');
            const isActive = button.classList.contains('active');

            document.querySelectorAll('.faq-question.active').forEach(item => {
                if (item !== button) {
                    item.classList.remove('active');
                    item.setAttribute('aria-expanded', 'false');
                    item.closest('.faq-item').querySelector('.faq-answer').classList.remove('active');
                }
            });

            button.classList.toggle('active');
            answer.classList.toggle('active');
            button.setAttribute('aria-expanded', !isActive);
        }

        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const faqItems = document.querySelectorAll('.faq-item');
            let visibleCount = 0;

            faqItems.forEach((item, index) => {
                const question = item.querySelector('.faq-question span:last-child').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer-text').textContent.toLowerCase();
                const matches = question.includes(searchTerm) || answer.includes(searchTerm);

                item.style.display = matches ? 'block' : 'none';
                if (matches) visibleCount++;

                if (searchTerm.trim() !== '') {
                    const questionBtn = item.querySelector('.faq-question');
                    questionBtn.classList.remove('active');
                    item.querySelector('.faq-answer').classList.remove('active');
                    questionBtn.setAttribute('aria-expanded', 'false');
                }
            });

            const noResults = document.getElementById('noResults');
            noResults.style.display = visibleCount === 0 && searchTerm.trim() !== '' ? 'block' : 'none';
        });

        document.querySelectorAll('.faq-answer-text').forEach(answer => {
            answer.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>

<?php  

require_once __DIR__ . '/footer.php';

?>
