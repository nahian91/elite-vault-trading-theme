<?php
/**
 * Template Name: Feedback - Executive Tier
 * Description: Fully dynamic customer feedback and testimonial intake portal for Elite Vault Grading.
 *              Directly connects with the admin feedback ledger, handles security nonce verification,
 *              audits activity, and provides a luxury obsidian/gold UI without background grid lines.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// -------------------------------------------------------------------------
// 1. BACKEND FEEDBACK INTAKE & STORAGE ENGINE
// -------------------------------------------------------------------------
$feedback_status  = '';
$feedback_message = '';
$form_data        = array();

$support_email = get_option( 'evg_support_email', 'support@elitevaultgrading.com' );

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['evg_feedback_submission_nonce'] ) ) {
    if ( wp_verify_nonce( sanitize_key( $_POST['evg_feedback_submission_nonce'] ), 'evg_submit_feedback_action' ) ) {
        
        $customer_name     = sanitize_text_field( wp_unslash( $_POST['feedback_name'] ?? '' ) );
        $email_address     = sanitize_email( wp_unslash( $_POST['feedback_email'] ?? '' ) );
        $order_number      = sanitize_text_field( wp_unslash( $_POST['feedback_order_num'] ?? '' ) );
        $feedback_type     = sanitize_text_field( wp_unslash( $_POST['feedback_type'] ?? 'General Feedback' ) );
        $raw_rating        = isset( $_POST['experience_rating'] ) ? intval( $_POST['experience_rating'] ) : 5;
        $rating            = max( 1, min( 5, $raw_rating ) );
        $feedback_text     = sanitize_textarea_field( wp_unslash( $_POST['feedback_text'] ?? '' ) );
        $raw_recommend     = sanitize_text_field( wp_unslash( $_POST['recommend'] ?? 'Yes' ) );
        $permission_to_use = isset( $_POST['permission_use'] ) ? 1 : 0;

        $allowed_recs = array( 'Yes', 'No', 'Not sure' );
        $recommend    = in_array( $raw_recommend, $allowed_recs, true ) ? $raw_recommend : 'Not sure';

        $form_data = compact( 'customer_name', 'email_address', 'order_number', 'feedback_type', 'rating', 'feedback_text', 'recommend', 'permission_to_use' );

        // Rate limiting cooldown (60-second window per IP)
        $client_ip = '0.0.0.0';
        if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $raw_ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
            if ( filter_var( $raw_ip, FILTER_VALIDATE_IP ) ) {
                $client_ip = $raw_ip;
            }
        }
        $ip_hash      = md5( $client_ip );
        $cooldown_key = 'evg_feedback_cooldown_' . $ip_hash;

        // Validation Checks
        if ( empty( $feedback_text ) ) {
            $feedback_status  = 'error';
            $feedback_message = __( 'Please provide your detailed experience message.', 'evg-platform' );
        } elseif ( ! empty( $email_address ) && ! is_email( $email_address ) ) {
            $feedback_status  = 'error';
            $feedback_message = __( 'Please provide a valid email address.', 'evg-platform' );
        } elseif ( false !== get_transient( $cooldown_key ) ) {
            $feedback_status  = 'error';
            $feedback_message = __( 'Please wait a moment before submitting another report.', 'evg-platform' );
        } else {
            global $wpdb;
            $table_feedback = $wpdb->prefix . 'evg_feedback';

            // Insert into wp_evg_feedback table (Fully synced with inc/feedback.php)
            $inserted = $wpdb->insert(
                $table_feedback,
                array(
                    'customer_name'     => $customer_name,
                    'email_address'     => $email_address,
                    'order_number'      => $order_number,
                    'feedback_type'     => $feedback_type,
                    'rating'            => $rating,
                    'feedback_text'     => $feedback_text,
                    'recommend'         => $recommend,
                    'permission_to_use' => $permission_to_use,
                    'status'            => 'Pending',
                    'admin_notes'       => '',
                    'submitted_at'      => current_time( 'mysql' ),
                ),
                array( '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s' )
            );

            if ( false !== $inserted ) {
                // Dispatch email alert with sanitized header construction
                $clean_name = preg_replace( "/[\r\n]+/", '', ( $customer_name ? $customer_name : 'Collector' ) );
                $subject    = sprintf( '[EVG Feedback] %d-Star Review from %s', $rating, $clean_name );
                $body       = "New feedback submitted to the platform:\n\n";
                $body      .= "Customer: {$clean_name}\n";
                $body      .= "Email: " . ( $email_address ? $email_address : 'Not provided' ) . "\n";
                $body      .= "Order Ref: " . ( $order_number ? '#' . $order_number : 'N/A' ) . "\n";
                $body      .= "Scope: {$feedback_type}\n";
                $body      .= "Rating: {$rating}/5 Stars\n";
                $body      .= "Recommendation: {$recommend}\n";
                $body      .= "Marketing Permission: " . ( $permission_to_use ? 'Granted' : 'Private' ) . "\n\n";
                $body      .= "Feedback Message:\n{$feedback_text}\n\n";
                $body      .= "---\nManage this entry via EVG Database > Feedback in WP-Admin.";

                $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
                if ( ! empty( $email_address ) ) {
                    $headers[] = 'Reply-To: ' . $clean_name . ' <' . $email_address . '>';
                }

                wp_mail( $support_email, $subject, $body, $headers );

                // Set 60-second cooldown
                set_transient( $cooldown_key, 1, 60 );

                // Activity Logging
                if ( class_exists( 'Elite_Vault_Grading_System' ) && method_exists( 'Elite_Vault_Grading_System', 'log_activity' ) ) {
                    Elite_Vault_Grading_System::log_activity( "Customer Feedback Logged ({$rating} Stars) by " . ( $email_address ? $email_address : 'Guest' ) );
                }

                $form_data        = array();
                $feedback_status  = 'success';
                $feedback_message = __( 'Thank you for your feedback. Your report has been securely registered with our quality assurance desk.', 'evg-platform' );
            } else {
                $feedback_status  = 'error';
                $feedback_message = __( 'A database error occurred while registering your report. Please try again.', 'evg-platform' );
            }
        }
    }
}

// Prefill collector information if logged in
if ( empty( $form_data ) && is_user_logged_in() ) {
    $current_user                 = wp_get_current_user();
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
    overflow-x: hidden;
  }
  
  .evg-container {
    max-width: 860px;
    margin: 0 auto;
    padding: 3rem 15px 5rem 15px;
  }

  .evg-title-xl { 
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(2rem, 3.5vw, 2.8rem); 
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
    padding: 30px 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
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

  .evg-control-matrix {
    display: flex; 
    flex-wrap: wrap; 
    gap: 1px;
    background: var(--evg-border-hairline);
    border: 1px solid var(--evg-border-hairline);
    border-radius: 6px;
    overflow: hidden;
  }
  
  .evg-control-cell {
    flex: 1;
    min-width: 60px;
    background: var(--evg-obsidian-panel);
    padding: 1rem 0.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    user-select: none;
  }
  
  .evg-control-cell span {
    font-size: 0.75rem;
    color: var(--evg-text-ash);
    font-family: monospace;
    font-weight: 700;
  }

  .evg-control-cell svg {
    color: var(--evg-text-ash);
    opacity: 0.4;
    transition: all 0.2s ease;
  }

  .evg-control-cell input[type="radio"] { display: none; }

  .evg-control-cell:hover { background: var(--evg-obsidian-elevated); }
  
  .evg-control-cell:has(input:checked) {
    background: rgba(212, 175, 55, 0.08);
  }
  .evg-control-cell:has(input:checked) span { color: var(--evg-gold-primary); }
  .evg-control-cell:has(input:checked) svg { 
    color: var(--evg-gold-primary); 
    opacity: 1; 
    fill: var(--evg-gold-primary); 
  }

  .evg-checkbox {
    appearance: none;
    -webkit-appearance: none;
    background-color: var(--evg-obsidian-elevated);
    margin: 0;
    margin-top: 3px;
    flex-shrink: 0;
    width: 1.15em; 
    height: 1.15em;
    border: 1px solid #2a2d35;
    border-radius: 3px;
    display: grid; 
    place-content: center;
    cursor: pointer; 
    transition: all 0.2s ease;
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
  @media (max-width: 767.98px) {
    .evg-container { padding: 2rem 15px 4rem 15px; }
    .evg-module { padding: 25px 15px; }
    .evg-control-matrix { flex-direction: column; }
    .evg-control-cell { flex-direction: row; justify-content: flex-start; padding: 0.8rem 1rem; gap: 10px; }
    div[style*="grid-template-columns: repeat(auto-fit"] { grid-template-columns: 1fr !important; gap: 12px !important; }
  }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- 1. EDITORIAL HEADER -->
        <header style="text-align: center; margin-bottom: 35px;">
            <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( '01 // Quality Assurance', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Collector', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Experience & Reviews', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 620px; margin: 0 auto; font-size: 0.92rem; line-height: 1.6;">
                <?php esc_html_e( 'Your feedback directly guides our certification standards. We review all submissions to continuously optimize our grading workflows and UK facilities.', 'evg-platform' ); ?>
            </p>
        </header>

        <!-- 2. FEEDBACK MODULE -->
        <div class="evg-module" style="margin-bottom: 25px;">
            
            <!-- STATUS NOTIFICATION BANNER -->
            <?php if ( 'success' === $feedback_status ) : ?>
                <div style="background: rgba(52, 199, 89, 0.08); border: 1px solid rgba(52, 199, 89, 0.3); border-radius: 4px; padding: 20px; text-align: center; margin-bottom: 25px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(52, 199, 89, 0.1); border: 1px solid rgba(52, 199, 89, 0.4); color: #34c759; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <h3 style="color: #ffffff; font-size: 1.1rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Report Registered Successfully', 'evg-platform' ); ?></h3>
                    <p style="color: #e5e5ea; font-size: 0.82rem; margin: 0; line-height: 1.5;"><?php echo esc_html( $feedback_message ); ?></p>
                </div>
            <?php elseif ( 'error' === $feedback_status ) : ?>
                <div style="background: rgba(255, 69, 58, 0.08); border: 1px solid rgba(255, 69, 58, 0.3); border-radius: 4px; padding: 14px; margin-bottom: 20px;">
                    <span style="color: #ff453a; font-weight: 700; font-size: 0.82rem; display: block; margin-bottom: 4px;">✕ <?php esc_html_e( 'Submission Error', 'evg-platform' ); ?></span>
                    <p style="color: #e5e5ea; font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php echo esc_html( $feedback_message ); ?></p>
                </div>
            <?php endif; ?>

            <form action="<?php echo esc_url( add_query_arg( array(), get_permalink() ) ); ?>" method="post" class="evg-feedback-form">
                <?php wp_nonce_field( 'evg_submit_feedback_action', 'evg_feedback_submission_nonce' ); ?>
                
                <!-- IDENTIFICATION SECTION -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Registered Name (Optional)', 'evg-platform' ); ?></label>
                        <input type="text" name="feedback_name" class="evg-form-control" placeholder="e.g. John Doe" value="<?php echo esc_attr( $form_data['customer_name'] ?? '' ); ?>">
                    </div>
                    <div>
                        <label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Account Email (Optional)', 'evg-platform' ); ?></label>
                        <input type="email" name="feedback_email" class="evg-form-control" placeholder="client@example.com" value="<?php echo esc_attr( $form_data['email_address'] ?? '' ); ?>">
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Order Reference Number (Optional)', 'evg-platform' ); ?></label>
                        <input type="text" name="feedback_order_num" class="evg-form-control" placeholder="e.g. EVG-84920" value="<?php echo esc_attr( $form_data['order_number'] ?? '' ); ?>">
                    </div>
                </div>

                <div style="height: 1px; background: var(--evg-border-hairline); margin: 25px 0;"></div>

                <!-- CATEGORY -->
                <div style="margin-bottom: 25px;">
                    <label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Feedback Scope / Category', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                    <select name="feedback_type" class="evg-form-control" style="cursor: pointer;" required>
                        <option value="General Feedback" <?php selected( ( $form_data['feedback_type'] ?? '' ), 'General Feedback' ); ?>><?php esc_html_e( 'General Feedback', 'evg-platform' ); ?></option>
                        <option value="Grading Experience" <?php selected( ( $form_data['feedback_type'] ?? '' ), 'Grading Experience' ); ?>><?php esc_html_e( 'Grading Quality & Accuracy', 'evg-platform' ); ?></option>
                        <option value="Submission Process" <?php selected( ( $form_data['feedback_type'] ?? '' ), 'Submission Process' ); ?>><?php esc_html_e( 'Submission & Intake Process', 'evg-platform' ); ?></option>
                        <option value="Website Experience" <?php selected( ( $form_data['feedback_type'] ?? '' ), 'Website Experience' ); ?>><?php esc_html_e( 'Portal & Tracking Experience', 'evg-platform' ); ?></option>
                        <option value="Customer Service" <?php selected( ( $form_data['feedback_type'] ?? '' ), 'Customer Service' ); ?>><?php esc_html_e( 'Customer Support Desk', 'evg-platform' ); ?></option>
                        <option value="Suggestion for Improvement" <?php selected( ( $form_data['feedback_type'] ?? '' ), 'Suggestion for Improvement' ); ?>><?php esc_html_e( 'Platform Suggestion / Feature Request', 'evg-platform' ); ?></option>
                    </select>
                </div>

                <!-- STAR RATING (Control Matrix) -->
                <div style="margin-bottom: 25px;">
                    <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Overall Rating', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                    <div class="evg-control-matrix">
                        <?php 
                        $current_rating = max( 1, min( 5, intval( $form_data['rating'] ?? 5 ) ) );
                        for ( $i = 1; $i <= 5; $i++ ) : 
                        ?>
                            <label class="evg-control-cell">
                                <input type="radio" name="experience_rating" value="<?php echo esc_attr( $i ); ?>" <?php checked( $current_rating, $i ); ?> required>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <span><?php echo esc_html( number_format( $i, 1 ) ); ?></span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- FEEDBACK DETAILS -->
                <div style="margin-bottom: 25px;">
                    <label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Your Feedback Message', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                    <textarea name="feedback_text" rows="5" class="evg-form-control" placeholder="<?php esc_attr_e( 'Please provide detailed impressions regarding your card grading, packaging, or customer service experience...', 'evg-platform' ); ?>" required><?php echo esc_textarea( $form_data['feedback_text'] ?? '' ); ?></textarea>
                </div>

                <!-- RECOMMENDATION OPTIONS -->
                <div style="margin-bottom: 25px;">
                    <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Would you recommend Elite Vault Grading?', 'evg-platform' ); ?></label>
                    <div class="evg-control-matrix" style="max-width: 440px;">
                        <?php 
                        $current_rec = $form_data['recommend'] ?? 'Yes';
                        $rec_options = array( 'Yes' => 'POSITIVE', 'No' => 'NEGATIVE', 'Not sure' => 'NEUTRAL' );
                        foreach ( $rec_options as $val => $lbl ) :
                        ?>
                            <label class="evg-control-cell">
                                <input type="radio" name="recommend" value="<?php echo esc_attr( $val ); ?>" <?php checked( $current_rec, $val ); ?>>
                                <span><?php echo esc_html( $lbl ); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- TESTIMONIAL PERMISSION CONSENT -->
                <div style="background: var(--evg-obsidian-base); border: 1px solid #1a1c22; border-radius: 4px; padding: 16px; margin-bottom: 25px;">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <input class="evg-checkbox" type="checkbox" name="permission_use" id="permUse" value="1" <?php checked( ( $form_data['permission_to_use'] ?? 1 ), 1 ); ?>>
                        <label style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; cursor: pointer; margin: 0;" for="permUse">
                            <?php esc_html_e( 'I grant Elite Vault Grading permission to feature this feedback as a public verified testimonial on the website.', 'evg-platform' ); ?>
                        </label>
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <button type="submit" class="btn-evg-executive">
                    <?php esc_html_e( 'Transmit Feedback Report', 'evg-platform' ); ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left: 8px;">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
                
            </form>
        </div>
        
        <!-- FOOTER HELP DIRECT ROUTING -->
        <div style="text-align: center;">
            <p style="color: var(--evg-text-ash); font-size: 0.78rem; line-height: 1.6; margin: 0;">
                <?php esc_html_e( 'For immediate technical support regarding active submissions, please contact our dispatch team at', 'evg-platform' ); ?><br>
                <a href="mailto:<?php echo esc_attr( $support_email ); ?>" style="color: #ffffff; text-decoration: underline; margin-top: 4px; display: inline-block; word-break: break-all;">
                    <?php echo esc_html( $support_email ); ?>
                </a>
            </p>
        </div>

    </div>
</main>

<?php get_footer(); ?>