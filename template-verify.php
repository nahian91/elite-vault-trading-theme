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

<div class="evg-master-wrapper" style="background-color: var(--evg-obsidian-base, #050505); min-height: 100vh; padding: 4rem 20px 6rem 20px;">
    <div class="evg-container" style="max-width: 920px; margin: 0 auto;">
        <?php
        // Calls the public verification terminal from the core plugin (inc/verification.php)
        if ( function_exists( 'evg_render_public_slab_certificate' ) ) {
            $cert_param = isset( $_GET['cert'] ) ? sanitize_text_field( wp_unslash( $_GET['cert'] ) ) : '';
            if ( ! empty( $cert_param ) ) {
                evg_render_public_slab_certificate( $cert_param );
            } else {
                ?>
                <div style="background: #0f0f11; border: 1px solid #222224; border-radius: 14px; padding: 40px; text-align: center; color: #ffffff;">
                    <h2 style="color: #d4af37; font-size: 22px; margin-top: 0;"><?php esc_html_e( 'Slab Verification Lookup', 'evg-platform' ); ?></h2>
                    <p style="color: #8e8e93; font-size: 14px; margin-bottom: 25px;">
                        <?php esc_html_e( 'Please provide a valid slab certificate ID in the URL query (e.g. /verify/?cert=EVG-00142).', 'evg-platform' ); ?>
                    </p>
                    <form method="get" action="" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                        <input type="text" name="cert" placeholder="EVG-00000" style="background: #141416; border: 1px solid #28282b; color: #fff; padding: 12px 16px; border-radius: 6px; font-family: monospace; outline: none; text-align: center;" required>
                        <button type="submit" style="background: #d4af37; color: #0a0a0a; border: none; padding: 12px 24px; font-weight: 800; border-radius: 6px; cursor: pointer; text-transform: uppercase;"><?php esc_html_e( 'Verify Slab', 'evg-platform' ); ?></button>
                    </form>
                </div>
                <?php
            }
        } else {
            ?>
            <div style="background: #0f0f11; border: 1px solid #222224; border-radius: 14px; padding: 50px; text-align: center; color: #ffffff;">
                <h2 style="color: #d4af37; font-size: 20px; margin-top: 0;"><?php esc_html_e( 'Verification Module Offline', 'evg-platform' ); ?></h2>
                <p style="color: #8e8e93; font-size: 14px; margin-bottom: 0;">
                    <?php esc_html_e( 'The verification terminal module is currently initializing. Please ensure the Elite Vault Grading plugin core is active.', 'evg-platform' ); ?>
                </p>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>