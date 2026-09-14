<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package evg
 */

get_header();
?>

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
        min-height: 80vh;
        font-family: "Montserrat", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--evg-text-ash);
        padding: 5rem 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .evg-404-container {
        max-width: 700px;
        width: 100%;
        background: var(--evg-obsidian-panel);
        border: 1px solid var(--evg-border-hairline);
        border-radius: 12px;
        padding: 50px 30px;
        text-align: center;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8), 0 0 20px var(--evg-gold-glow);
        margin: 0 auto;
    }

    .evg-404-code {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(4rem, 8vw, 6rem);
        font-weight: 700;
        background: linear-gradient(170deg, var(--evg-gold-light) 0%, var(--evg-gold-muted) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        margin-bottom: 15px;
        text-align: center;
    }

    .page-header {
        text-align: center;
    }

    .page-title {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.5rem, 3vw, 2rem);
        color: var(--evg-text-pure);
        margin-bottom: 15px;
        text-align: center;
    }

    .page-content {
        text-align: center;
    }

    .page-content p {
        color: var(--evg-text-ash);
        font-size: 0.95rem;
        margin-bottom: 30px;
        line-height: 1.6;
        text-align: center;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Search Form Center Alignment */
    .page-content .search-form {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-bottom: 30px;
        flex-wrap: wrap;
        width: 100%;
    }

    .page-content .search-field {
        background: var(--evg-obsidian-elevated);
        border: 1px solid var(--evg-border-hairline);
        color: var(--evg-text-pure);
        padding: 12px 16px;
        border-radius: 6px;
        outline: none;
        max-width: 320px;
        width: 100%;
        transition: border-color 0.2s ease;
        text-align: left;
    }

    .page-content .search-field:focus {
        border-color: var(--evg-gold-primary);
    }

    .page-content .search-submit {
        background: var(--evg-gold-primary);
        color: var(--evg-text-charcoal);
        border: none;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.1em;
        padding: 12px 24px;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s ease, box-shadow 0.2s ease;
    }

    .page-content .search-submit:hover {
        background: var(--evg-gold-light);
        box-shadow: 0 0 15px rgba(212, 175, 55, 0.3);
    }

    /* Action Links & Buttons Center Alignment */
    .evg-404-actions {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .evg-btn-home {
        background: transparent;
        color: var(--evg-gold-primary) !important;
        border: 1px solid var(--evg-gold-primary);
        padding: 0.8rem 1.5rem;
        text-decoration: none;
        font-weight: 800;
        border-radius: 4px;
        font-size: 0.8rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        transition: all 0.2s ease;
        display: inline-block;
    }

    .evg-btn-home:hover {
        background: rgba(212, 175, 55, 0.1);
        color: var(--evg-gold-light) !important;
    }
</style>

<div class="evg-master-wrapper">
    <main id="primary" class="site-main" style="width: 100%;">

        <section class="error-404 not-found evg-404-container">
            
            <div class="evg-404-code">404</div>

            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e( 'Vault Specimen Not Found', 'evg' ); ?></h1>
            </header><!-- .page-header -->

            <div class="page-content">
                <p><?php esc_html_e( 'It looks like the certificate or page you are looking for does not exist in our registry. Try searching the vault below:', 'evg' ); ?></p>

                <?php get_search_form(); ?>

                <div class="evg-404-actions">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="evg-btn-home">
                        <?php esc_html_e( '← Return to Vault Home', 'evg' ); ?>
                    </a>
                    <a href="<?php echo esc_url( home_url( '/buy-it-now' ) ); ?>" class="evg-btn-home">
                        <?php esc_html_e( 'Browse Marketplace', 'evg' ); ?>
                    </a>
                </div>

            </div><!-- .page-content -->
        </section><!-- .error-404 -->

    </main><!-- #main -->
</div>

<?php
get_footer();