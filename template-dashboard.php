<?php
/**
 * Template Name: My Account / Customer Dashboard - Executive Tier
 * Description: Fully dynamic, production-ready Customer Dashboard for Elite Vault Grading.
 *              Integrates directly with wp_evg_submissions, wp_evg_cards, and user meta.
 *              Provides real-time 10-stage pipeline telemetry, declared card ledgers, billing receipts,
 *              UK return logistics editing, and invoice printing without background grid lines.
 */

// Force authentication
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url( '/sign-in' ) );
    exit;
}

global $wpdb;

$current_user    = wp_get_current_user();
$current_user_id = $current_user->ID;
$display_name    = ! empty( $current_user->display_name ) ? esc_html( $current_user->display_name ) : 'Valued Collector';
$user_email      = ! empty( $current_user->user_email ) ? esc_html( $current_user->user_email ) : '';
$user_id_badge   = 'EVG-' . str_pad( $current_user_id, 5, '0', STR_PAD_LEFT );

$table_submissions = $wpdb->prefix . 'evg_submissions';
$table_cards       = $wpdb->prefix . 'evg_cards';

// -------------------------------------------------------------------------
// 1. HANDLE ADDRESS & PROFILE UPDATES
// -------------------------------------------------------------------------
$profile_notice = '';

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['evg_update_profile_nonce'] ) ) {
    if ( wp_verify_nonce( $_POST['evg_update_profile_nonce'], 'evg_update_profile_action' ) ) {
        
        $first_name     = sanitize_text_field( $_POST['first_name'] ?? '' );
        $last_name      = sanitize_text_field( $_POST['last_name'] ?? '' );
        $mobile_number  = sanitize_text_field( $_POST['mobile_number'] ?? '' );
        $house_number   = sanitize_text_field( $_POST['house_number'] ?? '' );
        $street_address = sanitize_text_field( $_POST['street_address'] ?? '' );
        $town_city      = sanitize_text_field( $_POST['town_city'] ?? '' );
        $county         = sanitize_text_field( $_POST['county'] ?? '' );
        $postcode       = sanitize_text_field( $_POST['postcode'] ?? '' );

        wp_update_user( array(
            'ID'           => $current_user_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => trim( $first_name . ' ' . $last_name ),
        ) );

        update_user_meta( $current_user_id, 'evg_mobile_number', $mobile_number );
        update_user_meta( $current_user_id, 'evg_house_number', $house_number );
        update_user_meta( $current_user_id, 'evg_street_address', $street_address );
        update_user_meta( $current_user_id, 'evg_town_city', $town_city );
        update_user_meta( $current_user_id, 'evg_county', $county );
        update_user_meta( $current_user_id, 'evg_postcode', strtoupper( $postcode ) );

        if ( class_exists( 'Elite_Vault_Grading_System' ) && method_exists( 'Elite_Vault_Grading_System', 'log_activity' ) ) {
            Elite_Vault_Grading_System::log_activity( "Customer ID {$current_user_id} updated shipping coordinates." );
        }

        $profile_notice = '<div style="background: rgba(52, 199, 89, 0.08); border: 1px solid rgba(52, 199, 89, 0.3); border-radius: 4px; padding: 14px 18px; margin-bottom: 20px; color: #34c759; font-size: 0.85rem; font-weight: 600;">✓ ' . esc_html__( 'Shipping coordinates updated successfully.', 'evg-platform' ) . '</div>';
    }
}

// -------------------------------------------------------------------------
// 2. FETCH LIVE DATA FROM DATABASE
// -------------------------------------------------------------------------
// Fetch all user submissions
$submissions = $wpdb->get_results( $wpdb->prepare( "
    SELECT * FROM {$table_submissions} 
    WHERE customer_id = %d 
    ORDER BY submission_date DESC
", $current_user_id ) );

// Fetch active submission for live telemetry
$active_submission = null;
foreach ( $submissions as $sub ) {
    if ( ! in_array( $sub->current_stage, array( 'Completed', 'Returned To Customer' ), true ) ) {
        $active_submission = $sub;
        break;
    }
}
if ( ! $active_submission && ! empty( $submissions ) ) {
    $active_submission = $submissions[0];
}

// Fetch all declared cards for this customer
$all_cards = $wpdb->get_results( $wpdb->prepare( "
    SELECT c.*, s.order_number, s.label_option, s.current_stage 
    FROM {$table_cards} c
    JOIN {$table_submissions} s ON c.submission_id = s.id
    WHERE s.customer_id = %d
    ORDER BY c.id DESC
", $current_user_id ) );

// Profile metadata
$first_name     = get_user_meta( $current_user_id, 'first_name', true );
$last_name      = get_user_meta( $current_user_id, 'last_name', true );
$mobile_number  = get_user_meta( $current_user_id, 'evg_mobile_number', true );
$house_number   = get_user_meta( $current_user_id, 'evg_house_number', true );
$street_address = get_user_meta( $current_user_id, 'evg_street_address', true );
$town_city      = get_user_meta( $current_user_id, 'evg_town_city', true );
$county         = get_user_meta( $current_user_id, 'evg_county', true );
$postcode       = get_user_meta( $current_user_id, 'evg_postcode', true );

// Official EVG 10-Stage Pipeline Sequence
$pipeline_stages = array(
    '01. Pre-Order Received'       => 'Pre-Order Received',
    '02. Awaiting Arrival'        => 'Cards Awaiting Arrival',
    '03. Package Received'         => 'Cards Received',
    '04. Authentication Check'     => 'Authentication Check',
    '05. Under Review'             => 'Under Review',
    '06. Grading In Progress'      => 'Grading In Progress',
    '07. Quality Control'          => 'Quality Control',
    '08. Encapsulation'            => 'Encapsulation',
    '09. Completed'                => 'Completed',
    '10. Dispatched'               => 'Returned To Customer'
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

  /* Solid clean background without grid lines */
  .evg-master-wrapper {
    background-color: var(--evg-obsidian-base);
    background-image: radial-gradient(circle at 50% 0%, rgba(212, 175, 55, 0.08), transparent 70%);
    background-size: 100% 100%;
    min-height: 100vh;
    font-family: "Montserrat", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    position: relative;
    z-index: 1;
    color: var(--evg-text-pure);
  }
  
  .evg-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 4rem 20px 6rem 20px;
  }

  .evg-title-xl { 
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(2rem, 3.5vw, 2.6rem); 
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

  /* Two Column Dashboard Grid */
  .evg-dashboard-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 28px;
    align-items: start;
  }

  /* Dashboard Navigation */
  .evg-nav-pills {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 0;
    margin: 0;
    list-style: none;
  }
  .evg-nav-pills .nav-btn {
    width: 100%;
    background: transparent;
    color: var(--evg-text-ash);
    border: none;
    border-left: 2px solid transparent;
    padding: 1.1rem 1.25rem;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-align: left;
    cursor: pointer;
  }
  .evg-nav-pills .nav-btn:hover {
    color: var(--evg-text-pure);
    background: var(--evg-obsidian-elevated);
  }
  .evg-nav-pills .nav-btn.active {
    background: rgba(212, 175, 55, 0.06);
    border-left-color: var(--evg-gold-primary);
    color: var(--evg-gold-primary);
  }

  /* Tab View Transitions */
  .evg-tab-panel {
    display: none;
  }
  .evg-tab-panel.active {
    display: block;
  }

  /* Form Elements */
  .evg-form-control {
    background: var(--evg-obsidian-elevated); 
    border: 1px solid #242428;
    color: var(--evg-text-pure); 
    border-radius: 4px; 
    padding: 0.85rem 1.25rem;
    font-size: 0.9rem; 
    width: 100%; 
    box-sizing: border-box;
    outline: none;
    transition: all 0.2s ease;
  }
  .evg-form-control:focus {
    border-color: var(--evg-gold-primary); 
    box-shadow: 0 0 0 1px var(--evg-gold-primary);
    background: #09090b;
  }
  .evg-form-control[readonly] { 
    color: var(--evg-gold-primary); 
    background: #07080a; 
  }

  /* Data Registry Tables */
  .evg-table-wrapper {
    background: var(--evg-obsidian-base); 
    border: 1px solid var(--evg-border-hairline); 
    border-radius: 6px; 
    overflow-x: auto;
  }
  .evg-table { 
    width: 100%; 
    border-collapse: collapse; 
    margin: 0; 
    font-size: 0.85rem; 
  }
  .evg-table th {
    background: var(--evg-obsidian-elevated); 
    color: var(--evg-gold-primary);
    font-size: 0.68rem; 
    text-transform: uppercase; 
    letter-spacing: 0.12em;
    padding: 1.1rem 1rem; 
    border-bottom: 1px solid var(--evg-border-hairline); 
    font-weight: 700; 
    text-align: left;
  }
  .evg-table td {
    background: transparent; 
    color: var(--evg-text-pure); 
    padding: 1.1rem 1rem;
    border-bottom: 1px solid var(--evg-border-hairline); 
    vertical-align: middle;
  }
  .evg-table tr:hover td { background: var(--evg-obsidian-elevated); }
  .evg-table tr:last-child td { border-bottom: none; }

  /* 10-Stage Pipeline Status Badges */
  .evg-track-badge {
    font-size: 0.68rem; 
    font-family: monospace; 
    letter-spacing: 0.08em;
    padding: 0.45rem 0.85rem; 
    border-radius: 4px; 
    font-weight: 700;
    display: inline-flex; 
    align-items: center; 
    gap: 0.4rem; 
    text-transform: uppercase;
  }
  .evg-track-completed { 
    background: rgba(52, 199, 89, 0.08); 
    border: 1px solid rgba(52, 199, 89, 0.3); 
    color: #34c759; 
  }
  .evg-track-active { 
    background: rgba(212, 175, 55, 0.12); 
    border: 1px solid var(--evg-gold-primary); 
    color: var(--evg-gold-light); 
    box-shadow: 0 0 12px var(--evg-gold-glow); 
  }
  .evg-track-pending { 
    background: var(--evg-obsidian-base); 
    border: 1px solid var(--evg-border-hairline); 
    color: #4a4f5c; 
  }

  /* Action Buttons */
  .btn-evg-executive {
    background: var(--evg-gold-primary); 
    color: var(--evg-text-charcoal) !important;
    font-size: 0.78rem; 
    font-weight: 800; 
    letter-spacing: 0.12em; 
    text-transform: uppercase;
    border: none; 
    border-radius: 4px; 
    padding: 0.85rem 1.6rem; 
    display: inline-flex; 
    align-items: center; 
    justify-content: center;
    transition: all 0.2s ease; 
    text-decoration: none; 
    cursor: pointer;
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
    padding: 0.75rem 1.4rem; 
    display: inline-flex; 
    align-items: center; 
    justify-content: center;
    transition: all 0.2s ease; 
    cursor: pointer; 
    text-decoration: none;
  }
  .btn-evg-outline:hover { 
    background: rgba(212, 175, 55, 0.1); 
    color: var(--evg-gold-light) !important; 
    border-color: var(--evg-gold-light); 
  }

  .evg-data-point { display: flex; flex-direction: column; gap: 0.25rem; }
  .evg-data-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--evg-text-ash); font-weight: 700; }
  .evg-data-value { font-size: 0.9rem; color: var(--evg-text-pure); font-weight: 600; }

  @media (max-width: 992px) {
    .evg-dashboard-layout { grid-template-columns: 1fr; }
  }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- 1. DASHBOARD HEADER / BANNER -->
        <header class="evg-module" style="padding: 35px 30px; margin-bottom: 35px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;">
                <div>
                    <span class="evg-label-micro" style="margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background: var(--evg-gold-primary); display: inline-block; box-shadow: 0 0 10px var(--evg-gold-primary);"></span>
                        <?php esc_html_e( 'Authenticated Collector Terminal', 'evg-platform' ); ?>
                    </span>
                    <h1 class="evg-title-xl"><?php esc_html_e( 'Welcome back,', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php echo $display_name; ?></span></h1>
                    <div style="display: flex; align-items: center; gap: 14px; font-size: 0.85rem; color: var(--evg-text-ash);">
                        <span><?php echo $user_email; ?></span>
                        <span style="color: var(--evg-border-hairline);">|</span>
                        <span>Client ID: <strong style="color: var(--evg-gold-primary); font-family: monospace;"><?php echo esc_html( $user_id_badge ); ?></strong></span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <a href="<?php echo esc_url( home_url( '/grade-now' ) ); ?>" class="btn-evg-executive">
                        + <?php esc_html_e( 'New Submission', 'evg-platform' ); ?>
                    </a>
                    <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="btn-evg-outline">
                        <?php esc_html_e( 'Sign Out', 'evg-platform' ); ?>
                    </a>
                </div>
            </div>
        </header>

        <!-- 2. MAIN WORKSPACE WITH TABS -->
        <div class="evg-dashboard-layout">
            
            <!-- LEFT: TERMINAL NAVIGATION -->
            <aside>
                <div class="evg-module" style="padding: 0; overflow: hidden; position: sticky; top: 2rem;">
                    <ul class="evg-nav-pills">
                        <li>
                            <button class="nav-btn active" data-target="tab-telemetry">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                <span><?php esc_html_e( 'Live Telemetry', 'evg-platform' ); ?></span>
                            </button>
                        </li>
                        <li>
                            <button class="nav-btn" data-target="tab-cards">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <span><?php esc_html_e( 'Declared Cards', 'evg-platform' ); ?> (<?php echo count( $all_cards ); ?>)</span>
                            </button>
                        </li>
                        <li>
                            <button class="nav-btn" data-target="tab-history">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <span><?php esc_html_e( 'Order Invoices', 'evg-platform' ); ?> (<?php echo count( $submissions ); ?>)</span>
                            </button>
                        </li>
                        <li>
                            <button class="nav-btn" data-target="tab-profile">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span><?php esc_html_e( 'UK Shipping Address', 'evg-platform' ); ?></span>
                            </button>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- RIGHT: TAB CONTENT DATA PANELS -->
            <main>
                <!-- TAB 01: LIVE TELEMETRY & SUBMISSIONS TRACKER -->
                <section class="evg-tab-panel active" id="tab-telemetry">
                    <div class="evg-module" style="padding: 35px 30px;">
                        
                        <?php if ( $active_submission ) : 
                            $stage_keys = array_values( $pipeline_stages );
                            $current_idx = array_search( $active_submission->current_stage, $stage_keys, true );
                            if ( false === $current_idx ) {
                                $current_idx = 0;
                            }
                        ?>
                            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--evg-border-hairline);">
                                <div>
                                    <span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Active Consignment Manifest', 'evg-platform' ); ?></span>
                                    <h2 style="color: #ffffff; font-size: 1.25rem; font-family: monospace; font-weight: 700; margin: 0;">
                                        #<?php echo esc_html( $active_submission->order_number ); ?>
                                    </h2>
                                </div>
                                <span class="evg-track-badge evg-track-active">
                                    ● <?php echo esc_html( $active_submission->current_stage ); ?>
                                </span>
                            </div>

                            <!-- Consignment Quick Data Row -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 18px; margin-bottom: 30px;">
                                <div class="evg-data-point">
                                    <span class="evg-data-label"><?php esc_html_e( 'Intake Date', 'evg-platform' ); ?></span>
                                    <span class="evg-data-value" style="font-family: monospace;"><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $active_submission->submission_date ) ) ); ?></span>
                                </div>
                                <div class="evg-data-point">
                                    <span class="evg-data-label"><?php esc_html_e( 'Service Tier', 'evg-platform' ); ?></span>
                                    <span class="evg-data-value"><?php echo esc_html( $active_submission->service_type ); ?></span>
                                </div>
                                <div class="evg-data-point">
                                    <span class="evg-data-label"><?php esc_html_e( 'Slab Design', 'evg-platform' ); ?></span>
                                    <span class="evg-data-value" style="color: var(--evg-gold-light);"><?php echo esc_html( $active_submission->label_option ); ?></span>
                                </div>
                                <div class="evg-data-point">
                                    <span class="evg-data-label"><?php esc_html_e( 'Declared Units', 'evg-platform' ); ?></span>
                                    <span class="evg-data-value"><?php echo esc_html( $active_submission->total_cards ); ?> Cards</span>
                                </div>
                                <div class="evg-data-point">
                                    <span class="evg-data-label"><?php esc_html_e( 'Settlement', 'evg-platform' ); ?></span>
                                    <span class="evg-data-value" style="color: #34c759; font-family: monospace;"><?php echo esc_html( $active_submission->payment_status ); ?></span>
                                </div>
                            </div>

                            <!-- Return Tracking if Dispatched -->
                            <?php if ( ! empty( $active_submission->return_tracking ) ) : ?>
                                <div style="background: rgba(52, 199, 89, 0.08); border: 1px solid rgba(52, 199, 89, 0.3); border-radius: 6px; padding: 16px; margin-bottom: 30px;">
                                    <span style="color: #34c759; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 4px;">
                                        <?php esc_html_e( 'Dispatched Tracking Reference (Royal Mail)', 'evg-platform' ); ?>
                                    </span>
                                    <span style="color: #ffffff; font-family: monospace; font-size: 1rem; font-weight: 700;">
                                        <?php echo esc_html( $active_submission->return_tracking ); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <!-- 10-Stage Visual Telemetry Matrix -->
                            <span class="evg-label-micro" style="color: #ffffff; margin-bottom: 12px;"><?php esc_html_e( 'Official 10-Stage Progression Telemetry', 'evg-platform' ); ?></span>
                            <div style="background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 20px;">
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <?php 
                                    $step_count = 0;
                                    foreach ( $pipeline_stages as $label => $key ) : 
                                        $badge_style = 'evg-track-pending';
                                        if ( $step_count < $current_idx ) {
                                            $badge_style = 'evg-track-completed';
                                        } elseif ( $step_count === $current_idx ) {
                                            $badge_style = 'evg-track-active';
                                        }
                                    ?>
                                        <span class="evg-track-badge <?php echo esc_attr( $badge_style ); ?>">
                                            <?php echo esc_html( $label ); ?> <?php echo ( $step_count < $current_idx ) ? '✓' : ''; ?>
                                        </span>
                                    <?php 
                                        $step_count++;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>

                        <?php else : ?>
                            <div style="text-align: center; padding: 40px 20px;">
                                <h3 style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0 0 10px 0;"><?php esc_html_e( 'No Active Grading Consignments', 'evg-platform' ); ?></h3>
                                <p style="color: var(--evg-text-ash); font-size: 0.9rem; max-width: 480px; margin: 0 auto 20px auto;">
                                    <?php esc_html_e( 'You do not have any active grading submissions currently in our pipeline.', 'evg-platform' ); ?>
                                </p>
                                <a href="<?php echo esc_url( home_url( '/grade-now' ) ); ?>" class="btn-evg-executive">
                                    <?php esc_html_e( 'Initialize Your First Submission', 'evg-platform' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>
                </section>

                <!-- TAB 02: ALL DECLARED CARDS -->
                <section class="evg-tab-panel" id="tab-cards">
                    <div class="evg-module" style="padding: 35px 30px;">
                        <span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Certified Holdings', 'evg-platform' ); ?></span>
                        <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700; margin: 0 0 25px 0;"><?php esc_html_e( 'Declared Asset Registry', 'evg-platform' ); ?></h2>

                        <?php if ( ! empty( $all_cards ) ) : ?>
                            <div class="evg-table-wrapper">
                                <table class="evg-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Card Identification', 'evg-platform' ); ?></th>
                                            <th><?php esc_html_e( 'Set Details', 'evg-platform' ); ?></th>
                                            <th><?php esc_html_e( 'Language', 'evg-platform' ); ?></th>
                                            <th><?php esc_html_e( 'Order Ref', 'evg-platform' ); ?></th>
                                            <th><?php esc_html_e( 'Stage', 'evg-platform' ); ?></th>
                                            <th style="text-align: right;"><?php esc_html_e( 'Final Grade', 'evg-platform' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ( $all_cards as $c ) : ?>
                                            <tr>
                                                <td><strong style="color: #ffffff; font-size: 0.9rem;"><?php echo esc_html( $c->card_name ); ?></strong></td>
                                                <td>
                                                    <span style="color: #ffffff;"><?php echo esc_html( $c->set_name ); ?></span>
                                                    <?php if ( ! empty( $c->card_number ) ) : ?>
                                                        <br><small style="color: var(--evg-text-ash); font-family: monospace;">#<?php echo esc_html( $c->card_number ); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span style="background: #141416; border: 1px solid #2c2c30; color: var(--evg-gold-primary); font-size: 0.65rem; font-family: monospace; padding: 2px 6px; border-radius: 4px;"><?php echo esc_html( strtoupper( substr( $c->language, 0, 3 ) ) ); ?></span></td>
                                                <td><span style="font-family: monospace; color: var(--evg-gold-light);">#<?php echo esc_html( $c->order_number ); ?></span></td>
                                                <td><span style="font-size: 0.75rem; color: var(--evg-text-ash);"><?php echo esc_html( $c->grading_status ); ?></span></td>
                                                <td style="text-align: right;">
                                                    <?php if ( ! empty( $c->final_grade ) ) : ?>
                                                        <span style="background: var(--evg-gold-primary); color: #0a0a0a; font-weight: 900; font-size: 0.85rem; padding: 3px 8px; border-radius: 4px; display: inline-block;">
                                                            EVG <?php echo esc_html( $c->final_grade ); ?>
                                                        </span>
                                                    <?php else : ?>
                                                        <span style="color: var(--evg-text-ash); font-size: 0.75rem; font-family: monospace;">PENDING</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <p style="color: var(--evg-text-ash); font-size: 0.9rem; margin: 0;"><?php esc_html_e( 'No cards currently logged under your account registry.', 'evg-platform' ); ?></p>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- TAB 03: FINANCIAL INVOICES & LEDGER -->
                <section class="evg-tab-panel" id="tab-history">
                    <div class="evg-module" style="padding: 35px 30px;">
                        <span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Billing & Invoices', 'evg-platform' ); ?></span>
                        <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700; margin: 0 0 25px 0;"><?php esc_html_e( 'Financial Ledgers', 'evg-platform' ); ?></h2>

                        <?php if ( ! empty( $submissions ) ) : ?>
                            <div class="evg-table-wrapper">
                                <table class="evg-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Order Ref', 'evg-platform' ); ?></th>
                                            <th><?php esc_html_e( 'Submission Date', 'evg-platform' ); ?></th>
                                            <th><?php esc_html_e( 'Service Plan', 'evg-platform' ); ?></th>
                                            <th><?php esc_html_e( 'Units', 'evg-platform' ); ?></th>
                                            <th><?php esc_html_e( 'Total Billed', 'evg-platform' ); ?></th>
                                            <th><?php esc_html_e( 'Status', 'evg-platform' ); ?></th>
                                            <th style="text-align: right;"><?php esc_html_e( 'Invoice', 'evg-platform' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ( $submissions as $s ) : ?>
                                            <tr>
                                                <td><strong style="color: var(--evg-gold-primary); font-family: monospace;">#<?php echo esc_html( $s->order_number ); ?></strong></td>
                                                <td style="color: var(--evg-text-ash); font-size: 0.8rem;"><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $s->submission_date ) ) ); ?></td>
                                                <td><?php echo esc_html( $s->service_type ); ?></td>
                                                <td><?php echo esc_html( $s->total_cards ); ?></td>
                                                <td><strong style="color: #ffffff; font-family: monospace;">£<?php echo esc_html( number_format( $s->total_amount, 2 ) ); ?></strong></td>
                                                <td>
                                                    <span style="color: <?php echo ( 'Paid' === $s->payment_status ) ? '#34c759' : '#ff9f0a'; ?>; font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">
                                                        ● <?php echo esc_html( $s->payment_status ); ?>
                                                    </span>
                                                </td>
                                                <td style="text-align: right;">
                                                    <a href="<?php echo esc_url( admin_url( 'admin-post.php?action=evg_download_invoice&submission_id=' . $s->id ) ); ?>" target="_blank" class="btn-evg-outline" style="padding: 0.35rem 0.75rem; font-size: 0.65rem;">
                                                        📄 <?php esc_html_e( 'PDF Slip', 'evg-platform' ); ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <p style="color: var(--evg-text-ash); font-size: 0.9rem; margin: 0;"><?php esc_html_e( 'No financial records cataloged.', 'evg-platform' ); ?></p>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- TAB 04: UK SHIPPING ADDRESS & PROFILE -->
                <section class="evg-tab-panel" id="tab-profile">
                    <div class="evg-module" style="padding: 35px 30px;">
                        <span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Logistics Registry', 'evg-platform' ); ?></span>
                        <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700; margin: 0 0 20px 0;"><?php esc_html_e( 'UK Return Shipping Coordinates', 'evg-platform' ); ?></h2>

                        <?php echo $profile_notice; ?>

                        <form action="<?php echo esc_url( get_permalink() ); ?>" method="post">
                            <?php wp_nonce_field( 'evg_update_profile_action', 'evg_update_profile_nonce' ); ?>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                                <div>
                                    <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'First Name', 'evg-platform' ); ?></label>
                                    <input type="text" name="first_name" class="evg-form-control" value="<?php echo esc_attr( $first_name ); ?>" required>
                                </div>
                                <div>
                                    <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Last Name', 'evg-platform' ); ?></label>
                                    <input type="text" name="last_name" class="evg-form-control" value="<?php echo esc_attr( $last_name ); ?>">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 25px;">
                                <div>
                                    <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Registered Email', 'evg-platform' ); ?></label>
                                    <input type="email" class="evg-form-control" value="<?php echo esc_attr( $user_email ); ?>" readonly>
                                </div>
                                <div>
                                    <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Contact Mobile Number', 'evg-platform' ); ?></label>
                                    <input type="tel" name="mobile_number" class="evg-form-control" value="<?php echo esc_attr( $mobile_number ); ?>">
                                </div>
                            </div>

                            <div style="border-top: 1px solid var(--evg-border-hairline); padding-top: 20px; margin-bottom: 18px;">
                                <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 15px 0;"><?php esc_html_e( 'UK Dispatch Address', 'evg-platform' ); ?></h3>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                                <div>
                                    <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'House Number / Name', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                                    <input type="text" name="house_number" class="evg-form-control" value="<?php echo esc_attr( $house_number ); ?>" required>
                                </div>
                                <div>
                                    <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Street Address', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                                    <input type="text" name="street_address" class="evg-form-control" value="<?php echo esc_attr( $street_address ); ?>" required>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 25px;">
                                <div>
                                    <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Town / City', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                                    <input type="text" name="town_city" class="evg-form-control" value="<?php echo esc_attr( $town_city ); ?>" required>
                                </div>
                                <div>
                                    <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'County', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                                    <input type="text" name="county" class="evg-form-control" value="<?php echo esc_attr( $county ); ?>" required>
                                </div>
                                <div>
                                    <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Postcode', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                                    <input type="text" name="postcode" class="evg-form-control" value="<?php echo esc_attr( $postcode ); ?>" required>
                                </div>
                            </div>

                            <button type="submit" class="btn-evg-executive">
                                <?php esc_html_e( 'Save & Update Coordinates', 'evg-platform' ); ?>
                            </button>
                        </form>
                    </div>
                </section>
            </main>

        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navButtons = document.querySelectorAll('.evg-nav-pills .nav-btn');
    const tabPanels  = document.querySelectorAll('.evg-tab-panel');

    navButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');

            navButtons.forEach(b => b.classList.remove('active'));
            tabPanels.forEach(p => p.classList.remove('active'));

            this.classList.add('active');
            const activePanel = document.getElementById(targetId);
            if (activePanel) {
                activePanel.classList.add('active');
            }
        });
    });
});
</script>

<?php get_footer(); ?>