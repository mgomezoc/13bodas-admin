<section class="majestic-registry" id="registry">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Mesa de Regalos</h2>
        <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
            Tu presencia es nuestro mejor regalo, pero si deseas obsequiarnos algo, aquí algunas sugerencias
        </p>
        
        <div class="registry-grid">
            <?php foreach ($event['registry_items'] as $index => $item): ?>
                <div class="registry-card" data-aos="fade-up" data-aos-delay="<?= ($index % 3) * 100 ?>">
                    <?php if (!empty($item['image_url'])): ?>
                        <div class="registry-image">
                            <img src="<?= esc($item['image_url']) ?>" alt="<?= esc($item['title']) ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="registry-content">
                        <h3 class="registry-title"><?= esc($item['title']) ?></h3>
                        
                        <?php if (!empty($item['description'])): ?>
                            <p class="registry-description"><?= esc($item['description']) ?></p>
                        <?php endif; ?>
                        
                        <?php if ($item['item_type'] === 'cash_fund'): ?>
                            <div class="registry-fund">
                                <i class="bi bi-cash-coin"></i>
                                <span>Fondo en Efectivo</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['price'])): ?>
                            <p class="registry-price">$<?= number_format($item['price'], 2) ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['purchase_url'])): ?>
                            <a href="<?= esc($item['purchase_url']) ?>" 
                               target="_blank" 
                               class="registry-button"
                               rel="noopener">
                                <i class="bi bi-gift"></i>
                                Regalar
                            </a>
                        <?php elseif (!empty($item['store_name'])): ?>
                            <p class="registry-store">
                                <i class="bi bi-shop"></i>
                                <?= esc($item['store_name']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($item['is_claimed']): ?>
                            <div class="registry-claimed">
                                <i class="bi bi-check-circle-fill"></i>
                                Ya fue apartado
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
