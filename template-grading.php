<?php
/**
 * Template Name: Grading Process & Scale - Executive Tier
 * Description: Clean, high-performance grading architecture template for Elite Vault Grading.
 *              Features dynamic turnaround metrics, the 7-step operational pipeline, 
 *              the 4 diagnostic pillars with interactive calculators, the strict 1–10 whole-number scale,
 *              and an ultra-premium dark luxury aesthetic.
 */

$turnaround_time = get_option( 'evg_turnaround_time', '30-45 Business Days' );
$price_standard  = floatval( get_option( 'evg_price_standard', 15.00 ) );

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

  /* Grid Matrices (Used for Pipeline & Pillars) */
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
    transition: all 0.3s ease;
  }
  .evg-grid-cell:hover { 
    background: var(--evg-obsidian-elevated); 
  }
  
  .evg-pipeline-matrix { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
  .evg-pillar-matrix { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }

  /* Icons */
  .evg-icon { color: var(--evg-text-ash); margin-bottom: 1.25rem; transition: all 0.3s ease; }
  .evg-grid-cell:hover .evg-icon { color: var(--evg-gold-primary); transform: translateY(-2px); }

  /* 1-10 Scale Registry */
  .evg-scale-registry {
    list-style: none; 
    padding: 0; 
    margin: 0;
  }
  .evg-scale-row {
    display: flex; 
    align-items: center; 
    gap: 1.75rem;
    padding: 1.35rem 2rem; 
    border-bottom: 1px solid var(--evg-border-hairline);
    transition: background 0.2s ease;
  }
  .evg-scale-row:last-child { border-bottom: none; }
  .evg-scale-row:hover { background: rgba(212, 175, 55, 0.03); }
  
  .evg-grade-badge {
    width: 50px; 
    height: 50px; 
    border-radius: 6px;
    display: flex; 
    align-items: center; 
    justify-content: center;
    font-weight: 800; 
    font-size: 1.3rem; 
    font-family: monospace; 
    flex-shrink: 0;
    border: 1px solid transparent;
  }
  
  .evg-grade-10 {
    background: rgba(212, 175, 55, 0.12); 
    border-color: var(--evg-gold-primary);
    color: var(--evg-gold-light); 
    box-shadow: 0 0 20px var(--evg-gold-glow);
  }
  .evg-grade-9 { 
    background: var(--evg-obsidian-elevated); 
    border-color: var(--evg-border-gold-faint); 
    color: var(--evg-gold-primary); 
  }
  .evg-grade-standard { 
    background: var(--evg-obsidian-elevated); 
    border-color: var(--evg-border-hairline); 
    color: var(--evg-text-ash); 
  }

  /* Diagnostic Calculator Widget */
  .evg-calc-card {
    background: var(--evg-obsidian-panel);
    border: 1px solid var(--evg-border-gold-faint);
    border-radius: 8px;
    padding: 30px;
    margin-bottom: 50px;
  }
  .evg-calc-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1.2fr;
    gap: 20px;
    align-items: center;
  }
  .evg-calc-input {
    background: var(--evg-obsidian-elevated);
    border: 1px solid #242428;
    color: #ffffff;
    padding: 10px 14px;
    border-radius: 4px;
    width: 100%;
    font-family: monospace;
    font-size: 0.9rem;
    box-sizing: border-box;
    outline: none;
  }
  .evg-calc-input:focus {
    border-color: var(--evg-gold-primary);
  }
  .evg-calc-result-box {
    background: var(--evg-obsidian-base);
    border: 1px solid var(--evg-border-hairline);
    border-radius: 6px;
    padding: 15px 20px;
    text-align: center;
  }

  /* Buttons */
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
    .evg-calc-grid { grid-template-columns: 1fr; }
  }

  @media (max-width: 768px) {
    .evg-scale-row {
      padding: 1.25rem 1rem;
      gap: 1rem;
    }
    .evg-grade-badge {
      width: 42px;
      height: 42px;
      font-size: 1.1rem;
    }
  }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- 1. EDITORIAL HEADER -->
        <header style="text-align: center; margin-bottom: 45px; padding-bottom: 25px; border-bottom: 1px solid var(--evg-border-hairline);">
            <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Architectural Integrity', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Diagnostic', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Standards & Scale', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 720px; margin: 0 auto 15px auto; font-size: 0.95rem; line-height: 1.6;">
                <?php esc_html_e( 'Every Pokémon card entrusted to Elite Vault Grading receives a rigorous, consistent, and transparent assessment. From intake check-in to tamper-evident sonic encapsulation, precision drives our entire operational architecture.', 'evg-platform' ); ?>
            </p>
            <div style="display: inline-flex; align-items: center; gap: 12px; font-family: monospace; font-size: 0.75rem; color: var(--evg-gold-light); background: var(--evg-obsidian-elevated); padding: 6px 16px; border-radius: 4px; border: 1px solid var(--evg-border-gold-faint);">
                <span><?php printf( esc_html__( 'STANDARD BASE RATE: £%.2f / CARD', 'evg-platform' ), $price_standard ); ?></span>
                <span style="color: #4a4f5c;">|</span>
                <span><?php printf( esc_html__( 'TURNAROUND: %s', 'evg-platform' ), esc_html( $turnaround_time ) ); ?></span>
            </div>
        </header>

        <!-- 2. 7-STEP OPERATIONAL PIPELINE -->
        <section style="margin-bottom: 50px;">
            <div style="margin-bottom: 20px;">
                <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 4px;"><?php esc_html_e( '01 // Certification Journey', 'evg-platform' ); ?></span>
                <h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'The 7-Stage Grading Pipeline', 'evg-platform' ); ?></h2>
            </div>

            <div class="evg-grid-matrix evg-pipeline-matrix">
                <!-- Step 1 -->
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;"><?php esc_html_e( 'Stage 01', 'evg-platform' ); ?></span>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Registry Check-In', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.6; margin: 0;">
                        <?php esc_html_e( 'Consignments are verified, unboxed under secure surveillance, cross-referenced with your declared submission data, and logged into our central tracking database.', 'evg-platform' ); ?>
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;"><?php esc_html_e( 'Stage 02', 'evg-platform' ); ?></span>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Authentication Check', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.6; margin: 0;">
                        <?php esc_html_e( 'Optical and structural verification to screen for counterfeit prints, card trimming, recolouring, pressing, or ungradable physical alterations.', 'evg-platform' ); ?>
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;"><?php esc_html_e( 'Stage 03', 'evg-platform' ); ?></span>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Diagnostic Assessment', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.6; margin: 0;">
                        <?php esc_html_e( 'Rigorous multi-point evaluation across Centring, Corners, Edges, and Surface parameters. High-resolution photo evidence logs all notable flaws.', 'evg-platform' ); ?>
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;"><?php esc_html_e( 'Stage 04', 'evg-platform' ); ?></span>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Grade Determination', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.6; margin: 0;">
                        <?php esc_html_e( 'Diagnostic category sub-scores and overall eye appeal are synthesized into a definitive whole-number grade on our strict 1–10 scale.', 'evg-platform' ); ?>
                    </p>
                </div>

                <!-- Step 5 -->
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;"><?php esc_html_e( 'Stage 05', 'evg-platform' ); ?></span>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Quality Control (QC)', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.6; margin: 0;">
                        <?php esc_html_e( 'Dual-grader verification ensures label data accuracy, metadata matching, and slab optic cleanliness prior to sealing authorization.', 'evg-platform' ); ?>
                    </p>
                </div>

                <!-- Step 6 -->
                <div class="evg-grid-cell">
                    <span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;"><?php esc_html_e( 'Stage 06', 'evg-platform' ); ?></span>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Sonic Encapsulation', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.6; margin: 0;">
                        <?php esc_html_e( 'Ultrasonic welding locks the card permanently inside a durable, tamper-evident protective slab engineered for archival preservation.', 'evg-platform' ); ?>
                    </p>
                </div>

                <!-- Step 7 -->
                <div class="evg-grid-cell" style="grid-column: 1 / -1; background: rgba(212, 175, 55, 0.04);">
                    <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 8px;"><?php esc_html_e( 'Stage 07 // Finalization', 'evg-platform' ); ?></span>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Secure UK Insured Dispatch', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.6; margin: 0;">
                        <?php esc_html_e( 'Encapsulated slabs undergo a final optical polish, receive individual protective sleeves, and are securely boxed for tracked return delivery across the United Kingdom.', 'evg-platform' ); ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- 3. THE 4 ASSESSMENT PILLARS -->
        <section style="margin-bottom: 50px;">
            <div style="text-align: center; margin-bottom: 25px;">
                <span class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( '02 // Core Parameters', 'evg-platform' ); ?></span>
                <h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'The 4 Assessment Pillars', 'evg-platform' ); ?></h2>
            </div>

            <div class="evg-grid-matrix evg-pillar-matrix" style="text-align: center;">
                <div class="evg-grid-cell">
                    <svg class="evg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Centring', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Precision border proportion measurements on both front and back artwork frames.', 'evg-platform' ); ?></p>
                </div>
                <div class="evg-grid-cell">
                    <svg class="evg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 20h16a2 2 0 0 0 2-2V4"/><path d="M4 4v16"/></svg>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Corners', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Microscopic review for corner whitening, edge chipping, and cut radius sharpness.', 'evg-platform' ); ?></p>
                </div>
                <div class="evg-grid-cell">
                    <svg class="evg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Edges', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Perimeter inspection for silvering, rough cuts, stock flaking, and handling wear.', 'evg-platform' ); ?></p>
                </div>
                <div class="evg-grid-cell">
                    <svg class="evg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 2 7 12 22 22 7 12 2"/></svg>
                    <h3 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Surface', 'evg-platform' ); ?></h3>
                    <p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Glazing inspection for print lines, scratches, holo clouding, indentations, and gloss.', 'evg-platform' ); ?></p>
                </div>
            </div>
        </section>

        <!-- 4. INTERACTIVE CENTRING DIAGNOSTIC CALCULATOR -->
        <section class="evg-calc-card">
            <span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Diagnostic Tool', 'evg-platform' ); ?></span>
            <h3 style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0 0 10px 0;"><?php esc_html_e( 'Border Centring Diagnostic Estimator', 'evg-platform' ); ?></h3>
            <p style="color: var(--evg-text-ash); font-size: 0.85rem; margin: 0 0 20px 0;">
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
                    <div id="calcRatioOutput" style="font-family: monospace; font-size: 1.3rem; font-weight: 800; color: var(--evg-gold-primary); margin-bottom: 4px;">50 / 50</div>
                    <span id="calcGradeVerdict" style="font-size: 0.75rem; color: #34c759; font-weight: 700;">✓ GEM MINT 10 CENTRING ELIGIBLE</span>
                </div>
            </div>
        </section>

        <!-- 5. ELITE VAULT 1-10 GRADING SCALE -->
        <section style="margin-bottom: 50px;">
            <div class="evg-module">
                <div style="padding: 30px; border-bottom: 1px solid var(--evg-border-hairline); background: #08080a;">
                    <span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( '03 // The Numeric Hierarchy', 'evg-platform' ); ?></span>
                    <h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( '1–10 Whole Number Scale', 'evg-platform' ); ?></h2>
                    <p style="color: var(--evg-text-ash); font-size: 0.88rem; margin: 0; line-height: 1.5;">
                        <?php esc_html_e( 'We employ a strict whole-number hierarchy. No half-grades or 9.5 variations are permitted within the Elite Vault Grading system.', 'evg-platform' ); ?>
                    </p>
                </div>
                
                <ul class="evg-scale-registry">
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-10">10</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 10 — Elite Gem', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Virtually flawless; exceptional centring (55/45 or better), razor sharp corners, clean edges, and an immaculate surface free of print defects.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-9">9</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 9 — Mint', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Outstanding presentation with centring up to 60/40 and only very minor manufacturing nuances or microscopic handling marks visible under high magnification.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">8</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 8 — Near Mint / Mint', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Extremely clean condition with slight edge whitening or minor centring inconsistencies allowed upon close inspection.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">7</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 7 — Near Mint', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Good quality card showing slight corner wear, minor whitening on edges, or light surface micro-scratches.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">6</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 6 — Excellent', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Noticeable edge whitening, corner softness, moderate handling signs, or minor printing defects across the surface.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">5</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 5 — Very Good', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Clear evidence of handling, moderate edge wear, corner radius softness, and reduced overall eye appeal.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">4</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 4 — Good', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Significant visible wear, multiple areas of edge whitening, light surface creases, and heavily reduced appeal.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">3</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 3 — Fair', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Heavily worn with substantial imperfections, surface scratches, print deterioration, and notable structural flaws.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">2</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 2 — Poor', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Major condition problems, severe surface scratches, and corner creases; card remains authentic and identifiable.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                    <li class="evg-scale-row">
                        <div class="evg-grade-badge evg-grade-standard">1</div>
                        <div>
                            <h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 1 — Damaged', 'evg-platform' ); ?></h3>
                            <p style="color: var(--evg-text-ash); font-size: 0.85rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Heavily compromised card with severe creasing, tears, pinholes, or major surface deterioration.', 'evg-platform' ); ?></p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- 6. CALL TO ACTION -->
        <section class="evg-module" style="padding: 40px; text-align: center;">
            <span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 10px;"><?php esc_html_e( 'Initialize Protocol', 'evg-platform' ); ?></span>
            <h2 style="color: #ffffff; font-size: 1.4rem; font-weight: 700; margin: 0 0 10px 0;"><?php esc_html_e( 'Ready to Preserve Your Collection?', 'evg-platform' ); ?></h2>
            <p style="color: var(--evg-text-ash); max-width: 600px; margin: 0 auto 25px auto; font-size: 0.9rem; line-height: 1.6;">
                <?php esc_html_e( 'Experience verified assessment standards, tamper-evident sonic encapsulation, and transparent stage tracking.', 'evg-platform' ); ?>
            </p>
            <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                <a href="<?php echo esc_url( home_url( '/grade-now' ) ); ?>" class="btn-evg-executive">
                    <?php esc_html_e( 'Grade Now', 'evg-platform' ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/pre-order' ) ); ?>" class="btn-evg-outline">
                    <?php esc_html_e( 'First Drop Pre-Order', 'evg-platform' ); ?>
                </a>
            </div>
        </section>

    </div>
</main>

<script>
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

        if (higher <= 55) {
            verdictOutput.textContent = '✓ ELITE GEM 10 CENTRING ELIGIBLE (55/45 or better)';
            verdictOutput.style.color = '#34c759';
        } else if (higher <= 60) {
            verdictOutput.textContent = '✓ MINT 9 CENTRING ELIGIBLE (60/40 or better)';
            verdictOutput.style.color = '#d4af37';
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