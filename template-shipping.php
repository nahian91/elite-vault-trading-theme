<?php
/**
 * Template Name: Shipping Guidelines - Executive Tier
 * Description: Logistics, packaging, and parcel preparation protocol portal for Elite Vault Grading.
 *              Includes 4-step packaging guidelines, courier liability boundaries, pre-dispatch checklists,
 *              live admin settings routing, and solid dark luxury styling with background lines removed.
 *
 * @package EliteVaultGrading
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Fetch Dynamic Admin Settings
$support_email   = get_option( 'evg_support_email', 'support@elitevaultgrading.com' );
$turnaround_time = get_option( 'evg_turnaround_time', '5-10 Business Days' );
$return_shipping = floatval( get_option( 'evg_return_shipping_fee', 9.99 ) );

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

  .evg-grid-matrix {
    display: grid; 
    gap: 1px;
    background: var(--evg-border-hairline); 
    border: 1px solid var(--evg-border-hairline); 
    border-radius: 8px; 
    overflow: hidden;
  }
  .evg-grid-cell {
    background: var(--evg-obsidian-panel); 
    padding: 2rem 1.75rem; 
    transition: background 0.3s ease;
  }
  .evg-grid-cell:hover { background: var(--evg-obsidian-elevated); }
  
  .evg-step-matrix { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }

  .evg-icon { color: var(--evg-text-ash); margin-bottom: 1.25rem; transition: all 0.3s ease; }
  .evg-grid-cell:hover .evg-icon { color: var(--evg-gold-primary); transform: translateY(-2px); }

  .evg-split-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    align-items: stretch;
  }

  .evg-meta-list { list-style: none; padding: 0; margin: 0; }
  .evg-meta-list li {
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    padding: 0.95rem 0; 
    border-bottom: 1px solid var(--evg-border-hairline);
    font-size: 0.88rem;
  }
  .evg-meta-list li:last-child { border-bottom: none; }

  .evg-checklist-item {
    background: var(--evg-obsidian-elevated);
    border: 1px solid #222226;
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    transition: border-color 0.2s ease;
  }
  .evg-checklist-item:hover {
    border-color: var(--evg-border-gold-faint);
  }
  .evg-check-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(52, 199, 89, 0.12);
    border: 1px solid rgba(52, 199, 89, 0.4);
    color: #34c759;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 800;
    flex-shrink: 0;
    margin-top: 2px;
  }

  .btn-evg-executive {
    background: var(--evg-gold-primary); 
    color: var(--evg-text-charcoal) !important;
    font-size: 0.85rem; 
    font-weight: 800; 
    letter-spacing: 0.15em; 
    text-transform: uppercase;
    border: none; 
    border-radius: 4px; 
    padding: 1.25rem 2.5rem; 
    display: inline-flex; 
    align-items: center; 
    justify-content: center;
    transition: all 0.3s ease; 
    cursor: pointer; 
    text-decoration: none;
  }
  .btn-evg-executive:hover { 
    background: var(--evg-gold-light); 
    box-shadow: 0 0 25px rgba(212, 175, 55, 0.3); 
  }
  
  .btn-evg-outline {
    background: transparent; 
    color: var(--evg-gold-primary) !important; 
    font-size: 0.85rem; 
    font-weight: 800; 
    letter-spacing: 0.15em; 
    text-transform: uppercase;
    border: 1px solid var(--evg-gold-primary); 
    border-radius: 4px; 
    padding: 1.25rem 2.5rem; 
    display: inline-flex; 
    align-items: center; 
    justify-content: center;
    transition: all 0.3s ease; 
    cursor: pointer; 
    text-decoration: none;
  }
  .btn-evg-outline:hover { 
    background: rgba(212, 175, 55, 0.1); 
    color: var(--evg-gold-light) !important; 
    border-color: var(--evg-gold-light); 
  }

  @media (max-width: 992px) {
    .evg-split-layout { grid-template-columns: 1fr; }
  }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- 1. EDITORIAL HEADER -->
        <header style="text-align: center; margin-bottom: 45px; padding-bottom: 25px; border-bottom: 1px solid var(--evg-border-hairline);">
            <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Logistics & Handling Specification', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Shipping &', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Packaging Guidelines', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 720px; margin: 0 auto 20px auto; font-size: 0.95rem; line-height: 1.6;">
                <?php printf( esc_html__( 'Proper packaging ensures your Pokémon cards arrive safely at our UK vault facility without transit damage. Consignments are turned around within %s from facility arrival.', 'evg-platform' ), esc_html( $turnaround_time ) ); ?>
            </p>
            <div style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; background: var(--evg-obsidian-panel); border: 1px solid var(--evg-border-gold-faint); border-radius: 4px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-primary)" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <span style="font-size: 0.75rem; font-family: monospace; color: var(--evg-gold-light); font-weight: 700;">
                    <?php esc_html_e( 'UK SUBMISSIONS ONLY • TRACKED & INSURED POSTAGE RECOMMENDED', 'evg-platform' ); ?>
                </span>
            </div>
        </header>

        <!-- 2. STEP-BY-STEP PREPARATION PROTOCOL -->
        <section style="margin-bottom: 50px;">
            <div style="text-align: center; margin-bottom: 25px;">
                <span class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( '01 // Asset Safeguarding', 'evg-platform' ); ?></span>
                <h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'Card Preparation Protocol', 'evg-platform' ); ?></h2>
            </div>

            <div class="evg-grid-matrix evg-step-matrix" style="text-align: center;">
                <div class="evg-grid-cell">
                    <svg class="evg-icon" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>
                    </svg>
                    <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 4px;"><?php esc_html_e( 'Step 01', 'evg-platform' ); ?></span>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Penny Sleeves', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                        <?php esc_html_e( 'Insert each card into a soft penny sleeve to protect surface gloss and perimeter foil from micro-scratches.', 'evg-platform' ); ?>
                    </p>
                </div>

                <div class="evg-grid-cell">
                    <svg class="evg-icon" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>
                    </svg>
                    <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 4px;"><?php esc_html_e( 'Step 02', 'evg-platform' ); ?></span>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Semi-Rigid Holders', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                        <?php esc_html_e( 'Place sleeved cards into flexible semi-rigid card holders. Please avoid sending cards in screw-down or magnetic cases.', 'evg-platform' ); ?>
                    </p>
                </div>

                <div class="evg-grid-cell">
                    <svg class="evg-icon" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polygon points="12 2 2 7 12 22 22 7 12 2"/>
                    </svg>
                    <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 4px;"><?php esc_html_e( 'Step 03', 'evg-platform' ); ?></span>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Cardboard Sandwich', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                        <?php esc_html_e( 'Sandwich your holders firmly between rigid cardboard blanks, securing with elastic bands or tape so cards cannot slide.', 'evg-platform' ); ?>
                    </p>
                </div>

                <div class="evg-grid-cell">
                    <svg class="evg-icon" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polygon points="21 16 12 21 3 16 3 8 12 3 21 8 21 16"/><polyline points="3 8 12 13 21 8"/><polyline points="12 21 12 13"/>
                    </svg>
                    <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 4px;"><?php esc_html_e( 'Step 04', 'evg-platform' ); ?></span>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Padded Outer Box', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                        <?php esc_html_e( 'Pack inside a sturdy outer box with bubble wrap, enclosing your printed order confirmation manifest sheet.', 'evg-platform' ); ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- 3. POSTAGE, INSURANCE & RETURN POLICY -->
        <section class="evg-split-layout" style="margin-bottom: 50px;">
            <div class="evg-module" style="padding: 35px 30px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <span class="evg-label-micro" style="color: #ffffff; margin-bottom: 12px;"><?php esc_html_e( '02 // Transit Liability', 'evg-platform' ); ?></span>
                    <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700; margin: 0 0 14px 0;"><?php esc_html_e( 'Inbound Shipping & Insurance', 'evg-platform' ); ?></h2>
                    <p style="color: var(--evg-text-ash); font-size: 0.88rem; line-height: 1.65; margin: 0 0 20px 0;">
                        <?php esc_html_e( 'We strongly advise dispatching all submissions via an insured, tracked courier service (such as Royal Mail Special Delivery Guaranteed) reflecting the true market value of your cards.', 'evg-platform' ); ?>
                    </p>
                </div>

                <div style="background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 20px;">
                    <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 6px;"><?php esc_html_e( 'Liability Boundary', 'evg-platform' ); ?></span>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                        <?php esc_html_e( 'Until your parcel arrives and is scanned into our system, transit responsibility remains with your chosen courier service. Tracking updates to "Cards Received" automatically upon facility check-in.', 'evg-platform' ); ?>
                    </p>
                </div>
            </div>

            <div class="evg-module" style="padding: 35px 30px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <span class="evg-label-micro" style="color: #ffffff; margin-bottom: 12px;"><?php esc_html_e( '03 // Return Routing', 'evg-platform' ); ?></span>
                    <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700; margin: 0 0 14px 0;"><?php esc_html_e( 'Return Dispatch & Logistics', 'evg-platform' ); ?></h2>
                    <p style="color: var(--evg-text-ash); font-size: 0.88rem; line-height: 1.65; margin: 0 0 15px 0;">
                        <?php esc_html_e( 'Following quality control and sonic encapsulation, your graded collection is packed in individual sleeves, cushioned with foam, and returned to your registered UK address.', 'evg-platform' ); ?>
                    </p>
                    
                    <ul class="evg-meta-list" style="margin-bottom: 20px;">
                        <li>
                            <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Return Courier', 'evg-platform' ); ?></span>
                            <span style="color: #ffffff; font-weight: 600;"><?php esc_html_e( 'Royal Mail Tracked 24 / Special Delivery', 'evg-platform' ); ?></span>
                        </li>
                        <li>
                            <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Flat Return Shipping Rate', 'evg-platform' ); ?></span>
                            <span style="color: var(--evg-gold-light); font-family: monospace; font-size: 0.85rem; font-weight: 700;">&pound;<?php echo esc_html( number_format( $return_shipping, 2 ) ); ?></span>
                        </li>
                        <li>
                            <span style="color: var(--evg-text-ash);"><?php esc_html_e( 'Live Dispatch Alert', 'evg-platform' ); ?></span>
                            <span style="color: #ffffff; font-weight: 600;"><?php esc_html_e( 'Tracking reference uploaded to account', 'evg-platform' ); ?></span>
                        </li>
                    </ul>
                </div>

                <div style="background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 16px;">
                    <span class="evg-label-micro" style="color: #ffffff; margin-bottom: 4px;"><?php esc_html_e( 'In-Person Collection', 'evg-platform' ); ?></span>
                    <p style="color: var(--evg-text-ash); font-size: 0.78rem; line-height: 1.4; margin: 0;">
                        <?php esc_html_e( 'In-person drop-off or collection is available by strict prior arrangement only. Please contact support prior to placing your order.', 'evg-platform' ); ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- 4. PRE-DISPATCH CHECKLIST -->
        <section class="evg-module" style="padding: 35px 30px; margin-bottom: 50px;">
            <span class="evg-label-micro" style="color: #ffffff; margin-bottom: 8px;"><?php esc_html_e( '04 // Pre-Dispatch Audit', 'evg-platform' ); ?></span>
            <h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0 0 25px 0;"><?php esc_html_e( 'Submission Packing Checklist', 'evg-platform' ); ?></h2>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div class="evg-checklist-item">
                    <div class="evg-check-icon">✓</div>
                    <div>
                        <h3 style="color: #ffffff; font-size: 0.92rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Penny Sleeves & Semi-Rigid Holders Fitted', 'evg-platform' ); ?></h3>
                        <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                            <?php esc_html_e( 'Every individual card is penny-sleeved and placed securely inside a semi-rigid holder (avoid top-loaders with tape or screw-down cases).', 'evg-platform' ); ?>
                        </p>
                    </div>
                </div>

                <div class="evg-checklist-item">
                    <div class="evg-check-icon">✓</div>
                    <div>
                        <h3 style="color: #ffffff; font-size: 0.92rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Printed Order Confirmation Manifest Enclosed', 'evg-platform' ); ?></h3>
                        <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                            <?php esc_html_e( 'A printed copy of your submission manifest containing your Order Reference Number and Full Name is enclosed inside the package.', 'evg-platform' ); ?>
                        </p>
                    </div>
                </div>

                <div class="evg-checklist-item">
                    <div class="evg-check-icon">✓</div>
                    <div>
                        <h3 style="color: #ffffff; font-size: 0.92rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Rigid Outer Cardboard Box Secured', 'evg-platform' ); ?></h3>
                        <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                            <?php esc_html_e( 'Cards are sandwiched between rigid cardboard pieces and packaged in a durable cardboard outer box cushioned with bubble wrap.', 'evg-platform' ); ?>
                        </p>
                    </div>
                </div>

                <div class="evg-checklist-item">
                    <div class="evg-check-icon">✓</div>
                    <div>
                        <h3 style="color: #ffffff; font-size: 0.92rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Tracked & Insured Courier Label Affixed', 'evg-platform' ); ?></h3>
                        <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                            <?php esc_html_e( 'Parcel includes door-to-door tracking and adequate postal insurance coverage matching your cards\' declared values.', 'evg-platform' ); ?>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. CALL TO ACTION -->
        <section class="evg-module" style="padding: 40px; text-align: center;">
            <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 10px;"><?php esc_html_e( 'Direct Support Help Desk', 'evg-platform' ); ?></span>
            <h2 style="color: #ffffff; font-size: 1.4rem; font-weight: 700; margin: 0 0 10px 0;"><?php esc_html_e( 'Need Packaging or Logistics Advice?', 'evg-platform' ); ?></h2>
            <p style="color: var(--evg-text-ash); max-width: 600px; margin: 0 auto 25px auto; font-size: 0.9rem; line-height: 1.6;">
                <?php esc_html_e( 'Our UK customer service desk is available to assist with special consignments, bulk packing queries, or courier recommendations.', 'evg-platform' ); ?>
            </p>
            <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn-evg-executive">
                    <?php esc_html_e( 'Contact Support', 'evg-platform' ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/faq' ) ); ?>" class="btn-evg-outline">
                    <?php esc_html_e( 'Read FAQs', 'evg-platform' ); ?>
                </a>
            </div>
            <div style="color: var(--evg-text-ash); font-size: 0.8rem; margin-top: 20px;">
                <?php esc_html_e( 'Or email our logistics team directly at', 'evg-platform' ); ?> <a href="mailto:<?php echo esc_attr( $support_email ); ?>" style="color: #ffffff; text-decoration: underline;"><?php echo esc_html( $support_email ); ?></a>
            </div>
        </section>

    </div>
</main>

<?php get_footer(); ?>