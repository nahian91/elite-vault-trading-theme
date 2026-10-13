<?php
/**
 * Template Name: Grade Now / Submit For Grading - Executive Tier
 * Description: Complete, high-performance customer intake portal for card grading submissions.
 *              Features 100% dynamic admin-controlled tiers, customizable label surcharge pricing,
 *              live JavaScript fee calculation, guest authentication intercept routing,
 *              direct relational database writing to wp_evg_submissions & wp_evg_cards,
 *              and updated £9.99 base tier pricing with 5-10 business day turnaround compliance.
 *
 * @package evg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -------------------------------------------------------------------------
// 1. DYNAMIC SYSTEM SETTINGS & PRICING RESOLUTION
// -------------------------------------------------------------------------
global $wpdb;

$evg_table_submissions = $wpdb->prefix . 'evg_submissions';
$evg_table_cards       = $wpdb->prefix . 'evg_cards';

// Admin settings from wp_options (configured in inc/settings.php).
$evg_accept_submissions = get_option( 'evg_accept_submissions', 'yes' );
$evg_turnaround_time     = get_option( 'evg_turnaround_time', '5-10 Business Days' );
$evg_price_standard      = (float) get_option( 'evg_price_standard', 9.99 );
$evg_price_upgrade       = (float) get_option( 'evg_price_premium_upgrade', 2.99 );
$evg_shipping_fee        = (float) get_option( 'evg_return_shipping_fee', 9.99 );
$evg_max_cards_limit     = max( 1, absint( get_option( 'evg_max_cards_per_submission', 50 ) ) );

// Fetch dynamic or fallback service tiers.
$evg_service_tiers = get_option(
	'evg_service_tiers',
	array(
		'Standard Base Grading' => array(
			'label' => 'Standard Grading (1-10 Scale)',
			'price' => $evg_price_standard,
		),
		'Express Speed Tier'    => array(
			'label' => 'Express Speed (2-3 Business Days)',
			'price' => $evg_price_standard + 4.99,
		),
	)
);

// Fetch dynamic or fallback slab design options with individual surcharges.
$evg_slab_options = get_option(
	'evg_slab_options',
	array(
		'Standard Label'         => array(
			'label'     => 'Standard Label',
			'surcharge' => 0.00,
		),
		'Custom Gold Foil Label' => array(
			'label'     => 'Custom Gold Foil Label',
			'surcharge' => $evg_price_upgrade,
		),
		'Vault Door Edition'     => array(
			'label'     => 'Vault Door Edition',
			'surcharge' => $evg_price_upgrade,
		),
	)
);

$evg_error_message = '';

// -------------------------------------------------------------------------
// 2. BACKEND FORM SUBMISSION HANDLER
// -------------------------------------------------------------------------
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['evg_grade_submit_nonce'] ) ) {
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['evg_grade_submit_nonce'] ) ), 'evg_grade_submit_action' ) ) {
		$evg_error_message = __( 'Security token expired. Please refresh and try again.', 'evg' );
	} elseif ( 'yes' !== $evg_accept_submissions ) {
		$evg_error_message = __( 'Grading submissions are temporarily at capacity (Sold Out). Please try again shortly.', 'evg' );
	} elseif ( ! is_user_logged_in() ) {
		$evg_redirect_target = add_query_arg( 'redirect_to', home_url( '/grade-now' ), home_url( '/sign-in' ) );
		wp_safe_redirect( $evg_redirect_target );
		exit;
	} else {
		$evg_current_user_id = get_current_user_id();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Processed per-element downstream.
		$evg_declared_cards  = isset( $_POST['cards'] ) && is_array( $_POST['cards'] ) ? array_values( (array) wp_unslash( $_POST['cards'] ) ) : array();
		$evg_service_type    = isset( $_POST['service_type'] ) ? sanitize_text_field( wp_unslash( $_POST['service_type'] ) ) : 'Standard Base Grading';
		$evg_label_option    = isset( $_POST['label_option'] ) ? sanitize_text_field( wp_unslash( $_POST['label_option'] ) ) : 'Standard Label';
		$evg_total_card_cnt  = count( $evg_declared_cards );

		if ( $evg_total_card_cnt < 1 ) {
			$evg_error_message = __( 'Please declare at least one card in your submission roster.', 'evg' );
		} elseif ( $evg_total_card_cnt > $evg_max_cards_limit ) {
			/* translators: %d: Maximum allowed cards limit */
			$evg_error_message = sprintf( __( 'The maximum allowable allocation per submission parcel is %d cards.', 'evg' ), $evg_max_cards_limit );
		} else {
			// Calculate base cost and slab upgrade cost + flat shipping.
			$evg_selected_base_rate = isset( $evg_service_tiers[ $evg_service_type ]['price'] ) ? (float) $evg_service_tiers[ $evg_service_type ]['price'] : $evg_price_standard;
			$evg_selected_surcharge = isset( $evg_slab_options[ $evg_label_option ]['surcharge'] ) ? (float) $evg_slab_options[ $evg_label_option ]['surcharge'] : 0.00;

			$evg_base_total    = $evg_total_card_cnt * $evg_selected_base_rate;
			$evg_upgrade_total = $evg_total_card_cnt * $evg_selected_surcharge;
			$evg_final_total   = $evg_base_total + $evg_upgrade_total + $evg_shipping_fee;

			// Generate clean Order Reference ID.
			$evg_order_number = 'EVG-' . gmdate( 'Y' ) . '-' . strtoupper( wp_generate_password( 5, false, false ) );

			// Insert parent submission record with Pending payment status awaiting Stripe checkout.
			$evg_inserted_sub = $wpdb->insert(
				$evg_table_submissions,
				array(
					'order_number'         => $evg_order_number,
					'customer_id'          => $evg_current_user_id,
					'submission_date'      => current_time( 'mysql' ),
					'service_type'         => $evg_service_type,
					'submission_slot_tier' => $evg_total_card_cnt . ' Card Allocation',
					'total_cards'          => $evg_total_card_cnt,
					'label_option'         => $evg_label_option,
					'payment_status'       => 'Pending',
					'total_amount'         => number_format( (float) $evg_final_total, 2, '.', '' ),
					'current_stage'        => 'Cards Awaiting Arrival',
					'return_tracking'      => '',
				),
				array( '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
			);

			if ( false !== $evg_inserted_sub ) {
				$evg_submission_id = (int) $wpdb->insert_id;

				// Insert individual declared card units.
				foreach ( $evg_declared_cards as $c ) {
					$c_name  = isset( $c['name'] ) ? sanitize_text_field( $c['name'] ) : 'Untitled Card';
					$c_set   = isset( $c['set'] ) ? sanitize_text_field( $c['set'] ) : 'Unknown Set';
					$c_num   = isset( $c['number'] ) ? sanitize_text_field( $c['number'] ) : '';
					$c_lang  = isset( $c['language'] ) ? sanitize_text_field( $c['language'] ) : 'English';
					$c_cond  = isset( $c['condition'] ) ? sanitize_text_field( $c['condition'] ) : 'Raw / Near Mint';
					$c_notes = isset( $c['notes'] ) ? sanitize_textarea_field( $c['notes'] ) : '';

					$wpdb->insert(
						$evg_table_cards,
						array(
							'submission_id'       => $evg_submission_id,
							'card_name'           => $c_name,
							'set_name'            => $c_set,
							'card_number'         => $c_num,
							'language'            => $c_lang,
							'estimated_condition' => $c_cond,
							'customer_notes'      => $c_notes,
							'front_image_url'     => '',
							'back_image_url'      => '',
							'final_grade'         => 0,
							'grading_status'      => 'Pending Arrival',
						),
						array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
					);
				}

				if ( class_exists( 'Elite_Vault_Grading_System' ) && method_exists( 'Elite_Vault_Grading_System', 'log_activity' ) ) {
					Elite_Vault_Grading_System::log_activity( "Customer #{$evg_current_user_id} created submission #{$evg_order_number} ({$evg_total_card_cnt} cards). Routing to Stripe checkout." );
				}

				// Redirect directly to checkout to collect payment via Stripe.
				$evg_checkout_url = add_query_arg(
					array(
						'submission_id' => $evg_submission_id,
						'item_type'     => 'submission',
					),
					home_url( '/checkout' )
				);

				wp_safe_redirect( $evg_checkout_url );
				exit;
			} else {
				$evg_error_message = __( 'Database write error. Please contact EVG support.', 'evg' );
			}
		}
	}
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
		color: var(--evg-text-pure);
		position: relative;
		z-index: 1;
		overflow-x: hidden;
	}

	.evg-container {
		max-width: 1100px;
		margin: 0 auto;
		padding: 3rem 15px 5rem 15px;
	}

	.evg-title-xl {
		font-family: "Playfair Display", Georgia, serif;
		font-size: clamp(2rem, 3.8vw, 3rem);
		font-weight: 600;
		line-height: 1.15;
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
		margin-bottom: 6px;
	}

	.evg-panel {
		background: var(--evg-obsidian-panel);
		border: 1px solid var(--evg-border-hairline);
		border-radius: 8px;
		padding: 30px 20px;
		margin-bottom: 25px;
		box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
	}

	.evg-form-grid {
		display: grid;
		grid-template-columns: 1fr 340px;
		gap: 24px;
		align-items: start;
	}

	.evg-field-group {
		margin-bottom: 1.15rem;
	}
	.evg-field-group label {
		display: block;
		color: var(--evg-gold-primary);
		font-size: 0.72rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.1em;
		margin-bottom: 6px;
	}
	.evg-field-control {
		width: 100%;
		background: var(--evg-obsidian-elevated);
		border: 1px solid var(--evg-border-hairline);
		color: #ffffff;
		padding: 12px 14px;
		font-size: 0.88rem;
		border-radius: 6px;
		outline: none;
		box-sizing: border-box;
		transition: border-color 0.2s ease;
	}
	.evg-field-control:focus {
		border-color: var(--evg-gold-primary);
	}

	/* Card Declaration Roster Unit */
	.evg-card-item {
		background: var(--evg-obsidian-elevated);
		border: 1px solid #222226;
		border-radius: 6px;
		padding: 20px 15px;
		margin-bottom: 20px;
		position: relative;
		transition: border-color 0.2s ease;
	}
	.evg-card-item:hover {
		border-color: var(--evg-border-gold-faint);
	}
	.evg-card-item-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 14px;
		padding-bottom: 8px;
		border-bottom: 1px solid var(--evg-border-hairline);
		flex-wrap: wrap;
		gap: 6px;
	}
	.evg-card-item-title {
		font-size: 0.82rem;
		font-weight: 700;
		color: var(--evg-gold-light);
		text-transform: uppercase;
		letter-spacing: 0.1em;
	}
	.evg-btn-remove-card {
		background: transparent;
		border: none;
		color: #ff453a;
		font-size: 0.72rem;
		font-weight: 700;
		cursor: pointer;
		text-transform: uppercase;
	}
	.evg-btn-remove-card:hover {
		text-decoration: underline;
	}

	.evg-card-row {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 12px;
	}

	.btn-evg-gold {
		background: var(--evg-gold-primary);
		color: var(--evg-text-charcoal) !important;
		padding: 1.1rem 1.5rem;
		font-size: 0.82rem;
		font-weight: 800;
		letter-spacing: 0.15em;
		text-transform: uppercase;
		border: none;
		border-radius: 4px;
		cursor: pointer;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 100%;
		transition: all 0.2s ease;
		text-decoration: none;
		box-sizing: border-box;
		text-align: center;
	}
	.btn-evg-gold:hover {
		background: var(--evg-gold-light);
		box-shadow: 0 0 25px rgba(212, 175, 55, 0.3);
	}

	.btn-add-unit {
		background: transparent;
		border: 1px dashed var(--evg-gold-muted);
		color: var(--evg-gold-primary);
		padding: 12px;
		border-radius: 6px;
		font-size: 0.78rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.1em;
		cursor: pointer;
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 6px;
		transition: all 0.2s ease;
	}
	.btn-add-unit:hover {
		background: rgba(212, 175, 55, 0.08);
		border-color: var(--evg-gold-primary);
	}

	/* Sticky Cost Summary Box */
	.evg-summary-card {
		position: sticky;
		top: 20px;
		background: var(--evg-obsidian-panel);
		border: 1px solid var(--evg-border-hairline);
		border-radius: 8px;
		padding: 25px 20px;
		box-shadow: 0 20px 40px rgba(0,0,0,0.5);
	}
	.evg-summary-list {
		list-style: none;
		padding: 0;
		margin: 0 0 20px 0;
	}
	.evg-summary-list li {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		font-size: 0.82rem;
		padding: 9px 0;
		border-bottom: 1px solid var(--evg-border-hairline);
		color: var(--evg-text-ash);
		gap: 10px;
	}
	.evg-summary-list li strong {
		color: #ffffff;
		text-align: right;
	}

	/* Responsive Media Queries */
	@media (max-width: 991.98px) {
		.evg-form-grid { grid-template-columns: 1fr; }
		.evg-summary-card { position: static; margin-top: 10px; }
	}

	@media (max-width: 767.98px) {
		.evg-container { padding: 2rem 15px 4rem 15px; }
		.evg-panel { padding: 25px 15px; }
		.evg-card-row { grid-template-columns: 1fr; gap: 0; }
	}
</style>

<main class="evg-master-wrapper">
	<div class="evg-container">

		<!-- HEADER -->
		<header style="text-align: center; margin-bottom: 35px; padding-bottom: 20px; border-bottom: 1px solid var(--evg-border-hairline);">
			<span class="evg-label-micro"><?php esc_html_e( 'Official Certification & Authentication', 'evg' ); ?></span>
			<h1 class="evg-title-xl"><?php esc_html_e( 'Submit Cards For', 'evg' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Grading', 'evg' ); ?></span></h1>
			<p style="color: var(--evg-text-ash); max-width: 680px; margin: 0 auto 15px auto; font-size: 0.92rem; line-height: 1.6;">
				<?php
				printf(
					/* translators: %s: Standard base price formatted as localized currency */
					esc_html__( 'Standard grading starting from £%s per card. Declare your Pokémon TCG assets, configure custom labels, and secure your allocation in our UK vault registry.', 'evg' ),
					esc_html( number_format_i18n( $evg_price_standard, 2 ) )
				);
				?>
			</p>
			<div style="display: inline-flex; align-items: center; gap: 8px; font-family: monospace; font-size: 0.72rem; color: var(--evg-gold-light); background: var(--evg-obsidian-elevated); padding: 5px 14px; border-radius: 4px; border: 1px solid var(--evg-border-gold-faint); flex-wrap: wrap; justify-content: center;">
				<span>
					<?php
					printf(
						/* translators: %s: Turnaround time window */
						esc_html__( 'ESTIMATED TURNAROUND: %s', 'evg' ),
						esc_html( $evg_turnaround_time )
					);
					?>
				</span>
			</div>
		</header>

		<?php if ( ! empty( $evg_error_message ) ) : ?>
			<div style="background: rgba(255, 69, 58, 0.1); border-left: 4px solid #ff453a; color: #ffffff; padding: 14px 18px; border-radius: 4px; margin-bottom: 25px; font-size: 0.85rem;">
				<?php echo esc_html( $evg_error_message ); ?>
			</div>
		<?php endif; ?>

		<?php if ( 'yes' !== $evg_accept_submissions ) : ?>
			<div class="evg-panel" style="text-align: center; padding: 50px 15px;">
				<h3 style="color: #ff453a; font-size: 1.25rem; font-weight: 700; margin-bottom: 10px;"><?php esc_html_e( 'Grading Queue Sold Out', 'evg' ); ?></h3>
				<p style="color: var(--evg-text-ash); max-width: 500px; margin: 0 auto 20px auto; font-size: 0.88rem; line-height: 1.5;">
					<?php esc_html_e( 'Our laboratory capacity for this drop has been fully allocated to preserve our 5-10 business day turnaround standard. Please check back shortly or explore certified slabs on our public marketplace.', 'evg' ); ?>
				</p>
				<a href="<?php echo esc_url( home_url( '/buy-it-now' ) ); ?>" class="btn-evg-gold" style="width: auto; display: inline-flex;">
					<?php esc_html_e( 'Browse Marketplace Slabs', 'evg' ); ?>
				</a>
			</div>
		<?php else : ?>

			<form method="post" action="<?php echo esc_url( add_query_arg( array() ) ); ?>" id="evg-grading-form">
				<?php wp_nonce_field( 'evg_grade_submit_action', 'evg_grade_submit_nonce' ); ?>

				<div class="evg-form-grid">

					<!-- LEFT COLUMN: SUBMISSION & CARDS BUILDER -->
					<div>

						<!-- 1. Service Parameters -->
						<div class="evg-panel">
							<span class="evg-label-micro"><?php esc_html_e( '01 // Service Specification', 'evg' ); ?></span>
							<h2 style="font-size: 1.15rem; font-weight: 700; margin: 0 0 18px 0;"><?php esc_html_e( 'Grading Tier & Slab Architecture', 'evg' ); ?></h2>

							<div class="evg-field-group">
								<label><?php esc_html_e( 'Grading Service Level', 'evg' ); ?></label>
								<select name="service_type" id="evg_service_type" class="evg-field-control" onchange="evg_recalc_pricing()">
									<?php foreach ( $evg_service_tiers as $tier_key => $tier_data ) : ?>
										<option value="<?php echo esc_attr( $tier_key ); ?>" data-price="<?php echo esc_attr( $tier_data['price'] ); ?>">
											<?php echo esc_html( $tier_data['label'] ); ?> &mdash; &pound;<?php echo esc_html( number_format_i18n( (float) $tier_data['price'], 2 ) ); ?> / <?php esc_html_e( 'card', 'evg' ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="evg-field-group" style="margin-bottom: 0;">
								<label><?php esc_html_e( 'Label & Slab Housing Design', 'evg' ); ?></label>
								<select name="label_option" id="evg_label_option" class="evg-field-control" onchange="evg_recalc_pricing()">
									<?php
									foreach ( $evg_slab_options as $slab_key => $slab_data ) :
										$surcharge_text = ( $slab_data['surcharge'] > 0 ) ? ' (+£' . number_format_i18n( (float) $slab_data['surcharge'], 2 ) . ' / card)' : ' (Included in Base Rate)';
										?>
										<option value="<?php echo esc_attr( $slab_key ); ?>" data-surcharge="<?php echo esc_attr( $slab_data['surcharge'] ); ?>">
											<?php echo esc_html( $slab_data['label'] . $surcharge_text ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<!-- 2. Declared Card Manifest -->
						<div class="evg-panel">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 8px;">
								<div>
									<span class="evg-label-micro"><?php esc_html_e( '02 // Asset Ledger', 'evg' ); ?></span>
									<h2 style="font-size: 1.15rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'Declared Pokémon Cards', 'evg' ); ?></h2>
								</div>
								<span id="evg-card-counter-badge" style="font-family: monospace; font-size: 0.72rem; color: var(--evg-gold-primary); font-weight: 700;">
									<?php esc_html_e( '1 CARD IN ROSTER', 'evg' ); ?>
								</span>
							</div>

							<div id="evg-cards-container">
								<!-- Card Unit #1 -->
								<div class="evg-card-item" data-index="0">
									<div class="evg-card-item-header">
										<span class="evg-card-item-title"><?php esc_html_e( 'Card #1', 'evg' ); ?></span>
									</div>
									<div class="evg-field-group">
										<label><?php esc_html_e( 'Card Name *', 'evg' ); ?></label>
										<input type="text" name="cards[0][name]" class="evg-field-control" placeholder="<?php esc_attr_e( 'e.g. Charizard VMAX (Secret Rare)', 'evg' ); ?>" required>
									</div>
									<div class="evg-card-row">
										<div class="evg-field-group">
											<label><?php esc_html_e( 'Set / Expansion *', 'evg' ); ?></label>
											<input type="text" name="cards[0][set]" class="evg-field-control" placeholder="<?php esc_attr_e( 'e.g. Shining Fates', 'evg' ); ?>" required>
										</div>
										<div class="evg-field-group">
											<label><?php esc_html_e( 'Card Number', 'evg' ); ?></label>
											<input type="text" name="cards[0][number]" class="evg-field-control" placeholder="<?php esc_attr_e( 'e.g. 074/072', 'evg' ); ?>">
										</div>
									</div>
									<div class="evg-card-row">
										<div class="evg-field-group">
											<label><?php esc_html_e( 'Language', 'evg' ); ?></label>
											<select name="cards[0][language]" class="evg-field-control">
												<option value="English"><?php esc_html_e( 'English', 'evg' ); ?></option>
												<option value="Japanese"><?php esc_html_e( 'Japanese', 'evg' ); ?></option>
											</select>
										</div>
										<div class="evg-field-group">
											<label><?php esc_html_e( 'Estimated Condition', 'evg' ); ?></label>
											<select name="cards[0][condition]" class="evg-field-control">
												<option value="Raw / Near Mint"><?php esc_html_e( 'Near Mint (Raw)', 'evg' ); ?></option>
												<option value="Lightly Played"><?php esc_html_e( 'Lightly Played', 'evg' ); ?></option>
												<option value="Moderately Played"><?php esc_html_e( 'Moderately Played', 'evg' ); ?></option>
											</select>
										</div>
									</div>
									<div class="evg-field-group" style="margin-bottom: 0;">
										<label><?php esc_html_e( 'Special Instructions / Notes (Optional)', 'evg' ); ?></label>
										<input type="text" name="cards[0][notes]" class="evg-field-control" placeholder="<?php esc_attr_e( 'e.g. Check top centering or surface scratch notes', 'evg' ); ?>">
									</div>
								</div>
							</div>

							<button type="button" class="btn-add-unit" onclick="evg_add_card_unit()">
								+ <?php esc_html_e( 'Add Another Card to Submission', 'evg' ); ?>
							</button>
						</div>

					</div>

					<!-- RIGHT COLUMN: STICKY BILLING LEDGER -->
					<div>
						<div class="evg-summary-card">
							<span class="evg-label-micro"><?php esc_html_e( 'Billing Ledger', 'evg' ); ?></span>
							<h3 style="font-size: 1.1rem; font-weight: 700; margin: 0 0 14px 0;"><?php esc_html_e( 'Summary of Fees', 'evg' ); ?></h3>

							<ul class="evg-summary-list">
								<li>
									<span><?php esc_html_e( 'Declared Units', 'evg' ); ?></span>
									<strong id="summary-units-count"><?php esc_html_e( '1 Card', 'evg' ); ?></strong>
								</li>
								<li>
									<span><?php esc_html_e( 'Base Grading Rate', 'evg' ); ?></span>
									<strong id="summary-base-rate">&pound;<?php echo esc_html( number_format_i18n( (float) $evg_price_standard, 2 ) ); ?></strong>
								</li>
								<li>
									<span><?php esc_html_e( 'Slab Upgrade Surcharge', 'evg' ); ?></span>
									<strong id="summary-upgrade-rate">&pound;0.00</strong>
								</li>
								<li>
									<span><?php esc_html_e( 'UK Return Delivery', 'evg' ); ?></span>
									<strong style="color: #34c759; font-family: monospace; font-size: 0.72rem;">&pound;<?php echo esc_html( number_format_i18n( (float) $evg_shipping_fee, 2 ) ); ?> (<?php esc_html_e( 'TRACKED', 'evg' ); ?>)</strong>
								</li>
								<li style="border-bottom: none; padding-top: 12px; font-size: 1.05rem; color: #ffffff;">
									<strong><?php esc_html_e( 'Total Authorized', 'evg' ); ?></strong>
									<strong id="summary-total-amount" style="color: var(--evg-gold-primary); font-family: monospace;">
										&pound;<?php echo esc_html( number_format_i18n( (float) ( $evg_price_standard + $evg_shipping_fee ), 2 ) ); ?>
									</strong>
								</li>
							</ul>

							<?php if ( is_user_logged_in() ) : ?>
								<button type="submit" class="btn-evg-gold">
									<?php esc_html_e( 'Lock In & Continue to Payment', 'evg' ); ?> &rarr;
								</button>
							<?php else : ?>
								<a href="<?php echo esc_url( add_query_arg( 'redirect_to', home_url( '/grade-now' ), home_url( '/sign-in' ) ) ); ?>" class="btn-evg-gold">
									<?php esc_html_e( 'Log In To Complete Order', 'evg' ); ?> &rarr;
								</a>
								<p style="color: var(--evg-text-ash); font-size: 0.72rem; text-align: center; margin-top: 10px;">
									<?php esc_html_e( 'You will be redirected straight back after sign-in.', 'evg' ); ?>
								</p>
							<?php endif; ?>
						</div>
					</div>

				</div>
			</form>

		<?php endif; ?>

	</div>
</main>

<script>
	var evgCardIndex = 1;
	var returnShippingFee = <?php echo (float) $evg_shipping_fee; ?>;
	var maxCardsAllowed = <?php echo (int) $evg_max_cards_limit; ?>;

	function evg_recalc_pricing() {
		var cardCount = jQuery('#evg-cards-container .evg-card-item').length;
		
		var selectedTierOption = jQuery('#evg_service_type option:selected');
		var baseRate = parseFloat(selectedTierOption.data('price')) || <?php echo (float) $evg_price_standard; ?>;

		var selectedSlabOption = jQuery('#evg_label_option option:selected');
		var upgradeRatePerCard = parseFloat(selectedSlabOption.data('surcharge')) || 0.00;

		var baseTotal = cardCount * baseRate;
		var upgradeTotal = cardCount * upgradeRatePerCard;
		var finalTotal = baseTotal + upgradeTotal + returnShippingFee;

		jQuery('#summary-units-count').text(cardCount + (cardCount === 1 ? ' Card' : ' Cards'));
		jQuery('#evg-card-counter-badge').text(cardCount + (cardCount === 1 ? ' CARD IN ROSTER' : ' CARDS IN ROSTER'));
		jQuery('#summary-base-rate').text('£' + baseTotal.toFixed(2));
		jQuery('#summary-upgrade-rate').text('£' + upgradeTotal.toFixed(2));
		jQuery('#summary-total-amount').text('£' + finalTotal.toFixed(2));
	}

	function evg_add_card_unit() {
		var currentCount = jQuery('#evg-cards-container .evg-card-item').length;
		if (currentCount >= maxCardsAllowed) {
			alert('The maximum card allocation per submission is ' + maxCardsAllowed + ' cards.');
			return;
		}

		var html = '' +
			'<div class="evg-card-item" data-index="' + evgCardIndex + '">' +
				'<div class="evg-card-item-header">' +
					'<span class="evg-card-item-title">Card #' + (currentCount + 1) + '</span>' +
					'<button type="button" class="evg-btn-remove-card" onclick="evg_remove_card_unit(this)">&times; <?php echo esc_js( __( 'Remove', 'evg' ) ); ?></button>' +
				'</div>' +
				'<div class="evg-field-group">' +
					'<label><?php echo esc_js( __( 'Card Name *', 'evg' ) ); ?></label>' +
					'<input type="text" name="cards[' + evgCardIndex + '][name]" class="evg-field-control" placeholder="<?php echo esc_js( __( 'e.g. Pikachu VMAX', 'evg' ) ); ?>" required>' +
				'</div>' +
				'<div class="evg-card-row">' +
					'<div class="evg-field-group">' +
						'<label><?php echo esc_js( __( 'Set / Expansion *', 'evg' ) ); ?></label>' +
						'<input type="text" name="cards[' + evgCardIndex + '][set]" class="evg-field-control" placeholder="<?php echo esc_js( __( 'e.g. Vivid Voltage', 'evg' ) ); ?>" required>' +
					'</div>' +
					'<div class="evg-field-group">' +
						'<label><?php echo esc_js( __( 'Card Number', 'evg' ) ); ?></label>' +
						'<input type="text" name="cards[' + evgCardIndex + '][number]" class="evg-field-control" placeholder="<?php echo esc_js( __( 'e.g. 188/185', 'evg' ) ); ?>">' +
					'</div>' +
				'</div>' +
				'<div class="evg-card-row">' +
					'<div class="evg-field-group">' +
						'<label><?php echo esc_js( __( 'Language', 'evg' ) ); ?></label>' +
						'<select name="cards[' + evgCardIndex + '][language]" class="evg-field-control">' +
							'<option value="English"><?php echo esc_js( __( 'English', 'evg' ) ); ?></option>' +
							'<option value="Japanese"><?php echo esc_js( __( 'Japanese', 'evg' ) ); ?></option>' +
						'</select>' +
					'</div>' +
					'<div class="evg-field-group">' +
						'<label><?php echo esc_js( __( 'Estimated Condition', 'evg' ) ); ?></label>' +
						'<select name="cards[' + evgCardIndex + '][condition]" class="evg-field-control">' +
							'<option value="Raw / Near Mint"><?php echo esc_js( __( 'Near Mint (Raw)', 'evg' ) ); ?></option>' +
							'<option value="Lightly Played"><?php echo esc_js( __( 'Lightly Played', 'evg' ) ); ?></option>' +
							'<option value="Moderately Played"><?php echo esc_js( __( 'Moderately Played', 'evg' ) ); ?></option>' +
						'</select>' +
					'</div>' +
				'</div>' +
				'<div class="evg-field-group" style="margin-bottom: 0;">' +
					'<label><?php echo esc_js( __( 'Special Instructions / Notes (Optional)', 'evg' ) ); ?></label>' +
					'<input type="text" name="cards[' + evgCardIndex + '][notes]" class="evg-field-control" placeholder="<?php echo esc_js( __( 'e.g. Corner whitening notes', 'evg' ) ); ?>">' +
				'</div>' +
			'</div>';

		jQuery('#evg-cards-container').append(html);
		evgCardIndex++;
		evg_recalc_pricing();
	}

	function evg_remove_card_unit(btn) {
		jQuery(btn).closest('.evg-card-item').remove();
		
		// Re-index remaining visual card counter headers.
		jQuery('#evg-cards-container .evg-card-item').each(function(i, el) {
			jQuery(el).find('.evg-card-item-title').text('Card #' + (i + 1));
		});
		
		evg_recalc_pricing();
	}

	jQuery(document).ready(function() {
		evg_recalc_pricing();
	});
</script>

<?php
get_footer();