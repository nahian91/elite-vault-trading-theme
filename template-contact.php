<?php
/**
 * Template Name: Contact Us - Executive Tier
 * Description: Fully dynamic, production-ready contact portal for Elite Vault Grading.
 *              Features server-side enquiry dispatch, database feedback logging, dynamic admin settings routing,
 *              audit logging, 5-10 business day turnaround compliance, and a clean luxury dark UI.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// -------------------------------------------------------------------------
// 1. BACKEND ENQUIRY & TICKET DISPATCH ENGINE
// -------------------------------------------------------------------------
$contact_status  = '';
$contact_message = '';
$form_data       = array();

// Fetch Dynamic Admin Settings Configured in inc/settings.php
$support_email   = get_option( 'evg_support_email', 'support@elitevaultgrading.com' );
$turnaround_time = get_option( 'evg_turnaround_time', '5-10 Business Days' );

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['evg_contact_nonce'] ) ) {
    if ( wp_verify_nonce( sanitize_key( $_POST['evg_contact_nonce'] ), 'evg_contact_enquiry_action' ) ) {
        
        $customer_name   = sanitize_text_field( wp_unslash( $_POST['customer_name'] ?? '' ) );
        $email_address   = sanitize_email( wp_unslash( $_POST['email_address'] ?? '' ) );
        $order_number    = sanitize_text_field( wp_unslash( $_POST['order_number'] ?? '' ) );
        $feedback_type   = sanitize_text_field( wp_unslash( $_POST['enquiry_category'] ?? 'General Enquiry' ) );
        $message_content = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

        $form_data = compact( 'customer_name', 'email_address', 'order_number', 'feedback_type', 'message_content' );

        // Client IP Rate Limiting Guard (60-second cooldown)
        $client_ip = '0.0.0.0';
        if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $raw_ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
            if ( filter_var( $raw_ip, FILTER_VALIDATE_IP ) ) {
                $client_ip = $raw_ip;
            }
        }
        $ip_hash      = md5( $client_ip );
        $cooldown_key = 'evg_contact_cooldown_' . $ip_hash;

        // Validation
        if ( empty( $customer_name ) || empty( $email_address ) || empty( $message_content ) ) {
            $contact_status  = 'error';
            $contact_message = __( 'Please complete all required fields (*).', 'evg-platform' );
        } elseif ( ! is_email( $email_address ) ) {
            $contact_status  = 'error';
            $contact_message = __( 'Please supply a valid email address.', 'evg-platform' );
        } elseif ( false !== get_transient( $cooldown_key ) ) {
            $contact_status  = 'error';
            $contact_message = __( 'Please wait a moment before sending another transmission.', 'evg-platform' );
        } else {
            global $wpdb;
            $table_feedback = $wpdb->prefix . 'evg_feedback';

            // 1. Store in EVG Platform Feedback / Support Table
            $wpdb->insert(
                $table_feedback,
                array(
                    'customer_name'     => $customer_name,
                    'email_address'     => $email_address,
                    'order_number'      => $order_number,
                    'feedback_type'     => $feedback_type,
                    'rating'            => 5,
                    'feedback_text'     => $message_content,
                    'recommend'         => 'Yes',
                    'permission_to_use' => 0,
                    'status'            => 'Pending',
                    'admin_notes'       => '',
                    'submitted_at'      => current_time( 'mysql' ),
                ),
                array( '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s' )
            );

            // 2. Dispatch Email to Support Inbox with Sanitized Headers
            $clean_sender_name = preg_replace( "/[\r\n]+/", '', $customer_name );
            $subject           = sprintf( '[EVG Support Ticket] %s from %s', $feedback_type, $clean_sender_name );
            $body              = "New secure support transmission received:\n\n";
            $body             .= "Name: {$clean_sender_name}\n";
            $body             .= "Email: {$email_address}\n";
            if ( ! empty( $order_number ) ) {
                $body .= "Order Ref: #{$order_number}\n";
            }
            $body .= "Category: {$feedback_type}\n";
            $body .= "Time: " . current_time( 'mysql' ) . " GMT\n\n";
            $body .= "Message:\n{$message_content}\n\n";
            $body .= "---\nElite Vault Grading Automated Dispatch Engine";

            $headers = array(
                'Content-Type: text/plain; charset=UTF-8',
                'Reply-To: ' . $clean_sender_name . ' <' . $email_address . '>',
            );

            wp_mail( $support_email, $subject, $body, $headers );

            // 3. Set Rate Limit Cooldown (60 seconds)
            set_transient( $cooldown_key, 1, 60 );

            // 4. Security Audit Logging
            if ( class_exists( 'Elite_Vault_Grading_System' ) && method_exists( 'Elite_Vault_Grading_System', 'log_activity' ) ) {
                Elite_Vault_Grading_System::log_activity( "Support Ticket Opened by {$email_address} (Ref: {$order_number})" );
            }

            // Reset form on success
            $form_data       = array();
            $contact_status  = 'success';
            $contact_message = __( 'Enquiry transmitted securely. A support specialist will respond to your communications array shortly.', 'evg-platform' );
        }
    }
}

// Prefill data if logged in
if ( empty( $form_data ) && is_user_logged_in() ) {
    $current_user               = wp_get_current_user();
    $form_data['customer_name'] = $current_user->display_name;
    $form_data['email_address'] = $current_user->user_email;
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
  }
  
  .evg-container {
    max-width: 1140px;
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

  .evg-contact-layout {
    display: grid;
    grid-template-columns: 1.35fr 1fr;
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

  .evg-meta-list { list-style: none; padding: 0; margin: 0; }
  .evg-meta-list li {
    display: flex; 
    justify-content: space-between; 
    align-items: center;
    padding: 1rem 0; 
    border-bottom: 1px solid var(--evg-border-hairline);
    font-size: 0.88rem;
  }
  .evg-meta-list li:last-child { border-bottom: none; }

  .evg-topic-matrix {
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
    gap: 1px;
    background: var(--evg-border-hairline); 
    border: 1px solid var(--evg-border-hairline); 
    border-radius: 6px; 
    overflow: hidden;
  }
  .evg-topic-cell {
    background: var(--evg-obsidian-panel); 
    padding: 1.8rem 1.5rem; 
    transition: background 0.3s ease; 
  }
  .evg-topic-cell:hover { background: var(--evg-obsidian-elevated); }
  .evg-topic-cell:hover .evg-icon { color: var(--evg-gold-primary); transform: translateY(-2px); }
  .evg-icon { color: var(--evg-text-ash); margin-bottom: 0.85rem; transition: all 0.3s ease; }

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
    cursor: pointer; 
    text-decoration: none;
  }
  .btn-evg-executive:hover { 
    background: var(--evg-gold-light); 
    box-shadow: 0 0 25px rgba(212, 175, 55, 0.3); 
  }

  @media (max-width: 992px) {
    .evg-contact-layout { grid-template-columns: 1fr; }
  }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- 1. EDITORIAL HEADER -->
        <header style="text-align: center; margin-bottom: 45px; padding-bottom: 25px; border-bottom: 1px solid var(--evg-border-hairline);">
            <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( '01 // Secure Communications', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Contact', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Elite Vault Grading', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 680px; margin: 0 auto; font-size: 0.95rem; line-height: 1.6;">
                <?php esc_html_e( 'Direct communications portal for lot allocations, submission package queries, and active grading consignment verification.', 'evg-platform' ); ?>
            </p>
        </header>

        <!-- 2. TWO-COLUMN COMMAND LAYOUT -->
        <div class="evg-contact-layout" style="margin-bottom: 50px;">
            
            <!-- Left: Secure Ticket Form -->
            <div class="evg-module" style="padding: 35px 30px;">
                <div style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--evg-border-hairline);">
                    <h2 style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Open Secure Ticket', 'evg-platform' ); ?></h2>
                    <p style="color: var(--evg-text-ash); font-size: 0.85rem; margin: 0;">
                        <?php esc_html_e( 'For active consignments, please include your Order Reference Number to expedite routing.', 'evg-platform' ); ?>
                    </p>
                </div>

                <!-- Status Feedback Notification -->
                <?php if ( 'success' === $contact_status ) : ?>
                    <div style="background: rgba(52, 199, 89, 0.08); border: 1px solid rgba(52, 199, 89, 0.3); border-radius: 4px; padding: 16px; margin-bottom: 25px;">
                        <span style="color: #34c759; font-weight: 700; font-size: 0.85rem; display: block; margin-bottom: 4px;">✓ <?php esc_html_e( 'Transmission Delivered', 'evg-platform' ); ?></span>
                        <p style="color: #e5e5ea; font-size: 0.82rem; margin: 0; line-height: 1.5;"><?php echo esc_html( $contact_message ); ?></p>
                    </div>
                <?php elseif ( 'error' === $contact_status ) : ?>
                    <div style="background: rgba(255, 69, 58, 0.08); border: 1px solid rgba(255, 69, 58, 0.3); border-radius: 4px; padding: 16px; margin-bottom: 25px;">
                        <span style="color: #ff453a; font-weight: 700; font-size: 0.85rem; display: block; margin-bottom: 4px;">✕ <?php esc_html_e( 'Transmission Error', 'evg-platform' ); ?></span>
                        <p style="color: #e5e5ea; font-size: 0.82rem; margin: 0; line-height: 1.5;"><?php echo esc_html( $contact_message ); ?></p>
                    </div>
                <?php endif; ?>

                <form action="<?php echo esc_url( get_permalink() ); ?>" method="POST">
                    <?php wp_nonce_field( 'evg_contact_enquiry_action', 'evg_contact_nonce' ); ?>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                        <div>
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Registered Name', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                            <input type="text" name="customer_name" class="evg-form-control" placeholder="e.g. John Doe" value="<?php echo esc_attr( $form_data['customer_name'] ?? '' ); ?>" required>
                        </div>
                        <div>
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Account Email', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                            <input type="email" name="email_address" class="evg-form-control" placeholder="client@example.com" value="<?php echo esc_attr( $form_data['email_address'] ?? '' ); ?>" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                        <div>
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Order Ref (Optional)', 'evg-platform' ); ?></label>
                            <input type="text" name="order_number" class="evg-form-control" placeholder="e.g. EVG-84920" value="<?php echo esc_attr( $form_data['order_number'] ?? '' ); ?>">
                        </div>
                        <div>
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Enquiry Category', 'evg-platform' ); ?></label>
                            <?php $current_cat = $form_data['feedback_type'] ?? 'General Enquiry'; ?>
                            <select name="enquiry_category" class="evg-form-control" style="cursor: pointer;">
                                <option value="General Enquiry" <?php selected( $current_cat, 'General Enquiry' ); ?>><?php esc_html_e( 'General Enquiry', 'evg-platform' ); ?></option>
                                <option value="Grading Diagnostics" <?php selected( $current_cat, 'Grading Diagnostics' ); ?>><?php esc_html_e( 'Grading Diagnostics & Standards', 'evg-platform' ); ?></option>
                                <option value="Submission Intake" <?php selected( $current_cat, 'Submission Intake' ); ?>><?php esc_html_e( 'Submission Intake & Allocation', 'evg-platform' ); ?></option>
                                <option value="Billing & Invoices" <?php selected( $current_cat, 'Billing & Invoices' ); ?>><?php esc_html_e( 'Billing & Invoices', 'evg-platform' ); ?></option>
                                <option value="Collector Feedback" <?php selected( $current_cat, 'Collector Feedback' ); ?>><?php esc_html_e( 'Collector Feedback', 'evg-platform' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 24px;">
                        <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Transmission Message', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                        <textarea name="message" class="evg-form-control" rows="5" placeholder="<?php esc_attr_e( 'Detail your operational query or consignment questions...', 'evg-platform' ); ?>" required><?php echo esc_textarea( $form_data['message_content'] ?? '' ); ?></textarea>
                    </div>

                    <button type="submit" class="btn-evg-executive">
                        <?php esc_html_e( 'Submit Enquiry', 'evg-platform' ); ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left: 8px;">
                            <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Right: Operational Meta & SLAs -->
            <div class="evg-module" style="padding: 35px 30px; display: flex; flex-direction: column;">
                <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Service Level Agreements', 'evg-platform' ); ?></span>
                <h2 style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0 0 20px 0;"><?php esc_html_e( 'Operational Meta', 'evg-platform' ); ?></h2>
                
                <ul class="evg-meta-list" style="margin-bottom: 25px;">
                    <li>
                        <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Jurisdiction / Hub', 'evg-platform' ); ?></span>
                        <span style="color: #ffffff; font-weight: 600;"><?php esc_html_e( 'United Kingdom', 'evg-platform' ); ?></span>
                    </li>
                    <li>
                        <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Support Response SLA', 'evg-platform' ); ?></span>
                        <span style="color: #ffffff; font-family: monospace; font-weight: 700;"><?php esc_html_e( '24 - 48 HRS', 'evg-platform' ); ?></span>
                    </li>
                    <li>
                        <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Active Turnaround SLA', 'evg-platform' ); ?></span>
                        <span style="color: var(--evg-gold-primary); font-family: monospace; font-weight: 700;"><?php echo esc_html( $turnaround_time ); ?></span>
                    </li>
                </ul>

                <!-- Facility Schedule -->
                <div style="background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                    <span class="evg-label-micro" style="margin-bottom: 12px;"><?php esc_html_e( 'Facility Operating Hours (GMT)', 'evg-platform' ); ?></span>
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 8px; color: #ffffff;">
                        <span><?php esc_html_e( 'Mon - Fri:', 'evg-platform' ); ?></span>
                        <span style="font-family: monospace; font-weight: 600;">09:00 - 17:30</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--evg-text-ash);">
                        <span><?php esc_html_e( 'Sat - Sun:', 'evg-platform' ); ?></span>
                        <span><?php esc_html_e( 'Closed (Vault Intake Only)', 'evg-platform' ); ?></span>
                    </div>
                </div>

                <!-- Support Email Routing -->
                <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--evg-border-hairline);">
                    <span class="evg-label-micro" style="margin-bottom: 6px; color: var(--evg-gold-light);"><?php esc_html_e( 'Direct Dispatch Routing', 'evg-platform' ); ?></span>
                    <a href="mailto:<?php echo esc_attr( $support_email ); ?>" style="color: #ffffff; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; transition: color 0.2s;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-primary)" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <?php echo esc_html( $support_email ); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- 3. TRIAGE / SUPPORT MATRIX -->
        <div>
            <div style="text-align: center; margin-bottom: 25px;">
                <span class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( '02 // Support Infrastructure', 'evg-platform' ); ?></span>
                <h2 style="color: #ffffff; font-size: 1.4rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'Diagnostic Support Matrix', 'evg-platform' ); ?></h2>
            </div>
            
            <div class="evg-topic-matrix">
                <div class="evg-topic-cell">
                    <svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'General Enquiries', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Operations, drop announcements, and platform architecture updates.', 'evg-platform' ); ?></p>
                </div>
                <div class="evg-topic-cell">
                    <svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 2 7 12 22 22 7 12 2"/></svg>
                    <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Grading Diagnostics', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Centring tolerances, sub-grades, and whole-number (1-10) scale standards.', 'evg-platform' ); ?></p>
                </div>
                <div class="evg-topic-cell">
                    <svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Submission Intake', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Declaration formatting, grading intake windows, and bulk consignment allocations.', 'evg-platform' ); ?></p>
                </div>
                <div class="evg-topic-cell">
                    <svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Secure Logistics', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Semi-rigid packing, insured Royal Mail delivery, and check-in scans.', 'evg-platform' ); ?></p>
                </div>
                <div class="evg-topic-cell">
                    <svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Account & Billing', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Profile details, tax receipts, invoices, and transaction reconciliation.', 'evg-platform' ); ?></p>
                </div>
                <div class="evg-topic-cell">
                    <svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Feedback & Reviews', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Service reviews, recommendations, and public website testimonials.', 'evg-platform' ); ?></p>
                </div>
            </div>
        </div>

    </div>
</main>

<?php get_footer(); ?>