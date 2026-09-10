<?php
/**
 * Template Name: Gallery - Executive Tier (Paged Archive)
 * Description: Clean 4-column certified slab archive using specific filenames from assets/img, loading 12 images per click.
 *
 * @package EliteVaultGrading
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Exact list of gallery images from your folder (excluding logo.png)
$gallery_images = array(
    'IMG_0968.jpg', 'IMG_0971.jpg', 'IMG_0973.jpg', 'IMG_0974.jpg', 
    'IMG_0975.jpg', 'IMG_0978.jpg', 'IMG_0980.jpg', 'IMG_0982.jpg', 
    'IMG_0988.jpg', 'IMG_0989.jpg', 'IMG_0990.jpg', 'IMG_0993.jpg', 
    'IMG_0995.jpg', 'IMG_0999.jpg', 'IMG_1001.jpg', 'IMG_1004.jpg', 
    'IMG_1007.jpg', 'IMG_1009.jpg', 'IMG_1011.jpg', 'IMG_1014.jpg', 
    'IMG_1017.jpg', 'IMG_1018.jpg', 'IMG_1019.jpg', 'IMG_1024.jpg', 
    'IMG_1026.jpg', 'IMG_1028.jpg', 'IMG_1029.jpg', 'IMG_1030.jpg', 
    'IMG_1031.jpg', 'IMG_1032.jpg', 'IMG_1033.jpg', 'IMG_1034.jpg', 
    'IMG_1035.jpg', 'IMG_1036.jpg', 'IMG_1037.jpg', 'IMG_1038.jpg', 
    'IMG_1039.jpg', 'IMG_1040.jpg', 'IMG_1041.jpg', 'IMG_1042.jpg', 
    'IMG_1049.jpg', 'IMG_1050.jpg', 'IMG_1051.jpg', 'IMG_1052.jpg', 
    'IMG_1053.jpg', 'IMG_1055.jpg'
);

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
        padding: 3rem 15px 5rem 15px;
        overflow-x: hidden;
    }
    
    .evg-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .evg-title-xl { 
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(2rem, 4vw, 3rem); 
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
        gap: 20px;
        margin-top: 30px;
    }

    @media (max-width: 1024px) {
        .evg-gallery-grid-4 { grid-template-columns: repeat(2, 1fr); gap: 16px; }
    }
    @media (max-width: 575.98px) {
        .evg-gallery-grid-4 { grid-template-columns: 1fr; gap: 14px; }
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
        padding: 0;
        outline: none;
    }
    .evg-slab-thumb:hover,
    .evg-slab-thumb:focus-visible {
        border-color: var(--evg-gold-primary);
        transform: translateY(-4px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.8), 0 0 15px var(--evg-gold-glow);
    }
    .evg-slab-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .evg-slab-thumb.hidden-item {
        display: none;
    }

    /* Load More Button Wrapper */
    .evg-load-more-wrap {
        text-align: center;
        margin-top: 40px;
    }
    .evg-btn-load-more {
        background: var(--evg-obsidian-panel);
        border: 1px solid var(--evg-border-gold-faint);
        color: var(--evg-gold-light);
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        padding: 1rem 2rem;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        max-width: 320px;
    }
    .evg-btn-load-more:hover {
        background: var(--evg-gold-primary);
        color: var(--evg-text-charcoal);
        box-shadow: 0 0 20px rgba(212, 175, 55, 0.25);
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
        padding: 15px;
        box-sizing: border-box;
    }
    .evg-modal-overlay.active { display: flex; }

    .evg-modal-content-wrap {
        position: relative;
        max-width: 95vw;
        max-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .evg-modal-content-wrap img {
        max-width: 100%;
        max-height: 82vh;
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
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        z-index: 10;
    }
    .evg-modal-close:hover,
    .evg-modal-close:focus-visible { 
        background: var(--evg-gold-primary); 
        color: var(--evg-text-charcoal); 
        border-color: var(--evg-gold-primary); 
        outline: none;
    }

    @media (max-width: 767.98px) {
        .evg-modal-close { top: -40px; right: 5px; width: 34px; height: 34px; }
    }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- GALLERY HEADER -->
        <header style="text-align: center; margin-bottom: 20px;">
            <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Vault Archive', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Certified Slab', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Showcase', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 480px; margin: 0 auto; font-size: 0.92rem; line-height: 1.6;">
                <?php esc_html_e( 'A pristine gallery grid of authenticated masterworks preserved in Elite Vault protective slabs.', 'evg-platform' ); ?>
            </p>
        </header>

        <!-- 4-COLUMN IMAGE GRID -->
        <div class="evg-gallery-grid-4" id="evgGalleryGrid">
            <?php
            $base_img_uri = get_stylesheet_directory_uri() . '/assets/img/';
            foreach ( $gallery_images as $index => $filename ) :
                $img_url   = $base_img_uri . $filename;
                $alt_title = sprintf( __( 'Elite Vault Certified Specimen - %s', 'evg-platform' ), pathinfo( $filename, PATHINFO_FILENAME ) );
                // Hide items after the first 12 initially
                $hidden_class = ( $index >= 12 ) ? 'hidden-item' : '';
            ?>
                <button type="button" 
                        class="evg-slab-thumb <?php echo esc_attr( $hidden_class ); ?>" 
                        data-img-src="<?php echo esc_url( $img_url ); ?>" 
                        data-title="<?php echo esc_attr( $alt_title ); ?>"
                        aria-label="<?php echo esc_attr( sprintf( __( 'View %s in detail', 'evg-platform' ), $alt_title ) ); ?>">
                    <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $alt_title ); ?>" loading="lazy">
                </button>
            <?php endforeach; ?>
        </div>

        <!-- LOAD MORE BUTTON -->
        <div class="evg-load-more-wrap" id="evgLoadMoreWrap">
            <button type="button" class="evg-btn-load-more" id="evgLoadMoreBtn">
                <?php esc_html_e( 'Load More Masterworks', 'evg-platform' ); ?>
            </button>
        </div>

    </div>
</main>

<!-- IMAGE-ONLY LIGHTBOX MODAL -->
<div class="evg-modal-overlay" id="evgImageModal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Enlarged Slab Preview', 'evg-platform' ); ?>">
    <div class="evg-modal-content-wrap">
        <button type="button" class="evg-modal-close" id="evgModalClose" aria-label="<?php esc_attr_e( 'Close preview', 'evg-platform' ); ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <img id="modalActiveImage" src="" alt="">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalOverlay     = document.getElementById('evgImageModal');
    const modalClose       = document.getElementById('evgModalClose');
    const modalActiveImage = document.getElementById('modalActiveImage');
    const galleryGrid      = document.getElementById('evgGalleryGrid');
    const loadMoreBtn      = document.getElementById('evgLoadMoreBtn');
    const loadMoreWrap     = document.getElementById('evgLoadMoreWrap');
    let lastFocusedElement = null;

    function openModal(imgSrc, imgAlt) {
        lastFocusedElement = document.activeElement;
        modalActiveImage.src = imgSrc;
        modalActiveImage.alt = imgAlt || '';
        modalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        modalClose.focus();
    }

    function closeModal() {
        modalOverlay.classList.remove('active');
        modalActiveImage.src = '';
        modalActiveImage.alt = '';
        document.body.style.overflow = '';
        if (lastFocusedElement) {
            lastFocusedElement.focus();
        }
    }

    galleryGrid.addEventListener('click', function(e) {
        const thumb = e.target.closest('.evg-slab-thumb');
        if (thumb) {
            const imgSrc = thumb.getAttribute('data-img-src');
            const imgAlt = thumb.getAttribute('data-title');
            if (imgSrc) {
                openModal(imgSrc, imgAlt);
            }
        }
    });

    modalClose.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) closeModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay.classList.contains('active')) closeModal();
    });

    // Load 12 more items per click
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const hiddenItems = galleryGrid.querySelectorAll('.evg-slab-thumb.hidden-item');
            let shownCount = 0;
            
            hiddenItems.forEach(item => {
                if (shownCount < 12) {
                    item.classList.remove('hidden-item');
                    shownCount++;
                }
            });

            // If no more hidden items remain, hide the button wrapper
            if (galleryGrid.querySelectorAll('.evg-slab-thumb.hidden-item').length === 0) {
                loadMoreWrap.style.display = 'none';
            }
        });
    }
});
</script>

<?php get_footer(); ?>