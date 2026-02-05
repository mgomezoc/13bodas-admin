<section class="majestic-faq" id="faq">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Preguntas Frecuentes</h2>
        <div class="faq-accordion">
            <?php foreach ($event['faqs'] as $index => $faq): ?>
                <div class="faq-item" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <button class="faq-question" onclick="toggleFaq(<?= $index ?>)">
                        <span><?= esc($faq['question']) ?></span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer" id="faq-<?= $index ?>">
                        <?= nl2br(esc($faq['answer'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
function toggleFaq(index) {
    const answer = document.getElementById('faq-' + index);
    const allAnswers = document.querySelectorAll('.faq-answer');
    const allIcons = document.querySelectorAll('.faq-icon');
    
    allAnswers.forEach((item, i) => {
        if (i !== index) {
            item.classList.remove('active');
            allIcons[i].classList.remove('rotate');
        }
    });
    
    answer.classList.toggle('active');
    allIcons[index].classList.toggle('rotate');
}
</script>
