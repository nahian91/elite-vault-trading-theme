<?php
/**
 * Template Name: Grading Process & Scale - Executive Tier
 * Description: Clean, high-performance grading architecture template for Elite Vault Grading.
 *              Features dynamic turnaround metrics, 7-step pipeline, interactive calculators, 
 *              strict 1-10 whole-number scale, PO Box dispatch address, manual certificate numbering field (starting at 0100), 
 *              full multi-card submission form, and Stripe/Database backend processing.
 *
 * @package EliteVaultGrading
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$turnaround_time    = get_option( 'evg_turnaround_time', '5-10 Business Days' );
$price_standard     = floatval( get_option( 'evg_price_standard', 9.99 ) );
$accept_submissions = get_option( 'evg_accept_submissions', 'yes' );

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
        max-width: 1140px;
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
        padding: 1.75rem 1.25rem; 
        transition: all 0.3s ease;
    }
    .evg-grid-cell:hover { 
        background: var(--evg-obsidian-elevated); 
    }
    
    .evg-pipeline-matrix { 
        display: grid; 
        grid-template-columns: repeat(3, minmax(260px, 1fr));
        gap: 1px;
        justify-content: center;
    }
    .evg-pipeline-matrix .evg-grid-cell:nth-child(7) {
        grid-column: 2 / span 1;
    }

    .evg-pillar-matrix { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
    .evg-icon { color: var(--evg-text-ash); margin-bottom: 1.25rem; transition: all 0.3s ease; }
    .evg-grid-cell:hover .evg-icon { color: var(--evg-gold-primary); transform: translateY(-2px); }

    .evg-scale-registry { list-style: none; padding: 0; margin: 0; }
    .evg-scale-row {
        display: flex; 
        align-items: center; 
        gap: 1.25rem;
        padding: 1.25rem 1.5rem; 
        border-bottom: 1px solid var(--evg-border-hairline);
        transition: background 0.2s ease;
    }
    .evg-scale-row:last-child { border-bottom: none; }
    .evg-scale-row:hover { background: rgba(212, 175, 55, 0.03); }
    
    .evg-grade-badge {
        width: 46px; height: 46px; border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 1.2rem; font-family: monospace; flex-shrink: 0;
        border: 1px solid transparent;
    }
    .evg-grade-10 { background: rgba(212, 175, 55, 0.12); border-color: var(--evg-gold-primary); color: var(--evg-gold-light); box-shadow: 0 0 20px var(--evg-gold-glow); }
    .evg-grade-9 { background: var(--evg-obsidian-elevated); border-color: var(--evg-border-gold-faint); color: var(--evg-gold-primary); }
    .evg-grade-standard { background: var(--evg-obsidian-elevated); border-color: var(--evg-border-hairline); color: var(--evg-text-ash); }

    .evg-calc-card {
        background: var(--evg-obsidian-panel);
        border: 1px solid var(--evg-border-gold-faint);
        border-radius: 8px;
        padding: 25px 20px;
        margin-bottom: 40px;
    }
    .evg-calc-grid { display: grid; grid-template-columns: 1fr 1fr 1.2fr; gap: 15px; align-items: center; }
    .evg-calc-input {
        background: var(--evg-obsidian-elevated); border: 1px solid #242428; color: #ffffff;
        padding: 12px 14px; border-radius: 4px; width: 100%; font-family: monospace; font-size: 0.9rem; box-sizing: border-box; outline: none;
    }
    .evg-calc-input:focus { border-color: var(--evg-gold-primary); }
    .evg-calc-result-box { background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 14px 16px; text-align: center; }

    /* Form & Label Option Styles */
    .evg-form-control {
        background: var(--evg-obsidian-elevated); border: 1px solid #242428; color: var(--evg-text-pure);
        border-radius: 4px; padding: 0.85rem 1.25rem; font-size: 0.9rem; width: 100%; box-sizing: border-box; outline: none; transition: all 0.2s ease;
    }
    .evg-form-control:focus { border-color: var(--evg-gold-primary); box-shadow: 0 0 0 1px var(--evg-gold-primary); background: #09090b; }
    
    .evg-label-options-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 25px; }
    .evg-label-option-card {
        background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline);
        border-radius: 10px; padding: 24px; display: flex; align-items: center; gap: 22px; cursor: pointer; transition: all 0.2s ease;
    }
    .evg-label-option-card:hover { border-color: var(--evg-border-gold-faint); background: var(--evg-obsidian-elevated); }
    .evg-label-option-card input[type="radio"] { accent-color: var(--evg-gold-primary); width: 24px; height: 24px; cursor: pointer; }
    
    .evg-label-demo-img-wrap { position: relative; display: inline-block; cursor: zoom-in; }
    .evg-label-demo-img {
        width: 130px; height: 160px; object-fit: cover; border-radius: 8px;
        border: 2px solid var(--evg-border-gold-faint); background: #000; flex-shrink: 0; box-shadow: 0 8px 22px rgba(0,0,0,0.8); transition: transform 0.2s ease;
    }
    .evg-label-demo-img-wrap:hover .evg-label-demo-img { transform: scale(1.03); border-color: var(--evg-gold-primary); }
    .evg-zoom-hint {
        position: absolute; bottom: 6px; right: 6px; background: rgba(5, 5, 5, 0.8);
        color: var(--evg-gold-primary); font-size: 0.6rem; padding: 2px 6px; border-radius: 4px; font-family: monospace; border: 1px solid var(--evg-border-gold-faint); pointer-events: none;
    }

    /* Lightbox Modal Styles */
    #evgImageLightbox {
        display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%;
        background-color: rgba(3, 7, 18, 0.92); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;
    }
    #evgImageLightbox.is-active { display: flex; }
    .evg-lightbox-content {
        position: relative; max-width: 500px; width: 100%; background: var(--evg-obsidian-panel);
        border: 1px solid var(--evg-border-gold-faint); border-radius: 12px; padding: 25px; text-align: center; box-shadow: 0 25px 60px rgba(0,0,0,0.9);
    }
    .evg-lightbox-content img {
        max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 8px;
        border: 2px solid var(--evg-gold-primary); box-shadow: 0 10px 30px rgba(0,0,0,0.8); margin-bottom: 15px; display: block; margin-left: auto; margin-right: auto;
    }
    .evg-lightbox-close {
        position: absolute; top: 15px; right: 18px; background: var(--evg-obsidian-elevated);
        border: 1px solid var(--evg-border-hairline); color: #ffffff; font-size: 1.2rem; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;
    }
    .evg-lightbox-close:hover { background: var(--evg-gold-primary); color: var(--evg-text-charcoal); border-color: var(--evg-gold-primary); }

    .btn-evg-executive {
        background: var(--evg-gold-primary); color: var(--evg-text-charcoal) !important; font-size: 0.82rem; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase;
        border: none; border-radius: 4px; padding: 1.1rem 2rem; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s ease; cursor: pointer; text-decoration: none; text-align: center; width: 100%; box-sizing: border-box;
    }
    .btn-evg-executive:hover { background: var(--evg-gold-light); box-shadow: 0 0 25px rgba(212, 175, 55, 0.3); }
    .btn-evg-executive.disabled { background: #333336; color: #88888e !important; cursor: not-allowed; box-shadow: none; }
    
    .btn-evg-outline {
        background: transparent; color: var(--evg-gold-primary) !important; font-size: 0.82rem; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase;
        border: 1px solid var(--evg-gold-primary); border-radius: 4px; padding: 1.1rem 2rem; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s ease; cursor: pointer; text-decoration: none; text-align: center;
    }
    .btn-evg-outline:hover { background: rgba(212, 175, 55, 0.1); color: var(--evg-gold-light) !important; border-color: var(--evg-gold-light); }

    @media (max-width: 991.98px) {
        .evg-pipeline-matrix { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        .evg-pipeline-matrix .evg-grid-cell:nth-child(7) { grid-column: auto; }
        .evg-calc-grid { grid-template-columns: 1fr; }
        .evg-label-options-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 767.98px) {
        .evg-container { padding: 2rem 15px 4rem 15px; }
        .evg-scale-row { padding: 1rem 1rem; gap: 12px; }
        .evg-grade-badge { width: 38px; height: 38px; font-size: 1rem; }
        .btn-evg-outline { width: 100%; max-width: 320px; }
        .evg-module { padding: 25px 15px !important; }
        .evg-label-demo-img { width: 100px; height: 125px; }
    }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- 1. EDITORIAL HEADER WITH TOP "GRADE NOW" BUTTON -->
        <header style="text-align: center; margin-bottom: 35px; padding-bottom: 25px; border-bottom: 1px solid var(--evg-border-hairline);">
            <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Architectural Integrity', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Diagnostic', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Standards & Scale', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 720px; margin: 0 auto 20px auto; font-size: 0.92rem; line-height: 1.6;">
                <?php esc_html_e( 'Every Pokémon card entrusted to Elite Vault Grading receives a rigorous, consistent, and transparent assessment from intake to tamper-evident sonic encapsulation.', 'evg-platform' ); ?>
            </p>
            <div style="display: flex; justify-content: center; gap: 15px; align-items: center; flex-wrap: wrap;">
                <a href="#grading-form-section" class="btn-evg-executive" style="width: auto; padding: 0.8rem 1.8rem;">
                    <?php esc_html_e( 'Grade Now ↓', 'evg-platform' ); ?>
                </a>
            </div>
        </header>

        <!-- 2. 7-STEP OPERATIONAL PIPELINE -->
        <section style="margin-bottom: 40px;">
            <div style="margin-bottom: 16px;">
                <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 4px;"><?php esc_html_e( '01 // Certification Journey', 'evg-platform' ); ?></span>
                <h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'The 7-Stage Grading Pipeline', 'evg-platform' ); ?></h2>
            </div>

            <div class="evg-grid-matrix evg-pipeline-matrix">
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;">Stage 01</span>
                    <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;">Registry Check-In</h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;">Consignments are verified, unboxed under secure surveillance, and logged into our central database.</p>
                </div>
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;">Stage 02</span>
                    <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;">Authentication Check</h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;">Optical verification to screen for counterfeit prints, trimming, recolouring, or pressing.</p>
                </div>
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;">Stage 03</span>
                    <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;">Diagnostic Assessment</h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;">Multi-point evaluation across Centring, Corners, Edges, and Surface parameters.</p>
                </div>
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;">Stage 04</span>
                    <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;">Grade Determination</h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;">Sub-scores and eye appeal synthesized into a whole-number grade on our strict 1–10 scale.</p>
                </div>
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;">Stage 05</span>
                    <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;">Quality Control (QC)</h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;">Dual-grader verification ensures label data accuracy and slab optic cleanliness.</p>
                </div>
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;">Stage 06</span>
                    <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;">Sonic Encapsulation</h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;">Ultrasonic welding locks the card permanently inside a tamper-evident protective slab.</p>
                </div>
                <div class="evg-grid-cell" style="grid-column: 2 / span 1; background: rgba(212, 175, 55, 0.04);">
                    <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 8px;">Stage 07 // Finalization</span>
                    <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;">Secure UK Insured Dispatch</h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;">Encapsulated slabs undergo final polish and are securely boxed for tracked return delivery.</p>
                </div>
            </div>
        </section>

        <!-- 3. THE 4 ASSESSMENT PILLARS -->
        <section style="margin-bottom: 40px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <span class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( '02 // Core Parameters', 'evg-platform' ); ?></span>
                <h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'The 4 Assessment Pillars', 'evg-platform' ); ?></h2>
            </div>

            <div class="evg-grid-matrix evg-pillar-matrix" style="text-align: center;">
                <div class="evg-grid-cell">
                    <svg class="evg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
                    <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Centring', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Precision border proportion measurements on both front and back artwork frames.', 'evg-platform' ); ?></p>
                </div>
                <div class="evg-grid-cell">
                    <svg class="evg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 20h16a2 2 0 0 0 2-2V4"/><path d="M4 4v16"/></svg>
                    <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Corners', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Microscopic review for corner whitening, edge chipping, and cut radius sharpness.', 'evg-platform' ); ?></p>
                </div>
                <div class="evg-grid-cell">
                    <svg class="evg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg>
                    <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Edges', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Perimeter inspection for silvering, rough cuts, stock flaking, and handling wear.', 'evg-platform' ); ?></p>
                </div>
                <div class="evg-grid-cell">
                    <svg class="evg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 2 7 12 22 22 7 12 2"/></svg>
                    <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Surface', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Glazing inspection for print lines, scratches, holo clouding, indentations, and gloss.', 'evg-platform' ); ?></p>
                </div>
            </div>
        </section>

        <!-- 4. INTERACTIVE CENTRING DIAGNOSTIC CALCULATOR -->
        <section class="evg-calc-card">
            <span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Diagnostic Tool', 'evg-platform' ); ?></span>
            <h3 style="color: #ffffff; font-size: 1.15rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Border Centring Diagnostic Estimator', 'evg-platform' ); ?></h3>
            <p style="color: var(--evg-text-ash); font-size: 0.84rem; margin: 0 0 20px 0; line-height: 1.5;">
                <?php esc_html_e( 'Input border measurements (in mm or pixels) to calculate ratio splits and check minimum threshold eligibility for Grade 10 or Grade 9.', 'evg-platform' ); ?>
            </p>

            <div class="evg-calc-grid">
                <div>
                    <label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Left / Top Border (A)', 'evg-platform' ); ?></label>
                    <input type="number" id="calcBorderA" class="evg-calc-input" placeholder="e.g. 3.2" step="0.1" value="3.0">
                </div>
                <div>
                    <label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Right / Bottom Border (B)', 'evg-platform' ); ?></label>
                    <input type="number" id="calcBorderB" class="evg-calc-input" placeholder="e.g. 2.8" step="0.1" value="3.0">
                </div>
                <div class="evg-calc-result-box">
                    <span class="evg-label-micro" style="margin-bottom: 4px;"><?php esc_html_e( 'CALCULATED RATIO', 'evg-platform' ); ?></span>
                    <div id="calcRatioOutput" style="font-family: monospace; font-size: 1.2rem; font-weight: 800; color: var(--evg-gold-primary); margin-bottom: 4px;">50 / 50</div>
                    <span id="calcGradeVerdict" style="font-size: 0.72rem; color: #34c759; font-weight: 700;">✓ GEM MINT 10 CENTRING ELIGIBLE</span>
                </div>
            </div>
        </section>

        <!-- 5. ELITE VAULT 1-10 GRADING SCALE -->
        <section style="margin-bottom: 40px;">
            <div class="evg-module">
                <div style="padding: 25px 20px; border-bottom: 1px solid var(--evg-border-hairline); background: #08080a;">
                    <span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( '03 // The Numeric Hierarchy', 'evg-platform' ); ?></span>
                    <h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( '1–10 Whole Number Scale Examples', 'evg-platform' ); ?></h2>
                    <p style="color: var(--evg-text-ash); font-size: 0.85rem; margin: 0; line-height: 1.5;">
                        <?php esc_html_e( 'We employ a strict whole-number hierarchy. No half-grades or 9.5 variations are permitted within the Elite Vault Grading system.', 'evg-platform' ); ?>
                    </p>
                </div>
                
                <ul class="evg-scale-registry">
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-10">10</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 10 — Elite Mint', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Virtually flawless; exceptional centring (60/40 or better), razor sharp corners, clean edges, and an immaculate surface free of print defects.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-9">9</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 9 — Mint', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Outstanding presentation with centring up to 60/40 and only very minor manufacturing nuances or microscopic handling marks visible under high magnification.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">8</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 8 — Near Mint / Mint', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Extremely clean condition with slight edge whitening or minor centring inconsistencies allowed upon close inspection.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">7</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 7 — Near Mint', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Good quality card showing slight corner wear, minor whitening on edges, or light surface micro-scratches.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">6</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 6 — Excellent', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Noticeable edge whitening, corner softness, moderate handling signs, or minor printing defects across the surface.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">5</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 5 — Very Good', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Clear evidence of handling, moderate edge wear, corner radius softness, and reduced overall eye appeal.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">4</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 4 — Good', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Significant visible wear, multiple areas of edge whitening, light surface creases, and heavily reduced appeal.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">3</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 3 — Fair', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Heavily worn with substantial imperfections, surface scratches, print deterioration, and notable structural flaws.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">2</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 2 — Poor', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Major condition problems, severe surface scratches, and corner creases; card remains authentic and identifiable.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">1</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 1 — Damaged', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Heavily compromised card with severe creasing, tears, pinholes, or major surface deterioration.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- 6. OFFICIAL PO BOX DISPATCH ADDRESS -->
        <section class="evg-module" style="padding: 30px 25px; text-align: center; margin-bottom: 40px; border-color: var(--evg-border-gold-faint);">
            <div style="margin-bottom: 15px; display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 50%; background: rgba(212, 175, 55, 0.08); border: 1px solid rgba(212, 175, 55, 0.2);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--evg-gold-primary)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    <path d="M12 11h4"></path><path d="M12 15h4"></path><path d="M8 11h.01"></path><path d="M8 15h.01"></path>
                </svg>
            </div>
            <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 6px;">Submission Logistics</span>
            <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700; margin: 0 0 8px 0;">Official Card Dispatch Address</h2>
            <p style="color: var(--evg-text-ash); font-size: 0.84rem; margin: 0 auto 20px auto; max-width: 600px; line-height: 1.5;">
                Please securely package your cards and dispatch via Royal Mail Signed For or Special Delivery directly to our secure intake facility:
            </p>
            <div style="display: inline-block; background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline); border-radius: 8px; padding: 20px 35px; text-align: left; font-family: monospace; box-shadow: inset 0 2px 6px rgba(0,0,0,0.5);">
                <div style="color: var(--evg-gold-light); font-weight: 700; font-size: 0.95rem; margin-bottom: 6px; letter-spacing: 0.05em;">Elite Vault Grading</div>
                <div style="color: #ffffff; font-size: 0.9rem; line-height: 1.6;">PO Box 1755<br>Doncaster<br>DN1 9AS</div>
            </div>
        </section>

        <!-- 7. FULL SUBMISSION & LABEL CONFIGURATION FORM AT THE LAST -->
        <section id="grading-form-section" class="evg-module" style="padding: 35px 25px;">
            <div style="text-align: center; margin-bottom: 25px;">
                <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 6px;">Submission Portal</span>
                <h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;">Card Declaration & Label Configuration</h2>
            </div>

            <form action="" method="POST" style="max-width: 900px; margin: 0 auto;">
                <?php wp_nonce_field( 'evg_process_grading_order', 'evg_grading_submit_nonce' ); ?>
                
                <!-- Contact Info -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px;">
                    <div>
                        <label class="evg-label-micro" style="margin-bottom: 6px;">Full Name *</label>
                        <input type="text" name="customer_name" class="evg-form-control" placeholder="John Doe" required>
                    </div>
                    <div>
                        <label class="evg-label-micro" style="margin-bottom: 6px;">Email Address *</label>
                        <input type="email" name="customer_email" class="evg-form-control" placeholder="client@example.com" required>
                    </div>
                    <div>
                        <label class="evg-label-micro" style="margin-bottom: 6px;">Phone Number *</label>
                        <input type="tel" name="customer_phone" class="evg-form-control" placeholder="+44 7000 000000" required>
                    </div>
                </div>

                <!-- Dynamic Cards Declaration Table (With Manual Certificate Number Input starting from 0100) -->
                <div style="margin-bottom: 30px;">
                    <label class="evg-label-micro" style="margin-bottom: 12px;">Declare Specimens & Manual Cert Numbers (Starting from 0100)</label>
                    <div id="evgCardsList" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 15px;">
                        <div class="evg-card-row-item" style="background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline); border-radius: 8px; padding: 15px; display: grid; grid-template-columns: 1.8fr 1.3fr 1fr 0.8fr 0.9fr auto; gap: 10px; align-items: center;">
                            <input type="text" name="card_name[]" class="evg-form-control" placeholder="Card Name (e.g. Charizard)" required>
                            <input type="text" name="card_set[]" class="evg-form-control" placeholder="Set (e.g. Shining Fates)" required>
                            <input type="text" name="card_number[]" class="evg-form-control" placeholder="Card No (e.g. SV107)" required>
                            <input type="number" name="card_qty[]" class="evg-form-control" placeholder="Qty" value="1" min="1" required>
                            <input type="text" name="cert_number[]" class="evg-form-control" placeholder="Cert # (0100)" value="0100" style="font-family:monospace;" required>
                            <button type="button" style="background: rgba(255,69,58,0.1); border: 1px solid rgba(255,69,58,0.3); color: #ff453a; width: 38px; height: 38px; border-radius: 6px; cursor: pointer; font-weight: bold;" onclick="removeCardRow(this)">✕</button>
                        </div>
                    </div>
                    <button type="button" class="btn-evg-outline" onclick="addCardRow()" style="padding: 8px 16px; font-size: 0.75rem;">+ Add Another Card</button>
                </div>

                <!-- Slab Label Options -->
                <div style="margin-bottom: 30px;">
                    <label class="evg-label-micro" style="margin-bottom: 12px;">Select Slab Label Option (Click imej to zoom)</label>
                    <div class="evg-label-options-grid">
                        <label class="evg-label-option-card">
                            <input type="radio" name="slab_label_tier" value="black_basic" checked>
                            <div class="evg-label-demo-img-wrap" onclick="openEvgLightbox(this)">
                                <img src="https://elitevaultgrading.com/wp-content/uploads/2026/09/black.jpeg" alt="Black Basic Label" class="evg-label-demo-img">
                                <span class="evg-zoom-hint">🔍 Zoom</span>
                            </div>
                            <div>
                                <strong style="color: #fff; display: block; margin-bottom: 4px;">Black Basic label</strong>
                                <span style="color: var(--evg-gold-primary); font-family: monospace; font-weight: 700;">£0.00</span>
                            </div>
                        </label>
                        <label class="evg-label-option-card">
                            <input type="radio" name="slab_label_tier" value="colour_match">
                            <div class="evg-label-demo-img-wrap" onclick="openEvgLightbox(this)">
                                <img src="https://elitevaultgrading.com/wp-content/uploads/2026/09/color.jpeg" alt="Colour Match Label" class="evg-label-demo-img">
                                <span class="evg-zoom-hint">🔍 Zoom</span>
                            </div>
                            <div>
                                <strong style="color: #fff; display: block; margin-bottom: 4px;">Colour match</strong>
                                <span style="color: var(--evg-gold-primary); font-family: monospace; font-weight: 700;">£0.99</span>
                            </div>
                        </label>
                        <label class="evg-label-option-card">
                            <input type="radio" name="slab_label_tier" value="lightening">
                            <div class="evg-label-demo-img-wrap" onclick="openEvgLightbox(this)">
                                <img src="https://elitevaultgrading.com/wp-content/uploads/2026/09/lighting.jpeg" alt="Lightening Label" class="evg-label-demo-img">
                                <span class="evg-zoom-hint">🔍 Zoom</span>
                            </div>
                            <div>
                                <strong style="color: #fff; display: block; margin-bottom: 4px;">Lightening</strong>
                                <span style="color: var(--evg-gold-primary); font-family: monospace; font-weight: 700;">£0.99</span>
                            </div>
                        </label>
                        <label class="evg-label-option-card">
                            <input type="radio" name="slab_label_tier" value="extended_art">
                            <div class="evg-label-demo-img-wrap" onclick="openEvgLightbox(this)">
                                <img src="https://elitevaultgrading.com/wp-content/uploads/2026/09/extended.jpeg" alt="Extended Artwork Label" class="evg-label-demo-img">
                                <span class="evg-zoom-hint">🔍 Zoom</span>
                            </div>
                            <div>
                                <strong style="color: #fff; display: block; margin-bottom: 4px;">Extended Artwork</strong>
                                <span style="color: var(--evg-gold-primary); font-family: monospace; font-weight: 700;">£2.99</span>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-evg-executive">
                    Proceed to Secure Payment
                </button>
            </form>
        </section>

    </div>
</main>

<!-- Lightbox Modal Container -->
<div id="evgImageLightbox" onclick="closeEvgLightbox()">
    <div class="evg-lightbox-content" onclick="event.stopPropagation()">
        <button class="evg-lightbox-close" onclick="closeEvgLightbox()">×</button>
        <img id="evgLightboxImg" src="" alt="Enlarged Label View">
        <div id="evgLightboxCaption" style="color: var(--evg-gold-light); font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;"></div>
    </div>
</div>

<script>
function addCardRow() {
    var container = document.getElementById('evgCardsList');
    var firstRow = container.querySelector('.evg-card-row-item');
    var newRow = firstRow.cloneNode(true);
    var inputs = newRow.querySelectorAll('input');
    inputs.forEach(function(input) {
        if(input.type === 'number') input.value = 1;
        else if(input.name.includes('cert_number')) input.value = '0101';
        else input.value = '';
    });
    container.appendChild(newRow);
}

function removeCardRow(btn) {
    var container = document.getElementById('evgCardsList');
    if (container.querySelectorAll('.evg-card-row-item').length > 1) {
        btn.closest('.evg-card-row-item').remove();
    } else {
        alert('You must declare at least one card.');
    }
}

function openEvgLightbox(element) {
    var img = element.querySelector('img');
    var lightbox = document.getElementById('evgImageLightbox');
    var lightboxImg = document.getElementById('evgLightboxImg');
    var caption = document.getElementById('evgLightboxCaption');
    if (img && lightbox && lightboxImg) {
        lightboxImg.src = img.src;
        caption.textContent = img.alt || 'Label Preview';
        lightbox.classList.add('is-active');
    }
}

function closeEvgLightbox() {
    var lightbox = document.getElementById('evgImageLightbox');
    if (lightbox) lightbox.classList.remove('is-active');
}

document.addEventListener('DOMContentLoaded', function() {
    var borderA = document.getElementById('calcBorderA');
    var borderB = document.getElementById('calcBorderB');
    var ratioOutput = document.getElementById('calcRatioOutput');
    var verdictOutput = document.getElementById('calcGradeVerdict');

    function calculateCentering() {
        var a = parseFloat(borderA.value) || 0;
        var b = parseFloat(borderB.value) || 0;

        if (a <= 0 || b <= 0) {
            ratioOutput.textContent = '-- / --';
            verdictOutput.textContent = 'Enter positive measurements';
            verdictOutput.style.color = '#8e8e93';
            return;
        }

        var total = a + b;
        var percentA = Math.round((a / total) * 100);
        var percentB = 100 - percentA;

        var higher = Math.max(percentA, percentB);
        var lower = Math.min(percentA, percentB);

        ratioOutput.textContent = higher + ' / ' + lower;

        if (higher <= 60) {
            verdictOutput.textContent = '✓ GEM MINT 10 / MINT 9 CENTRING ELIGIBLE (60/40 or better)';
            verdictOutput.style.color = '#34c759';
        } else if (higher <= 70) {
            verdictOutput.textContent = '⚠ GRADE 8 OR 7 CENTRING THRESHOLD';
            verdictOutput.style.color = '#ff9f0a';
        } else {
            verdictOutput.textContent = '✕ OFF-CENTER / SUB-7 CENTRING THRESHOLD';
            verdictOutput.style.color = '#ff453a';
        }
    }

    if (borderA && borderB) {
        borderA.addEventListener('input', calculateCentering);
        borderB.addEventListener('input', calculateCentering);
    }
});
</script>

<?php get_footer(); ?>