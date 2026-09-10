<?php
/**
 * Template Name: Create Account - UK Registry
 * Description: Fully dynamic UK-exclusive account registration template for Elite Vault Grading.
 *              Includes incoming redirect preservation (e.g. from /marketplace, /submit, or /verify),
 *              automatic post-registration authentication, metadata binding, password show/hide eye toggles,
 *              and fixed, non-editable United Kingdom country lock.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// -------------------------------------------------------------------------
// 1. RESOLVE REDIRECT TARGET & GUEST GATING
// -------------------------------------------------------------------------
$redirect_to = '';
if ( isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) ) {
    $raw_redirect = esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) );
    $redirect_to  = wp_validate_redirect( $raw_redirect, home_url( '/my-account' ) );
}

// Redirect if already authenticated
if ( is_user_logged_in() && 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
    $current_user = wp_get_current_user();
    $staff_roles  = array( 'administrator', 'head_grader', 'grader', 'support_team' );
    
    if ( ! empty( array_intersect( $staff_roles, (array) $current_user->roles ) ) ) {
        wp_safe_redirect( admin_url( 'admin.php?page=evg_management_system&tab=dashboard' ) );
    } elseif ( ! empty( $redirect_to ) ) {
        wp_safe_redirect( $redirect_to );
    } else {
        wp_safe_redirect( home_url( '/my-account' ) );
    }
    exit;
}

// -------------------------------------------------------------------------
// 2. BACKEND REGISTRATION ENGINE
// -------------------------------------------------------------------------
$registration_errors = new WP_Error();
$form_data           = array();

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['evg_register_nonce'] ) ) {
    if ( wp_verify_nonce( sanitize_key( $_POST['evg_register_nonce'] ), 'evg_user_registration_action' ) ) {
        
        // Sanitize incoming fields
        $first_name       = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
        $last_name        = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
        $email            = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $confirm_email    = isset( $_POST['confirm_email'] ) ? sanitize_email( wp_unslash( $_POST['confirm_email'] ) ) : '';
        $mobile_number    = isset( $_POST['mobile_number'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile_number'] ) ) : '';
        $username         = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
        $password         = isset( $_POST['password'] ) ? (string) $_POST['password'] : '';
        $confirm_password = isset( $_POST['confirm_password'] ) ? (string) $_POST['confirm_password'] : '';
        
        // UK Address parameters (Country strictly locked to United Kingdom)
        $house_number   = isset( $_POST['house_number'] ) ? sanitize_text_field( wp_unslash( $_POST['house_number'] ) ) : '';
        $street_address = isset( $_POST['street_address'] ) ? sanitize_text_field( wp_unslash( $_POST['street_address'] ) ) : '';
        $town_city      = isset( $_POST['town_city'] ) ? sanitize_text_field( wp_unslash( $_POST['town_city'] ) ) : '';
        $county         = isset( $_POST['county'] ) ? sanitize_text_field( wp_unslash( $_POST['county'] ) ) : '';
        $raw_postcode   = isset( $_POST['postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) ) : '';
        $postcode       = strtoupper( trim( preg_replace( '/\s+/', ' ', $raw_postcode ) ) );
        $country        = 'United Kingdom';
        
        // Preferences & Compliance Checkboxes
        $pref_updates  = isset( $_POST['pref_updates'] ) ? 'yes' : 'no';
        $pref_offers   = isset( $_POST['pref_offers'] ) ? 'yes' : 'no';
        $terms_agree   = isset( $_POST['terms_agree'] );
        $privacy_agree = isset( $_POST['privacy_agree'] );
        $age_check     = isset( $_POST['age_check'] );

        // Retain values on validation failure
        $form_data = compact(
            'first_name', 'last_name', 'email', 'confirm_email', 'mobile_number',
            'username', 'house_number', 'street_address', 'town_city', 'county',
            'postcode', 'country', 'pref_updates', 'pref_offers'
        );

        // Validation Checks
        if ( empty( $first_name ) ) {
            $registration_errors->add( 'empty_first_name', __( 'First Name is required.', 'evg-platform' ) );
        }

        if ( empty( $email ) || ! is_email( $email ) ) {
            $registration_errors->add( 'invalid_email', __( 'A valid email address is required.', 'evg-platform' ) );
        } elseif ( strtolower( $email ) !== strtolower( $confirm_email ) ) {
            $registration_errors->add( 'email_mismatch', __( 'Email addresses do not match.', 'evg-platform' ) );
        } elseif ( email_exists( $email ) ) {
            $registration_errors->add( 'email_exists', __( 'An account with this email address already exists.', 'evg-platform' ) );
        }

        // Generate username if omitted
        if ( empty( $username ) ) {
            $email_parts = explode( '@', $email );
            $base_user   = sanitize_user( current( $email_parts ), true );
            if ( empty( $base_user ) ) {
                $base_user = 'collector';
            }
            $username = $base_user;
            $suffix   = 1;
            while ( username_exists( $username ) ) {
                $username = $base_user . $suffix;
                $suffix++;
            }
        } elseif ( username_exists( $username ) ) {
            $registration_errors->add( 'username_exists', __( 'This username is already taken. Please choose another.', 'evg-platform' ) );
        }

        // Strict Cryptographic Password Policy
        if ( mb_strlen( $password ) < 8 ) {
            $registration_errors->add( 'password_len', __( 'Password must be at least 8 characters long.', 'evg-platform' ) );
        }
        if ( ! preg_match( '/[A-Z]/', $password ) ) {
            $registration_errors->add( 'password_upper', __( 'Password must contain at least one uppercase letter.', 'evg-platform' ) );
        }
        if ( ! preg_match( '/[a-z]/', $password ) ) {
            $registration_errors->add( 'password_lower', __( 'Password must contain at least one lowercase letter.', 'evg-platform' ) );
        }
        if ( ! preg_match( '/[0-9]/', $password ) ) {
            $registration_errors->add( 'password_num', __( 'Password must contain at least one number.', 'evg-platform' ) );
        }
        if ( ! preg_match( '/[!@#$%^&*()\-_=+{};:,<.>]/', $password ) ) {
            $registration_errors->add( 'password_spec', __( 'Password must contain at least one special character.', 'evg-platform' ) );
        }
        if ( $password !== $confirm_password ) {
            $registration_errors->add( 'password_mismatch', __( 'Passwords do not match.', 'evg-platform' ) );
        }

        // Mandatory UK Address Check
        if ( empty( $house_number ) || empty( $street_address ) || empty( $town_city ) || empty( $postcode ) ) {
            $registration_errors->add( 'empty_address', __( 'Please complete all address fields.', 'evg-platform' ) );
        }

        // Mandatory Agreement Check
        if ( ! $terms_agree || ! $privacy_agree || ! $age_check ) {
            $registration_errors->add( 'terms_required', __( 'You must agree to the Terms, Privacy Policy, and confirm you are over 18.', 'evg-platform' ) );
        }

        // Process Registration & Auto-Login
        if ( empty( $registration_errors->get_error_messages() ) ) {
            $user_id = wp_create_user( $username, $password, $email );

            if ( is_wp_error( $user_id ) ) {
                $registration_errors->add( 'creation_failed', $user_id->get_error_message() );
            } else {
                wp_update_user( array(
                    'ID'           => $user_id,
                    'first_name'   => $first_name,
                    'last_name'    => $last_name,
                    'display_name' => trim( $first_name . ' ' . $last_name ),
                ) );

                // Store Custom User Meta
                update_user_meta( $user_id, 'evg_house_number', $house_number );
                update_user_meta( $user_id, 'evg_street_address', $street_address );
                update_user_meta( $user_id, 'evg_town_city', $town_city );
                update_user_meta( $user_id, 'evg_county', $county );
                update_user_meta( $user_id, 'evg_postcode', $postcode );
                update_user_meta( $user_id, 'evg_country', 'United Kingdom' );
                update_user_meta( $user_id, 'evg_mobile_number', $mobile_number );
                update_user_meta( $user_id, 'evg_pref_updates', $pref_updates );
                update_user_meta( $user_id, 'evg_opt_in_promotions', $pref_offers );
                update_user_meta( $user_id, 'evg_age_consent', 'yes' );

                if ( class_exists( 'Elite_Vault_Grading_System' ) && method_exists( 'Elite_Vault_Grading_System', 'log_activity' ) ) {
                    Elite_Vault_Grading_System::log_activity( "Registered New Collector Account: {$email} (User ID #{$user_id})" );
                }

                // Automatically authenticate user session
                wp_set_current_user( $user_id );
                wp_set_auth_cookie( $user_id, true );

                // Direct routing honoring safe redirect_to
                if ( ! empty( $redirect_to ) ) {
                    wp_safe_redirect( $redirect_to );
                } else {
                    wp_safe_redirect( home_url( '/my-account' ) );
                }
                exit;
            }
        }
    }
}

get_header(); ?>

<style>
  :root {
    --evg-gold-primary: #d4af37;
    --evg-gold-light: #f3e5ab;
    --evg-gold-muted: #aa8c2c;
    --evg-gold-glow: rgba(212, 175, 55, 0.12);
    
    --evg-obsidian-base: #050505;
    --evg-obsidian-panel: #0d0d0f;
    --evg-obsidian-elevated: #141416;
    
    --evg-border-hairline: #1f1f23;
    --evg-border-gold-faint: rgba(212, 175, 55, 0.2);

    --evg-text-pure: #ffffff;
    --evg-text-ash: #8e8e93;
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
    max-width: 860px;
    margin: 0 auto;
    padding: 4rem 20px 6rem 20px;
  }

  .evg-title-xl { 
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(2rem, 3.5vw, 2.8rem); 
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
    padding: 40px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
  }

  .evg-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }
  .evg-grid-full { grid-column: span 2; }

  .evg-field-wrap {
    display: flex;
    flex-direction: column;
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
    transition: all 0.2s ease;
  }
  .evg-form-control:focus {
    outline: none;
    border-color: var(--evg-gold-primary);
    box-shadow: 0 0 0 1px var(--evg-gold-primary);
    background: #09090b;
  }
  .evg-form-control::placeholder { color: #4a4f5c; font-weight: 300; }
  
  /* Readonly styling for Country */
  .evg-form-control:read-only { 
    color: var(--evg-gold-primary) !important; 
    background: #08080a !important; 
    border-color: var(--evg-border-gold-faint) !important;
    cursor: not-allowed;
    font-weight: 600;
  }

  /* Password Wrapper with Eye Toggle */
  .evg-password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
  }
  .evg-password-wrapper input {
    padding-right: 46px !important;
  }
  .evg-eye-toggle {
    position: absolute;
    right: 12px;
    background: transparent;
    border: none;
    cursor: pointer;
    color: var(--evg-text-ash);
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s ease;
  }
  .evg-eye-toggle:hover {
    color: var(--evg-gold-primary);
  }

  /* Strength Meter */
  .evg-strength-meter {
    height: 4px; 
    border-radius: 2px; 
    background: var(--evg-obsidian-elevated);
    overflow: hidden; 
    width: 100%; 
    display: flex; 
    gap: 3px;
  }
  .evg-strength-segment {
    flex: 1; 
    height: 100%; 
    background: #1c1f26; 
    transition: background 0.3s ease;
  }

  /* Custom Checkboxes */
  .evg-checkbox-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
  }
  .evg-checkbox {
    appearance: none; -webkit-appearance: none;
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
  .btn-evg-executive:hover { 
    background: var(--evg-gold-light); 
    box-shadow: 0 0 25px rgba(212, 175, 55, 0.3); 
  }

  .param-item {
    transition: color 0.2s ease;
  }
  .param-item.valid {
    color: #34c759 !important;
  }
  .param-item.valid::before {
    content: "✓ ";
  }
  .param-item.invalid::before {
    content: "▸ ";
  }

  @media (max-width: 768px) {
    .evg-form-grid { grid-template-columns: 1fr; }
    .evg-grid-full { grid-column: span 1; }
    .evg-module { padding: 25px; }
  }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- Header -->
        <header style="margin-bottom: 40px; text-align: center;">
            <span class="evg-label-micro" style="margin-bottom: 12px;"><?php esc_html_e( 'Client Registry', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Create Your', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Elite Vault Account', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 650px; margin: 0 auto; font-size: 0.95rem; line-height: 1.6;">
                <?php esc_html_e( 'Join Elite Vault Grading to submit Pokémon cards, track your grading orders, and receive updates throughout the grading process.', 'evg-platform' ); ?>
            </p>
        </header>

        <!-- Form Module -->
        <div class="evg-module">

            <!-- Error Messages -->
            <?php if ( ! empty( $registration_errors->get_error_messages() ) ) : ?>
                <div style="background: rgba(255, 69, 58, 0.06); border: 1px solid rgba(255, 69, 58, 0.3); border-radius: 4px; padding: 20px; margin-bottom: 30px;">
                    <span class="evg-label-micro" style="color: #ff453a; margin-bottom: 8px;"><?php esc_html_e( 'Registration Issues Encountered', 'evg-platform' ); ?></span>
                    <ul style="color: #e5e5ea; font-size: 0.85rem; line-height: 1.6; margin: 0; padding-left: 20px;">
                        <?php foreach ( $registration_errors->get_error_messages() as $err_msg ) : ?>
                            <li><?php echo esc_html( $err_msg ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?php echo esc_url( add_query_arg( array(), get_permalink() ) ); ?>" method="post" class="evg-register-form">
                <?php wp_nonce_field( 'evg_user_registration_action', 'evg_register_nonce' ); ?>
                
                <?php if ( ! empty( $redirect_to ) ) : ?>
                    <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">
                <?php endif; ?>

                <!-- SECTION 01: PERSONAL IDENTIFICATION -->
                <div style="margin-bottom: 40px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid var(--evg-border-hairline); padding-bottom: 8px; margin-bottom: 20px;">
                        <span class="evg-label-micro" style="color: #ffffff;"><?php esc_html_e( '01 // Primary Identification', 'evg-platform' ); ?></span>
                        <span style="font-size: 0.65rem; letter-spacing: 0.1em; font-family: monospace; color: var(--evg-gold-primary);">* <?php esc_html_e( 'REQUIRED', 'evg-platform' ); ?></span>
                    </div>
                    
                    <div class="evg-form-grid">
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'First Name', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                            <input type="text" name="first_name" class="evg-form-control" placeholder="e.g. John" value="<?php echo esc_attr( $form_data['first_name'] ?? '' ); ?>" required autocomplete="given-name">
                        </div>
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Last Name', 'evg-platform' ); ?></label>
                            <input type="text" name="last_name" class="evg-form-control" placeholder="e.g. Doe" value="<?php echo esc_attr( $form_data['last_name'] ?? '' ); ?>" autocomplete="family-name">
                        </div>
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Email Address', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                            <input type="email" name="email" class="evg-form-control" placeholder="client@domain.com" value="<?php echo esc_attr( $form_data['email'] ?? '' ); ?>" required autocomplete="email">
                        </div>
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Confirm Email Address', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                            <input type="email" name="confirm_email" class="evg-form-control" placeholder="client@domain.com" value="<?php echo esc_attr( $form_data['confirm_email'] ?? '' ); ?>" required autocomplete="email">
                        </div>
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Mobile Number (Optional)', 'evg-platform' ); ?></label>
                            <input type="tel" name="mobile_number" class="evg-form-control" placeholder="+44 7123 456789" value="<?php echo esc_attr( $form_data['mobile_number'] ?? '' ); ?>" autocomplete="tel">
                        </div>
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Username (Optional)', 'evg-platform' ); ?></label>
                            <input type="text" name="username" class="evg-form-control" placeholder="e.g. VaultCollector" value="<?php echo esc_attr( $form_data['username'] ?? '' ); ?>" autocomplete="username">
                        </div>
                    </div>
                </div>

                <!-- SECTION 02: CRYPTOGRAPHIC SECURITY -->
                <div style="margin-bottom: 40px;">
                    <div style="border-bottom: 1px solid var(--evg-border-hairline); padding-bottom: 8px; margin-bottom: 20px;">
                        <span class="evg-label-micro" style="color: #ffffff;"><?php esc_html_e( '02 // Password & Cryptographic Security', 'evg-platform' ); ?></span>
                    </div>
                    
                    <div class="evg-form-grid">
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Password', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                            <div class="evg-password-wrapper">
                                <input type="password" name="password" id="evgPassword" class="evg-form-control" placeholder="••••••••••••" required autocomplete="new-password">
                                <button type="button" class="evg-eye-toggle" data-target="evgPassword" aria-label="Toggle password visibility">
                                    <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-closed" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Confirm Password', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                            <div class="evg-password-wrapper">
                                <input type="password" name="confirm_password" id="evgConfirmPassword" class="evg-form-control" placeholder="••••••••••••" required autocomplete="new-password">
                                <button type="button" class="evg-eye-toggle" data-target="evgConfirmPassword" aria-label="Toggle confirm password visibility">
                                    <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-closed" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="evg-grid-full">
                            <div style="background: var(--evg-obsidian-base); border: 1px solid #1a1c22; border-radius: 4px; padding: 20px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <span class="evg-label-micro" style="color: #ffffff;"><?php esc_html_e( 'Password Requirements', 'evg-platform' ); ?></span>
                                    <span id="strengthIndicatorLabel" style="font-size: 0.68rem; font-family: monospace; color: #8e8e93; font-weight: 700;"><?php esc_html_e( 'AWAITING INPUT', 'evg-platform' ); ?></span>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; color: var(--evg-text-ash); font-size: 0.75rem; font-family: monospace; margin-bottom: 12px;">
                                    <div class="param-item" id="reqLen"><?php esc_html_e( 'Minimum 8 characters', 'evg-platform' ); ?></div>
                                    <div class="param-item" id="reqCase"><?php esc_html_e( 'Uppercase & Lowercase letter', 'evg-platform' ); ?></div>
                                    <div class="param-item" id="reqNum"><?php esc_html_e( 'At least one number', 'evg-platform' ); ?></div>
                                    <div class="param-item" id="reqSpec"><?php esc_html_e( 'At least one special character', 'evg-platform' ); ?></div>
                                </div>
                                
                                <div class="evg-strength-meter" id="strengthMeter">
                                    <div class="evg-strength-segment" id="seg1"></div>
                                    <div class="evg-strength-segment" id="seg2"></div>
                                    <div class="evg-strength-segment" id="seg3"></div>
                                    <div class="evg-strength-segment" id="seg4"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 03: LOGISTICS COORDINATES -->
                <div style="margin-bottom: 40px;">
                    <div style="border-bottom: 1px solid var(--evg-border-hairline); padding-bottom: 8px; margin-bottom: 20px;">
                        <span class="evg-label-micro" style="color: #ffffff;"><?php esc_html_e( '03 // Address Details (UK Registry)', 'evg-platform' ); ?></span>
                    </div>
                    
                    <div class="evg-form-grid">
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'House Number / Name', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                            <input type="text" name="house_number" class="evg-form-control" placeholder="e.g. Flat 4B or 12 Vault Way" value="<?php echo esc_attr( $form_data['house_number'] ?? '' ); ?>" required autocomplete="address-line1">
                        </div>
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Street Address', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                            <input type="text" name="street_address" class="evg-form-control" placeholder="e.g. High Street" value="<?php echo esc_attr( $form_data['street_address'] ?? '' ); ?>" required autocomplete="address-line2">
                        </div>
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Town / City', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                            <input type="text" name="town_city" class="evg-form-control" placeholder="e.g. London" value="<?php echo esc_attr( $form_data['town_city'] ?? '' ); ?>" required autocomplete="address-level2">
                        </div>
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'County', 'evg-platform' ); ?></label>
                            <input type="text" name="county" class="evg-form-control" placeholder="e.g. Greater London" value="<?php echo esc_attr( $form_data['county'] ?? '' ); ?>" autocomplete="address-level1">
                        </div>
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Postcode', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                            <input type="text" name="postcode" class="evg-form-control" placeholder="e.g. SW1A 1AA" value="<?php echo esc_attr( $form_data['postcode'] ?? '' ); ?>" required autocomplete="postal-code">
                        </div>
                        <div class="evg-field-wrap">
                            <label class="evg-label-micro" style="margin-bottom: 8px; color: var(--evg-text-ash);"><?php esc_html_e( 'Country (Fixed)', 'evg-platform' ); ?></label>
                            <input type="text" name="country" class="evg-form-control" value="United Kingdom" readonly>
                        </div>
                    </div>
                </div>

                <!-- SECTION 04: AUTHORIZATIONS & PREFERENCES -->
                <div style="margin-bottom: 40px;">
                    <div style="border-bottom: 1px solid var(--evg-border-hairline); padding-bottom: 8px; margin-bottom: 20px;">
                        <span class="evg-label-micro" style="color: #ffffff;"><?php esc_html_e( '04 // Preferences & Authorizations', 'evg-platform' ); ?></span>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                        <div class="evg-checkbox-row">
                            <input class="evg-checkbox" type="checkbox" name="pref_updates" id="prefUpdates" value="yes" <?php checked( ( $form_data['pref_updates'] ?? 'yes' ), 'yes' ); ?>>
                            <label style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; cursor: pointer;" for="prefUpdates">
                                <?php esc_html_e( 'Receive grading updates by email', 'evg-platform' ); ?>
                            </label>
                        </div>
                        <div class="evg-checkbox-row">
                            <input class="evg-checkbox" type="checkbox" name="pref_offers" id="prefOffers" value="yes" <?php checked( ( $form_data['pref_offers'] ?? 'no' ), 'yes' ); ?>>
                            <label style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; cursor: pointer;" for="prefOffers">
                                <?php esc_html_e( 'Receive exclusive offers and promotions (Optional)', 'evg-platform' ); ?>
                            </label>
                        </div>
                    </div>

                    <div style="background: var(--evg-obsidian-base); border: 1px solid #1a1c22; border-radius: 4px; padding: 20px;">
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div class="evg-checkbox-row">
                                <input class="evg-checkbox" type="checkbox" name="terms_agree" id="termsAgree" required>
                                <label style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; cursor: pointer;" for="termsAgree">
                                    <?php esc_html_e( 'I agree to the Terms & Conditions', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span>
                                </label>
                            </div>
                            <div class="evg-checkbox-row">
                                <input class="evg-checkbox" type="checkbox" name="privacy_agree" id="privacyAgree" required>
                                <label style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; cursor: pointer;" for="privacyAgree">
                                    <?php esc_html_e( 'I have read the Privacy Policy', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span>
                                </label>
                            </div>
                            <div class="evg-checkbox-row">
                                <input class="evg-checkbox" type="checkbox" name="age_check" id="ageCheck" required>
                                <label style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; cursor: pointer;" for="ageCheck">
                                    <?php esc_html_e( 'I am over 18 years old, or I have permission from a parent or guardian', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-evg-executive" style="margin-bottom: 25px;">
                    <?php esc_html_e( 'Create My Account', 'evg-platform' ); ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left: 8px;">
                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>

                <!-- Sign In Link -->
                <?php
                $signin_url = home_url( '/sign-in' );
                if ( ! empty( $redirect_to ) ) {
                    $signin_url = add_query_arg( 'redirect_to', $redirect_to, $signin_url );
                }
                ?>
                <div style="text-align: center; padding-top: 20px; border-top: 1px solid var(--evg-border-hairline);">
                    <span style="color: var(--evg-text-ash); font-size: 0.85rem;"><?php esc_html_e( 'Already have an account?', 'evg-platform' ); ?></span>
                    <a href="<?php echo esc_url( $signin_url ); ?>" class="evg-label-micro" style="color: #ffffff; text-decoration: none; margin-top: 8px;">
                        <?php esc_html_e( 'Sign In →', 'evg-platform' ); ?>
                    </a>
                </div>

            </form>
        </div>
        
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Password Strength Validation Logic
    const passwordInput = document.getElementById('evgPassword');
    const label = document.getElementById('strengthIndicatorLabel');
    const reqLen = document.getElementById('reqLen');
    const reqCase = document.getElementById('reqCase');
    const reqNum = document.getElementById('reqNum');
    const reqSpec = document.getElementById('reqSpec');
    
    const segs = [
        document.getElementById('seg1'),
        document.getElementById('seg2'),
        document.getElementById('seg3'),
        document.getElementById('seg4')
    ];

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const val = passwordInput.value;
            let score = 0;

            const hasLen = val.length >= 8;
            const hasCase = /[A-Z]/.test(val) && /[a-z]/.test(val);
            const hasNum = /[0-9]/.test(val);
            const hasSpec = /[!@#$%^&*()\-_=+{};:,<.>]/.test(val);

            toggleReq(reqLen, hasLen);
            toggleReq(reqCase, hasCase);
            toggleReq(reqNum, hasNum);
            toggleReq(reqSpec, hasSpec);

            if (hasLen) score++;
            if (hasCase) score++;
            if (hasNum) score++;
            if (hasSpec) score++;

            segs.forEach(s => s.style.backgroundColor = '#1c1f26');

            if (val.length === 0) {
                label.textContent = 'AWAITING INPUT';
                label.style.color = '#8e8e93';
            } else if (score <= 1) {
                segs[0].style.backgroundColor = '#ff453a';
                label.textContent = 'WEAK ENCRYPTION';
                label.style.color = '#ff453a';
            } else if (score === 2) {
                segs[0].style.backgroundColor = '#ff9f0a';
                segs[1].style.backgroundColor = '#ff9f0a';
                label.textContent = 'MODERATE';
                label.style.color = '#ff9f0a';
            } else if (score === 3) {
                segs[0].style.backgroundColor = '#d4af37';
                segs[1].style.backgroundColor = '#d4af37';
                segs[2].style.backgroundColor = '#d4af37';
                label.textContent = 'STRONG';
                label.style.color = '#d4af37';
            } else if (score === 4) {
                segs.forEach(s => s.style.backgroundColor = '#34c759');
                label.textContent = 'EXCELLENT';
                label.style.color = '#34c759';
            }
        });
    }

    function toggleReq(el, isValid) {
        if (!el) return;
        if (isValid) {
            el.classList.add('valid');
            el.classList.remove('invalid');
        } else {
            el.classList.remove('valid');
            el.classList.add('invalid');
        }
    }

    // 2. Password Toggle Eye Logic
    const eyeButtons = document.querySelectorAll('.evg-eye-toggle');
    eyeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const inputField = document.getElementById(targetId);
            const eyeOpen = this.querySelector('.eye-open');
            const eyeClosed = this.querySelector('.eye-closed');

            if (!inputField) return;

            if (inputField.type === 'password') {
                inputField.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                inputField.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        });
    });
});
</script>

<?php get_footer(); ?>