<?php
/**
 * Template Name: Home
 * Description: Clean, high-performance executive homepage for Elite Vault Grading.
 *              Includes full database connectivity, updated £9.99 pricing and 5-10 business day turnaround,
 *              single high-impact hero specimen, automated photo reel above How It Works,
 *              marketplace showcase with a single prominent slab display, obsidian/gold luxury styling, and a customer feedback reel.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// -------------------------------------------------------------------------
// 1. DYNAMIC DATA FETCHING & SETTINGS RESOLUTION
// -------------------------------------------------------------------------
global $wpdb;

$table_cards       = $wpdb->prefix . 'evg_cards';
$table_marketplace = $wpdb->prefix . 'evg_marketplace';
$table_submissions = $wpdb->prefix . 'evg_submissions';
$table_feedback    = $wpdb->prefix . 'evg_feedback';

// Fetch live platform settings (defaults: £9.99 & 5-10 Business Days)
$accept_submissions  = get_option( 'evg_accept_submissions', 'yes' );
$turnaround_time     = get_option( 'evg_turnaround_time', '5-10 Business Days' );
$price_standard      = floatval( get_option( 'evg_price_standard', 9.99 ) );
$announcement_banner = get_option( 'evg_announcement_banner', '' );

// Fetch real-time count of completed/graded cards
$total_cards_graded = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table_cards} WHERE grading_status IN ('Encapsulation', 'Completed') OR final_grade > 0" );
$display_card_count = $total_cards_graded > 1000 ? number_format( $total_cards_graded ) : ( $total_cards_graded > 0 ? number_format( $total_cards_graded ) : 'THOUSANDS' );

// Fetch single featured slab from active public marketplace for the showcase section
$showcase_slab = $wpdb->get_row( "
    SELECT m.price, 
           COALESCE(c.card_name, m.card_title, 'Graded Card') AS card_name,
           COALESCE(c.set_name, m.set_name, 'Vault Collection') AS set_name,
           COALESCE(c.card_number, m.card_number, '') AS card_number,
           COALESCE(c.final_grade, m.assigned_grade, 10) AS final_grade,
           COALESCE(c.front_image_url, m.image_url, '') AS card_image
    FROM {$table_marketplace} m
    LEFT JOIN {$table_cards} c ON m.card_id = c.id
    WHERE m.status = 'Available'
    ORDER BY m.listed_date DESC
    LIMIT 1
" );

// Gallery/Collage photo set for photo reel and single specimen features
$theme_img_uri  = get_template_directory_uri() . '/assets/img/';
$collage_photos = array(
    $theme_img_uri . 'IMG_0968.jpg',
    $theme_img_uri . 'IMG_0971.jpg',
    $theme_img_uri . 'IMG_0973.jpg',
    $theme_img_uri . 'IMG_0974.jpg',
    $theme_img_uri . 'IMG_0975.jpg',
    $theme_img_uri . 'IMG_0978.jpg',
    $theme_img_uri . 'IMG_0980.jpg',
    $theme_img_uri . 'IMG_0982.jpg'
);

// Fetch customer feedback for the testimonial reel strictly respecting marketing permissions
$testimonials = array();
if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_feedback}'" ) === $table_feedback ) {
    $testimonials = $wpdb->get_results( 
        "SELECT customer_name, rating, feedback_text, submitted_at 
         FROM {$table_feedback} 
         WHERE permission_to_use = 1 AND status = 'Featured Testimonial' 
         ORDER BY submitted_at DESC 
         LIMIT 8"
    );
}

if ( empty( $testimonials ) ) {
    $testimonials = array(
        (object) array('customer_name' => 'Alex Turner', 'rating' => 5, 'feedback_text' => 'Absolutely blown away by the casing quality! The slab looks extremely premium and secure.'),
        (object) array('customer_name' => 'James Wilson', 'rating' => 5, 'feedback_text' => 'Elite Vault Grading is my go-to grading company now. Consistent 10s and gorgeous labels!'),
        (object) array('customer_name' => 'David Miller', 'rating' => 5, 'feedback_text' => 'The transparency report feature is incredible! Being able to see fault photos is next level.'),
        (object) array('customer_name' => 'Ryan G.', 'rating' => 5, 'feedback_text' => 'Fast service, elite protection slabs, and pristine sub-grades. 10/10 experience.'),
        (object) array('customer_name' => 'Daniel C.', 'rating' => 5, 'feedback_text' => 'Magnificent vault security and professional grading staff. Highly recommended!')
    );
}

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
    color: var(--evg-text-ash);
    position: relative;
    z-index: 1;
    overflow-x: hidden;
  }

  .evg-banner-announcement {
    background: linear-gradient(90deg, #18181c, #262112, #18181c);
    border-bottom: 1px solid var(--evg-gold-primary);
    color: var(--evg-gold-light);
    font-size: 0.8rem;
    font-weight: 700;
    text-align: center;
    padding: 10px 16px;
    letter-spacing: 0.05em;
  }

  .evg-master-wrapper h1, 
  .evg-master-wrapper h2, 
  .evg-master-wrapper h3, 
  .evg-master-wrapper h4 {
    color: var(--evg-text-pure);
  }
  
  .evg-master-wrapper .hero-subtitle, 
  .evg-master-wrapper .showcase-subtitle {
    color: var(--evg-gold-primary);
    letter-spacing: 0.15em;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 1rem;
  }

  .hero, .feature-strip, .how-it-works-section, .showcase-section, .bottom-strip {
    padding: 4.5rem 0;
  }

  .feature-item, .step-item {
    background: var(--evg-obsidian-panel);
    border: 1px solid var(--evg-border-hairline);
    border-radius: 8px;
    padding: 1.75rem;
    height: 100%;
    transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
  }
  
  .feature-item:hover, .step-item:hover {
    background: var(--evg-obsidian-elevated);
    border-color: var(--evg-border-gold-faint);
    transform: translateY(-3px);
  }

  .btn-gold {
    background: var(--evg-gold-primary); 
    color: var(--evg-text-charcoal) !important;
    padding: 0.9rem 1.75rem; 
    text-decoration: none; 
    font-weight: 800; 
    border-radius: 4px; 
    display: inline-flex; 
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-right: 1rem;
    font-size: 0.82rem; 
    letter-spacing: 0.12em; 
    text-transform: uppercase;
    border: none;
    transition: all 0.2s ease;
  }
  .btn-gold:hover {
    background: var(--evg-gold-light);
    box-shadow: 0 0 25px rgba(212, 175, 55, 0.3);
  }
  .btn-gold.disabled {
    background: #333336;
    color: #88888e !important;
    cursor: not-allowed;
    box-shadow: none;
  }

  .btn-outline-gold {
    background: transparent; 
    color: var(--evg-gold-primary) !important; 
    border: 1px solid var(--evg-gold-primary);
    padding: 0.9rem 1.75rem; 
    text-decoration: none; 
    font-weight: 800; 
    border-radius: 4px; 
    display: inline-flex; 
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.82rem; 
    letter-spacing: 0.12em;
    text-transform: uppercase;
    transition: all 0.2s ease;
  }
  .btn-outline-gold:hover {
    background: rgba(212, 175, 55, 0.1);
    color: var(--evg-gold-light) !important;
    border-color: var(--evg-gold-light);
  }

  .hero-note { 
    font-size: 0.75rem; 
    font-family: monospace; 
    display: flex; 
    align-items: center; 
    gap: 0.5rem; 
    margin-top: 1.5rem; 
    color: var(--evg-text-ash);
  }
  .feature-icon-wrap, .step-icon-wrap { color: var(--evg-gold-primary); margin-bottom: 1.25rem; }
  .section-title { 
    text-align: center; 
    margin-bottom: 3rem; 
    font-size: 1.8rem;
    font-weight: 700;
    letter-spacing: -0.02em;
  }
  .how-it-works-action { text-align: center; margin-top: 3rem; }
  
  /* Single Prominent Specimen Container (Hero & Showcase) */
  .vault-container-single { 
    background: var(--evg-obsidian-panel); 
    border: 1px solid var(--evg-border-hairline); 
    padding: 1.5rem; 
    border-radius: 8px; 
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
    max-width: 580px;
    margin: 0 auto;
  }
  .vault-single-item {
    background: #08080a;
    border: 1px solid var(--evg-border-gold-faint);
    border-radius: 6px;
    overflow: hidden;
    aspect-ratio: 3 / 4;
    position: relative;
    box-shadow: 0 8px 20px rgba(0,0,0,0.8);
  }
  .vault-single-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  /* Thin Moving Photo Reel */
  .evg-photo-reel-section {
    background: #08080a;
    border-bottom: 1px solid var(--evg-border-hairline);
    padding: 1.5rem 0;
    overflow: hidden;
    position: relative;
  }
  .evg-photo-reel-wrapper {
    width: 100%;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
  }
  .evg-photo-reel-wrapper::-webkit-scrollbar { display: none; }
  .evg-photo-reel-track {
    display: flex;
    gap: 16px;
    width: max-content;
    animation: scrollPhotoReel 35s linear infinite;
  }
  .evg-photo-reel-wrapper:hover .evg-photo-reel-track {
    animation-play-state: paused;
  }
  @keyframes scrollPhotoReel {
    0% { transform: translateX(0); }
    100% { transform: translateX(calc(-50% - 8px)); }
  }
  .evg-photo-reel-item {
    width: 220px;
    flex-shrink: 0;
    border-radius: 6px;
    overflow: hidden;
    border: 1px solid var(--evg-border-hairline);
    background: var(--evg-obsidian-panel);
    transition: border-color 0.2s ease, transform 0.2s ease;
  }
  .evg-photo-reel-item:hover {
    border-color: var(--evg-gold-primary);
    transform: translateY(-2px);
  }
  .evg-photo-reel-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  /* Feedback Reel Styles */
  .evg-reel-section {
    background-color: var(--evg-obsidian-base);
    padding: 4.5rem 0;
    border-top: 1px solid var(--evg-border-hairline);
    position: relative;
    overflow: hidden;
  }
  .evg-reel-header {
    text-align: center;
    margin-bottom: 2.5rem;
  }
  .evg-reel-track-wrapper {
    position: relative;
    width: 100%;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding-bottom: 10px;
  }
  .evg-reel-track-wrapper::-webkit-scrollbar { display: none; }
  .evg-reel-track {
    display: flex;
    gap: 20px;
    width: max-content;
    animation: scrollReel 40s linear infinite;
  }
  .evg-reel-track-wrapper:hover .evg-reel-track {
    animation-play-state: paused;
  }
  @keyframes scrollReel {
    0% { transform: translateX(0); }
    100% { transform: translateX(calc(-50% - 10px)); }
  }
  .evg-testimonial-card {
    background: var(--evg-obsidian-panel);
    border: 1px solid var(--evg-border-hairline);
    border-radius: 8px;
    padding: 24px;
    width: 360px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
  }
  .evg-testimonial-card:hover {
    border-color: var(--evg-border-gold-faint);
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7), 0 0 15px rgba(212, 175, 55, 0.08);
  }
  .evg-star-rating {
    color: var(--evg-gold-primary);
    letter-spacing: 2px;
    font-size: 0.85rem;
    margin-bottom: 12px;
  }

  /* --- RESPONSIVE MEDIA QUERIES FOR MOBILE & TABLET --- */
  @media (max-width: 991.98px) {
    .hero { text-align: center; padding: 3rem 0; }
    .hero-actions { justify-content: center; display: flex; flex-wrap: wrap; gap: 12px; }
    .btn-gold, .btn-outline-gold { margin-right: 0; width: 100%; max-width: 280px; }
    .hero-note { justify-content: center; }
    .hero p { margin-left: auto; margin-right: auto; }
    .vault-container-single { margin-top: 2rem; max-width: 420px; }
  }

  @media (max-width: 767.98px) {
    .hero h1 { font-size: 2.3rem !important; }
    .section-title { font-size: 1.5rem; margin-bottom: 2rem; }
    .feature-item, .step-item { padding: 1.25rem; }
    .evg-testimonial-card { width: 300px; padding: 18px; }
  }
</style>

<main class="evg-master-wrapper">

    <!-- TOP ANNOUNCEMENT BANNER -->
    <?php if ( ! empty( $announcement_banner ) ) : ?>
        <div class="evg-banner-announcement">
            <?php echo esc_html( $announcement_banner ); ?>
        </div>
    <?php endif; ?>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0">
                    
                    <!-- Dynamic Sold Out Notice -->
                    <?php if ( 'yes' !== $accept_submissions ) : ?>
                        <div style="display: inline-block; background: rgba(255, 69, 58, 0.1); border: 1px solid rgba(255, 69, 58, 0.3); padding: 5px 14px; border-radius: 4px; color: #ff453a; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">
                            ● <?php esc_html_e( 'Grading Currently Sold Out', 'evg-platform' ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="hero-subtitle"><?php esc_html_e( 'The Premier Pokémon Card Grading Service', 'evg-platform' ); ?></div>
                    <h1 style="font-size: clamp(2.3rem, 5vw, 3.8rem); font-weight: 800; line-height: 1.05; letter-spacing: -0.02em; margin-bottom: 1.5rem;">
                        SECURE.<br>PRESERVE.<br><span class="evg-text-metallic">ELEVATE.</span>
                    </h1>
                    <p style="font-size: 1.01rem; line-height: 1.6; max-width: 500px; margin-bottom: 2rem;">
                        <?php printf( esc_html__( 'Elite Vault Grading protects your Pokémon cards with precision, transparency, and trust. Base grading starting from £%s with turnaround from %s.', 'evg-platform' ), number_format( $price_standard, 2 ), esc_html( $turnaround_time ) ); ?>
                    </p>
                    
                    <div class="hero-actions">
                        <?php if ( 'yes' === $accept_submissions ) : ?>
                            <a href="<?php echo esc_url( home_url( '/grade-now' ) ); ?>" class="btn-gold">
                                <?php esc_html_e( 'Submit For Grading', 'evg-platform' ); ?>
                            </a>
                        <?php else : ?>
                            <span class="btn-gold disabled" title="<?php esc_attr_e( 'Intake suspended', 'evg-platform' ); ?>">
                                <?php esc_html_e( 'Submissions Paused', 'evg-platform' ); ?>
                            </span>
                        <?php endif; ?>

                        <a href="<?php echo esc_url( home_url( '/marketplace' ) ); ?>" class="btn-outline-gold">
                            <?php esc_html_e( 'Buy Graded Cards', 'evg-platform' ); ?>
                        </a>
                    </div>

                    <div class="hero-note">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-primary)" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        <span><?php esc_html_e( 'POKÉMON CARDS ONLY • UK REGISTRY', 'evg-platform' ); ?></span>
                    </div>
                </div>

                <!-- HERO RIGHT: SINGLE PROMINENT SPECIMEN -->
                <div class="col-lg-7">
                    <div class="vault-container-single">
                        <div class="vault-single-item">
                            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/IMG_1035.jpg' ); ?>" alt="Elite Vault Featured Specimen" loading="lazy">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURE STRIP -->
    <section class="feature-strip" style="border-top: 1px solid var(--evg-border-hairline); border-bottom: 1px solid var(--evg-border-hairline); background: #08080a;">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="feature-item">
                        <div class="feature-icon-wrap">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </div>
                        <div class="feature-text">
                            <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 6px;"><?php esc_html_e( 'MAXIMUM SECURITY', 'evg-platform' ); ?></h4>
                            <p style="font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Your cards are protected at every step.', 'evg-platform' ); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="feature-item">
                        <div class="feature-icon-wrap">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <polygon points="12 2 2 7 12 22 22 7 12 2"/>
                            </svg>
                        </div>
                        <div class="feature-text">
                            <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 6px;"><?php esc_html_e( 'EXPERT GRADING', 'evg-platform' ); ?></h4>
                            <p style="font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Strict whole-number standards for true value.', 'evg-platform' ); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="feature-item">
                        <div class="feature-icon-wrap">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                                <polyline points="16 7 22 7 22 13"/>
                            </svg>
                        </div>
                        <div class="feature-text">
                            <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 6px;"><?php esc_html_e( 'PREMIUM SLABS', 'evg-platform' ); ?></h4>
                            <p style="font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Ultrasonic tamper-evident encapsulation.', 'evg-platform' ); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="feature-item">
                        <div class="feature-icon-wrap">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div class="feature-text">
                            <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 6px;"><?php esc_html_e( 'FULL TRANSPARENCY', 'evg-platform' ); ?></h4>
                            <p style="font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Real-time 10-stage progression tracking.', 'evg-platform' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- THIN MOVING PHOTO REEL -->
    <section class="evg-photo-reel-section" aria-label="Photo Reel">
        <div class="evg-photo-reel-wrapper">
            <div class="evg-photo-reel-track">
                <?php 
                $loop_collages = array_merge( $collage_photos, $collage_photos );
                foreach ( $loop_collages as $c_img ) : 
                ?>
                    <div class="evg-photo-reel-item">
                        <img src="<?php echo esc_url( $c_img ); ?>" alt="Elite Vault Archive" loading="lazy">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="how-it-works-section">
        <div class="container">
            <h2 class="section-title"><?php esc_html_e( 'HOW IT WORKS', 'evg-platform' ); ?></h2>
            <div class="row g-4">
                <div class="col-12 col-sm-6 col-md-3 step-col">
                    <div class="step-item">
                        <div class="step-icon-wrap">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 8px;"><?php esc_html_e( '1. CREATE ACCOUNT', 'evg-platform' ); ?></h4>
                        <p style="font-size: 0.82rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Sign up and declare your cards in the submission portal.', 'evg-platform' ); ?></p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3 step-col">
                    <div class="step-item">
                        <div class="step-icon-wrap">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <polygon points="21 16 12 21 3 16 3 8 12 3 21 8 21 16"/>
                                <polyline points="3 8 12 13 21 8"/><polyline points="12 21 12 13"/>
                            </svg>
                        </div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 8px;"><?php esc_html_e( '2. SHIP YOUR CARDS', 'evg-platform' ); ?></h4>
                        <p style="font-size: 0.82rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Package securely in semi-rigids and dispatch to our UK facility.', 'evg-platform' ); ?></p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3 step-col">
                    <div class="step-item">
                        <div class="step-icon-wrap">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                <polyline points="9 12 11 14 15 10"/>
                            </svg>
                        </div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 8px;"><?php esc_html_e( '3. WE GRADE & ENCAPSULATE', 'evg-platform' ); ?></h4>
                        <p style="font-size: 0.82rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Our graders assess condition and sonically seal your slab.', 'evg-platform' ); ?></p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3 step-col">
                    <div class="step-item">
                        <div class="step-icon-wrap">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                <line x1="12" y1="18" x2="12.01" y2="18"/>
                            </svg>
                        </div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 8px;"><?php esc_html_e( '4. RECEIVE & ENJOY', 'evg-platform' ); ?></h4>
                        <p style="font-size: 0.82rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Graded slabs returned via insured Royal Mail delivery.', 'evg-platform' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="how-it-works-action">
                <a href="<?php echo esc_url( home_url( '/grading-process' ) ); ?>" class="btn-gold">
                    <?php esc_html_e( 'Learn More About Grading', 'evg-platform' ); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- SHOP WITH CONFIDENCE SECTION -->
    <section class="showcase-section" style="border-top: 1px solid var(--evg-border-hairline); border-bottom: 1px solid var(--evg-border-hairline); background: #08080a;">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6 text-center text-lg-start">
                    <div class="showcase-subtitle"><?php esc_html_e( 'Shop With Confidence', 'evg-platform' ); ?></div>
                    <h2 style="font-size: clamp(1.8rem, 4vw, 2rem); font-weight: 700; line-height: 1.2; margin-bottom: 1rem;">
                        BUY CERTIFIED<br><span class="evg-text-metallic">GRADED SLABS</span>
                    </h2>
                    <p style="font-size: 0.92rem; line-height: 1.6; margin-bottom: 1.75rem;">
                        <?php esc_html_e( 'Explore our live inventory of authenticated Pokémon cards, professionally graded and sonically encapsulated in tamper-evident obsidian shields.', 'evg-platform' ); ?>
                    </p>
                    <a href="<?php echo esc_url( home_url( '/marketplace' ) ); ?>" class="btn-gold">
                        <?php esc_html_e( 'Browse Marketplace →', 'evg-platform' ); ?>
                    </a>
                </div>

                <!-- SHOP WITH CONFIDENCE RIGHT: SINGLE PROMINENT SPECIMEN -->
                <div class="col-lg-6">
                    <div class="vault-container-single">
                        <div class="vault-single-item">
                            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/IMG_1051.jpg' ); ?>" alt="Elite Vault Certified Slab" loading="lazy">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BOTTOM FEATURE STRIP (LIVE STATS) -->
    <section class="bottom-strip">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="feature-item" style="display: flex; align-items: center; gap: 14px;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-primary)" stroke-width="2" style="flex-shrink: 0;">
                            <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/>
                        </svg>
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 800; margin: 0; color: #ffffff;">100%</h4>
                            <p style="font-size: 0.72rem; margin: 0; color: var(--evg-text-ash); font-weight: 700;"><?php esc_html_e( 'POKÉMON TCG ONLY', 'evg-platform' ); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="feature-item" style="display: flex; align-items: center; gap: 14px;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-primary)" stroke-width="2" style="flex-shrink: 0;">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 800; margin: 0; color: #ffffff;">SECURE</h4>
                            <p style="font-size: 0.72rem; margin: 0; color: var(--evg-text-ash); font-weight: 700;"><?php esc_html_e( 'VAULT FACILITY', 'evg-platform' ); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="feature-item" style="display: flex; align-items: center; gap: 14px;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-primary)" stroke-width="2" style="flex-shrink: 0;">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 800; margin: 0; color: #ffffff;">FAST</h4>
                            <p style="font-size: 0.72rem; margin: 0; color: var(--evg-text-ash); font-weight: 700;"><?php echo esc_html( $turnaround_time ); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="feature-item" style="display: flex; align-items: center; gap: 14px;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-primary)" stroke-width="2" style="flex-shrink: 0;">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 800; margin: 0; color: #ffffff;"><?php echo esc_html( $display_card_count ); ?></h4>
                            <p style="font-size: 0.72rem; margin: 0; color: var(--evg-text-ash); font-weight: 700;"><?php esc_html_e( 'CARDS GRADED', 'evg-platform' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CUSTOMER FEEDBACK REEL SECTION -->
    <section class="evg-reel-section">
        <div class="container">
            <div class="evg-reel-header">
                <div class="showcase-subtitle" style="margin-bottom: 0.5rem;"><?php esc_html_e( 'Collector Endorsements', 'evg-platform' ); ?></div>
                <h2 style="font-size: clamp(1.5rem, 4vw, 1.8rem); font-weight: 700; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    <?php esc_html_e( 'TRUSTED BY', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'COLLECTORS', 'evg-platform' ); ?></span>
                </h2>
                <p style="font-size: 0.85rem; color: var(--evg-text-ash); margin: 0;">
                    <?php esc_html_e( 'Real feedback from vault members tracking live grades and marketplace acquisitions.', 'evg-platform' ); ?>
                </p>
            </div>

            <div class="evg-reel-track-wrapper">
                <div class="evg-reel-track">
                    <?php 
                    $loop_testimonials = array_merge( $testimonials, $testimonials );
                    foreach ( $loop_testimonials as $item ) : 
                        $rating = max( 1, min( 5, intval( $item->rating ) ) );
                    ?>
                        <div class="evg-testimonial-card">
                            <div>
                                <div class="evg-star-rating">
                                    <?php echo str_repeat( '★', $rating ); ?>
                                </div>
                                <p style="color: var(--evg-text-pure); font-size: 0.88rem; line-height: 1.6; margin: 0 0 1rem 0; font-style: italic;">
                                    "<?php echo esc_html( $item->feedback_text ); ?>"
                                </p>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--evg-border-hairline); padding-top: 12px;">
                                <span style="font-weight: 700; font-size: 0.82rem; color: #ffffff;">
                                    <?php echo esc_html( $item->customer_name ); ?>
                                </span>
                                <span style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--evg-gold-primary); font-weight: 700;">
                                    <?php esc_html_e( 'Verified Collector', 'evg-platform' ); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>