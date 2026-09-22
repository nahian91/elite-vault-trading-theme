<?php
/**
 * Template Name: Certificate Verification & QR Registry
 * Description: Public card verification lookup page for Elite Vault Grading.
 *              Allows scanning QR codes or searching by cert number to display 
 *              overall grade and all 4 diagnostic sub-grades.
 *
 * @package EliteVaultGrading
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table_cards = $wpdb->prefix . 'evg_cards';

$search_query = isset( $_GET['cert'] ) ? sanitize_text_field( wp_unslash( $_GET['cert'] ) ) : '';
$card = null;
$searched = false;

if ( ! empty( $search_query ) ) {
    $searched = true;
    // Search by certificate number or ID
    $card = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$table_cards} WHERE cert_number = %s OR id = %d LIMIT 1",
        $search_query,
        absint( $search_query )
    ) );
}

get_header(); ?>

<style>
    :root {
        --evg-gold: #D4AF37;
        --evg-gold-light: #F3E5AB;
        --evg-gold-muted: #AA8C2C;
        --evg-obsidian-base: #050505;
        --evg-obsidian-panel: #0D0D0F;
        --evg-obsidian-elevated: #141416;
        --evg-border-hairline: #1F1F23;
        --evg-border-gold-faint: rgba(212, 175, 55, 0.2);
        --evg-text-pure: #FFFFFF;
        --evg-text-ash: #8E8E93;
    }

    .evg-verify-wrapper {
        background-color: var(--evg-obsidian-base);
        background-image: radial-gradient(circle at 50% 0%, rgba(212, 175, 55, 0.08), transparent 70%);
        min-height: 85vh;
        font-family: "Montserrat", sans-serif;
        color: var(--evg-text-pure);
        padding: 4rem 15px;
    }
    .evg-verify-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .evg-verify-search-box {
        background: var(--evg-obsidian-panel);
        border: 1px solid var(--evg-border-gold-faint);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 35px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.6);
    }
    .evg-search-input-group {
        display: flex;
        gap: 10px;
    }
    .evg-search-input {
        flex: 1;
        background: var(--evg-obsidian-elevated);
        border: 1px solid #2a2a2e;
        color: #ffffff;
        padding: 14px 18px;
        border-radius: 6px;
        font-family: monospace;
        font-size: 1rem;
        outline: none;
    }
    .evg-search-input:focus {
        border-color: var(--evg-gold);
    }
    .evg-search-btn {
        background: var(--evg-gold);
        color: #030406;
        border: none;
        padding: 0 28px;
        border-radius: 6px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        cursor: pointer;
        font-size: 0.85rem;
    }
    .evg-search-btn:hover {
        background: var(--evg-gold-light);
    }

    /* Result Card Styling */
    .evg-cert-card {
        background: var(--evg-obsidian-panel);
        border: 1px solid var(--evg-border-gold-faint);
        border-radius: 14px;
        padding: 30px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.8);
        position: relative;
    }
    .evg-subgrades-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-top: 25px;
    }
    .evg-subgrade-box {
        background: var(--evg-obsidian-elevated);
        border: 1px solid #222226;
        border-radius: 8px;
        padding: 15px 10px;
        text-align: center;
    }
    .evg-subgrade-val {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--evg-gold);
        font-family: monospace;
        margin-top: 5px;
    }
    .evg-overall-badge {
        width: 75px;
        height: 75px;
        background: rgba(212, 175, 55, 0.12);
        border: 2px solid var(--evg-gold);
        color: var(--evg-gold-light);
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 900;
        font-family: monospace;
    }
</style>

<main class="evg-verify-wrapper">
    <div class="evg-container evg-verify-container">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <span style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.2em; font-weight: 700; color: var(--evg-gold); display: block; margin-bottom: 6px;">
                Official Specimen Verification
            </span>
            <h1 style="font-family: 'Playfair Display', serif; font-size: 2.2rem; font-weight: 700; margin: 0 0 10px 0;">
                Public Certificate Registry
            </h1>
            <p style="color: var(--evg-text-ash); font-size: 0.88rem; margin: 0;">
                Enter the certification number printed on your EVG slab label or scanned via QR code.
            </p>
        </div>

        <!-- Search Bar -->
        <div class="evg-verify-search-box">
            <form method="GET" action="">
                <div class="evg-search-input-group">
                    <input type="text" name="cert" class="evg-search-input" placeholder="e.g. 0100" value="<?php echo esc_attr( $search_query ); ?>" required>
                    <button type="submit" class="evg-search-btn">Verify</button>
                </div>
            </form>
        </div>

        <!-- Result Display -->
        <?php if ( $searched ) : ?>
            <?php if ( $card ) : ?>
                <div class="evg-cert-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap;">
                        <div>
                            <span style="font-size: 0.72rem; color: var(--evg-gold); font-family: monospace; font-weight: 700; letter-spacing: 0.1em; display: block; margin-bottom: 6px;">
                                CERTIFICATION #<?php echo esc_html( $card->cert_number ? $card->cert_number : sprintf( '%04d', $card->id ) ); ?>
                            </span>
                            <h2 style="font-size: 1.6rem; font-weight: 800; margin: 0 0 6px 0; color: #fff;">
                                <?php echo esc_html( $card->card_name ); ?>
                            </h2>
                            <p style="color: var(--evg-text-ash); font-size: 0.9rem; margin: 0;">
                                <?php echo esc_html( $card->set_name ); ?> <?php echo ! empty( $card->card_number ) ? '• #' . esc_html( $card->card_number ) : ''; ?>
                            </p>
                        </div>

                        <!-- Overall Grade Badge -->
                        <div class="evg-overall-badge">
                            <span style="font-size: 0.6rem; letter-spacing: 0.1em; text-transform: uppercase;">GRADE</span>
                            <?php echo esc_html( $card->final_grade ? $card->final_grade : '10' ); ?>
                        </div>
                    </div>

                    <!-- 4 Sub-Grades (Like Ace Grading) -->
                    <div class="evg-subgrades-grid">
                        <div class="evg-subgrade-box">
                            <span style="font-size: 0.65rem; color: var(--evg-text-ash); text-transform: uppercase; font-weight: 700;">Centring</span>
                            <div class="evg-subgrade-val"><?php echo esc_html( ! empty( $card->sub_centering ) ? $card->sub_centering : '10' ); ?></div>
                        </div>
                        <div class="evg-subgrade-box">
                            <span style="font-size: 0.65rem; color: var(--evg-text-ash); text-transform: uppercase; font-weight: 700;">Corners</span>
                            <div class="evg-subgrade-val"><?php echo esc_html( ! empty( $card->sub_corners ) ? $card->sub_corners : '10' ); ?></div>
                        </div>
                        <div class="evg-subgrade-box">
                            <span style="font-size: 0.65rem; color: var(--evg-text-ash); text-transform: uppercase; font-weight: 700;">Edges</span>
                            <div class="evg-subgrade-val"><?php echo esc_html( ! empty( $card->sub_edges ) ? $card->sub_edges : '9' ); ?></div>
                        </div>
                        <div class="evg-subgrade-box">
                            <span style="font-size: 0.65rem; color: var(--evg-text-ash); text-transform: uppercase; font-weight: 700;">Surface</span>
                            <div class="evg-subgrade-val"><?php echo esc_html( ! empty( $card->sub_surface ) ? $card->sub_surface : '10' ); ?></div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div style="text-align: center; padding: 40px; background: var(--evg-obsidian-panel); border-radius: 10px; border: 1px solid var(--evg-border-hairline);">
                    <p style="color: #ff453a; font-weight: 700; margin-bottom: 8px;">No certificate record found for: "<?php echo esc_html( $search_query ); ?>"</p>
                    <p style="color: var(--evg-text-ash); font-size: 0.85rem; margin: 0;">Please check the certificate number and try again.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>