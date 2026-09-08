<?php
/**
 * Template Name: Gallery - Executive Tier (4-Column Image Archive)
 * Description: Clean 4-column certified slab archive loading 16 local images from assets/img (gallery-1.jpg to gallery-16.jpg) with an image-only lightbox popup.
 */

get_header(); ?>

<style>
    :root {
        --evg-gold-primary: #D4AF37;
        --evg-gold-light: #F3E5AB;
        --evg-gold-muted: #AA8C2C;
        --evg-gold-glow: rgba(212, 175, 55, 0.12);
        
        --evg-obsidian-base: #050505;
        --evg-obsidian-panel: #0D0D0F;
        --evg-obsidian-elevated: #141416;
        
        --evg-border-hairline: #1F1F23;
        --evg-border-gold-faint: rgba(212, 175, 55, 0.2);

        --evg-text-pure: #FFFFFF;
        --evg-text-ash: #8E8E93;
        --evg-text-charcoal: #030406;
    }

    .evg-master-wrapper {
        background-color: var(--evg-obsidian-base);
        background-image: radial-gradient(circle at 50% 0%, rgba(212, 175, 55, 0.08), transparent 70%);
        background-size: 100% 100%;
        min-height: 100vh;
        font-family: "Montserrat", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        position: relative;
        z-index: 1;
        color: var(--evg-text-pure);
        padding: 4rem 20px 6rem 20px;
    }
    
    .evg-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .evg-title-xl { 
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(2.2rem, 4vw, 3rem); 
        font-weight: 600; 
        letter-spacing: -0.02em; 
        line-height: 1.1; 
        color: #ffffff;
        margin: 0 0 10px 0;
    }
    
    .evg-text-metallic {
        background: linear-gradient(170deg, var(--evg-gold-light) 0%, var(--evg-gold-muted) 100%);
        -webkit-background-clip: text; 
        -webkit-text-fill-color: transparent; 
        background-clip: text;
    }
    
    .evg-label-micro { 
        font-size: 0.68rem; 
        text-transform: uppercase; 
        letter-spacing: 0.2em; 
        font-weight: 700; 
        color: var(--evg-gold-primary); 
        display: block; 
    }

    /* 4-Column Grid Layout */
    .evg-gallery-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-top: 40px;
    }

    @media (max-width: 1024px) {
        .evg-gallery-grid-4 { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .evg-gallery-grid-4 { grid-template-columns: 1fr; }
    }

    .evg-slab-thumb {
        background: var(--evg-obsidian-panel);
        border: 1px solid var(--evg-border-hairline);
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
        aspect-ratio: 3 / 4;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .evg-slab-thumb:hover {
        border-color: var(--evg-gold-primary);
        transform: translateY(-4px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.8), 0 0 15px var(--evg-gold-glow);
    }
    .evg-slab-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Image-Only Lightbox Modal */
    .evg-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(3, 4, 6, 0.92);
        backdrop-filter: blur(10px);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 30px;
    }
    .evg-modal-overlay.active { display: flex; }

    .evg-modal-content-wrap {
        position: relative;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .evg-modal-content-wrap img {
        max-width: 100%;
        max-height: 85vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 30px 80px rgba(0,0,0,0.95), 0 0 35px var(--evg-gold-glow);
        border: 1px solid var(--evg-border-gold-faint);
    }

    .evg-modal-close {
        position: absolute;
        top: -45px;
        right: 0;
        background: var(--evg-obsidian-panel);
        border: 1px solid var(--evg-border-hairline);
        color: var(--evg-text-ash);
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        z-index: 10;
    }
    .evg-modal-close:hover { background: var(--evg-gold-primary); color: var(--evg-text-charcoal); border-color: var(--evg-gold-primary); }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- GALLERY HEADER -->
        <header style="text-align: center; margin-bottom: 20px;">
            <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Vault Archive', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Certified Slab', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Showcase', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 480px; margin: 0 auto; font-size: 0.95rem; line-height: 1.6;">
                <?php esc_html_e( 'A 4-column pristine gallery grid of authenticated masterworks preserved in Elite Vault protective slabs.', 'evg-platform' ); ?>
            </p>
        </header>

        <!-- 4-COLUMN IMAGE GRID (16 Images from assets/img) -->
        <div class="evg-gallery-grid-4">
            
            <?php
            // Loop from 1 to 16 to dynamically load gallery-1.jpg through gallery-16.jpg
            for ( $i = 1; $i <= 16; $i++ ) :
                // Adjust theme directory path if this template is inside your plugin or theme
                $img_url = get_template_directory_uri() . '/assets/img/gallery-' . $i . '.jpg';
            ?>
                <div class="evg-slab-thumb" data-img-src="<?php echo esc_url( $img_url ); ?>" data-title="<?php echo esc_attr( 'Certified Specimen #' . $i ); ?>">
                    <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( 'Elite Vault Certified Slab ' . $i ); ?>" loading="lazy">
                </div>
            <?php endfor; ?>

        </div>

    </div>
</main>

<!-- IMAGE-ONLY LIGHTBOX MODAL -->
<div class="evg-modal-overlay" id="evgImageModal">
    <div class="evg-modal-content-wrap">
        <button type="button" class="evg-modal-close" id="evgModalClose" aria-label="Close modal">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <!-- Lightbox image container -->
        <img id="modalActiveImage" src="" alt="Enlarged Slab View">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalOverlay = document.getElementById('evgImageModal');
    const modalClose = document.getElementById('evgModalClose');
    const thumbs = document.querySelectorAll('.evg-slab-thumb');
    const modalActiveImage = document.getElementById('modalActiveImage');

    thumbs.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const imgSrc = this.getAttribute('data-img-src');
            modalActiveImage.src = imgSrc;
            modalOverlay.classList.add('active');
        });
    });

    function closeModal() {
        modalOverlay.classList.remove('active');
        modalActiveImage.src = ''; // Clear source on close
    }

    modalClose.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
});
</script>

<?php get_footer(); ?>