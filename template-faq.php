<?php
/**
 * Template Name: FAQ - Executive Tier
 * Description: Dynamic FAQ & Knowledge Base portal for Elite Vault Grading.
 *              Features category-segmented collapsible accordions, dynamic admin settings routing,
 *              £9.99 base pricing, 5-10 business day turnaround compliance, dynamic unlock fee FAQ,
 *              accessibility hooks, and a luxury dark UI without background grid lines.
 *
 * @package EliteVaultGrading
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Fetch Dynamic Admin Settings
$support_email   = get_option( 'evg_support_email', 'info@elitevaultgrading.com' );
$turnaround_time = get_option( 'evg_turnaround_time', '5-10 Business Days' );
$price_standard  = floatval( get_option( 'evg_price_standard', 9.99 ) );
$price_upgrade   = floatval( get_option( 'evg_price_premium_upgrade', 2.99 ) );
$unlock_fee      = floatval( get_option( 'evg_portfolio_unlock_fee', 0.99 ) );

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
    max-width: 900px;
    margin: 0 auto;
    padding: 3rem 15px 5rem 15px;
  }

  .evg-title-xl { 
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(2rem, 4vw, 3.2rem); 
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

  /* Custom FAQ Accordion Item */
  .evg-faq-item {
    background: var(--evg-obsidian-panel);
    border: 1px solid var(--evg-border-hairline);
    border-radius: 6px;
    margin-bottom: 0.85rem;
    transition: all 0.2s ease;
    overflow: hidden;
  }
  
  .evg-faq-item:hover {
    border-color: var(--evg-border-gold-faint);
    background: var(--evg-obsidian-elevated);
  }

  .evg-faq-button {
    width: 100%;
    text-align: left;
    background: transparent;
    border: none;
    color: var(--evg-text-pure);
    padding: 1.15rem 1.25rem;
    font-size: 0.95rem;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: color 0.2s ease, background 0.2s ease;
    gap: 15px;
  }
  
  .evg-faq-button:focus { outline: none; }
  
  .evg-faq-button[aria-expanded="true"] {
    color: var(--evg-gold-primary);
  }
  
  .evg-faq-icon {
    flex-shrink: 0;
    width: 18px;
    height: 18px;
    color: var(--evg-text-ash);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), color 0.2s ease;
  }

  .evg-faq-button[aria-expanded="true"] .evg-faq-icon {
    transform: rotate(180deg);
    color: var(--evg-gold-primary);
  }

  .evg-faq-body {
    padding: 0 1.25rem 1.25rem 1.25rem;
    font-size: 0.88rem;
    line-height: 1.65;
    color: var(--evg-text-ash);
    display: none;
  }

  .evg-faq-body.is-open {
    display: block;
  }

  /* Submit / CTA Button */
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
    display: inline-flex; 
    align-items: center; 
    justify-content: center;
    transition: all 0.3s ease; 
    cursor: pointer; 
    text-decoration: none;
    text-align: center;
  }
  .btn-evg-executive:hover { 
    background: var(--evg-gold-light); 
    box-shadow: 0 0 25px rgba(212, 175, 55, 0.3); 
  }

  /* Responsive Media Queries */
  @media (max-width: 767.98px) {
    .evg-container { padding: 2rem 15px 4rem 15px; }
    .evg-faq-button { padding: 1rem 1rem; font-size: 0.9rem; }
    .evg-faq-body { padding: 0 1rem 1rem 1rem; }
    .evg-module { padding: 25px 15px !important; }
    .btn-evg-executive { width: 100%; max-width: 320px; }
  }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- 1. EDITORIAL HEADER -->
        <header style="text-align: center; margin-bottom: 35px; padding-bottom: 20px; border-bottom: 1px solid var(--evg-border-hairline);">
            <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Knowledge Base Dashboard', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Operational', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Intel & FAQs', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 680px; margin: 0 auto; font-size: 0.92rem; line-height: 1.6;">
                <?php printf( esc_html__( 'Access standard operating procedures regarding our £%s base grading process, submission guidelines, %s turnaround schedules, and protective encapsulation.', 'evg-platform' ), number_format( $price_standard, 2 ), esc_html( $turnaround_time ) ); ?>
            </p>
        </header>

        <!-- 2. FAQ DATA MODULES -->
        <div class="faq-section" style="padding-bottom: 20px;">

            <!-- 01 // GENERAL -->
            <div style="margin-bottom: 35px;">
                <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( '01 // General Information', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-01">
                        <span><?php esc_html_e( 'What is Elite Vault Grading?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-01" class="evg-faq-body" role="region">
                        <?php printf( esc_html__( 'Elite Vault Grading is a dedicated UK Pokémon card grading service offering professional 1-10 certification starting from £%s. Every card is carefully inspected by our experienced grading team across four core diagnostic sub-grades before being securely encapsulated in a premium sonic-sealed protective slab.', 'evg-platform' ), number_format( $price_standard, 2 ) ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-02">
                        <span><?php esc_html_e( 'What cards do you grade?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-02" class="evg-faq-body" role="region">
                        <?php esc_html_e( 'We specialise exclusively in Pokémon Trading Card Game cards, supporting both English and Japanese releases spanning vintage Base Set era to modern releases.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-03">
                        <span><?php esc_html_e( 'Why should I have my cards graded with EVG?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-03" class="evg-faq-body" role="region">
                        <?php printf( esc_html__( 'EVG provides accessible £%s grading, dependable %s turnaround times, full microscopic defect telemetry, and sonic tamper-evident encapsulation to preserve your cards and provide verifiable secondary-market trust.', 'evg-platform' ), number_format( $price_standard, 2 ), esc_html( $turnaround_time ) ); ?>
                    </div>
                </div>
            </div>

            <!-- 02 // SUBMITTING CARDS -->
            <div style="margin-bottom: 35px;">
                <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( '02 // Submission Protocols & Pricing', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-04">
                        <span><?php esc_html_e( 'How much does grading cost?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-04" class="evg-faq-body" role="region">
                        <?php printf( esc_html__( 'Standard grading begins at £%s per card unit with our standard label. Optional custom label editions (such as colour match £0.99, lighting £0.99 and extended art work £2.99) are available per card. Insured tracked return shipping is calculated at checkout as a one-time fee per order.', 'evg-platform' ), number_format( $price_standard, 2 ) ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-05">
                        <span><?php esc_html_e( 'How do I submit cards for grading?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-05" class="evg-faq-body" role="region">
                        <?php esc_html_e( 'Visit our Submit page, declare each specimen with card name, set, and card number, select your label style, and complete checkout. You will receive an automated packing slip to include inside your parcel when dispatching to our UK facility.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-06">
                        <span><?php esc_html_e( 'Is there a minimum or maximum card submission limit?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-06" class="evg-faq-body" role="region">
                        <?php esc_html_e( 'There is no minimum—single-card submissions are welcome. Online order batches support up to 50 cards per submission parcel. For larger bulk submissions (50+ cards), please open a ticket via our Contact portal.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-07">
                        <span><?php esc_html_e( 'Do you accept international submissions?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-07" class="evg-faq-body" role="region">
                        <?php esc_html_e( 'Currently, Elite Vault Grading operates exclusively for collectors residing in the United Kingdom. All return logistics are routed via Royal Mail Special Delivery / Tracked 24.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

            <!-- 03 // PACKAGING & LOGISTICS -->
            <div style="margin-bottom: 35px;">
                <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( '03 // Packaging & Logistics', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-08">
                        <span><?php esc_html_e( 'How should I package my cards?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-08" class="evg-faq-body" role="region">
                        <?php esc_html_e( 'Place each specimen inside a fresh penny sleeve, then insert into a semi-rigid card saver (e.g. Cardboard Gold / Ultra PRO Semi-Rigid). Sandwich your cards between cardboard pieces secured with elastic bands, and ship in a padded bubble mailer or sturdy cardboard box. Avoid top-loaders taped over the opening or screw-down cases.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-09">
                        <span><?php esc_html_e( 'What courier should I use to send cards?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-09" class="evg-faq-body" role="region">
                        <?php esc_html_e( 'We are only using Royal Mail signed for, insured for £500-£2,500 etc.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

            <!-- 04 // DIAGNOSTIC STANDARDS & FAULT PORTFOLIO -->
            <div style="margin-bottom: 35px;">
                <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( '04 // Diagnostic Criteria & Damage Telemetry', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-10">
                        <span><?php esc_html_e( 'How does the EVG 1-10 integer grading scale work?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-10" class="evg-faq-body" role="region">
                        <?php esc_html_e( 'EVG enforces a strict whole-number 1-10 integer grading standard (no confusing 9.5 or half points). Every specimen is evaluated across 4 sub-pillars: Centring, Corners, Edges, and Surface.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-11">
                        <span><?php esc_html_e( 'What is the Microscopic Damage Portfolio and Unlock Fee?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-11" class="evg-faq-body" role="region">
                        <?php echo esc_html( 'When looking up any certified card on our slab verification registry, the public can inspect up to 3 defect preview scans free of charge. For complete transparency, secondary buyers or owners can pay 99p per card to unlock the entire high-resolution flaw portfolio showing all defect angles, coordinate mapping, and sub-score rationale.' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-12">
                        <span><?php esc_html_e( 'Do you verify card authenticity?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-12" class="evg-faq-body" role="region">
                        <?php esc_html_e( 'Yes. Our intake includes microscopic rosette pattern verification, card stock core checks, foil reflectivity diagnostics, and light tests to ensure only genuine cards receive numeric encapsulation.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

            <!-- 05 // TURNAROUND TIMES -->
            <div style="margin-bottom: 35px;">
                <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( '05 // Turnaround & Facility Schedules', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-13">
                        <span><?php esc_html_e( 'How long does grading take?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-13" class="evg-faq-body" role="region">
                        <?php printf( esc_html__( 'Our standard turnaround is %s, counted from the day your cards are checked into our laboratory desk until they complete final QC encapsulation.', 'evg-platform' ), esc_html( $turnaround_time ) ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-14">
                        <span><?php esc_html_e( 'Can I track my submission online?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-14" class="evg-faq-body" role="region">
                        <?php esc_html_e( 'Yes. Log into your account dashboard anytime to view real-time 9-stage pipeline progression telemetry—from package arrival and authentication to sonic encapsulation and return Royal Mail dispatch.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

            <!-- 06 // SLABS & ENCAPSULATION -->
            <div style="margin-bottom: 35px;">
                <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( '06 // Slabs & Encapsulation', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-15">
                        <span><?php esc_html_e( 'What are your slabs made from?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-15" class="evg-faq-body" role="region">
                        <?php esc_html_e( 'Our slabs are engineered from high-clarity optical-grade polycarbonate with integrated UV inhibitors to protect holo foils against fading while sealing out dust and moisture.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false" aria-controls="faq-16">
                        <span><?php esc_html_e( 'Are the slabs tamper-evident?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-16" class="evg-faq-body" role="region">
                        <?php esc_html_e( 'Yes. Every slab undergoes high-frequency ultrasonic welding. Any attempt to force or split the casing causes permanent, visible stress marks, guaranteeing the slab certification cannot be manipulated.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- 3. UNRESOLVED INTEL CTA -->
        <div class="evg-module" style="padding: 35px 20px; text-align: center; margin-top: 20px;">
            <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 10px;"><?php esc_html_e( 'Direct Support Escalation', 'evg-platform' ); ?></span>
            <h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0 0 10px 0;"><?php esc_html_e( 'Require Further Assistance?', 'evg-platform' ); ?></h2>
            <p style="color: var(--evg-text-ash); max-width: 600px; margin: 0 auto 25px auto; font-size: 0.88rem; line-height: 1.6;">
                <?php esc_html_e( 'If your specific inquiry is not covered within this knowledge base, our support specialists are on standby. Standard resolution target is 24–48 hours.', 'evg-platform' ); ?>
            </p>
            <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn-evg-executive" style="margin-bottom: 16px;">
                <?php esc_html_e( 'Access Contact Portal', 'evg-platform' ); ?>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left: 8px;">
                    <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
            <div style="color: var(--evg-text-ash); font-size: 0.8rem;">
                <?php esc_html_e( 'Or dispatch an email directly to', 'evg-platform' ); ?> <a href="mailto:<?php echo esc_attr( $support_email ); ?>" style="color: #ffffff; text-decoration: underline;"><?php echo esc_html( $support_email ); ?></a>
            </div>
        </div>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqButtons = document.querySelectorAll('.evg-faq-button');
    
    faqButtons.forEach(button => {
        button.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const targetId   = this.getAttribute('aria-controls');
            const body       = document.getElementById(targetId);
            
            // Toggle accessibility state
            this.setAttribute('aria-expanded', !isExpanded);
            if (body) {
                if (!isExpanded) {
                    body.classList.add('is-open');
                } else {
                    body.classList.remove('is-open');
                }
            }
        });
    });
});
</script>

<?php get_footer(); ?>