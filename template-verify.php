<?php
/**
 * Template Name: Slab Verification & Damage Portfolio - Executive Tier
 * Description: Public certificate verification portal for Elite Vault Grading. Displays slab authentication,
 *              whole-number grades (1-10), 4 diagnostic sub-scores, up to 3 free preview defect photos,
 *              and the £0.99 portfolio unlock paywall.
 *
 * @package EliteVaultGrading
 */

if ( ! defined( 'ABSPATH' ) ) {
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
    overflow-x: hidden;
  }

  .evg-container {
    max-width: 920px;
    margin: 0 auto;
    padding: 3rem 15px 5rem 15px;
  }

  @media (max-width: 767.98px) {
    .evg-container { padding: 2rem 15px 4rem 15px; }
    form[style*="flex"] {
        flex-direction: column !important;
    }
    form[style*="flex"] input, 
    form[style*="flex"] button {
        width: 100% !important;
    }
  }
</style>

<div class="evg-master-wrapper">
    <div class="evg-container">
        <?php
        // Calls the public verification terminal from the core plugin (inc/verification.php)
        if ( function_exists( 'evg_render_public_slab_certificate' ) ) {
            $cert_param = isset( $_GET['cert'] ) ? sanitize_text_field( wp_unslash( $_GET['cert'] ) ) : '';
            if ( ! empty( $cert_param ) ) {
                evg_render_public_slab_certificate( $cert_param );
            } else {
                ?>
                <div style="background: #0f0f11; border: 1px solid #222224; border-radius: 14px; padding: 35px 20px; text-align: center; color: #ffffff;">
                    <h2 style="color: #d4af37; font-size: 1.35rem; margin-top: 0;"><?php esc_html_e( 'Slab Verification Lookup', 'evg-platform' ); ?></h2>
                    <p style="color: #8e8e93; font-size: 0.88rem; margin-bottom: 25px; line-height: 1.5;">
                        <?php esc_html_e( 'Please provide a valid slab certificate ID in the URL query (e.g. /verify/?cert=EVG-00142).', 'evg-platform' ); ?>
                    </p>
                    <form method="get" action="" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                        <input type="text" name="cert" placeholder="EVG-00000" style="background: #141416; border: 1px solid #28282b; color: #fff; padding: 12px 16px; border-radius: 6px; font-family: monospace; outline: none; text-align: center; max-width: 260px; width: 100%; box-sizing: border-box;" required>
                        <button type="submit" style="background: #d4af37; color: #0a0a0a; border: none; padding: 12px 24px; font-weight: 800; border-radius: 6px; cursor: pointer; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.1em;"><?php esc_html_e( 'Verify Slab', 'evg-platform' ); ?></button>
                    </form>
                </div>
                <?php
            }
        } else {
            ?>
            <div style="background: #0f0f11; border: 1px solid #222224; border-radius: 14px; padding: 40px 20px; text-align: center; color: #ffffff;">
                <h2 style="color: #d4af37; font-size: 1.2rem; margin-top: 0;"><?php esc_html_e( 'Verification Module Offline', 'evg-platform' ); ?></h2>
                <p style="color: #8e8e93; font-size: 0.85rem; margin-bottom: 0; line-height: 1.5;">
                    <?php esc_html_e( 'The verification terminal module is currently initializing. Please ensure the Elite Vault Grading plugin core is active.', 'evg-platform' ); ?>
                </p>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>