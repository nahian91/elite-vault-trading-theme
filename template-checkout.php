<?php
/**
 * Template Name: Payment & Checkout - Executive Tier
 * Description: Stripe-exclusive checkout and payment authorization engine for Elite Vault Grading.
 *              Handles both grading submission consignments and marketplace certified slab acquisitions
 *              exclusively through Stripe, with polished executive address badge formatting.
 */

// -------------------------------------------------------------------------
// 1. BACKEND INITIALIZATION & DATA RESOLUTION
// -------------------------------------------------------------------------
global $wpdb;

$table_submissions = $wpdb->prefix . 'evg_submissions';
$table_cards       = $wpdb->prefix . 'evg_cards';
$table_marketplace = $wpdb->prefix . 'evg_marketplace';
$table_orders      = $wpdb->prefix . 'evg_orders';

// Fetch Stripe & Global Settings
$stripe_publishable_key = get_option( 'evg_stripe_publishable_key', 'pk_test_placeholder_key' );
$price_standard_fee     = floatval( get_option( 'evg_price_standard', 15.00 ) );
$price_upgrade_fee      = floatval( get_option( 'evg_price_premium_upgrade', 5.00 ) );
$accept_submissions     = get_option( 'evg_accept_submissions', 'yes' );

// Resolve User & Context
$current_user_id = get_current_user_id();
$checkout_type   = isset( $_GET['item_type'] ) && 'marketplace' === sanitize_key( $_GET['item_type'] ) ? 'marketplace' : 'submission';

$submission_id = isset( $_GET['submission_id'] ) ? absint( $_GET['submission_id'] ) : ( isset( $_POST['submission_id'] ) ? absint( $_POST['submission_id'] ) : 0 );
$item_id       = isset( $_GET['item_id'] ) ? absint( $_GET['item_id'] ) : ( isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0 );

$submission       = null;
$marketplace_item = null;
$cards            = array();
$checkout_error   = '';

// 1A. Resolve Grading Submission Data
if ( 'submission' === $checkout_type && $submission_id > 0 ) {
    $submission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_submissions} WHERE id = %d", $submission_id ) );
    if ( $submission ) {
        $cards = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_cards} WHERE submission_id = %d ORDER BY id ASC", $submission_id ) );
    }
}

// 1B. Resolve Marketplace Purchase Data
if ( 'marketplace' === $checkout_type && $item_id > 0 ) {
    $marketplace_item = $wpdb->get_row( $wpdb->prepare( "
        SELECT m.*, 
               COALESCE(NULLIF(m.card_title, ''), c.card_name, 'Certified Card') as display_title,
               COALESCE(NULLIF(m.set_name, ''), c.set_name, 'N/A') as display_set,
               COALESCE(NULLIF(m.card_number, ''), c.card_number, 'N/A') as display_number,
               COALESCE(NULLIF(m.language, ''), c.language, 'English') as display_lang,
               COALESCE(m.assigned_grade, c.final_grade) as display_grade,
               COALESCE(NULLIF(m.image_url, ''), c.front_image_url) as display_img
        FROM {$table_marketplace} m
        LEFT JOIN {$table_cards} c ON m.card_id = c.id
        WHERE m.id = %d AND m.status = 'Available'
    ", $item_id ) );

    if ( ! $marketplace_item ) {
        $checkout_error = __( 'The selected marketplace item is no longer available or has been acquired.', 'evg-platform' );
    }
}

// -------------------------------------------------------------------------
// 2. PAYMENT PROCESSING (STRIPE CONFIRMATION POST-BACK)
// -------------------------------------------------------------------------
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['evg_checkout_nonce'] ) ) {
    if ( wp_verify_nonce( sanitize_key( $_POST['evg_checkout_nonce'] ), 'evg_process_checkout_action' ) ) {
        
        $order_notes     = isset( $_POST['order_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['order_notes'] ) ) : '';
        $stripe_token_id = isset( $_POST['stripe_token_id'] ) ? sanitize_text_field( wp_unslash( $_POST['stripe_token_id'] ) ) : '';
        $terms_agreed    = isset( $_POST['terms_agree'] );

        if ( ! $terms_agreed ) {
            $checkout_error = __( 'You must review and accept the Vault Terms & Conditions to complete authorization.', 'evg-platform' );
        } elseif ( 'submission' === $checkout_type ) {
            if ( ! $submission ) {
                $checkout_error = __( 'Invalid submission reference identified.', 'evg-platform' );
            } else {
                // Update submission status to Paid & advance pipeline stage
                $wpdb->update(
                    $table_submissions,
                    array(
                        'payment_status' => 'Paid',
                        'current_stage'  => 'Cards Awaiting Arrival',
                    ),
                    array( 'id' => $submission->id ),
                    array( '%s', '%s' ),
                    array( '%d' )
                );

                if ( ! empty( $order_notes ) && $current_user_id > 0 ) {
                    update_user_meta( $current_user_id, 'evg_last_order_notes_' . $submission->id, $order_notes );
                }

                if ( class_exists( 'Elite_Vault_Grading_System' ) && method_exists( 'Elite_Vault_Grading_System', 'log_activity' ) ) {
                    Elite_Vault_Grading_System::log_activity( "Payment Authorized via Stripe for Order #{$submission->order_number}. Stage set to: Cards Awaiting Arrival." );
                }

                wp_safe_redirect( add_query_arg( array( 'order' => $submission->id, 'payment' => 'success' ), home_url( '/my-account' ) ) );
                exit;
            }
        } elseif ( 'marketplace' === $checkout_type ) {
            if ( ! $marketplace_item || $marketplace_item->stock_quantity < 1 ) {
                $checkout_error = __( 'This item has just sold out and cannot be purchased.', 'evg-platform' );
            } else {
                // Decrement stock and toggle sold status if quantity is zero
                $new_stock  = max( 0, intval( $marketplace_item->stock_quantity ) - 1 );
                $new_status = ( 0 === $new_stock ) ? 'Sold' : 'Available';

                $wpdb->update(
                    $table_marketplace,
                    array(
                        'stock_quantity' => $new_stock,
                        'status'         => $new_status,
                    ),
                    array( 'id' => $marketplace_item->id ),
                    array( '%d', '%s' ),
                    array( '%d' )
                );

                // Insert into marketplace purchases ledger
                $order_ref = 'MKT-' . date( 'Y' ) . '-' . strtoupper( wp_generate_password( 5, false, false ) );
                $wpdb->insert(
                    $table_orders,
                    array(
                        'order_number'    => $order_ref,
                        'customer_id'     => $current_user_id,
                        'card_id'         => $marketplace_item->card_id,
                        'amount_paid'     => floatval( $marketplace_item->price ),
                        'payment_status'  => 'Paid',
                        'shipping_status' => 'Processing',
                        'tracking_number' => '',
                        'purchased_at'    => current_time( 'mysql' ),
                    ),
                    array( '%s', '%d', '%d', '%f', '%s', '%s', '%s', '%s' )
                );

                if ( class_exists( 'Elite_Vault_Grading_System' ) && method_exists( 'Elite_Vault_Grading_System', 'log_activity' ) ) {
                    Elite_Vault_Grading_System::log_activity( "Marketplace item #{$marketplace_item->id} ({$marketplace_item->display_title}) purchased via Stripe by User #{$current_user_id}." );
                }

                wp_safe_redirect( add_query_arg( array( 'purchase' => 'success', 'item_id' => $marketplace_item->id ), home_url( '/my-account' ) ) );
                exit;
            }
        }
    }
}

// -------------------------------------------------------------------------
// 3. COMPUTED MANIFEST & PRICING PARAMETERS
// -------------------------------------------------------------------------
if ( 'marketplace' === $checkout_type && $marketplace_item ) {
    $order_number   = 'MKT-' . $marketplace_item->id . '-' . date( 'ymd' );
    $service_type   = __( 'Marketplace Certified Slab Acquisition', 'evg-platform' );
    $label_option   = $marketplace_item->slab_information ? $marketplace_item->slab_information : 'Elite Vault Protective Slab';
    $card_count     = 1;
    $subtotal       = floatval( $marketplace_item->price );
    $upgrade_total  = 0.00;
    $total_payable  = $subtotal;
} else {
    $order_number   = $submission ? $submission->order_number : 'EVG-' . date( 'Y' ) . '-PENDING';
    $service_type   = $submission ? $submission->service_type : 'Standard Grading Protocol';
    $label_option   = $submission ? $submission->label_option : 'Standard Vault Slab';
    $card_count     = $submission ? intval( $submission->total_cards ) : count( $cards );

    $has_premium_label = ! in_array( $label_option, array( 'Standard Label', 'Standard Vault Slab' ), true );
    $subtotal          = $card_count * $price_standard_fee;
    $upgrade_total     = $has_premium_label ? ( $card_count * $price_upgrade_fee ) : 0.00;
    $total_payable     = $submission ? floatval( $submission->total_amount ) : ( $subtotal + $upgrade_total );
}

// Customer Identity & UK Logistics Resolution
$customer_user  = $submission ? get_userdata( $submission->customer_id ) : ( $current_user_id ? wp_get_current_user() : null );
$customer_name  = $customer_user ? $customer_user->display_name : __( 'Guest Collector', 'evg-platform' );
$customer_mail  = $customer_user ? $customer_user->user_email : '';

$target_user_id = $customer_user ? $customer_user->ID : $current_user_id;
$house_number   = $target_user_id ? get_user_meta( $target_user_id, 'evg_house_number', true ) : '';
$street_address = $target_user_id ? get_user_meta( $target_user_id, 'evg_street_address', true ) : '';
$town_city      = $target_user_id ? get_user_meta( $target_user_id, 'evg_town_city', true ) : '';
$county         = $target_user_id ? get_user_meta( $target_user_id, 'evg_county', true ) : '';
$postcode       = $target_user_id ? get_user_meta( $target_user_id, 'evg_postcode', true ) : '';
$mobile_number  = $target_user_id ? get_user_meta( $target_user_id, 'evg_mobile_number', true ) : '';

get_header(); ?>

<script src="https://js.stripe.com/v3/"></script>

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
  }
  
  .evg-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 4rem 20px 6rem 20px;
  }

  .evg-title-xl { 
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(2.4rem, 4vw, 3.2rem); 
    font-weight: 600; 
    letter-spacing: -0.02em; 
    line-height: 1.1; 
    color: #ffffff;
    margin: 0 0 12px 0;
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

  .evg-checkout-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 28px;
    align-items: start;
  }

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
  .evg-form-control::placeholder { color: #4A4F5C; font-weight: 300; }

  /* Polished Luxury Address Card */
  .evg-address-card {
    background: linear-gradient(145deg, #09090b 0%, #0d0d11 100%);
    border: 1px solid #222228;
    border-left: 3px solid var(--evg-gold-primary);
    border-radius: 6px;
    padding: 20px 22px;
    position: relative;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
  }
  .evg-address-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--evg-border-hairline);
  }
  .evg-address-name {
    color: #ffffff;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .evg-address-badge {
    background: rgba(212, 175, 55, 0.12);
    border: 1px solid var(--evg-border-gold-faint);
    color: var(--evg-gold-primary);
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    padding: 3px 8px;
    border-radius: 3px;
    font-family: monospace;
  }
  .evg-address-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    font-size: 0.84rem;
    line-height: 1.6;
  }
  .evg-address-line {
    color: var(--evg-text-ash);
    margin: 0;
  }
  .evg-address-postcode {
    color: var(--evg-gold-light);
    font-weight: 700;
    font-family: monospace;
    font-size: 0.92rem;
    letter-spacing: 0.05em;
  }

  /* Stripe Card Element Box */
  #evg-stripe-card-element {
    background: var(--evg-obsidian-base);
    border: 1px solid var(--evg-border-hairline);
    border-radius: 6px;
    padding: 16px 14px;
    transition: border-color 0.2s ease;
  }
  #evg-stripe-card-element.StripeElement--focus {
    border-color: var(--evg-gold-primary);
    box-shadow: 0 0 0 1px var(--evg-gold-primary);
  }

  .evg-checkbox {
    appearance: none;
    -webkit-appearance: none;
    background-color: var(--evg-obsidian-base); 
    margin: 0;
    flex-shrink: 0; 
    display: grid; 
    place-content: center; 
    cursor: pointer; 
    transition: all 0.2s ease;
    width: 1.15em; 
    height: 1.15em; 
    border: 1px solid #2a2d35; 
    border-radius: 3px;
    margin-top: 3px;
  }
  .evg-checkbox::before {
    content: ""; 
    width: 0.65em; 
    height: 0.65em; 
    transform: scale(0); 
    transition: 120ms transform ease-in-out;
    background-color: var(--evg-gold-primary);
    clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
  }
  .evg-checkbox:checked { border-color: var(--evg-gold-primary); }
  .evg-checkbox:checked::before { transform: scale(1); }

  .evg-meta-list { list-style: none; padding: 0; margin: 0; }
  .evg-meta-list li {
    display: flex; 
    justify-content: space-between; 
    align-items: flex-start;
    padding: 0.9rem 0; 
    border-bottom: 1px solid var(--evg-border-hairline);
    font-size: 0.88rem;
  }
  .evg-meta-list li:last-child { border-bottom: none; }

  .btn-evg-executive {
    background: var(--evg-gold-primary); 
    color: var(--evg-text-charcoal) !important;
    font-size: 0.85rem; 
    font-weight: 800; 
    letter-spacing: 0.15em; 
    text-transform: uppercase;
    border: none; 
    border-radius: 4px; 
    padding: 1.25rem 2rem; 
    display: flex; 
    align-items: center; 
    justify-content: center;
    width: 100%; 
    transition: all 0.3s ease; 
    text-decoration: none; 
    cursor: pointer;
  }
  .btn-evg-executive:hover:not(:disabled) { 
    background: var(--evg-gold-light); 
    box-shadow: 0 0 25px rgba(212, 175, 55, 0.3); 
  }
  .btn-evg-executive:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  @media (max-width: 992px) {
    .evg-checkout-grid { grid-template-columns: 1fr; }
    .evg-address-details { grid-template-columns: 1fr; }
  }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- 1. EDITORIAL HEADER -->
        <header style="text-align: center; margin-bottom: 45px; padding-bottom: 25px; border-bottom: 1px solid var(--evg-border-hairline);">
            <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Stage 03 // Stripe Checkout', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Secure Stripe', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Authorization', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 680px; margin: 0 auto; font-size: 0.95rem; line-height: 1.6;">
                <?php esc_html_e( 'Review your manifest, verify your UK delivery address, and complete authorization via our Stripe gateway.', 'evg-platform' ); ?>
            </p>
        </header>

        <!-- Sold Out Notice if Disabled for Submissions -->
        <?php if ( 'submission' === $checkout_type && 'yes' !== $accept_submissions ) : ?>
            <div style="background: rgba(255, 69, 58, 0.08); border: 1px solid rgba(255, 69, 58, 0.3); border-radius: 6px; padding: 20px; text-align: center; margin-bottom: 30px;">
                <span class="evg-label-micro" style="color: #ff453a; margin-bottom: 6px;"><?php esc_html_e( 'INTAKE CAPACITY REACHED', 'evg-platform' ); ?></span>
                <h3 style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'GRADING CURRENTLY SOLD OUT', 'evg-platform' ); ?></h3>
                <p style="color: var(--evg-text-ash); font-size: 0.85rem; margin: 0;">
                    <?php esc_html_e( 'We have reached maximum capacity for the current drop. Active allocations can complete payment below.', 'evg-platform' ); ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Checkout Error Notice -->
        <div id="stripe-error-card" style="<?php echo empty( $checkout_error ) ? 'display:none;' : ''; ?> background: rgba(255, 69, 58, 0.08); border: 1px solid rgba(255, 69, 58, 0.3); border-radius: 4px; padding: 16px; margin-bottom: 30px;">
            <span style="color: #ff453a; font-weight: 700; font-size: 0.85rem; display: block; margin-bottom: 4px;">✕ <?php esc_html_e( 'Authorization Error', 'evg-platform' ); ?></span>
            <p id="stripe-error-message" style="color: #e5e5ea; font-size: 0.85rem; margin: 0;"><?php echo esc_html( $checkout_error ); ?></p>
        </div>

        <form action="<?php echo esc_url( get_permalink() ); ?>" method="post" id="evg-stripe-payment-form">
            <?php wp_nonce_field( 'evg_process_checkout_action', 'evg_checkout_nonce' ); ?>
            <input type="hidden" name="submission_id" value="<?php echo esc_attr( $submission ? $submission->id : 0 ); ?>">
            <input type="hidden" name="item_id" value="<?php echo esc_attr( $marketplace_item ? $marketplace_item->id : 0 ); ?>">
            <input type="hidden" name="item_type" value="<?php echo esc_attr( $checkout_type ); ?>">
            <input type="hidden" name="stripe_token_id" id="stripe_token_id" value="">

            <!-- 2. CHECKOUT GRID -->
            <div class="evg-checkout-grid">

                <!-- LEFT: CONSIGNMENT / ITEM MANIFEST & UK LOGISTICS -->
                <div>
                    <div class="evg-module" style="padding: 35px 30px;">
                        
                        <!-- Header -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid var(--evg-border-hairline); padding-bottom: 12px; margin-bottom: 25px;">
                            <h2 style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0;">
                                <?php echo ( 'marketplace' === $checkout_type ) ? esc_html__( 'Item Acquisition Manifest', 'evg-platform' ) : esc_html__( 'Submission Manifest', 'evg-platform' ); ?>
                            </h2>
                            <span style="color: var(--evg-gold-primary); font-size: 0.75rem; font-family: monospace; font-weight: 700;">
                                REF: #<?php echo esc_html( $order_number ); ?>
                            </span>
                        </div>

                        <!-- Metadata -->
                        <ul class="evg-meta-list" style="margin-bottom: 30px;">
                            <li>
                                <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Acquisition / Service Tier', 'evg-platform' ); ?></span>
                                <span style="color: #ffffff; font-weight: 600;"><?php echo esc_html( $service_type ); ?></span>
                            </li>
                            <li>
                                <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Slab Architecture', 'evg-platform' ); ?></span>
                                <span style="color: var(--evg-gold-light); font-weight: 600;"><?php echo esc_html( $label_option ); ?></span>
                            </li>
                            <li>
                                <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Client Account', 'evg-platform' ); ?></span>
                                <span style="text-align: right; color: #ffffff; font-weight: 600;">
                                    <?php echo esc_html( $customer_name ); ?><br>
                                    <?php if ( ! empty( $customer_mail ) ) : ?>
                                        <span style="color: var(--evg-text-ash); font-size: 0.78rem; font-weight: normal;"><?php echo esc_html( $customer_mail ); ?></span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        </ul>

                        <!-- Declared Cards / Marketplace Item Breakdown -->
                        <div style="margin-bottom: 30px;">
                            <span class="evg-label-micro" style="color: #ffffff; margin-bottom: 12px;">
                                <?php echo ( 'marketplace' === $checkout_type ) ? esc_html__( 'Certified Item Specification', 'evg-platform' ) : sprintf( esc_html__( 'Declared Asset Manifest (%d Units)', 'evg-platform' ), $card_count ); ?>
                            </span>
                            <div style="background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 18px; max-height: 220px; overflow-y: auto;">
                                <ul class="evg-meta-list" style="font-size: 0.8rem; font-family: monospace;">
                                    <?php if ( 'marketplace' === $checkout_type && $marketplace_item ) : ?>
                                        <li style="border-bottom: none; padding-bottom: 0;">
                                            <span style="color: #ffffff;">
                                                <?php echo esc_html( $marketplace_item->display_title . ' (' . $marketplace_item->display_set . ' #' . $marketplace_item->display_number . ')' ); ?>
                                            </span>
                                            <span style="color: var(--evg-gold-primary); font-weight: 700;">
                                                <?php echo $marketplace_item->display_grade ? 'EVG ' . esc_html( $marketplace_item->display_grade ) : 'RAW'; ?>
                                            </span>
                                        </li>
                                    <?php elseif ( ! empty( $cards ) ) : ?>
                                        <?php foreach ( $cards as $idx => $card ) : ?>
                                            <li style="<?php echo ( $idx === count( $cards ) - 1 ) ? 'border-bottom: none; padding-bottom: 0;' : ''; ?>">
                                                <span style="color: var(--evg-text-ash);">
                                                    <?php echo esc_html( sprintf( '%02d. %s (%s)', $idx + 1, $card->card_name, $card->set_name ) ); ?>
                                                    <?php if ( ! empty( $card->card_number ) ) : ?> #<?php echo esc_html( $card->card_number ); ?><?php endif; ?>
                                                </span>
                                                <span style="color: var(--evg-gold-primary); font-weight: 700;"><?php echo esc_html( strtoupper( substr( $card->language, 0, 3 ) ) ); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <li>
                                            <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'No cards currently logged in active manifest.', 'evg-platform' ); ?></span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <!-- Return Logistics Coordinates (Nicely Formatted) -->
                        <div style="margin-bottom: 30px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 12px;">
                                <span class="evg-label-micro" style="color: #ffffff; margin: 0;"><?php esc_html_e( 'UK Logistics Routing (Destination Address)', 'evg-platform' ); ?></span>
                                <a href="<?php echo esc_url( home_url( '/my-account' ) ); ?>" style="color: var(--evg-gold-primary); text-decoration: none; font-size: 0.68rem; font-family: monospace; font-weight: 700;">
                                    <?php esc_html_e( 'EDIT ADDRESS →', 'evg-platform' ); ?>
                                </a>
                            </div>
                            
                            <div class="evg-address-card">
                                <div class="evg-address-header">
                                    <div class="evg-address-name">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-primary)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                        </svg>
                                        <span><?php echo esc_html( $customer_name ); ?></span>
                                    </div>
                                    <span class="evg-address-badge"><?php esc_html_e( 'UK MAINLAND', 'evg-platform' ); ?></span>
                                </div>
                                <div class="evg-address-details">
                                    <div>
                                        <p class="evg-address-line" style="color: #ffffff; font-weight: 600;">
                                            <?php echo esc_html( trim( $house_number . ' ' . $street_address ) ? trim( $house_number . ' ' . $street_address ) : __( 'Street address unverified', 'evg-platform' ) ); ?>
                                        </p>
                                        <p class="evg-address-line">
                                            <?php echo esc_html( trim( $town_city . ( $county ? ', ' . $county : '' ) ) ); ?>
                                        </p>
                                        <p class="evg-address-line" style="font-size: 0.76rem; color: #5a5f6e; margin-top: 4px;">
                                            United Kingdom
                                        </p>
                                    </div>
                                    <div style="display: flex; flex-direction: column; justify-content: space-between; align-items: flex-end; text-align: right;">
                                        <div>
                                            <span style="font-size: 0.65rem; color: var(--evg-text-ash); text-transform: uppercase; letter-spacing: 0.1em; display: block;"><?php esc_html_e( 'POSTAL CODE', 'evg-platform' ); ?></span>
                                            <span class="evg-address-postcode"><?php echo esc_html( $postcode ? strtoupper( $postcode ) : 'NOT SET' ); ?></span>
                                        </div>
                                        <?php if ( ! empty( $mobile_number ) ) : ?>
                                            <span style="font-size: 0.75rem; color: var(--evg-text-ash); font-family: monospace;">
                                                📞 <?php echo esc_html( $mobile_number ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Special Directives -->
                        <div>
                            <span class="evg-label-micro" style="color: #ffffff; margin-bottom: 10px;"><?php esc_html_e( 'Special Handling Directives (Optional)', 'evg-platform' ); ?></span>
                            <textarea name="order_notes" rows="3" class="evg-form-control" placeholder="<?php esc_attr_e( 'Enter specific handling instructions or consignment notes for our grading desk...', 'evg-platform' ); ?>"></textarea>
                        </div>

                    </div>
                </div>

                <!-- RIGHT: FINANCIAL LEDGER & STRIPE PAYMENT -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    
                    <!-- Financial Ledger -->
                    <div class="evg-module" style="padding: 35px 30px;">
                        <h2 style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0 0 20px 0; border-bottom: 1px solid var(--evg-border-hairline); padding-bottom: 12px;">
                            <?php esc_html_e( 'Financial Ledger', 'evg-platform' ); ?>
                        </h2>
                        
                        <ul class="evg-meta-list" style="margin-bottom: 25px;">
                            <?php if ( 'marketplace' === $checkout_type ) : ?>
                                <li>
                                    <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Marketplace Certified Item', 'evg-platform' ); ?></span>
                                    <span style="color: #ffffff; font-weight: 600;">&pound;<?php echo esc_html( number_format( $subtotal, 2 ) ); ?></span>
                                </li>
                            <?php else : ?>
                                <li>
                                    <span style="color: var(--evg-text-ash);"><?php printf( esc_html__( 'Base Grading (%d Cards @ £%.2f)', 'evg-platform' ), $card_count, $price_standard_fee ); ?></span>
                                    <span style="color: #ffffff; font-weight: 600;">&pound;<?php echo esc_html( number_format( $subtotal, 2 ) ); ?></span>
                                </li>
                                <?php if ( $upgrade_total > 0 ) : ?>
                                    <li>
                                        <span style="color: var(--evg-text-ash);"><?php printf( esc_html__( 'Custom Label Upgrades (%d Cards @ +£%.2f)', 'evg-platform' ), $card_count, $price_upgrade_fee ); ?></span>
                                        <span style="color: var(--evg-gold-light); font-weight: 600;">&pound;<?php echo esc_html( number_format( $upgrade_total, 2 ) ); ?></span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <li>
                                <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Insured UK Delivery & Tracking', 'evg-platform' ); ?></span>
                                <span style="color: #34c759; font-family: monospace; font-size: 0.78rem; font-weight: 700;"><?php esc_html_e( 'INCLUDED', 'evg-platform' ); ?></span>
                            </li>
                        </ul>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: var(--evg-obsidian-elevated); border: 1px solid var(--evg-border-gold-faint); border-radius: 6px;">
                            <span style="color: #ffffff; font-weight: 600; font-size: 1rem;"><?php esc_html_e( 'Total Amount Payable', 'evg-platform' ); ?></span>
                            <span style="color: var(--evg-gold-primary); font-family: monospace; font-size: 1.6rem; font-weight: 800;">
                                &pound;<?php echo esc_html( number_format( $total_payable, 2 ) ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Stripe Card Element Module -->
                    <div class="evg-module" style="padding: 35px 30px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--evg-border-hairline); padding-bottom: 12px; margin-bottom: 20px;">
                            <h2 style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0;">
                                <?php esc_html_e( 'Stripe Secure Payment', 'evg-platform' ); ?>
                            </h2>
                            <span style="color: var(--evg-gold-primary); font-size: 0.75rem; font-family: monospace; font-weight: 700;">STRIPE VAULT</span>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Credit / Debit Card Details', 'evg-platform' ); ?></label>
                            <div id="evg-stripe-card-element">
                                <!-- Stripe Elements mounts card input here -->
                            </div>
                        </div>

                        <!-- Terms Agreement -->
                        <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 25px;">
                            <input class="evg-checkbox" type="checkbox" name="terms_agree" id="termsAgree" required>
                            <label style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; cursor: pointer; margin: 0;" for="termsAgree">
                                <?php esc_html_e( 'I have reviewed the manifest and agree to the', 'evg-platform' ); ?> <a href="<?php echo esc_url( home_url( '/terms' ) ); ?>" style="color: #ffffff; text-decoration: underline;"><?php esc_html_e( 'Vault Terms & Conditions', 'evg-platform' ); ?></a>. <span style="color: var(--evg-gold-primary);">*</span>
                            </label>
                        </div>

                        <!-- Authorization Button -->
                        <button type="submit" id="evg-stripe-submit-btn" class="btn-evg-executive" style="margin-bottom: 20px;">
                            <span id="btn-label-text"><?php printf( esc_html__( 'Authorize £%.2f with Stripe', 'evg-platform' ), $total_payable ); ?></span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left: 8px;">
                                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </button>

                        <div style="display: flex; align-items: center; justify-content: center; gap: 8px; color: var(--evg-text-ash); font-size: 0.7rem; font-family: monospace;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-muted)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span><?php esc_html_e( '256-BIT ENCRYPTED STRIPE SSL GATEWAY', 'evg-platform' ); ?></span>
                        </div>
                    </div>

                </div>

            </div>
        </form>

        <!-- 3. POST-AUTHORIZATION PROTOCOL -->
        <div style="margin-top: 50px; padding-top: 40px; border-top: 1px solid var(--evg-border-hairline);">
            <div style="text-align: center; margin-bottom: 30px;">
                <span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Next Steps', 'evg-platform' ); ?></span>
                <h3 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'Post-Authorization Protocol', 'evg-platform' ); ?></h3>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; text-align: center;">
                <div style="background: var(--evg-obsidian-panel); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 25px 20px;">
                    <span style="color: var(--evg-gold-primary); font-family: monospace; font-size: 1.4rem; font-weight: 800; display: block; margin-bottom: 8px;">01</span>
                    <p style="color: #ffffff; font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Payment is verified via secure 256-bit encrypted Stripe handshake.', 'evg-platform' ); ?></p>
                </div>
                <div style="background: var(--evg-obsidian-panel); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 25px 20px;">
                    <span style="color: var(--evg-gold-primary); font-family: monospace; font-size: 1.4rem; font-weight: 800; display: block; margin-bottom: 8px;">02</span>
                    <p style="color: #ffffff; font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'System generates your PDF manifest invoice and packaging packing slip.', 'evg-platform' ); ?></p>
                </div>
                <div style="background: var(--evg-obsidian-panel); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 25px 20px;">
                    <span style="color: var(--evg-gold-primary); font-family: monospace; font-size: 1.4rem; font-weight: 800; display: block; margin-bottom: 8px;">03</span>
                    <p style="color: #ffffff; font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'You securely package and dispatch your cards to our UK facility.', 'evg-platform' ); ?></p>
                </div>
                <div style="background: var(--evg-obsidian-panel); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 25px 20px;">
                    <span style="color: var(--evg-gold-primary); font-family: monospace; font-size: 1.4rem; font-weight: 800; display: block; margin-bottom: 8px;">04</span>
                    <p style="color: #ffffff; font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Live stage tracking telemetry activates upon our barcode scan check-in.', 'evg-platform' ); ?></p>
                </div>
            </div>
        </div>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var stripeKey = '<?php echo esc_js( $stripe_publishable_key ); ?>';
    var stripe = Stripe(stripeKey);
    var elements = stripe.elements();

    var style = {
        base: {
            color: '#ffffff',
            fontFamily: '"Montserrat", sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '15px',
            '::placeholder': {
                color: '#4a4f5c'
            }
        },
        invalid: {
            color: '#ff453a',
            iconColor: '#ff453a'
        }
    };

    var cardElement = elements.create('card', { style: style });
    cardElement.mount('#evg-stripe-card-element');

    var form = document.getElementById('evg-stripe-payment-form');
    var submitBtn = document.getElementById('evg-stripe-submit-btn');
    var btnLabel = document.getElementById('btn-label-text');
    var errorCard = document.getElementById('stripe-error-card');
    var errorMsg = document.getElementById('stripe-error-message');

    cardElement.on('change', function(event) {
        if (event.error) {
            errorCard.style.display = 'block';
            errorMsg.textContent = event.error.message;
        } else {
            errorCard.style.display = 'none';
            errorMsg.textContent = '';
        }
    });

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        submitBtn.disabled = true;
        btnLabel.textContent = 'Authorizing with Stripe...';

        stripe.createToken(cardElement).then(function(result) {
            if (result.error) {
                errorCard.style.display = 'block';
                errorMsg.textContent = result.error.message;
                submitBtn.disabled = false;
                btnLabel.textContent = 'Authorize &pound;<?php echo esc_js( number_format( $total_payable, 2 ) ); ?> with Stripe';
            } else {
                document.getElementById('stripe_token_id').value = result.token.id;
                form.submit();
            }
        });
    });
});
</script>

<?php get_footer(); ?>