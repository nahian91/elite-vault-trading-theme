<?php
/**
 * Template Name: Slab Verification & Damage Portfolio - Executive Tier
 * Description: Public certificate verification portal for Elite Vault Grading. Displays slab authentication,
 *              whole-number grades (1-10), 4 diagnostic sub-scores, up to 3 free preview defect photos,
 *              and the £0.99 portfolio unlock paywall with guaranteed QR Code integration.
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
    max-width: 960px;
    margin: 0 auto;
    padding: 3rem 15px 5rem 15px;
  }

  /* Guaranteed QR Code Panel Styles */
  .evg-verified-wrapper {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 20px;
    align-items: start;
  }

  .evg-qr-floating-box {
    background: var(--evg-obsidian-panel);
    border: 1px solid var(--evg-border-gold-faint);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.7);
    min-width: 170px;
  }

  .evg-qr-floating-box img {
    width: 130px;
    height: 130px;
    background: #ffffff;
    padding: 6px;
    border-radius: 6px;
    display: block;
    margin: 0 auto 10px auto;
  }

  @media (max-width: 767.98px) {
    .evg-container { padding: 2rem 15px 4rem 15px; }
    .evg-verified-wrapper { grid-template-columns: 1fr; }
    .evg-qr-floating-box { width: 100%; box-sizing: border-box; }
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
        $cert_param = isset( $_GET['cert'] ) ? sanitize_text_field( wp_unslash( $_GET['cert'] ) ) : '';

        if ( ! empty( $cert_param ) ) {
            // Generate QR Code URL specifically for this certificate
            $current_verify_url = home_url( '/verify/?cert=' . urlencode( $cert_param ) );
            $qr_api_url         = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode( $current_verify_url );
            ?>
            <div class="evg-verified-wrapper">
                <!-- Main Certificate Terminal -->
                <div>
                    <?php
                    if ( function_exists( 'evg_render_public_slab_certificate' ) ) {
                        evg_render_public_slab_certificate( $cert_param );
                    } else {
                        echo '<div style="background:#0f0f11; padding:20px; border-radius:8px; text-align:center;">' . esc_html__( 'Certificate rendering module initializing...', 'evg-platform' ) . '</div>';
                    }
                    ?>
                </div>

                <!-- Dedicated QR Code Verification Card -->
                <div class="evg-qr-floating-box">
                    <span style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.15em; color: var(--evg-gold-primary); font-weight: 700; display: block; margin-bottom: 8px;">
                        <?php esc_html_e( 'Scan to Verify', 'evg-platform' ); ?>
                    </span>
                    <img src="<?php echo esc_url( $qr_api_url ); ?>" alt="Slab Verification QR Code" loading="lazy">
                    <span style="font-size: 0.72rem; color: var(--evg-text-ash); font-family: monospace; display: block; font-weight: 600;">
                        <?php echo esc_html( $cert_param ); ?>
                    </span>
                </div>
            </div>
            <?php
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
        ?>
    </div>
</div>

<?php get_footer(); ?>