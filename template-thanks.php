<?php
/**
 * Template Name: Thank You / Order Confirmation - Executive Tier
 * Description: Fully dynamic order confirmation portal for Elite Vault Grading.
 *              Connects directly to wp_evg_submissions and wp_evg_cards,
 *              fetches live intake details, generates packing slip links,
 *              and provides solid dark luxury styling with background lines removed.
 *
 * @package EliteVaultGrading
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$table_submissions = $wpdb->prefix . 'evg_submissions';
$table_cards       = $wpdb->prefix . 'evg_cards';

// -------------------------------------------------------------------------
// 1. DYNAMIC DATA RESOLUTION & ACCESS VERIFICATION
// -------------------------------------------------------------------------
$current_user_id = get_current_user_id();
$submission_id   = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : ( isset( $_GET['submission_id'] ) ? absint( $_GET['submission_id'] ) : 0 );
$submission      = null;
$cards           = array();

if ( $submission_id > 0 ) {
    // Only allow access to the order if the current user owns it, or if staff member
    if ( current_user_can( 'manage_options' ) || ( class_exists( 'Elite_Vault_Grading_System' ) && Elite_Vault_Grading_System::has_access( array( 'administrator', 'support_team' ) ) ) ) {
        $submission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_submissions} WHERE id = %d", $submission_id ) );
    } elseif ( $current_user_id > 0 ) {
        $submission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_submissions} WHERE id = %d AND customer_id = %d", $submission_id, $current_user_id ) );
    }

    if ( $submission ) {
        $cards = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_cards} WHERE submission_id = %d ORDER BY id ASC", $submission_id ) );
    }
}

// Fallbacks & Parameters
$order_number    = $submission ? $submission->order_number : ( isset( $_GET['order_id'] ) ? sanitize_text_field( wp_unslash( $_GET['order_id'] ) ) : 'EVG-' . date( 'Y' ) . '-INTAKE' );
$submission_tier = $submission ? $submission->service_type : ( isset( $_GET['tier'] ) ? sanitize_text_field( wp_unslash( $_GET['tier'] ) ) : 'Standard Base Protocol' );
$label_option    = $submission ? $submission->label_option : ( isset( $_GET['label'] ) ? sanitize_text_field( wp_unslash( $_GET['label'] ) ) : 'Standard Vault Slab' );
$card_count      = $submission ? intval( $submission->total_cards ) : ( ! empty( $cards ) ? count( $cards ) : ( isset( $_GET['cards'] ) ? absint( $_GET['cards'] ) : 1 ) );
$total_amount    = $submission ? floatval( $submission->total_amount ) : 0.00;

// Resolve Customer Email
$customer_user = $submission ? get_userdata( $submission->customer_id ) : ( is_user_logged_in() ? wp_get_current_user() : null );
$raw_email     = $customer_user ? $customer_user->user_email : ( isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : 'collector@domain.co.uk' );

// Mask email for privacy display (e.g., j***e@domain.co.uk)
$order_email = $raw_email;
if ( strpos( $raw_email, '@' ) !== false ) {
    list( $user_part, $domain_part ) = explode( '@', $raw_email );
    if ( mb_strlen( $user_part ) > 2 ) {
        $masked_user = mb_substr( $user_part, 0, 1 ) . str_repeat( '*', mb_strlen( $user_part ) - 2 ) . mb_substr( $user_part, -1 );
    } else {
        $masked_user = mb_substr( $user_part, 0, 1 ) . '*';
    }
    $order_email = $masked_user . '@' . $domain_part;
}

// Fetch Dynamic Admin Settings
$support_email   = get_option( 'evg_support_email', 'support@elitevaultgrading.com' );
$turnaround_time = get_option( 'evg_turnaround_time', '5-10 Business Days' );

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
    overflow-x: hidden;
  }
  
  .evg-container {
    max-width: 920px;
    margin: 0 auto;
    padding: 3rem 15px 5rem 15px;
  }

  .evg-title-xl { 
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(2rem, 3.8vw, 3rem); 
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

  .evg-module {
    background: var(--evg-obsidian-panel);
    border: 1px solid var(--evg-border-hairline);
    border-radius: 8px;
    position: relative;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
  }

  .evg-security-seal {
    width: 65px; 
    height: 65px; 
    border-radius: 50%;
    background: var(--evg-obsidian-elevated);
    border: 1px solid var(--evg-gold-primary);
    display: inline-flex; 
    align-items: center; 
    justify-content: center;
    color: var(--evg-gold-primary); 
    box-shadow: 0 0 20px var(--evg-gold-glow);
  }

  .evg-grid-matrix {
    display: grid; 
    gap: 1px;
    background: var(--evg-border-hairline); 
    border: 1px solid var(--evg-border-hairline); 
    border-radius: 8px; 
    overflow: hidden;
  }
  .evg-grid-cell {
    background: var(--evg-obsidian-panel); 
    padding: 1.75rem 1.25rem; 
    transition: background 0.3s ease; 
    text-align: center;
  }
  .evg-grid-cell:hover { background: var(--evg-obsidian-elevated); }
  .evg-workflow-matrix { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }

  .btn-evg-executive {
    background: var(--evg-gold-primary); 
    color: var(--evg-text-charcoal) !important;
    font-size: 0.78rem; 
    font-weight: 800; 
    letter-spacing: 0.12em; 
    text-transform: uppercase;
    border: none; 
    border-radius: 4px; 
    padding: 0.9rem 1.4rem; 
    display: inline-flex; 
    align-items: center; 
    justify-content: center;
    transition: all 0.2s ease; 
    text-decoration: none; 
    cursor: pointer;
    text-align: center;
  }
  .btn-evg-executive:hover { 
    background: var(--evg-gold-light); 
    box-shadow: 0 0 20px var(--evg-gold-glow); 
  }
  
  .btn-evg-outline {
    background: transparent; 
    color: var(--evg-gold-primary) !important;
    font-size: 0.75rem; 
    font-weight: 800; 
    letter-spacing: 0.1em; 
    text-transform: uppercase;
    border: 1px solid var(--evg-gold-primary); 
    border-radius: 4px; 
    padding: 0.85rem 1.25rem; 
    display: inline-flex; 
    align-items: center; 
    justify-content: center;
    transition: all 0.2s ease; 
    cursor: pointer; 
    text-decoration: none;
    text-align: center;
  }
  .btn-evg-outline:hover { 
    background: rgba(212, 175, 55, 0.1); 
    color: var(--evg-gold-light) !important; 
    border-color: var(--evg-gold-light); 
  }

  .evg-copy-btn {
    background: var(--evg-obsidian-elevated); 
    border: 1px solid #28282c;
    color: var(--evg-text-ash); 
    transition: all 0.2s ease; 
    border-radius: 4px; 
    padding: 0.4rem 0.6rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .evg-copy-btn:hover { 
    background: #09090b; 
    border-color: var(--evg-gold-primary); 
    color: var(--evg-gold-primary); 
  }

  .evg-meta-list { list-style: none; padding: 0; margin: 0; }
  .evg-meta-list li {
    display: flex; 
    justify-content: space-between; 
    align-items: center;
    padding: 0.85rem 0; 
    border-bottom: 1px solid var(--evg-border-hairline);
    font-size: 0.85rem;
    gap: 10px;
  }
  .evg-meta-list li:last-child { border-bottom: none; }

  /* Responsive Media Queries */
  @media (max-width: 767.98px) {
    .evg-container { padding: 2rem 15px 4rem 15px; }
    .evg-module { padding: 25px 15px; }
    .evg-module > div[style*="display: flex"] { flex-direction: column; align-items: stretch !important; gap: 15px; }
    .evg-module > div[style*="display: flex"] > div:last-child { display: grid; grid-template-columns: 1fr; gap: 8px; }
    .evg-module > div[style*="display: flex"] > div:last-child a, 
    .evg-module > div[style*="display: flex"] > div:last-child button { width: 100%; }
  }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- 1. CERTIFICATION BANNER & HERO -->
        <header style="text-align: center; margin-bottom: 35px;">
            <div class="evg-security-seal" style="margin-bottom: 16px;">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <polyline points="9 12 11 14 15 10"/>
                </svg>
            </div>
            
            <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 8px;"><?php esc_html_e( 'Vault Intake Reserved & Confirmed', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Allocation', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Locked & Verified', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 600px; margin: 0 auto; font-size: 0.92rem; line-height: 1.6;">
                <?php esc_html_e( 'Payment confirmed. Your Pokémon card grading allocation has been logged into our central UK registry and is awaiting shipment.', 'evg-platform' ); ?>
            </p>
        </header>

        <!-- 2. CONSIGNMENT MANIFEST MODULE -->
        <section class="evg-module" style="padding: 30px 20px; margin-bottom: 35px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid var(--evg-border-hairline);">
                <div>
                    <span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Official Intake Reference ID', 'evg-platform' ); ?></span>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                        <h2 style="color: #ffffff; font-size: 1.2rem; font-family: monospace; font-weight: 700; margin: 0; letter-spacing: 1px;">
                            #<?php echo esc_html( $order_number ); ?>
                        </h2>
                        <button class="evg-copy-btn" onclick="navigator.clipboard.writeText('<?php echo esc_js( $order_number ); ?>'); alert('<?php esc_attr_e( 'Order Reference copied to clipboard.', 'evg-platform' ); ?>');" title="<?php esc_attr_e( 'Copy Reference', 'evg-platform' ); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                        </button>
                    </div>
                    <p style="color: var(--evg-text-ash); font-size: 0.82rem; margin: 0;">
                        <?php printf( esc_html__( 'Confirmation voucher and order manifest transmitted to %s.', 'evg-platform' ), '<strong style="color:#ffffff;">' . esc_html( $order_email ) . '</strong>' ); ?>
                    </p>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="<?php echo esc_url( home_url( '/my-account' ) ); ?>" class="btn-evg-executive">
                        <?php esc_html_e( 'Live Tracking Portal', 'evg-platform' ); ?>
                    </a>
                    <button type="button" class="btn-evg-outline" onclick="window.print()">
                        🖨️ <?php esc_html_e( 'Print Manifest Sheet', 'evg-platform' ); ?>
                    </button>
                </div>
            </div>

            <!-- INTAKE METRICS -->
            <ul class="evg-meta-list" style="padding-top: 5px;">
                <li>
                    <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Submission Service Tier', 'evg-platform' ); ?></span>
                    <span style="color: #ffffff; font-weight: 600; text-align: right;"><?php echo esc_html( $submission_tier ); ?></span>
                </li>
                <li>
                    <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Declared Asset Manifest', 'evg-platform' ); ?></span>
                    <span style="color: var(--evg-gold-light); font-weight: 700; text-align: right;"><?php echo esc_html( $card_count ); ?> <?php esc_html_e( 'Pokémon Cards Declared', 'evg-platform' ); ?></span>
                </li>
                <li>
                    <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Slab Design Option', 'evg-platform' ); ?></span>
                    <span style="color: #ffffff; font-weight: 600; text-align: right;"><?php echo esc_html( $label_option ); ?></span>
                </li>
                <?php if ( $total_amount > 0 ) : ?>
                    <li>
                        <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Total Billed & Settled', 'evg-platform' ); ?></span>
                        <span style="color: #34c759; font-family: monospace; font-weight: 700; font-size: 0.92rem;">£<?php echo esc_html( number_format( (float) $total_amount, 2 ) ); ?></span>
                    </li>
                <?php endif; ?>
                <li>
                    <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Estimated Turnaround SLA', 'evg-platform' ); ?></span>
                    <span style="color: var(--evg-gold-primary); font-family: monospace; font-weight: 700;"><?php echo esc_html( $turnaround_time ); ?></span>
                </li>
                <li>
                    <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Return Delivery (UK)', 'evg-platform' ); ?></span>
                    <span style="color: #34c759; font-family: monospace; font-size: 0.75rem; font-weight: 700;"><?php esc_html_e( 'ROYAL MAIL TRACKED', 'evg-platform' ); ?></span>
                </li>
            </ul>
        </section>

        <!-- 3. PREPARATION WORKFLOW MATRIX -->
        <section style="margin-bottom: 35px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <span class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Consignment Roadmap', 'evg-platform' ); ?></span>
                <h3 style="color: #ffffff; font-size: 1.25rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'What Happens Next?', 'evg-platform' ); ?></h3>
            </div>

            <div class="evg-grid-matrix evg-workflow-matrix">
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 6px;"><?php esc_html_e( 'Stage 01', 'evg-platform' ); ?></span>
                    <h4 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Package Your Cards', 'evg-platform' ); ?></h4>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                        <?php esc_html_e( 'Place cards in penny sleeves and semi-rigids. Enclose a printed copy of this reference slip inside a padded box.', 'evg-platform' ); ?>
                    </p>
                </div>

                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 6px;"><?php esc_html_e( 'Stage 02', 'evg-platform' ); ?></span>
                    <h4 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Tracked UK Dispatch', 'evg-platform' ); ?></h4>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                        <?php esc_html_e( 'Post via an insured, tracked courier (e.g. Royal Mail Special Delivery) covering your total declared valuation.', 'evg-platform' ); ?>
                    </p>
                </div>

                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 6px;"><?php esc_html_e( 'Stage 03', 'evg-platform' ); ?></span>
                    <h4 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Live Vault Telemetry', 'evg-platform' ); ?></h4>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                        <?php esc_html_e( 'Track check-in, authentication, sub-scoring, and sonic encapsulation directly from your member dashboard.', 'evg-platform' ); ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- 4. SUPPORT UTILITY FOOTER -->
        <footer class="evg-module" style="padding: 20px 15px; text-align: center;">
            <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.6; margin: 0;">
                <?php esc_html_e( 'Need amendments or corrections prior to shipping your cards?', 'evg-platform' ); ?><br>
                <?php esc_html_e( 'Contact our UK verification desk directly at', 'evg-platform' ); ?> 
                <a href="mailto:<?php echo esc_attr( $support_email ); ?>" style="color: #ffffff; text-decoration: underline; font-weight: 600; word-break: break-all;">
                    <?php echo esc_html( $support_email ); ?>
                </a>
            </p>
        </footer>

    </div>
</main>

<?php get_footer(); ?>