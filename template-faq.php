<?php
/**
 * Template Name: FAQ - Executive Tier
 * Description: Dynamic FAQ & Knowledge Base portal for Elite Vault Grading.
 *              Features category-segmented collapsible accordions, dynamic admin settings routing,
 *              accessibility hooks, and a luxury dark UI without background grid lines.
 *
 * @package EliteVaultGrading
 */

// Fetch Dynamic Admin Settings
$support_email   = get_option( 'evg_support_email', 'elitevaultgrading@gmail.com' );
$turnaround_time = get_option( 'evg_turnaround_time', '30-45 Business Days' );

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

  /* Solid clean background without grid lines */
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
    max-width: 900px;
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
    padding: 1.25rem 1.5rem;
    font-size: 0.98rem;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: color 0.2s ease, background 0.2s ease;
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
    padding: 0 1.5rem 1.5rem 1.5rem;
    font-size: 0.9rem;
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
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- 1. EDITORIAL HEADER -->
        <header style="text-align: center; margin-bottom: 45px; padding-bottom: 25px; border-bottom: 1px solid var(--evg-border-hairline);">
            <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Knowledge Base Dashboard', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Operational', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Intel & FAQs', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 680px; margin: 0 auto; font-size: 0.95rem; line-height: 1.6;">
                <?php esc_html_e( 'Access standard operating procedures regarding our grading process, submission guidelines, facility turnaround schedules, and protective encapsulation.', 'evg-platform' ); ?>
            </p>
        </header>

        <!-- 2. FAQ DATA MODULES -->
        <div class="faq-section" style="padding-bottom: 20px;">

            <!-- 01 // GENERAL -->
            <div style="margin-bottom: 40px;">
                <span class="evg-label-micro" style="margin-bottom: 12px;"><?php esc_html_e( '01 // General Information', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'What is Elite Vault Grading?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Elite Vault Grading is a professional Pokémon card grading company dedicated to providing accurate, consistent and transparent grading. Every card is carefully inspected by our experienced grading team before being securely encapsulated in a premium protective slab designed to preserve and showcase your collection.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'What cards do you grade?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'We currently specialise in grading Pokémon Trading Card Game cards, including both English and Japanese releases.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'Why should I have my cards graded?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Professional grading helps verify authenticity, assess condition, protect your card for years to come and can increase buyer confidence when selling or trading.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

            <!-- 02 // SUBMITTING CARDS -->
            <div style="margin-bottom: 40px;">
                <span class="evg-label-micro" style="margin-bottom: 12px;"><?php esc_html_e( '02 // Submission Protocols', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'How do I submit my cards?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Choose your preferred grading service, complete the online submission form and package your cards securely before posting them to us. Once your submission has been received, we\'ll keep you updated throughout the grading process.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'Is there a minimum or maximum number of cards I can submit?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'No. Whether you\'re submitting a single card or an entire collection, we\'d be delighted to grade your cards.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'Can I submit cards for someone else?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Yes. Collectors, retailers and businesses are all welcome to submit cards on behalf of friends, customers or clients.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'Do you accept international submissions?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Not at the moment. Elite Vault Grading is currently accepting submissions from customers within the UK only. As we continue to grow, we plan to introduce international submissions in the near future.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

            <!-- 03 // PACKAGING & SHIPPING -->
            <div style="margin-bottom: 40px;">
                <span class="evg-label-micro" style="margin-bottom: 12px;"><?php esc_html_e( '03 // Packaging & Logistics', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'How should I package my cards?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'We recommend placing each card in a penny sleeve followed by a semi-rigid card holder. Secure your cards carefully so they cannot move during transit and use a sturdy box with suitable padding to protect your submission. Please avoid sending cards in screw-down holders unless requested.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'Should I insure my package?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Yes. We strongly recommend using a tracked and insured postal service that reflects the value of your submission.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'What happens once my cards arrive?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Once your submission reaches us, it will be checked into our system and you\'ll receive confirmation that your cards have been safely received and logged into our facility.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

            <!-- 04 // GRADING DIAGNOSTICS -->
            <div style="margin-bottom: 40px;">
                <span class="evg-label-micro" style="margin-bottom: 12px;"><?php esc_html_e( '04 // Diagnostic Criteria & Standards', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'How are cards graded?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Every card is carefully assessed using our grading standards, taking into account: Centring, Corners, Edges, Surface, and Overall eye appeal on a strict 1-10 whole-number scale (no half grades or 9.5s). Each of these factors contributes towards the final grade awarded.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'Do you check cards for authenticity?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Yes. Every card is inspected for authenticity before grading begins. If we believe a card to be counterfeit or significantly altered, it will not receive a numerical grade.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'Can altered or restored cards be graded?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Cards that have been trimmed, recoloured, pressed, cleaned or otherwise altered may not qualify for a standard numerical grade. Where alterations are identified, an appropriate designation may be applied.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

            <!-- 05 // TURNAROUND TIMES -->
            <div style="margin-bottom: 40px;">
                <span class="evg-label-micro" style="margin-bottom: 12px;"><?php esc_html_e( '05 // Facility Turnaround Schedules', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'How long does grading take?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php printf( esc_html__( 'Turnaround times depend on current intake volumes. Our active standard estimate is currently %s, which begins once your submission has been checked into our grading desk.', 'evg-platform' ), esc_html( $turnaround_time ) ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'Can turnaround times change?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Occasionally. During high-volume release drops turnaround times may fluctuate slightly, but we always aim to complete every submission efficiently without compromising grading accuracy.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

            <!-- 06 // SLABS & ENCAPSULATION -->
            <div style="margin-bottom: 40px;">
                <span class="evg-label-micro" style="margin-bottom: 12px;"><?php esc_html_e( '06 // Encapsulation & Slabs', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'What are your slabs made from?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Our slabs are manufactured using high-quality, ultra-clear acoustic sealed polymers designed to provide UV-resistant protection while offering exceptional clarity to showcase your card.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'Are the slabs tamper-evident?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Yes. Every Elite Vault slab is sonically sealed and designed to show irreversible evidence if anyone attempts to open or tamper with it, guaranteeing the integrity of the certificate.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

            <!-- 07 // RETURNS & LOGISTICS -->
            <div style="margin-bottom: 40px;">
                <span class="evg-label-micro" style="margin-bottom: 12px;"><?php esc_html_e( '07 // Return Dispatch & Logistics', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'How will my cards be returned?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Once your order completes encapsulation and final QC, your slabs are packaged in protective sleeves, padded secure boxes, and dispatched via Royal Mail Tracked / Special Delivery.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'Can I collect my order in person?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Collection may be arranged under special VIP circumstances. Please contact our support team before placing your submission if you wish to discuss in-person handovers.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

            <!-- 08 // FINANCIAL & REGRADES -->
            <div style="margin-bottom: 40px;">
                <span class="evg-label-micro" style="margin-bottom: 12px;"><?php esc_html_e( '08 // Regrades, Reviews & Liability', 'evg-platform' ); ?></span>
                
                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'Can I submit a card that has already been graded?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Yes. Cards previously graded by third-party companies or by EVG can be submitted for crossover review or re-grading under standard submission terms.', 'evg-platform' ); ?>
                    </div>
                </div>

                <div class="evg-faq-item">
                    <button class="evg-faq-button" type="button" aria-expanded="false">
                        <span><?php esc_html_e( 'Are my cards insured while in your facility?', 'evg-platform' ); ?></span>
                        <svg class="evg-faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="evg-faq-body">
                        <?php esc_html_e( 'Yes. All cards in our care are housed in monitored, vault-secured facilities. Full liability boundaries and declared value compensation limits are outlined in our Terms & Conditions.', 'evg-platform' ); ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- 3. UNRESOLVED INTEL CTA -->
        <div class="evg-module" style="padding: 40px; text-align: center; margin-top: 20px;">
            <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 10px;"><?php esc_html_e( 'Direct Support Escalation', 'evg-platform' ); ?></span>
            <h2 style="color: #ffffff; font-size: 1.4rem; font-weight: 700; margin: 0 0 10px 0;"><?php esc_html_e( 'Require Further Assistance?', 'evg-platform' ); ?></h2>
            <p style="color: var(--evg-text-ash); max-width: 600px; margin: 0 auto 25px auto; font-size: 0.9rem; line-height: 1.6;">
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
            const body = this.nextElementSibling;
            
            // Toggle state
            this.setAttribute('aria-expanded', !isExpanded);
            if (!isExpanded) {
                body.classList.add('is-open');
            } else {
                body.classList.remove('is-open');
            }
        });
    });
});
</script>

<?php get_footer(); ?>