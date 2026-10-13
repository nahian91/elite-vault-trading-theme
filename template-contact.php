<?php
/**
 * Template Name: Contact Us - Executive Tier
 * Description: Fully dynamic, production-ready contact portal for Elite Vault Grading.
 *              Features server-side enquiry dispatch, database feedback logging, dynamic admin settings routing,
 *              audit logging, 5-10 business day turnaround compliance, and a clean luxury dark UI with centered alignment.
 *
 * @package evg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -------------------------------------------------------------------------
// 1. BACKEND ENQUIRY & TICKET DISPATCH ENGINE
// -------------------------------------------------------------------------
$evg_contact_status  = '';
$evg_contact_message = '';
$evg_form_data       = array();

// Fetch Dynamic Admin Settings Configured in inc/settings.php.
$evg_support_email   = get_option( 'evg_support_email', 'info@elitevaultgrading.com' );
$evg_turnaround_time = get_option( 'evg_turnaround_time', '5-10 Business Days' );

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['evg_contact_nonce'] ) ) {
	if ( wp_verify_nonce( sanitize_key( wp_unslash( $_POST['evg_contact_nonce'] ) ), 'evg_contact_enquiry_action' ) ) {

		$evg_customer_name   = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
		$evg_email_address   = isset( $_POST['email_address'] ) ? sanitize_email( wp_unslash( $_POST['email_address'] ) ) : '';
		$evg_order_number    = isset( $_POST['order_number'] ) ? sanitize_text_field( wp_unslash( $_POST['order_number'] ) ) : '';
		$evg_feedback_type   = isset( $_POST['enquiry_category'] ) ? sanitize_text_field( wp_unslash( $_POST['enquiry_category'] ) ) : 'General Enquiry';
		$evg_message_content = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

		$evg_form_data = array(
			'customer_name'   => $evg_customer_name,
			'email_address'   => $evg_email_address,
			'order_number'    => $evg_order_number,
			'feedback_type'   => $evg_feedback_type,
			'message_content' => $evg_message_content,
		);

		// Client IP Rate Limiting Guard (60-second cooldown).
		$evg_client_ip = '0.0.0.0';
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$evg_raw_ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			if ( filter_var( $evg_raw_ip, FILTER_VALIDATE_IP ) ) {
				$evg_client_ip = $evg_raw_ip;
			}
		}
		$evg_ip_hash      = md5( $evg_client_ip );
		$evg_cooldown_key = 'evg_contact_cooldown_' . $evg_ip_hash;

		// Validation.
		if ( empty( $evg_customer_name ) || empty( $evg_email_address ) || empty( $evg_message_content ) ) {
			$evg_contact_status  = 'error';
			$evg_contact_message = __( 'Please complete all required fields (*).', 'evg' );
		} elseif ( ! is_email( $evg_email_address ) ) {
			$evg_contact_status  = 'error';
			$evg_contact_message = __( 'Please supply a valid email address.', 'evg' );
		} elseif ( false !== get_transient( $evg_cooldown_key ) ) {
			$evg_contact_status  = 'error';
			$evg_contact_message = __( 'Please wait a moment before sending another transmission.', 'evg' );
		} else {
			global $wpdb;
			$evg_table_feedback = $wpdb->prefix . 'evg_feedback';

			// 1. Store in EVG Platform Feedback / Support Table.
			$wpdb->insert(
				$evg_table_feedback,
				array(
					'customer_name'     => $evg_customer_name,
					'email_address'     => $evg_email_address,
					'order_number'      => $evg_order_number,
					'feedback_type'     => $evg_feedback_type,
					'rating'            => 5,
					'feedback_text'     => $evg_message_content,
					'recommend'         => 'Yes',
					'permission_to_use' => 0,
					'status'            => 'Pending',
					'admin_notes'       => '',
					'submitted_at'      => current_time( 'mysql' ),
				),
				array( '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s' )
			);

			// 2. Dispatch Email to Support Inbox with Sanitized Headers.
			$evg_clean_sender_name = preg_replace( "/[\r\n]+/", '', $evg_customer_name );
			/* translators: 1: Enquiry category, 2: Customer name */
			$evg_subject = sprintf( __( '[EVG Support Ticket] %1$s from %2$s', 'evg' ), $evg_feedback_type, $evg_clean_sender_name );
			$evg_body    = "New secure support transmission received:\n\n";
			$evg_body   .= "Name: {$evg_clean_sender_name}\n";
			$evg_body   .= "Email: {$evg_email_address}\n";
			if ( ! empty( $evg_order_number ) ) {
				$evg_body .= "Order Ref: #{$evg_order_number}\n";
			}
			$evg_body .= "Category: {$evg_feedback_type}\n";
			$evg_body .= 'Time: ' . current_time( 'mysql' ) . " GMT\n\n";
			$evg_body .= "Message:\n{$evg_message_content}\n\n";
			$evg_body .= "---\nElite Vault Grading Automated Dispatch Engine";

			$evg_headers = array(
				'Content-Type: text/plain; charset=UTF-8',
				'Reply-To: ' . $evg_clean_sender_name . ' <' . $evg_email_address . '>',
			);

			wp_mail( $evg_support_email, $evg_subject, $evg_body, $evg_headers );

			// 3. Set Rate Limit Cooldown (60 seconds).
			set_transient( $evg_cooldown_key, 1, 60 );

			// 4. Security Audit Logging.
			if ( class_exists( 'Elite_Vault_Grading_System' ) && method_exists( 'Elite_Vault_Grading_System', 'log_activity' ) ) {
				Elite_Vault_Grading_System::log_activity( "Support Ticket Opened by {$evg_email_address} (Ref: {$evg_order_number})" );
			}

			// Reset form on success.
			$evg_form_data       = array();
			$evg_contact_status  = 'success';
			$evg_contact_message = __( 'Enquiry transmitted securely. A support specialist will respond to your communications array shortly.', 'evg' );
		}
	}
}

// Prefill data if logged in.
if ( empty( $evg_form_data ) && is_user_logged_in() ) {
	$evg_current_user              = wp_get_current_user();
	$evg_form_data['customer_name'] = $evg_current_user->display_name;
	$evg_form_data['email_address'] = $evg_current_user->user_email;
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
		position: relative;
		z-index: 1;
		color: var(--evg-text-pure);
		overflow-x: hidden;
	}

	.evg-container {
		max-width: 1140px;
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

	.evg-contact-layout {
		display: grid;
		grid-template-columns: 1.35fr 1fr;
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

	/* Centered Diagnostic Support Matrix */
	.evg-topic-matrix {
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		gap: 1px;
		background: var(--evg-border-hairline); 
		border: 1px solid var(--evg-border-hairline); 
		border-radius: 6px; 
		overflow: hidden;
	}
	.evg-topic-cell {
		background: var(--evg-obsidian-panel); 
		padding: 1.5rem 1.25rem; 
		flex: 1 1 280px;
		max-width: 360px;
		text-align: center;
		transition: background 0.3s ease; 
	}
	.evg-topic-cell:hover { background: var(--evg-obsidian-elevated); }
	.evg-topic-cell:hover .evg-icon { color: var(--evg-gold-primary); transform: translateY(-2px); }
	.evg-icon { color: var(--evg-text-ash); margin-bottom: 0.75rem; transition: all 0.3s ease; }

	.btn-evg-executive {
		background: var(--evg-gold-primary); 
		color: var(--evg-text-charcoal) !important;
		font-size: 0.82rem; 
		font-weight: 800; 
		letter-spacing: 0.15em; 
		text-transform: uppercase;
		border: none; 
		border-radius: 4px; 
		padding: 1.1rem 2rem; 
		display: flex; 
		align-items: center; 
		justify-content: center; 
		width: 100%;
		transition: all 0.3s ease; 
		cursor: pointer; 
		text-decoration: none;
		box-sizing: border-box;
	}
	.btn-evg-executive:hover { 
		background: var(--evg-gold-light); 
		box-shadow: 0 0 25px rgba(212, 175, 55, 0.3); 
	}

	/* Responsive Media Queries */
	@media (max-width: 991.98px) {
		.evg-contact-layout { grid-template-columns: 1fr; }
	}

	@media (max-width: 767.98px) {
		.evg-container { padding: 2rem 15px 4rem 15px; }
		.evg-module { padding: 25px 15px !important; }
		.evg-contact-layout > div > form > div[style*="grid-template-columns"] {
			grid-template-columns: 1fr !important;
			gap: 15px !important;
		}
	}
</style>

<main class="evg-master-wrapper">
	<div class="evg-container">

		<!-- 1. EDITORIAL HEADER -->
		<header style="text-align: center; margin-bottom: 35px; padding-bottom: 20px; border-bottom: 1px solid var(--evg-border-hairline);">
			<span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( '01 // Secure Communications', 'evg' ); ?></span>
			<h1 class="evg-title-xl"><?php esc_html_e( 'Contact', 'evg' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Elite Vault Grading', 'evg' ); ?></span></h1>
			<p style="color: var(--evg-text-ash); max-width: 680px; margin: 0 auto; font-size: 0.92rem; line-height: 1.6;">
				<?php esc_html_e( 'Direct communications portal for lot allocations, submission package queries, and active grading consignment verification.', 'evg' ); ?>
			</p>
		</header>

		<!-- 2. TWO-COLUMN COMMAND LAYOUT -->
		<div class="evg-contact-layout" style="margin-bottom: 40px;">

			<!-- Left: Secure Ticket Form -->
			<div class="evg-module" style="padding: 30px 20px;">
				<div style="margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--evg-border-hairline);">
					<h2 style="color: #ffffff; font-size: 1.15rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Open Secure Ticket', 'evg' ); ?></h2>
					<p style="color: var(--evg-text-ash); font-size: 0.82rem; margin: 0;">
						<?php esc_html_e( 'For active consignments, please include your Order Reference Number to expedite routing.', 'evg' ); ?>
					</p>
				</div>

				<!-- Status Feedback Notification -->
				<?php if ( 'success' === $evg_contact_status ) : ?>
					<div style="background: rgba(52, 199, 89, 0.08); border: 1px solid rgba(52, 199, 89, 0.3); border-radius: 4px; padding: 14px; margin-bottom: 20px;">
						<span style="color: #34c759; font-weight: 700; font-size: 0.82rem; display: block; margin-bottom: 4px;">&#10003; <?php esc_html_e( 'Transmission Delivered', 'evg' ); ?></span>
						<p style="color: #e5e5ea; font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php echo esc_html( $evg_contact_message ); ?></p>
					</div>
				<?php elseif ( 'error' === $evg_contact_status ) : ?>
					<div style="background: rgba(255, 69, 58, 0.08); border: 1px solid rgba(255, 69, 58, 0.3); border-radius: 4px; padding: 14px; margin-bottom: 20px;">
						<span style="color: #ff453a; font-weight: 700; font-size: 0.82rem; display: block; margin-bottom: 4px;">&times; <?php esc_html_e( 'Transmission Error', 'evg' ); ?></span>
						<p style="color: #e5e5ea; font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php echo esc_html( $evg_contact_message ); ?></p>
					</div>
				<?php endif; ?>

				<form action="<?php echo esc_url( get_permalink() ); ?>" method="POST">
					<?php wp_nonce_field( 'evg_contact_enquiry_action', 'evg_contact_nonce' ); ?>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
						<div>
							<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Registered Name', 'evg' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
							<input type="text" name="customer_name" class="evg-form-control" placeholder="<?php esc_attr_e( 'e.g. John Doe', 'evg' ); ?>" value="<?php echo isset( $evg_form_data['customer_name'] ) ? esc_attr( $evg_form_data['customer_name'] ) : ''; ?>" required>
						</div>
						<div>
							<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Account Email', 'evg' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
							<input type="email" name="email_address" class="evg-form-control" placeholder="<?php esc_attr_e( 'client@example.com', 'evg' ); ?>" value="<?php echo isset( $evg_form_data['email_address'] ) ? esc_attr( $evg_form_data['email_address'] ) : ''; ?>" required>
						</div>
					</div>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
						<div>
							<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Order Ref (Optional)', 'evg' ); ?></label>
							<input type="text" name="order_number" class="evg-form-control" placeholder="<?php esc_attr_e( 'e.g. EVG-84920', 'evg' ); ?>" value="<?php echo isset( $evg_form_data['order_number'] ) ? esc_attr( $evg_form_data['order_number'] ) : ''; ?>">
						</div>
						<div>
							<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Enquiry Category', 'evg' ); ?></label>
							<?php $evg_current_cat = isset( $evg_form_data['feedback_type'] ) ? $evg_form_data['feedback_type'] : 'General Enquiry'; ?>
							<select name="enquiry_category" class="evg-form-control" style="cursor: pointer;">
								<option value="General Enquiry" <?php selected( $evg_current_cat, 'General Enquiry' ); ?>><?php esc_html_e( 'General Enquiry', 'evg' ); ?></option>
								<option value="Grading Diagnostics" <?php selected( $evg_current_cat, 'Grading Diagnostics' ); ?>><?php esc_html_e( 'Grading Diagnostics & Standards', 'evg' ); ?></option>
								<option value="Submission Intake" <?php selected( $evg_current_cat, 'Submission Intake' ); ?>><?php esc_html_e( 'Submission Intake & Allocation', 'evg' ); ?></option>
								<option value="Billing & Invoices" <?php selected( $evg_current_cat, 'Billing & Invoices' ); ?>><?php esc_html_e( 'Billing & Invoices', 'evg' ); ?></option>
								<option value="Collector Feedback" <?php selected( $evg_current_cat, 'Collector Feedback' ); ?>><?php esc_html_e( 'Collector Feedback', 'evg' ); ?></option>
							</select>
						</div>
					</div>

					<div style="margin-bottom: 20px;">
						<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Transmission Message', 'evg' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
						<textarea name="message" class="evg-form-control" rows="5" placeholder="<?php esc_attr_e( 'Detail your operational query or consignment questions...', 'evg' ); ?>" required><?php echo isset( $evg_form_data['message_content'] ) ? esc_textarea( $evg_form_data['message_content'] ) : ''; ?></textarea>
					</div>

					<button type="submit" class="btn-evg-executive">
						<?php esc_html_e( 'Submit Enquiry', 'evg' ); ?>
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left: 8px;">
							<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
						</svg>
					</button>
				</form>
			</div>

			<!-- Right: Operational Meta & SLAs -->
			<div class="evg-module" style="padding: 30px 20px; display: flex; flex-direction: column;">
				<span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Service Level Agreements', 'evg' ); ?></span>
				<h2 style="color: #ffffff; font-size: 1.15rem; font-weight: 700; margin: 0 0 16px 0;"><?php esc_html_e( 'Operational Meta', 'evg' ); ?></h2>

				<ul class="evg-meta-list" style="margin-bottom: 20px;">
					<li>
						<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Jurisdiction / Hub', 'evg' ); ?></span>
						<span style="color: #ffffff; font-weight: 600;"><?php esc_html_e( 'United Kingdom', 'evg' ); ?></span>
					</li>
					<li>
						<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Support Response SLA', 'evg' ); ?></span>
						<span style="color: #ffffff; font-family: monospace; font-weight: 700;"><?php esc_html_e( '24 - 48 HRS', 'evg' ); ?></span>
					</li>
					<li>
						<span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Active Turnaround SLA', 'evg' ); ?></span>
						<span style="color: var(--evg-gold-primary); font-family: monospace; font-weight: 700;"><?php echo esc_html( $evg_turnaround_time ); ?></span>
					</li>
				</ul>

				<!-- Facility Schedule -->
				<div style="background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 16px; margin-bottom: 20px;">
					<span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Facility Operating Hours (GMT)', 'evg' ); ?></span>
					<div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 6px; color: #ffffff;">
						<span><?php esc_html_e( 'Mon - Fri:', 'evg' ); ?></span>
						<span style="font-family: monospace; font-weight: 600;">09:00 - 17:30</span>
					</div>
					<div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: var(--evg-text-ash);">
						<span><?php esc_html_e( 'Sat - Sun:', 'evg' ); ?></span>
						<span><?php esc_html_e( 'Closed (Vault Intake Only)', 'evg' ); ?></span>
					</div>
				</div>

				<!-- Support Email Routing -->
				<div style="margin-top: auto; padding-top: 16px; border-top: 1px solid var(--evg-border-hairline);">
					<span class="evg-label-micro" style="margin-bottom: 6px; color: var(--evg-gold-light);"><?php esc_html_e( 'Direct Dispatch Routing', 'evg' ); ?></span>
					<a href="<?php echo esc_url( 'mailto:' . antispambot( $evg_support_email ) ); ?>" style="color: #ffffff; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 8px; transition: color 0.2s; word-break: break-all;">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-primary)" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
						<?php echo esc_html( antispambot( $evg_support_email ) ); ?>
					</a>
				</div>
			</div>
		</div>

		<!-- 3. TRIAGE / SUPPORT MATRIX (Centered) -->
		<div>
			<div style="text-align: center; margin-bottom: 20px;">
				<span class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( '02 // Support Infrastructure', 'evg' ); ?></span>
				<h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'Diagnostic Support Matrix', 'evg' ); ?></h2>
			</div>

			<div class="evg-topic-matrix">
				<div class="evg-topic-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'General Enquiries', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Operations, drop announcements, and platform architecture updates.', 'evg' ); ?></p>
				</div>
				<div class="evg-topic-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 2 7 12 22 22 7 12 2"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Grading Diagnostics', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Centring tolerances, sub-grades, and whole-number (1-10) scale standards.', 'evg' ); ?></p>
				</div>
				<div class="evg-topic-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Submission Intake', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Declaration formatting, grading intake windows, and bulk consignment allocations.', 'evg' ); ?></p>
				</div>
				<div class="evg-topic-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Secure Logistics', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Semi-rigid packing, insured Royal Mail delivery, and check-in scans.', 'evg' ); ?></p>
				</div>
				<div class="evg-topic-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Account & Billing', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Profile details, tax receipts, invoices, and transaction reconciliation.', 'evg' ); ?></p>
				</div>
				<div class="evg-topic-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Feedback & Reviews', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Service reviews, recommendations, and public website testimonials.', 'evg' ); ?></p>
				</div>
			</div>
		</div>

	</div>
</main>

<?php
get_footer();