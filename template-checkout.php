<?php
/**
 * Template Name: Payment & Checkout - Executive Tier
 * Description: Stripe-exclusive checkout and payment authorization engine for Elite Vault Grading.
 *              Handles grading submission consignments, marketplace certified slab acquisitions,
 *              and microscopic fault portfolio unlocks (£0.99) exclusively through Stripe,
 *              with polished executive address badge formatting and updated £6.99 shipping fee.
 *
 * @package evg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -------------------------------------------------------------------------
// 1. BACKEND INITIALIZATION & AUTHENTICATION RESOLUTION
// -------------------------------------------------------------------------
global $wpdb;

$evg_table_submissions = $wpdb->prefix . 'evg_submissions';
$evg_table_cards       = $wpdb->prefix . 'evg_cards';
$evg_table_marketplace = $wpdb->prefix . 'evg_marketplace';
$evg_table_orders      = $wpdb->prefix . 'evg_orders';
$evg_table_unlocks     = $wpdb->prefix . 'evg_portfolio_unlocks';

// Gating: Require user authentication so receipts and unlocks associate with an account.
if ( ! is_user_logged_in() ) {
	$evg_request_uri     = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$evg_redirect_target = add_query_arg( array(), home_url( $evg_request_uri ) );
	wp_safe_redirect( add_query_arg( 'redirect_to', $evg_redirect_target, home_url( '/sign-in' ) ) );
	exit;
}

$evg_current_user    = wp_get_current_user();
$evg_current_user_id = $evg_current_user->ID;

// Fetch Global Settings.
$evg_price_standard_fee     = (float) get_option( 'evg_price_standard', 9.99 );
$evg_price_upgrade_fee      = (float) get_option( 'evg_price_premium_upgrade', 2.99 );
$evg_portfolio_unlock_fee   = (float) get_option( 'evg_portfolio_unlock_fee', 0.99 );
$evg_shipping_fee_standard  = (float) get_option( 'evg_return_shipping_fee', 6.99 );
$evg_turnaround_time        = get_option( 'evg_turnaround_time', '10-15 Business Days' );
$evg_accept_submissions     = get_option( 'evg_accept_submissions', 'yes' );

// Determine checkout mode: 'submission', 'marketplace', or 'unlock_portfolio'.
// phpcs:disable WordPress.Security.NonceVerification.Recommended
$evg_checkout_type = 'submission';
if ( isset( $_GET['action'] ) && 'unlock_portfolio' === sanitize_key( wp_unslash( $_GET['action'] ) ) ) {
	$evg_checkout_type = 'unlock_portfolio';
} elseif ( ( isset( $_GET['item_type'] ) && 'marketplace' === sanitize_key( wp_unslash( $_GET['item_type'] ) ) ) || ( isset( $_POST['item_type'] ) && 'marketplace' === sanitize_key( wp_unslash( $_POST['item_type'] ) ) ) ) {
	$evg_checkout_type = 'marketplace';
}

$evg_submission_id = isset( $_REQUEST['submission_id'] ) ? absint( $_REQUEST['submission_id'] ) : 0;
$evg_item_id       = isset( $_REQUEST['item_id'] ) ? absint( $_REQUEST['item_id'] ) : 0;
$evg_card_id       = isset( $_REQUEST['card_id'] ) ? absint( $_REQUEST['card_id'] ) : 0;
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$evg_submission       = null;
$evg_marketplace_item = null;
$evg_unlock_card      = null;
$evg_cards            = array();
$evg_checkout_error   = '';

// 1A. Resolve Grading Submission Data.
if ( 'submission' === $evg_checkout_type && $evg_submission_id > 0 ) {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$evg_submission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$evg_table_submissions} WHERE id = %d AND customer_id = %d", $evg_submission_id, $evg_current_user_id ) );
	if ( $evg_submission ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$evg_cards = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$evg_table_cards} WHERE submission_id = %d ORDER BY id ASC", $evg_submission_id ) );
	} else {
		$evg_checkout_error = __( 'The requested grading submission could not be verified for this account.', 'evg' );
	}
}

// 1B. Resolve Marketplace Purchase Data.
if ( 'marketplace' === $evg_checkout_type && $evg_item_id > 0 ) {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$evg_marketplace_item = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT m.*, 
					COALESCE(NULLIF(m.card_title, ''), c.card_name, 'Certified Card') as display_title,
					COALESCE(NULLIF(m.set_name, ''), c.set_name, 'N/A') as display_set,
					COALESCE(NULLIF(m.card_number, ''), c.card_number, 'N/A') as display_number,
					COALESCE(NULLIF(m.language, ''), c.language, 'English') as display_lang,
					COALESCE(m.assigned_grade, c.final_grade) as display_grade,
					COALESCE(NULLIF(m.image_url, ''), c.front_image_url) as display_img
			FROM {$evg_table_marketplace} m
			LEFT JOIN {$evg_table_cards} c ON m.card_id = c.id
			WHERE m.id = %d AND m.status = 'Available'",
			$evg_item_id
		)
	);

	if ( ! $evg_marketplace_item ) {
		$evg_checkout_error = __( 'The selected marketplace item is no longer available or has been acquired.', 'evg' );
	}
}

// 1C. Resolve Damage Portfolio Unlock Data.
if ( 'unlock_portfolio' === $evg_checkout_type && $evg_card_id > 0 ) {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$evg_unlock_card = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT c.*, s.order_number 
			FROM {$evg_table_cards} c
			LEFT JOIN {$evg_table_submissions} s ON c.submission_id = s.id
			WHERE c.id = %d",
			$evg_card_id
		)
	);

	if ( ! $evg_unlock_card ) {
		$evg_checkout_error = __( 'The certified card reference for this portfolio unlock request was not found.', 'evg' );
	}
}

// -------------------------------------------------------------------------
// 2. COMPUTED MANIFEST & PRICING PARAMETERS
// -------------------------------------------------------------------------
if ( 'marketplace' === $evg_checkout_type && $evg_marketplace_item ) {
	$evg_order_number   = 'MKT-' . $evg_marketplace_item->id . '-' . gmdate( 'ymd' );
	$evg_service_type   = __( 'Marketplace Certified Slab Acquisition', 'evg' );
	$evg_label_option   = ! empty( $evg_marketplace_item->slab_information ) ? $evg_marketplace_item->slab_information : 'Elite Vault Protective Slab';
	$evg_card_count     = 1;
	$evg_subtotal       = (float) $evg_marketplace_item->price;
	$evg_upgrade_total  = 0.00;
	$evg_shipping_total = 0.00;
	$evg_total_payable  = $evg_subtotal;
} elseif ( 'unlock_portfolio' === $evg_checkout_type && $evg_unlock_card ) {
	$evg_order_number   = 'UNLK-' . $evg_unlock_card->id . '-' . gmdate( 'ymd' );
	$evg_service_type   = __( 'Damage Portfolio Full Telemetry Unlock', 'evg' );
	$evg_label_option   = __( 'Digital Access & Microscopic Evidence Report', 'evg' );
	$evg_card_count     = 1;
	$evg_subtotal       = $evg_portfolio_unlock_fee;
	$evg_upgrade_total  = 0.00;
	$evg_shipping_total = 0.00;
	$evg_total_payable  = $evg_portfolio_unlock_fee;
} else {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$evg_order_number = $evg_submission ? $evg_submission->order_number : 'EVG-' . gmdate( 'Y' ) . '-INTAKE';
	$evg_service_type = $evg_submission ? $evg_submission->service_type : ( isset( $_REQUEST['service_speed_tier'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['service_speed_tier'] ) ) : 'Standard (10-15 Business Days)' );
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
	$evg_label_option = $evg_submission ? $evg_submission->label_option : 'Standard Label';
	$evg_card_count   = $evg_submission ? absint( $evg_submission->total_cards ) : count( $evg_cards );

	$evg_has_premium_label = ( false !== stripos( $evg_label_option, 'gold' ) || false !== stripos( $evg_label_option, 'custom' ) || false !== stripos( $evg_label_option, 'vault door' ) );
	$evg_subtotal          = $evg_card_count * $evg_price_standard_fee;
	$evg_upgrade_total     = $evg_has_premium_label ? ( $evg_card_count * $evg_price_upgrade_fee ) : 0.00;
	$evg_shipping_total    = ( isset( $evg_submission->fulfilment_type ) && 'collection' === $evg_submission->fulfilment_type ) ? 0.00 : $evg_shipping_fee_standard;
	$evg_total_payable     = $evg_submission ? (float) $evg_submission->total_amount : ( $evg_subtotal + $evg_upgrade_total + $evg_shipping_total );
}

// User Address Verification.
$evg_customer_name  = $evg_current_user->display_name ? $evg_current_user->display_name : $evg_current_user->user_login;
$evg_customer_mail  = $evg_current_user->user_email;
$evg_house_number   = get_user_meta( $evg_current_user_id, 'evg_house_number', true );
$evg_street_address = get_user_meta( $evg_current_user_id, 'evg_street_address', true );
$evg_town_city      = get_user_meta( $evg_current_user_id, 'evg_town_city', true );
$evg_county         = get_user_meta( $evg_current_user_id, 'evg_county', true );
$evg_postcode       = get_user_meta( $evg_current_user_id, 'evg_postcode', true );
$evg_mobile_number  = get_user_meta( $evg_current_user_id, 'evg_mobile_number', true );

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
		max-width: 1200px;
		margin: 0 auto;
		padding: 3rem 15px 5rem 15px;
	}

	.evg-title-xl { 
		font-family: "Playfair Display", Georgia, serif;
		font-size: clamp(2rem, 4vw, 3.2rem); 
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
		gap: 24px;
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
		padding: 18px 20px;
		position: relative;
		box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
	}
	.evg-address-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 12px;
		padding-bottom: 10px;
		border-bottom: 1px solid var(--evg-border-hairline);
	}
	.evg-address-name {
		color: #ffffff;
		font-size: 0.95rem;
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
		font-size: 0.62rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: 0.12em;
		padding: 2px 6px;
		border-radius: 3px;
		font-family: monospace;
	}
	.evg-address-details {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 12px;
		font-size: 0.82rem;
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
		font-size: 0.9rem;
		letter-spacing: 0.05em;
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
		padding: 0.8rem 0; 
		border-bottom: 1px solid var(--evg-border-hairline);
		font-size: 0.85rem;
		gap: 10px;
	}
	.evg-meta-list li:last-child { border-bottom: none; }

	.btn-evg-executive {
		background: var(--evg-gold-primary); 
		color: var(--evg-text-charcoal) !important;
		font-size: 0.82rem; 
		font-weight: 800; 
		letter-spacing: 0.15em; 
		text-transform: uppercase;
		border: none; 
		border-radius: 4px; 
		padding: 1.1rem 1.5rem; 
		display: flex; 
		align-items: center; 
		justify-content: center;
		width: 100%; 
		transition: all 0.3s ease; 
		text-decoration: none; 
		cursor: pointer;
		box-sizing: border-box;
	}
	.btn-evg-executive:hover:not(:disabled) { 
		background: var(--evg-gold-light); 
		box-shadow: 0 0 25px rgba(212, 175, 55, 0.3); 
	}
	.btn-evg-executive:disabled {
		opacity: 0.6;
		cursor: not-allowed;
	}

	@media (max-width: 991.98px) {
		.evg-checkout-grid { grid-template-columns: 1fr; }
	}

	@media (max-width: 767.98px) {
		.evg-container { padding: 2rem 15px 4rem 15px; }
		.evg-address-details { grid-template-columns: 1fr; gap: 8px; }
		.evg-module { padding: 25px 15px !important; }
	}
</style>

<main class="evg-master-wrapper">
	<div class="evg-container">

		<!-- 1. EDITORIAL HEADER -->
		<header style="text-align: center; margin-bottom: 35px; padding-bottom: 20px; border-bottom: 1px solid var(--evg-border-hairline);">
			<span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Stage 03 // Stripe Checkout', 'evg' ); ?></span>
			<h1 class="evg-title-xl"><?php esc_html_e( 'Secure Stripe', 'evg' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Authorization', 'evg' ); ?></span></h1>
			<p style="color: var(--evg-text-ash); max-width: 680px; margin: 0 auto; font-size: 0.92rem; line-height: 1.6;">
				<?php esc_html_e( 'Review your manifest, verify your UK delivery address, and proceed directly to Stripe for payment.', 'evg' ); ?>
			</p>
		</header>

		<!-- Sold Out Notice if Disabled for Submissions -->
		<?php if ( 'submission' === $evg_checkout_type && 'yes' !== $evg_accept_submissions ) : ?>
			<div style="background: rgba(255, 69, 58, 0.08); border: 1px solid rgba(255, 69, 58, 0.3); border-radius: 6px; padding: 20px; text-align: center; margin-bottom: 25px;">
				<span class="evg-label-micro" style="color: #ff453a; margin-bottom: 6px;"><?php esc_html_e( 'INTAKE CAPACITY REACHED', 'evg' ); ?></span>
				<h3 style="color: #ffffff; font-size: 1.15rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'GRADING CURRENTLY SOLD OUT', 'evg' ); ?></h3>
				<p style="color: var(--evg-text-ash); font-size: 0.82rem; margin: 0;">
					<?php esc_html_e( 'We have reached maximum capacity for the current intake window. Existing consignments can finalize payment below.', 'evg' ); ?>
				</p>
			</div>
		<?php endif; ?>

		<!-- Checkout Error Notice -->
		<div id="stripe-error-card" style="<?php echo empty( $evg_checkout_error ) ? 'display:none;' : ''; ?> background: rgba(255, 69, 58, 0.08); border: 1px solid rgba(255, 69, 58, 0.3); border-radius: 4px; padding: 14px; margin-bottom: 25px;">
			<span style="color: #ff453a; font-weight: 700; font-size: 0.82rem; display: block; margin-bottom: 4px;">&times; <?php esc_html_e( 'Authorization Error', 'evg' ); ?></span>
			<p id="stripe-error-message" style="color: #e5e5ea; font-size: 0.82rem; margin: 0;"><?php echo esc_html( $evg_checkout_error ); ?></p>
		</div>

		<!-- FORM POSTS TO BACKEND ADMIN-POST HANDLER -->
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="evg-stripe-payment-form">
			<input type="hidden" name="action" value="evg_direct_stripe_checkout">
			<?php wp_nonce_field( 'evg_stripe_checkout_action', 'evg_checkout_nonce' ); ?>
			<input type="hidden" name="submission_id" value="<?php echo esc_attr( $evg_submission ? $evg_submission->id : 0 ); ?>">
			<input type="hidden" name="item_id" value="<?php echo esc_attr( $evg_marketplace_item ? $evg_marketplace_item->id : 0 ); ?>">
			<input type="hidden" name="card_id" value="<?php echo esc_attr( $evg_unlock_card ? $evg_unlock_card->id : 0 ); ?>">
			<input type="hidden" name="item_type" value="<?php echo esc_attr( $evg_checkout_type ); ?>">

			<!-- Metadata for Stripe Session & Customer Mapping -->
			<input type="hidden" name="first_name" value="<?php echo esc_attr( $evg_current_user->first_name ? $evg_current_user->first_name : $evg_customer_name ); ?>">
			<input type="hidden" name="last_name" value="<?php echo esc_attr( $evg_current_user->last_name ); ?>">
			<input type="hidden" name="customer_email" value="<?php echo esc_attr( $evg_customer_mail ); ?>">
			<input type="hidden" name="mobile_number" value="<?php echo esc_attr( $evg_mobile_number ); ?>">
			<input type="hidden" name="address_line_1" value="<?php echo esc_attr( trim( $evg_house_number . ' ' . $evg_street_address ) ); ?>">
			<input type="hidden" name="city" value="<?php echo esc_attr( $evg_town_city ); ?>">
			<input type="hidden" name="county" value="<?php echo esc_attr( $evg_county ); ?>">
			<input type="hidden" name="postcode" value="<?php echo esc_attr( $evg_postcode ); ?>">
			<input type="hidden" name="service_speed_tier" value="<?php echo esc_attr( $evg_service_type ); ?>">
			<input type="hidden" name="fulfilment_method" value="<?php echo esc_attr( $evg_submission && isset( $evg_submission->fulfilment_type ) ? $evg_submission->fulfilment_type : 'postage' ); ?>">
			<input type="hidden" name="slab_label_tier" value="<?php echo esc_attr( $evg_label_option ); ?>">
			<input type="hidden" name="calculated_total_amount" value="<?php echo esc_attr( number_format( (float) $evg_total_payable, 2, '.', '' ) ); ?>">

			<!-- 2. CHECKOUT GRID -->
			<div class="evg-checkout-grid">

				<!-- LEFT: CONSIGNMENT / ITEM MANIFEST & UK LOGISTICS -->
				<div>
					<div class="evg-module" style="padding: 30px 20px;">

						<!-- Header -->
						<div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid var(--evg-border-hairline); padding-bottom: 12px; margin-bottom: 20px; flex-wrap: wrap; gap: 8px;">
							<h2 style="color: #ffffff; font-size: 1.15rem; font-weight: 700; margin: 0;">
								<?php
								if ( 'marketplace' === $evg_checkout_type ) {
									esc_html_e( 'Item Acquisition Manifest', 'evg' );
								} elseif ( 'unlock_portfolio' === $evg_checkout_type ) {
									esc_html_e( 'Telemetry Unlock Manifest', 'evg' );
								} else {
									esc_html_e( 'Submission Manifest', 'evg' );
								}
								?>
							</h2>
							<span style="color: var(--evg-gold-primary); font-size: 0.72rem; font-family: monospace; font-weight: 700;">
								<?php
								printf(
									/* translators: %s: Order identifier or reference number */
									esc_html__( 'REF: #%s', 'evg' ),
									esc_html( $evg_order_number )
								);
								?>
							</span>
						</div>

						<!-- Metadata -->
						<ul class="evg-meta-list" style="margin-bottom: 25px;">
							<li>
								<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Service / Acquisition Tier', 'evg' ); ?></span>
								<span style="color: #ffffff; font-weight: 600; text-align: right;"><?php echo esc_html( $evg_service_type ); ?></span>
							</li>
							<li>
								<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Slab Specification / Access', 'evg' ); ?></span>
								<span style="color: var(--evg-gold-light); font-weight: 600; text-align: right;"><?php echo esc_html( $evg_label_option ); ?></span>
							</li>
							<?php if ( 'submission' === $evg_checkout_type ) : ?>
								<li>
									<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Turnaround Commitment', 'evg' ); ?></span>
									<span style="color: #ffffff; font-family: monospace; font-weight: 700;"><?php echo esc_html( $evg_service_type ); ?></span>
								</li>
							<?php endif; ?>
							<li>
								<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Client Account', 'evg' ); ?></span>
								<span style="text-align: right; color: #ffffff; font-weight: 600; word-break: break-all;">
									<?php echo esc_html( $evg_customer_name ); ?><br>
									<?php if ( ! empty( $evg_customer_mail ) ) : ?>
										<span style="color: var(--evg-text-ash); font-size: 0.75rem; font-weight: normal;"><?php echo esc_html( $evg_customer_mail ); ?></span>
									<?php endif; ?>
								</span>
							</li>
						</ul>

						<!-- Declared Cards / Marketplace Item Breakdown -->
						<div style="margin-bottom: 25px;">
							<span class="evg-label-micro" style="color: #ffffff; margin-bottom: 10px;">
								<?php
								if ( 'marketplace' === $evg_checkout_type ) {
									esc_html_e( 'Certified Item Specification', 'evg' );
								} elseif ( 'unlock_portfolio' === $evg_checkout_type ) {
									esc_html_e( 'Target Specimen Scans', 'evg' );
								} else {
									printf(
										/* translators: %s: Number of cards declared in submission */
										esc_html__( 'Declared Asset Manifest (%s Units)', 'evg' ),
										esc_html( number_format_i18n( $evg_card_count ) )
									);
								}
								?>
							</span>
							<div style="background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 15px; max-height: 200px; overflow-y: auto;">
								<ul class="evg-meta-list" style="font-size: 0.78rem; font-family: monospace;">
									<?php if ( 'marketplace' === $evg_checkout_type && $evg_marketplace_item ) : ?>
										<li style="border-bottom: none; padding-bottom: 0;">
											<span style="color: #ffffff;">
												<?php echo esc_html( $evg_marketplace_item->display_title . ' (' . $evg_marketplace_item->display_set . ' #' . $evg_marketplace_item->display_number . ')' ); ?>
											</span>
											<span style="color: var(--evg-gold-primary); font-weight: 700;">
												<?php echo ! empty( $evg_marketplace_item->display_grade ) ? esc_html( 'EVG ' . $evg_marketplace_item->display_grade ) : esc_html__( 'RAW', 'evg' ); ?>
											</span>
										</li>
									<?php elseif ( 'unlock_portfolio' === $evg_checkout_type && $evg_unlock_card ) : ?>
										<li style="border-bottom: none; padding-bottom: 0;">
											<span style="color: #ffffff;">
												<?php echo esc_html( $evg_unlock_card->card_name . ' (' . $evg_unlock_card->set_name . ' #' . $evg_unlock_card->card_number . ')' ); ?>
											</span>
											<span style="color: var(--evg-gold-primary); font-weight: 700;">
												<?php echo esc_html( 'CERT: EVG-' . str_pad( (string) $evg_unlock_card->id, 5, '0', STR_PAD_LEFT ) ); ?>
											</span>
										</li>
									<?php elseif ( ! empty( $evg_cards ) ) : ?>
										<?php foreach ( $evg_cards as $idx => $card ) : ?>
											<li style="<?php echo ( $idx === count( $evg_cards ) - 1 ) ? 'border-bottom: none; padding-bottom: 0;' : ''; ?>">
												<span style="color: var(--evg-text-ash);">
													<?php echo esc_html( sprintf( '%02d. %s (%s)', $idx + 1, $card->card_name, $card->set_name ) ); ?>
													<?php if ( ! empty( $card->card_number ) ) : ?>
														#<?php echo esc_html( $card->card_number ); ?>
													<?php endif; ?>
												</span>
												<span style="color: var(--evg-gold-primary); font-weight: 700;">
													<?php echo esc_html( strtoupper( substr( (string) $card->language, 0, 3 ) ) ); ?>
												</span>
											</li>
										<?php endforeach; ?>
									<?php else : ?>
										<li>
											<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'No cards currently logged in active manifest.', 'evg' ); ?></span>
										</li>
									<?php endif; ?>
								</ul>
							</div>
						</div>

						<!-- Return Logistics Coordinates -->
						<?php if ( 'unlock_portfolio' !== $evg_checkout_type ) : ?>
							<div style="margin-bottom: 25px;">
								<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 10px; flex-wrap: wrap; gap: 4px;">
									<span class="evg-label-micro" style="color: #ffffff; margin: 0;"><?php esc_html_e( 'UK Logistics Routing (Destination Address)', 'evg' ); ?></span>
									<a href="<?php echo esc_url( home_url( '/my-account' ) ); ?>" style="color: var(--evg-gold-primary); text-decoration: none; font-size: 0.68rem; font-family: monospace; font-weight: 700;">
										<?php esc_html_e( 'EDIT ADDRESS &rarr;', 'evg' ); ?>
									</a>
								</div>

								<div class="evg-address-card">
									<div class="evg-address-header">
										<div class="evg-address-name">
											<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-primary)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
												<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
											</svg>
											<span><?php echo esc_html( $evg_customer_name ); ?></span>
										</div>
										<span class="evg-address-badge"><?php esc_html_e( 'UK MAINLAND', 'evg' ); ?></span>
									</div>
									<div class="evg-address-details">
										<div>
											<p class="evg-address-line" style="color: #ffffff; font-weight: 600;">
												<?php echo esc_html( trim( $evg_house_number . ' ' . $evg_street_address ) ? trim( $evg_house_number . ' ' . $evg_street_address ) : __( 'Street address unverified', 'evg' ) ); ?>
											</p>
											<p class="evg-address-line">
												<?php echo esc_html( trim( $evg_town_city . ( $evg_county ? ', ' . $evg_county : '' ) ) ); ?>
											</p>
											<p class="evg-address-line" style="font-size: 0.74rem; color: #5a5f6e; margin-top: 4px;">
												<?php esc_html_e( 'United Kingdom', 'evg' ); ?>
											</p>
										</div>
										<div style="display: flex; flex-direction: column; justify-content: space-between; align-items: flex-end; text-align: right;">
											<div>
												<span style="font-size: 0.62rem; color: var(--evg-text-ash); text-transform: uppercase; letter-spacing: 0.1em; display: block;"><?php esc_html_e( 'POSTAL CODE', 'evg' ); ?></span>
												<span class="evg-address-postcode"><?php echo esc_html( $evg_postcode ? strtoupper( $evg_postcode ) : __( 'NOT SET', 'evg' ) ); ?></span>
											</div>
											<?php if ( ! empty( $evg_mobile_number ) ) : ?>
												<span style="font-size: 0.72rem; color: var(--evg-text-ash); font-family: monospace; margin-top: 4px;">
													&#128222; <?php echo esc_html( $evg_mobile_number ); ?>
												</span>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<!-- Special Directives -->
						<div>
							<span class="evg-label-micro" style="color: #ffffff; margin-bottom: 8px;"><?php esc_html_e( 'Special Handling Directives (Optional)', 'evg' ); ?></span>
							<textarea name="submission_notes" rows="3" class="evg-form-control" placeholder="<?php esc_attr_e( 'Enter specific handling instructions or consignment notes for our grading desk...', 'evg' ); ?>"></textarea>
						</div>

					</div>
				</div>

				<!-- RIGHT: FINANCIAL LEDGER & STRIPE CHECKOUT -->
				<div style="display: flex; flex-direction: column; gap: 20px;">

					<!-- Financial Ledger -->
					<div class="evg-module" style="padding: 30px 20px;">
						<h2 style="color: #ffffff; font-size: 1.15rem; font-weight: 700; margin: 0 0 16px 0; border-bottom: 1px solid var(--evg-border-hairline); padding-bottom: 12px;">
							<?php esc_html_e( 'Financial Ledger', 'evg' ); ?>
						</h2>

						<ul class="evg-meta-list" style="margin-bottom: 20px;">
							<?php if ( 'marketplace' === $evg_checkout_type ) : ?>
								<li>
									<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Marketplace Certified Item', 'evg' ); ?></span>
									<span style="color: #ffffff; font-weight: 600;">&pound;<?php echo esc_html( number_format_i18n( (float) $evg_subtotal, 2 ) ); ?></span>
								</li>
								<li>
									<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Insured UK Delivery & Tracking', 'evg' ); ?></span>
									<span style="color: #34c759; font-family: monospace; font-size: 0.75rem; font-weight: 700;"><?php esc_html_e( 'INCLUDED', 'evg' ); ?></span>
								</li>
							<?php elseif ( 'unlock_portfolio' === $evg_checkout_type ) : ?>
								<li>
									<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Full Damage Portfolio Unlock Fee', 'evg' ); ?></span>
									<span style="color: #ffffff; font-weight: 600;">&pound;<?php echo esc_html( number_format_i18n( (float) $evg_subtotal, 2 ) ); ?></span>
								</li>
								<li>
									<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Access Fulfillment', 'evg' ); ?></span>
									<span style="color: #34c759; font-family: monospace; font-size: 0.75rem; font-weight: 700;"><?php esc_html_e( 'INSTANT UNLOCK', 'evg' ); ?></span>
								</li>
							<?php else : ?>
								<li>
									<span style="color: var(--evg-text-ash);">
										<?php
										printf(
											/* translators: 1: Card count, 2: Standard unit price */
											esc_html__( 'Base Grading (%1$s Cards @ £%2$s)', 'evg' ),
											esc_html( number_format_i18n( $evg_card_count ) ),
											esc_html( number_format_i18n( $evg_price_standard_fee, 2 ) )
										);
										?>
									</span>
									<span style="color: #ffffff; font-weight: 600;">&pound;<?php echo esc_html( number_format_i18n( (float) $evg_subtotal, 2 ) ); ?></span>
								</li>
								<?php if ( $evg_upgrade_total > 0 ) : ?>
									<li>
										<span style="color: var(--evg-text-ash);">
											<?php
											printf(
												/* translators: 1: Card count, 2: Upgrade unit price */
												esc_html__( 'Custom Label Upgrades (%1$s Cards @ +£%2$s)', 'evg' ),
												esc_html( number_format_i18n( $evg_card_count ) ),
												esc_html( number_format_i18n( $evg_price_upgrade_fee, 2 ) )
											);
											?>
										</span>
										<span style="color: var(--evg-gold-light); font-weight: 600;">&pound;<?php echo esc_html( number_format_i18n( (float) $evg_upgrade_total, 2 ) ); ?></span>
									</li>
								<?php endif; ?>
								<li>
									<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Return Postage / Handover', 'evg' ); ?></span>
									<span style="color: #ffffff; font-weight: 600;">&pound;<?php echo esc_html( number_format_i18n( (float) $evg_shipping_total, 2 ) ); ?></span>
								</li>
							<?php endif; ?>
						</ul>

						<div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: var(--evg-obsidian-elevated); border: 1px solid var(--evg-border-gold-faint); border-radius: 6px; flex-wrap: wrap; gap: 8px; margin-bottom: 24px;">
							<span style="color: #ffffff; font-weight: 600; font-size: 0.95rem;"><?php esc_html_e( 'Total Amount Payable', 'evg' ); ?></span>
							<span style="color: var(--evg-gold-primary); font-family: monospace; font-size: 1.4rem; font-weight: 800;">
								&pound;<?php echo esc_html( number_format_i18n( (float) $evg_total_payable, 2 ) ); ?>
							</span>
						</div>

						<!-- Terms Agreement -->
						<div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 22px;">
							<input class="evg-checkbox" type="checkbox" name="terms_agree" id="termsAgree" required>
							<label style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; cursor: pointer; margin: 0;" for="termsAgree">
								<?php esc_html_e( 'I have reviewed the manifest and agree to the', 'evg' ); ?> <a href="<?php echo esc_url( home_url( '/terms' ) ); ?>" style="color: #ffffff; text-decoration: underline;"><?php esc_html_e( 'Vault Terms & Conditions', 'evg' ); ?></a>. <span style="color: var(--evg-gold-primary);">*</span>
							</label>
						</div>

						<!-- Proceed to Official Stripe Hosted Checkout -->
						<button type="submit" id="evg-stripe-submit-btn" class="btn-evg-executive" style="margin-bottom: 16px;" <?php disabled( ! empty( $evg_checkout_error ) ); ?>>
							<span>
								<?php
								printf(
									/* translators: %s: Total payable monetary value */
									esc_html__( 'Proceed to Stripe Checkout (£%s)', 'evg' ),
									esc_html( number_format_i18n( (float) $evg_total_payable, 2 ) )
								);
								?>
							</span>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left: 8px;">
								<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
							</svg>
						</button>

						<div style="display: flex; align-items: center; justify-content: center; gap: 6px; color: var(--evg-text-ash); font-size: 0.68rem; font-family: monospace; text-align: center;">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-muted)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
							<span><?php esc_html_e( 'SECURE REDIRECT TO STRIPE HOSTED CHECKOUT', 'evg' ); ?></span>
						</div>
					</div>

				</div>

			</div>
		</form>

		<!-- 3. POST-AUTHORIZATION PROTOCOL -->
		<div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--evg-border-hairline);">
			<div style="text-align: center; margin-bottom: 25px;">
				<span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Next Steps', 'evg' ); ?></span>
				<h3 style="color: #ffffff; font-size: 1.25rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'Post-Authorization Protocol', 'evg' ); ?></h3>
			</div>

			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; text-align: center;">
				<div style="background: var(--evg-obsidian-panel); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 20px 15px;">
					<span style="color: var(--evg-gold-primary); font-family: monospace; font-size: 1.3rem; font-weight: 800; display: block; margin-bottom: 6px;">01</span>
					<p style="color: #ffffff; font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Payment is verified via secure 256-bit encrypted Stripe handshake.', 'evg' ); ?></p>
				</div>
				<div style="background: var(--evg-obsidian-panel); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 20px 15px;">
					<span style="color: var(--evg-gold-primary); font-family: monospace; font-size: 1.3rem; font-weight: 800; display: block; margin-bottom: 6px;">02</span>
					<p style="color: #ffffff; font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'System generates your PDF manifest invoice and packaging packing slip.', 'evg' ); ?></p>
				</div>
				<div style="background: var(--evg-obsidian-panel); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 20px 15px;">
					<span style="color: var(--evg-gold-primary); font-family: monospace; font-size: 1.3rem; font-weight: 800; display: block; margin-bottom: 6px;">03</span>
					<p style="color: #ffffff; font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'You securely package and dispatch your cards to our UK facility.', 'evg' ); ?></p>
				</div>
				<div style="background: var(--evg-obsidian-panel); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 20px 15px;">
					<span style="color: var(--evg-gold-primary); font-family: monospace; font-size: 1.3rem; font-weight: 800; display: block; margin-bottom: 6px;">04</span>
					<p style="color: #ffffff; font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Live stage tracking telemetry activates upon our barcode scan check-in.', 'evg' ); ?></p>
				</div>
			</div>
		</div>

	</div>
</main>

<?php
get_footer();