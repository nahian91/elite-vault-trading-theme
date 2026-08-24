<?php
/**
 * Template Name: Home
 * Description: Clean, high-performance executive homepage for Elite Vault Grading.
 *              Includes full database connectivity, dynamic admin settings resolution,
 *              live marketplace cards, and solid obsidian/gold luxury styling with background lines removed.
 */

// -------------------------------------------------------------------------
// 1. DYNAMIC DATA FETCHING & SETTINGS RESOLUTION
// -------------------------------------------------------------------------
global $wpdb;

$table_cards       = $wpdb->prefix . 'evg_cards';
$table_marketplace = $wpdb->prefix . 'evg_marketplace';
$table_submissions = $wpdb->prefix . 'evg_submissions';

// Fetch live platform settings configured in inc/settings.php
$accept_submissions = get_option( 'evg_accept_submissions', 'yes' );
$turnaround_time    = get_option( 'evg_turnaround_time', '30-45 Business Days' );
$price_standard     = floatval( get_option( 'evg_price_standard', 15.00 ) );

// Fetch real-time count of completed/graded cards
$total_cards_graded = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table_cards} WHERE grading_status = 'Completed' OR final_grade > 0" );
$display_card_count = $total_cards_graded > 1000 ? number_format( $total_cards_graded ) : 'THOUSANDS';

// Fetch 3 showcase slabs from active public marketplace
$showcase_cards = $wpdb->get_results( "
    SELECT m.price, c.card_name, c.set_name, c.card_number, c.final_grade, c.front_image_url 
    FROM {$table_marketplace} m
    JOIN {$table_cards} c ON m.card_id = c.id
    WHERE m.status = 'Available'
    ORDER BY m.listed_date DESC
    LIMIT 3
" );

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

  /* Solid clean background without grid lines */
  .evg-master-wrapper {
    background-color: var(--evg-obsidian-base);
    background-image: radial-gradient(circle at 50% 0%, rgba(212, 175, 55, 0.08), transparent 70%);
    background-size: 100% 100%;
    min-height: 100vh;
    font-family: "Montserrat", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: var(--evg-text-ash);
    position: relative;
    z-index: 1;
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

  .feature-item, .step-item, .mini-slab {
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
  
  /* Mockup Slab Container */
  .vault-container { 
    background: var(--evg-obsidian-panel); 
    border: 1px solid var(--evg-border-hairline); 
    padding: 2.5rem; 
    border-radius: 8px; 
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
  }
  .slab { 
    border: 2px solid #2a2a2e; 
    padding: 1.25rem; 
    border-radius: 8px; 
    background: #08080a; 
    box-shadow: 0 10px 30px rgba(0,0,0,0.8);
  }
  .slab-label { 
    display: flex; 
    justify-content: space-between; 
    font-size: 0.72rem; 
    color: var(--evg-gold-light); 
    margin-bottom: 0.75rem; 
    font-family: monospace; 
    font-weight: 700;
    padding: 6px 10px;
    background: #141416;
    border: 1px solid #2c2c30;
    border-radius: 4px;
  }
  .slab-card-art { 
    height: 240px; 
    background: linear-gradient(180deg, rgba(239, 68, 68, 0.15) 0%, rgba(3, 4, 6, 0.9) 100%), #450a0a; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    color: white; 
    border-radius: 4px; 
    border: 1px solid rgba(239, 68, 68, 0.3); 
    font-size: 0.95rem;
    font-weight: 700;
    letter-spacing: 0.05em;
  }

  /* Showcase Section */
  .card-trio { display: flex; gap: 1rem; justify-content: center; }
  .mini-slab { 
    padding: 10px;
    max-width: 130px;
    flex: 1;
  }
  .mini-slab-header { 
    font-size: 0.65rem; 
    color: var(--evg-gold-primary); 
    margin-bottom: 0.5rem; 
    text-align: left; 
    font-family: monospace;
    font-weight: 700;
  }
  .mini-slab-art { 
    height: 120px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    border-radius: 4px; 
    color: white; 
    font-size: 0.75rem; 
    font-weight: 700;
    text-align: center;
    padding: 6px;
    background: #000000;
    border: 1px solid var(--evg-border-hairline); 
    overflow: hidden;
  }
  .mini-slab-art img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  @media (max-width: 992px) {
    .hero { text-align: center; }
    .hero-actions { justify-content: center; display: flex; flex-wrap: wrap; gap: 10px; }
    .btn-gold, .btn-outline-gold { margin-right: 0; }
    .hero-note { justify-content: center; }
  }
</style>

<main class="evg-master-wrapper">

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    
                    <!-- Dynamic Sold Out Notice -->
                    <?php if ( 'yes' !== $accept_submissions ) : ?>
                        <div style="display: inline-block; background: rgba(255, 69, 58, 0.1); border: 1px solid rgba(255, 69, 58, 0.3); padding: 4px 12px; border-radius: 4px; color: #ff453a; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">
                            ● <?php esc_html_e( 'Grading Currently Sold Out', 'evg-platform' ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="hero-subtitle"><?php esc_html_e( 'The Premier Pokémon Card Grading Service', 'evg-platform' ); ?></div>
                    <h1 style="font-size: clamp(2.5rem, 5vw, 3.8rem); font-weight: 800; line-height: 1.05; letter-spacing: -0.02em; margin-bottom: 1.5rem;">
                        SECURE.<br>PRESERVE.<br><span class="evg-text-metallic">ELEVATE.</span>
                    </h1>
                    <p style="font-size: 1.05rem; line-height: 1.6; max-width: 500px; margin-bottom: 2rem;">
                        <?php esc_html_e( 'Elite Vault Grading is dedicated to protecting your Pokémon cards with precision, transparency, and trust.', 'evg-platform' ); ?>
                    </p>
                    
                    <div class="hero-actions">
                        <a href="<?php echo esc_url( home_url( '/grade-now' ) ); ?>" class="btn-gold">
                            <?php esc_html_e( 'Submit For Grading', 'evg-platform' ); ?>
                        </a>
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

                <div class="col-lg-6">
                    <div class="vault-container">
                        <div class="slab">
                            <div class="slab-label">
                                <div>ELITE VAULT GRADING</div>
                                <div style="color: var(--evg-gold-primary); font-weight: 800;">GEM MT 10</div>
                            </div>
                            <div class="slab-card-art">
                                <span>[ Charizard VMAX #074/072 ]</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURE STRIP 1 -->
    <section class="feature-strip" style="border-top: 1px solid var(--evg-border-hairline); border-bottom: 1px solid var(--evg-border-hairline); background: #08080a;">
        <div class="container">
            <div class="row g-4">
                <div class="col-6 col-md-3">
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
                <div class="col-6 col-md-3">
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
                <div class="col-6 col-md-3">
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
                <div class="col-6 col-md-3">
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

    <!-- HOW IT WORKS -->
    <section class="how-it-works-section">
        <div class="container">
            <h2 class="section-title"><?php esc_html_e( 'HOW IT WORKS', 'evg-platform' ); ?></h2>
            <div class="row g-4">
                <div class="col-6 col-md-3 step-col">
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
                <div class="col-6 col-md-3 step-col">
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
                <div class="col-6 col-md-3 step-col">
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
                <div class="col-6 col-md-3 step-col">
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

    <!-- SHOWCASE SECTION -->
    <section class="showcase-section" style="border-top: 1px solid var(--evg-border-hairline); border-bottom: 1px solid var(--evg-border-hairline); background: #08080a;">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-md-4">
                    <div class="showcase-subtitle"><?php esc_html_e( 'Shop With Confidence', 'evg-platform' ); ?></div>
                    <h2 style="font-size: 1.8rem; font-weight: 700; line-height: 1.2; margin-bottom: 1rem;">
                        BUY GRADED<br><span class="evg-text-metallic">POKÉMON CARDS</span>
                    </h2>
                    <p style="font-size: 0.9rem; line-height: 1.6; margin-bottom: 1.75rem;">
                        <?php esc_html_e( 'Explore our hand-selected inventory of professionally certified and sonically encapsulated cards.', 'evg-platform' ); ?>
                    </p>
                    <a href="<?php echo esc_url( home_url( '/marketplace' ) ); ?>" class="btn-gold">
                        <?php esc_html_e( 'Browse Cards', 'evg-platform' ); ?>
                    </a>
                </div>

                <!-- Showcase Cards -->
                <div class="col-md-4 text-center">
                    <div class="card-trio">
                        <?php if ( ! empty( $showcase_cards ) ) : ?>
                            <?php foreach ( $showcase_cards as $sc ) : ?>
                                <div class="mini-slab">
                                    <div class="mini-slab-header">EVG <?php echo esc_html( $sc->final_grade ? $sc->final_grade : '10' ); ?></div>
                                    <div class="mini-slab-art">
                                        <?php if ( ! empty( $sc->front_image_url ) ) : ?>
                                            <img src="<?php echo esc_url( $sc->front_image_url ); ?>" alt="<?php echo esc_attr( $sc->card_name ); ?>">
                                        <?php else : ?>
                                            <span><?php echo esc_html( $sc->card_name ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="mini-slab">
                                <div class="mini-slab-header">EVG 10</div>
                                <div class="mini-slab-art art-pikachu">Pikachu</div>
                            </div>
                            <div class="mini-slab">
                                <div class="mini-slab-header">EVG 10</div>
                                <div class="mini-slab-art art-charizard">Charizard</div>
                            </div>
                            <div class="mini-slab">
                                <div class="mini-slab-header">EVG 10</div>
                                <div class="mini-slab-art art-blastoise">Blastoise</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4 text-md-end text-start">
                    <div class="showcase-subtitle"><?php esc_html_e( 'Early Allocations', 'evg-platform' ); ?></div>
                    <h2 style="font-size: 1.8rem; font-weight: 700; line-height: 1.2; margin-bottom: 1rem;">
                        PRE-ORDER<br><span class="evg-text-metallic">FIRST DROP</span>
                    </h2>
                    <p style="font-size: 0.9rem; line-height: 1.6; margin-bottom: 1.75rem;">
                        <?php printf( esc_html__( 'Secure tier pricing from £%.2f per card with an estimated turnaround of %s.', 'evg-platform' ), $price_standard, esc_html( $turnaround_time ) ); ?>
                    </p>
                    <a href="<?php echo esc_url( home_url( '/pre-order' ) ); ?>" class="btn-gold">
                        <?php esc_html_e( 'Pre-Order Now', 'evg-platform' ); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- BOTTOM FEATURE STRIP (LIVE STATS) -->
    <section class="bottom-strip">
        <div class="container">
            <div class="row g-4">
                <div class="col-6 col-md-3">
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

                <div class="col-6 col-md-3">
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

                <div class="col-6 col-md-3">
                    <div class="feature-item" style="display: flex; align-items: center; gap: 14px;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-primary)" stroke-width="2" style="flex-shrink: 0;">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 800; margin: 0; color: #ffffff;"><?php echo esc_html( $turnaround_time ); ?></h4>
                            <p style="font-size: 0.72rem; margin: 0; color: var(--evg-text-ash); font-weight: 700;"><?php esc_html_e( 'EST. TURNAROUND', 'evg-platform' ); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
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

</main>

<?php get_footer(); ?>