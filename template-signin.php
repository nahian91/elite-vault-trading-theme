<?php
/**
 * Template Name: Sign In - Executive Tier
 * Description: Fully dynamic authentication terminal matching Elite Vault Grading specifications, 
 *              featuring server-side authentication, post-login target redirection, captcha filter bypass
 *              for frontend signons, explicit authentication cookies, and an interactive password toggle eye icon.
 */

// -------------------------------------------------------------------------
// 1. RESOLVE REDIRECT TARGET
// -------------------------------------------------------------------------
$redirect_to = '';
if ( isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) ) {
    $redirect_to = esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) );
}

// -------------------------------------------------------------------------
// 2. BACKEND AUTHENTICATION ENGINE
// -------------------------------------------------------------------------
$login_error = '';

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['evg_login_nonce'] ) ) {
    if ( wp_verify_nonce( sanitize_key( $_POST['evg_login_nonce'] ), 'evg_user_login_action' ) ) {
        
        $user_login = sanitize_text_field( wp_unslash( $_POST['log'] ?? '' ) );
        $user_pwd   = $_POST['pwd'] ?? '';
        $remember   = isset( $_POST['rememberme'] );

        if ( empty( $user_login ) || empty( $user_pwd ) ) {
            $login_error = __( 'Please provide both your identifier (email/username) and password.', 'evg-platform' );
        } else {
            // Resolve email to username if an email address was entered
            if ( is_email( $user_login ) ) {
                $user_obj = get_user_by( 'email', $user_login );
                if ( $user_obj ) {
                    $user_login = $user_obj->user_login;
                }
            }

            $credentials = array(
                'user_login'    => $user_login,
                'user_password' => $user_pwd,
                'remember'      => $remember,
            );

            // Bypass core captcha filter for frontend form authentication
            if ( class_exists( 'Elite_Vault_Grading_System' ) ) {
                $evg_instance = Elite_Vault_Grading_System::get_instance();
                remove_filter( 'authenticate', array( $evg_instance, 'validate_mathematical_captcha' ), 25 );
            }

            $user = wp_signon( $credentials, is_ssl() );

            if ( is_wp_error( $user ) ) {
                $login_error = wp_strip_all_tags( $user->get_error_message() );
            } else {
                // Set authenticated user and authentication cookies explicitly
                wp_set_current_user( $user->ID );
                wp_set_auth_cookie( $user->ID, $remember );

                // Audit logging
                if ( class_exists( 'Elite_Vault_Grading_System' ) && method_exists( 'Elite_Vault_Grading_System', 'log_activity' ) ) {
                    Elite_Vault_Grading_System::log_activity( "Collector Signed In: {$user->user_email} (ID #{$user->ID})" );
                }

                // Dynamic Routing
                $staff_roles = array( 'administrator', 'head_grader', 'grader', 'support_team' );
                $user_roles  = (array) $user->roles;

                if ( ! empty( array_intersect( $staff_roles, $user_roles ) ) ) {
                    wp_safe_redirect( admin_url( 'admin.php?page=evg_management_system&tab=dashboard' ) );
                } elseif ( ! empty( $redirect_to ) ) {
                    wp_safe_redirect( $redirect_to );
                } else {
                    wp_safe_redirect( home_url( '/dashboard' ) );
                }
                exit;
            }
        }
    }
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
        wp_safe_redirect( home_url( '/dashboard' ) );
    }
    exit;
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
    max-width: 520px;
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
    padding: 40px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
  }

  .evg-auth-emblem {
    width: 64px; 
    height: 64px; 
    border-radius: 50%;
    background: var(--evg-obsidian-elevated);
    border: 1px solid var(--evg-border-gold-faint);
    display: inline-flex; 
    align-items: center; 
    justify-content: center;
    color: var(--evg-gold-primary); 
    box-shadow: 0 0 20px var(--evg-gold-glow);
  }

  .evg-input-group {
    display: flex;
    align-items: center;
    background: var(--evg-obsidian-elevated);
    border: 1px solid #242428;
    border-radius: 4px;
    transition: all 0.2s ease;
  }
  .evg-input-group:focus-within {
    border-color: var(--evg-gold-primary);
    background: #09090b;
    box-shadow: 0 0 0 1px var(--evg-gold-primary);
  }
  .evg-input-addon {
    background: transparent; 
    border: none; 
    color: var(--evg-text-ash);
    padding-left: 1rem; 
    padding-right: 0.25rem; 
    display: flex; 
    align-items: center;
  }
  .evg-input-group:focus-within .evg-input-addon { color: var(--evg-gold-primary); }
  
  .evg-form-control {
    background: transparent !important; 
    border: none !important;
    color: var(--evg-text-pure) !important; 
    font-size: 0.9rem;
    padding: 0.85rem 1rem 0.85rem 0.5rem; 
    box-shadow: none !important; 
    width: 100%;
    outline: none;
  }
  .evg-form-control::placeholder { color: #4a4f5c; font-weight: 300; }

  /* Password Toggle Eye Button */
  .evg-eye-toggle {
    background: transparent;
    border: none;
    cursor: pointer;
    color: var(--evg-text-ash);
    padding: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s ease;
  }
  .evg-eye-toggle:hover {
    color: var(--evg-gold-primary);
  }

  .evg-checkbox {
    appearance: none; 
    -webkit-appearance: none;
    background-color: var(--evg-obsidian-elevated); 
    margin: 0;
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

  .evg-feature-matrix {
    display: grid; 
    grid-template-columns: repeat(2, 1fr); 
    gap: 1px;
    background: var(--evg-border-hairline); 
    border: 1px solid var(--evg-border-hairline); 
    border-radius: 6px; 
    overflow: hidden;
  }
  .evg-feature-cell {
    background: var(--evg-obsidian-panel); 
    padding: 1.25rem 1rem; 
    text-align: center; 
    transition: background 0.3s ease;
  }
  .evg-feature-cell:hover { background: var(--evg-obsidian-elevated); }

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
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- 1. AUTHENTICATION HEADER -->
        <header style="text-align: center; margin-bottom: 35px;">
            <div class="evg-auth-emblem" style="margin-bottom: 20px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            
            <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Security Clearance', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Terminal', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Sign In', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 420px; margin: 0 auto; font-size: 0.95rem; line-height: 1.6;">
                <?php esc_html_e( 'Access your member dashboard to track live grading stages, review submitted cards, and manage marketplace purchases.', 'evg-platform' ); ?>
            </p>
        </header>

        <!-- 2. LOGIN FORM MODULE -->
        <div class="evg-module" style="margin-bottom: 25px;">
            
            <!-- NOTIFICATION / ERROR MESSAGES -->
            <?php if ( ! empty( $login_error ) ) : ?>
                <div style="background: rgba(255, 69, 58, 0.08); border: 1px solid rgba(255, 69, 58, 0.3); border-radius: 4px; padding: 14px 18px; margin-bottom: 25px;" role="alert">
                    <div style="display: flex; align-items: center; gap: 8px; color: #ff453a; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span><?php esc_html_e( 'Authorization Error', 'evg-platform' ); ?></span>
                    </div>
                    <p style="color: #e5e5ea; margin: 0; font-size: 0.8rem;">
                        <?php echo esc_html( $login_error ); ?>
                    </p>
                </div>
            <?php endif; ?>

            <form action="<?php echo esc_url( add_query_arg( array(), get_permalink() ) ); ?>" method="post" class="evg-signin-form">
                <?php wp_nonce_field( 'evg_user_login_action', 'evg_login_nonce' ); ?>
                
                <?php if ( ! empty( $redirect_to ) ) : ?>
                    <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">
                <?php endif; ?>

                <!-- USERNAME / EMAIL FIELD -->
                <div style="margin-bottom: 20px;">
                    <label class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( 'Email Address or Username', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                    <div class="evg-input-group">
                        <span class="evg-input-addon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input type="text" name="log" class="evg-form-control" placeholder="client@domain.com or username" value="<?php echo esc_attr( wp_unslash( $_POST['log'] ?? '' ) ); ?>" required autocomplete="username">
                    </div>
                </div>

                <!-- PASSWORD FIELD WITH EYE TOGGLE -->
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label class="evg-label-micro" style="margin: 0;"><?php esc_html_e( 'Password', 'evg-platform' ); ?> <span style="color: var(--evg-gold-primary);">*</span></label>
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" style="color: var(--evg-gold-light); text-decoration: none; font-size: 0.75rem;">
                            <?php esc_html_e( 'Reset Credentials?', 'evg-platform' ); ?>
                        </a>
                    </div>
                    <div class="evg-input-group">
                        <span class="evg-input-addon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input type="password" name="pwd" id="loginPassword" class="evg-form-control" placeholder="••••••••••••" required autocomplete="current-password">
                        <button type="button" class="evg-eye-toggle" data-target="loginPassword" aria-label="Toggle password visibility">
                            <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-closed" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <!-- REMEMBER ME -->
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                    <input class="evg-checkbox" type="checkbox" name="rememberme" id="rememberMe" value="forever">
                    <label style="color: var(--evg-text-ash); font-size: 0.85rem; cursor: pointer; margin: 0;" for="rememberMe">
                        <?php esc_html_e( 'Preserve authorization token on this device', 'evg-platform' ); ?>
                    </label>
                </div>

                <!-- SUBMIT BUTTON -->
                <button type="submit" name="wp-submit" class="btn-evg-executive" style="margin-bottom: 25px;">
                    <?php esc_html_e( 'Authenticate Session', 'evg-platform' ); ?>
                </button>

                <!-- SECURITY BADGE CALLOUT -->
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px; color: var(--evg-text-ash); font-size: 0.7rem; font-family: monospace; margin-bottom: 25px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-muted)" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <span><?php esc_html_e( '256-BIT ENCRYPTED VAULT LOGIN', 'evg-platform' ); ?></span>
                </div>

                <!-- SIGN UP REDIRECT -->
                <?php 
                $register_url = home_url( '/create-account' );
                if ( ! empty( $redirect_to ) ) {
                    $register_url = add_query_arg( 'redirect_to', urlencode( $redirect_to ), $register_url );
                }
                ?>
                <div style="text-align: center; padding-top: 20px; border-top: 1px solid var(--evg-border-hairline);">
                    <span style="color: var(--evg-text-ash); font-size: 0.85rem;"><?php esc_html_e( 'Lacking active credentials?', 'evg-platform' ); ?></span>
                    <a href="<?php echo esc_url( $register_url ); ?>" class="evg-label-micro" style="color: #ffffff; text-decoration: none; margin-top: 8px;">
                        <?php esc_html_e( 'Establish Account →', 'evg-platform' ); ?>
                    </a>
                </div>

            </form>
        </div>

        <!-- 3. CAPABILITIES PREVIEW MATRIX -->
        <section class="evg-feature-matrix" aria-label="Portal Capabilities">
            <div class="evg-feature-cell">
                <span style="display: block; color: #ffffff; font-size: 0.8rem; margin-bottom: 4px;"><?php esc_html_e( 'Live Telemetry', 'evg-platform' ); ?></span>
                <span class="evg-label-micro" style="color: var(--evg-text-ash);"><?php esc_html_e( 'Order Tracking', 'evg-platform' ); ?></span>
            </div>
            <div class="evg-feature-cell">
                <span style="display: block; color: #ffffff; font-size: 0.8rem; margin-bottom: 4px;"><?php esc_html_e( 'Asset Registry', 'evg-platform' ); ?></span>
                <span class="evg-label-micro" style="color: var(--evg-text-ash);"><?php esc_html_e( 'Intake Records', 'evg-platform' ); ?></span>
            </div>
            <div class="evg-feature-cell">
                <span style="display: block; color: #ffffff; font-size: 0.8rem; margin-bottom: 4px;"><?php esc_html_e( 'Secure Data', 'evg-platform' ); ?></span>
                <span class="evg-label-micro" style="color: var(--evg-text-ash);"><?php esc_html_e( 'Encrypted Slots', 'evg-platform' ); ?></span>
            </div>
            <div class="evg-feature-cell">
                <span style="display: block; color: #ffffff; font-size: 0.8rem; margin-bottom: 4px;"><?php esc_html_e( 'UK Support', 'evg-platform' ); ?></span>
                <span class="evg-label-micro" style="color: var(--evg-text-ash);"><?php esc_html_e( 'Direct Desk', 'evg-platform' ); ?></span>
            </div>
        </section>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
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