<?php
/**
 * Template Name: Thank You / Order Confirmation - Executive Tier
 * Description: Fully dynamic order confirmation portal for Elite Vault Grading.
 *              Connects directly to wp_evg_submissions and wp_evg_cards,
 *              fetches live intake details, generates packing slip links,
 *              displays speed of service turnaround, updated £6.99 postage,
 *              and provides solid dark luxury styling.
 *
 * @package evg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$evg_table_submissions = $wpdb->prefix . 'evg_submissions';
$evg_table_cards       = $wpdb->prefix . 'evg_cards';

// -------------------------------------------------------------------------
// 1. DYNAMIC DATA RESOLUTION (Supports order_number string & numeric ID)
// -------------------------------------------------------------------------
// phpcs:disable WordPress.Security.NonceVerification.Recommended
$evg_order_param = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : ( isset( $_GET['order_id'] ) ? sanitize_text_field( wp_unslash( $_GET['order_id'] ) ) : '' );
$evg_submission_id = isset( $_GET['submission_id'] ) ? absint( $_GET['submission_id'] ) : ( is_numeric( $evg_order_param ) ? absint( $evg_order_param ) : 0 );
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$evg_submission = null;
$evg_cards      = array();

if ( ! empty( $evg_order_param ) && ! is_numeric( $evg_order_param ) ) {
	// Lookup by unique order reference string (e.g. EVG-ABC123).
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$evg_submission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$evg_table_submissions} WHERE order_number = %s", $evg_order_param ) );
} elseif ( $evg_submission_id > 0 ) {
	// Lookup by numeric ID.
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$evg_submission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$evg_table_submissions} WHERE id = %d", $evg_submission_id ) );
}

if ( $evg_submission ) {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$evg_cards = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$evg_table_cards} WHERE submission_id = %d ORDER BY id ASC", $evg_submission->id ) );
}

// Fallbacks & Parameters.
$evg_order_number    = $evg_submission ? $evg_submission->order_number : ( ! empty( $evg_order_param ) ? $evg_order_param : 'EVG-' . gmdate( 'Y' ) . '-INTAKE' );
$evg_submission_tier = $evg_submission ? $evg_submission->service_type : 'Standard (10-15 Business Days)';
$evg_label_option    = $evg_submission ? $evg_submission->label_option : 'Black Basic';
$evg_card_count      = $evg_submission ? absint( $evg_submission->total_cards ) : ( ! empty( $evg_cards ) ? count( $evg_cards ) : 1 );
$evg_total_amount    = $evg_submission ? (float) $evg_submission->total_amount : 0.00;
$evg_fulfilment_type = $evg_submission && isset( $evg_submission->fulfilment_type ) ? $evg_submission->fulfilment_type : 'postage';

// Resolve Customer Email.
// phpcs:disable WordPress.Security.NonceVerification.Recommended
$evg_customer_user = ( $evg_submission && $evg_submission->customer_id > 0 ) ? get_userdata( $evg_submission->customer_id ) : ( is_user_logged_in() ? wp_get_current_user() : null );
$evg_raw_email     = $evg_customer_user ? $evg_customer_user->user_email : ( isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : 'collector@domain.co.uk' );
// phpcs:enable WordPress.Security.NonceVerification.Recommended

// Mask email for privacy display.
$evg_order_email = $evg_raw_email;
if ( false !== strpos( $evg_raw_email, '@' ) ) {
	list( $evg_user_part, $evg_domain_part ) = explode( '@', $evg_raw_email );
	if ( mb_strlen( $evg_user_part ) > 2 ) {
		$evg_masked_user = mb_substr( $evg_user_part, 0, 1 ) . str_repeat( '*', mb_strlen( $evg_user_part ) - 2 ) . mb_substr( $evg_user_part, -1 );
	} else {
		$evg_masked_user = mb_substr( $evg_user_part, 0, 1 ) . '*';
	}
	$evg_order_email = $evg_masked_user . '@' . $evg_domain_part;
}

// Fetch Dynamic Admin Settings.
$evg_support_email = get_option( 'evg_support_email', 'info@elitevaultgrading.com' );

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

	.evg-dispatch-box {
		background: #141416;
		border: 1px dashed var(--evg-gold-primary);
		border-radius: 8px;
		padding: 24px;
		margin: 24px 0 0 0;
		text-align: center;
	}

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

	@media (max-width: 767.98px) {
		.evg-container { padding: 2rem 15px 4rem 15px; }
		.evg-module { padding: 25px 15px; }
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

			<span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 8px;"><?php esc_html_e( 'Vault Intake Reserved & Confirmed', 'evg' ); ?></span>
			<h1 class="evg-title-xl"><?php esc_html_e( 'Allocation', 'evg' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Locked & Verified', 'evg' ); ?></span></h1>
			<p style="color: var(--evg-text-ash); max-width: 600px; margin: 0 auto; font-size: 0.92rem; line-height: 1.6;">
				<?php esc_html_e( 'Payment confirmed. Your Pokémon card grading allocation has been logged into our central UK registry and is awaiting shipment.', 'evg' ); ?>
			</p>
		</header>

		<!-- 2. CONSIGNMENT MANIFEST MODULE -->
		<section class="evg-module" style="padding: 30px 20px; margin-bottom: 35px;">
			<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid var(--evg-border-hairline);">
				<div>
					<span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Official Intake Reference ID', 'evg' ); ?></span>
					<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
						<h2 style="color: #ffffff; font-size: 1.3rem; font-family: monospace; font-weight: 700; margin: 0; letter-spacing: 1px;">
							#<?php echo esc_html( $evg_order_number ); ?>
						</h2>
						<button class="evg-copy-btn" onclick="navigator.clipboard.writeText('<?php echo esc_js( $evg_order_number ); ?>'); alert('<?php echo esc_js( __( 'Order Reference copied to clipboard.', 'evg' ) ); ?>');" title="<?php esc_attr_e( 'Copy Reference', 'evg' ); ?>">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
								<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
							</svg>
						</button>
					</div>
					<p style="color: var(--evg-text-ash); font-size: 0.82rem; margin: 0;">
						<?php
						printf(
							/* translators: %s: Masked recipient email address */
							esc_html__( 'Confirmation voucher and order manifest transmitted to %s.', 'evg' ),
							'<strong style="color:#ffffff;">' . esc_html( $evg_order_email ) . '</strong>'
						);
						?>
					</p>
				</div>

				<div style="display: flex; gap: 10px; flex-wrap: wrap;">
					<a href="<?php echo esc_url( home_url( '/my-account' ) ); ?>" class="btn-evg-executive">
						<?php esc_html_e( 'Live Tracking Portal', 'evg' ); ?>
					</a>
					<button type="button" class="btn-evg-outline" onclick="window.print()">
						&#128438; <?php esc_html_e( 'Print Manifest Sheet', 'evg' ); ?>
					</button>
				</div>
			</div>

			<!-- INTAKE METRICS -->
			<ul class="evg-meta-list" style="padding-top: 5px;">
				<li>
					<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Submission Service Speed', 'evg' ); ?></span>
					<span style="color: var(--evg-gold-light); font-weight: 700; text-align: right;"><?php echo esc_html( $evg_submission_tier ); ?></span>
				</li>
				<li>
					<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Declared Asset Manifest', 'evg' ); ?></span>
					<span style="color: #ffffff; font-weight: 700; text-align: right;">
						<?php
						printf(
							/* translators: %s: Number of cards declared */
							esc_html__( '%s Pokémon Cards Declared', 'evg' ),
							esc_html( number_format_i18n( $evg_card_count ) )
						);
						?>
					</span>
				</li>
				<li>
					<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Slab Design Option', 'evg' ); ?></span>
					<span style="color: #ffffff; font-weight: 600; text-align: right;"><?php echo esc_html( $evg_label_option ); ?></span>
				</li>
				<?php if ( $evg_total_amount > 0 ) : ?>
					<li>
						<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Total Billed & Settled', 'evg' ); ?></span>
						<span style="color: #34c759; font-family: monospace; font-weight: 700; font-size: 0.95rem;">
							&pound;<?php echo esc_html( number_format_i18n( (float) $evg_total_amount, 2 ) ); ?>
						</span>
					</li>
				<?php endif; ?>
				<li>
					<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Delivery / Handover Method', 'evg' ); ?></span>
					<span style="color: <?php echo ( 'collection' === $evg_fulfilment_type ) ? 'var(--evg-gold-primary)' : '#34c759'; ?>; font-family: monospace; font-size: 0.8rem; font-weight: 700;">
						<?php
						if ( 'collection' === $evg_fulfilment_type ) {
							esc_html_e( 'COLLECTION – DONCASTER (£0.00)', 'evg' );
						} else {
							esc_html_e( 'ROYAL MAIL INSURED POSTAGE (£6.99)', 'evg' );
						}
						?>
					</span>
				</li>
			</ul>

			<!-- REGISTERED CARDS MANIFEST (IF DECLARED) -->
			<?php if ( ! empty( $evg_cards ) ) : ?>
				<div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--evg-border-hairline);">
					<span class="evg-label-micro" style="margin-bottom: 12px; color: var(--evg-gold-primary);"><?php esc_html_e( 'Declared Collectibles Manifest', 'evg' ); ?></span>
					<div style="display: flex; flex-direction: column; gap: 8px;">
						<?php foreach ( $evg_cards as $idx => $card ) : ?>
							<div style="display: flex; justify-content: space-between; align-items: center; background: #141416; padding: 10px 14px; border-radius: 6px; border: 1px solid #222224; font-size: 0.85rem;">
								<span>
									<strong style="color: #fff;"><?php echo esc_html( ( $idx + 1 ) . '. ' . $card->card_name ); ?></strong> 
									<span style="color: var(--evg-text-ash); font-size: 0.78rem;">(<?php echo esc_html( $card->set_name ); ?>)</span>
								</span>
								<span style="font-family: monospace; color: var(--evg-gold-light); font-weight: 600;">
									#<?php echo esc_html( $card->card_number ? $card->card_number : 'N/A' ); ?>
								</span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- OFFICIAL DISPATCH ADDRESS BOX -->
			<div class="evg-dispatch-box">
				<span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 6px;"><?php esc_html_e( 'Physical Dispatch Destination', 'evg' ); ?></span>
				<p style="color: var(--evg-text-ash); font-size: 0.85rem; margin: 0 auto 12px auto; max-width: 550px;">
					<?php
					printf(
						/* translators: %s: Order reference number */
						esc_html__( 'Please clearly write your order reference %s on paper inside your package and dispatch to:', 'evg' ),
						'<strong style="color:#ffffff;">#' . esc_html( $evg_order_number ) . '</strong>'
					);
					?>
				</p>
				<div style="display: inline-block; background: #09090b; border: 1px solid var(--evg-border-gold-faint); border-radius: 6px; padding: 12px 28px; font-family: monospace; font-size: 0.95rem; color: #ffffff; line-height: 1.5;">
					<strong style="color: var(--evg-gold-light);"><?php esc_html_e( 'Elite Vault Grading', 'evg' ); ?></strong><br>
					PO Box 1755<br>
					Doncaster<br>
					DN1 9AS
				</div>
			</div>
		</section>

		<!-- 3. PREPARATION WORKFLOW MATRIX -->
		<section style="margin-bottom: 35px;">
			<div style="text-align: center; margin-bottom: 20px;">
				<span class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Consignment Roadmap', 'evg' ); ?></span>
				<h3 style="color: #ffffff; font-size: 1.25rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'What Happens Next?', 'evg' ); ?></h3>
			</div>

			<div class="evg-grid-matrix evg-workflow-matrix">
				<div class="evg-grid-cell">
					<span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 6px;"><?php esc_html_e( 'Stage 01', 'evg' ); ?></span>
					<h4 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Package Your Cards', 'evg' ); ?></h4>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
						<?php esc_html_e( 'Place cards in penny sleeves and semi-rigids. Enclose a printed copy of this reference slip inside a padded box.', 'evg' ); ?>
					</p>
				</div>

				<div class="evg-grid-cell">
					<span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 6px;"><?php esc_html_e( 'Stage 02', 'evg' ); ?></span>
					<h4 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Tracked UK Dispatch', 'evg' ); ?></h4>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
						<?php esc_html_e( 'Post via an insured, tracked courier (e.g. Royal Mail Special Delivery) covering your total declared valuation.', 'evg' ); ?>
					</p>
				</div>

				<div class="evg-grid-cell">
					<span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 6px;"><?php esc_html_e( 'Stage 03', 'evg' ); ?></span>
					<h4 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Live Vault Telemetry', 'evg' ); ?></h4>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
						<?php esc_html_e( 'Track check-in, authentication, sub-scoring, and sonic encapsulation directly from your member dashboard.', 'evg' ); ?>
					</p>
				</div>
			</div>
		</section>

		<!-- 4. SUPPORT UTILITY FOOTER -->
		<footer class="evg-module" style="padding: 20px 15px; text-align: center;">
			<p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.6; margin: 0;">
				<?php esc_html_e( 'Need amendments or corrections prior to shipping your cards?', 'evg' ); ?><br>
				<?php esc_html_e( 'Contact our UK verification desk directly at', 'evg' ); ?> 
				<a href="<?php echo esc_url( 'mailto:' . antispambot( $evg_support_email ) ); ?>" style="color: #ffffff; text-decoration: underline; font-weight: 600; word-break: break-all;">
					<?php echo esc_html( antispambot( $evg_support_email ) ); ?>
				</a>
			</p>
		</footer>

	</div>
</main>

<?php
get_footer();