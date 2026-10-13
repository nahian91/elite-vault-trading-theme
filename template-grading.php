<?php
/**
 * Template Name: Grading Process & Scale - Executive Tier
 * Description: Clean, high-performance grading architecture template for Elite Vault Grading.
 *              Features dynamic turnaround metrics, 7-step pipeline, interactive calculators, 
 *              strict 1-10 whole-number scale, PO Box dispatch address, Postage vs Collection (Doncaster) declaration logic,
 *              Speed of Service turnaround selection (Standard, Expedited, Fast Tracked),
 *              Classic vs Simple Submission switcher, customer detail & address fields, 
 *              submission notes, promo coupon code & instant auto-updating live summary.
 *
 * @package evg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$evg_turnaround_time    = get_option( 'evg_turnaround_time', '4-6 weeks' );
$evg_price_standard     = (float) get_option( 'evg_price_standard', 9.99 );
$evg_shipping_standard  = (float) get_option( 'evg_return_shipping_fee', 6.99 );
$evg_accept_submissions = get_option( 'evg_accept_submissions', 'yes' );

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

	/* Ace Grading Style Sections */
	.evg-form-box-section {
		background: var(--evg-obsidian-base);
		border: 1px solid var(--evg-border-hairline);
		border-radius: 8px;
		padding: 22px;
		margin-bottom: 22px;
	}

	.evg-box-title {
		font-size: 0.95rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: 0.08em;
		color: #ffffff;
		margin: 0 0 4px 0;
	}

	.evg-box-subtitle {
		color: var(--evg-text-ash);
		font-size: 0.8rem;
		margin: 0 0 16px 0;
	}

	.evg-form-control {
		background: var(--evg-obsidian-elevated);
		border: 1px solid #242428;
		color: var(--evg-text-pure);
		border-radius: 4px;
		padding: 0.85rem 1.25rem;
		font-size: 0.9rem;
		width: 100%;
		box-sizing: border-box;
		outline: none;
		transition: all 0.2s ease;
	}
	.evg-form-control:focus {
		border-color: var(--evg-gold-primary);
		box-shadow: 0 0 0 1px var(--evg-gold-primary);
		background: #09090b;
	}

	/* Style Switcher: Classic vs Simple */
	.evg-style-switcher {
		display: grid;
		grid-template-columns: 1fr 1fr;
		border: 1px solid var(--evg-border-gold-faint);
		border-radius: 6px;
		overflow: hidden;
		margin-bottom: 20px;
	}
	.evg-style-btn {
		padding: 14px;
		text-align: center;
		background: var(--evg-obsidian-elevated);
		color: var(--evg-text-ash);
		font-weight: 700;
		font-size: 0.85rem;
		cursor: pointer;
		transition: all 0.2s ease;
	}
	.evg-style-btn.active {
		background: var(--evg-gold-primary);
		color: var(--evg-text-charcoal);
	}

	.evg-label-options-grid {
		display: grid;
		grid-template-columns: repeat(2, 1fr);
		gap: 20px;
		margin-bottom: 25px;
	}
	.evg-label-option-card {
		background: var(--evg-obsidian-elevated);
		border: 1px solid var(--evg-border-hairline);
		border-radius: 10px;
		padding: 24px;
		display: flex;
		align-items: center;
		gap: 22px;
		cursor: pointer;
		transition: all 0.2s ease;
	}
	.evg-label-option-card:hover {
		border-color: var(--evg-border-gold-faint);
		background: #18181c;
	}
	.evg-label-option-card input[type="radio"] {
		accent-color: var(--evg-gold-primary);
		width: 24px;
		height: 24px;
		cursor: pointer;
	}

	.evg-label-demo-img-wrap { position: relative; display: inline-block; cursor: zoom-in; }
	.evg-label-demo-img {
		width: 130px;
		height: 160px;
		object-fit: cover;
		border-radius: 8px;
		border: 2px solid var(--evg-border-gold-faint);
		background: #000;
		flex-shrink: 0;
		box-shadow: 0 8px 22px rgba(0,0,0,0.8);
		transition: transform 0.2s ease;
	}
	.evg-label-demo-img-wrap:hover .evg-label-demo-img { transform: scale(1.03); border-color: var(--evg-gold-primary); }
	.evg-zoom-hint {
		position: absolute; bottom: 6px; right: 6px; background: rgba(5, 5, 5, 0.8);
		color: var(--evg-gold-primary); font-size: 0.6rem; padding: 2px 6px; border-radius: 4px; font-family: monospace; border: 1px solid var(--evg-border-gold-faint); pointer-events: none;
	}

	/* Summary & Promo Box */
	.evg-summary-card {
		background: var(--evg-obsidian-elevated);
		border: 1px solid var(--evg-border-gold-faint);
		border-radius: 8px;
		padding: 22px;
		margin-bottom: 25px;
	}
	.evg-summary-line {
		display: flex;
		justify-content: space-between;
		padding: 8px 0;
		font-size: 0.85rem;
		border-bottom: 1px solid #1c1c20;
		color: var(--evg-text-ash);
	}
	.evg-summary-line strong {
		color: #ffffff;
	}
	.evg-summary-total {
		display: flex;
		justify-content: space-between;
		padding-top: 14px;
		margin-top: 6px;
		font-size: 1.15rem;
		font-weight: 800;
		color: var(--evg-gold-primary);
	}

	.evg-promo-box {
		background: var(--evg-obsidian-base);
		border: 1px solid var(--evg-border-hairline);
		border-radius: 6px;
		padding: 16px;
		margin-top: 18px;
	}
	.evg-promo-flex {
		display: flex;
		gap: 10px;
		margin-top: 8px;
	}

	/* Delivery & Speed Selector Styles */
	.evg-delivery-selector-wrap {
		background: var(--evg-obsidian-base);
		border: 1px solid var(--evg-border-hairline);
		border-radius: 8px;
		padding: 18px 20px;
		margin-bottom: 25px;
	}
	.evg-delivery-toggle-grid {
		display: grid;
		grid-template-columns: repeat(2, 1fr);
		gap: 15px;
		margin-top: 10px;
	}
	.evg-delivery-opt-label {
		background: var(--evg-obsidian-elevated);
		border: 1px solid #28282c;
		border-radius: 6px;
		padding: 14px;
		display: flex;
		align-items: center;
		gap: 12px;
		cursor: pointer;
		transition: all 0.2s ease;
	}
	.evg-delivery-opt-label:hover {
		border-color: var(--evg-gold-primary);
	}
	.evg-delivery-opt-label input[type="radio"] {
		accent-color: var(--evg-gold-primary);
		width: 18px;
		height: 18px;
	}
	.evg-collection-declaration-box {
		display: none;
		margin-top: 15px;
		padding: 14px 16px;
		background: rgba(212, 175, 55, 0.05);
		border: 1px solid var(--evg-border-gold-faint);
		border-radius: 6px;
	}
	.evg-declaration-check-label {
		display: flex;
		align-items: flex-start;
		gap: 10px;
		cursor: pointer;
		color: #f3e5ab;
		font-size: 0.8rem;
		line-height: 1.5;
	}
	.evg-declaration-check-label input[type="checkbox"] {
		accent-color: var(--evg-gold-primary);
		width: 18px;
		height: 18px;
		margin-top: 2px;
		flex-shrink: 0;
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
		.evg-delivery-toggle-grid { grid-template-columns: 1fr; }
	}

	@media (max-width: 767.98px) {
		.evg-container { padding: 2rem 15px 4rem 15px; }
		.evg-scale-row { padding: 1rem 1rem; gap: 12px; }
		.evg-grade-badge { width: 38px; height: 38px; font-size: 1rem; }
		.btn-evg-outline { width: 100%; max-width: 320px; }
		.evg-module { padding: 25px 15px !important; }
		.evg-label-demo-img { width: 100px; height: 125px; }
		.evg-card-row-item { grid-template-columns: 1fr !important; }
	}
</style>

<main class="evg-master-wrapper">
	<div class="evg-container">

		<!-- 1. EDITORIAL HEADER WITH TOP "GRADE NOW" BUTTON -->
		<header style="text-align: center; margin-bottom: 35px; padding-bottom: 25px; border-bottom: 1px solid var(--evg-border-hairline);">
			<span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Architectural Integrity', 'evg' ); ?></span>
			<h1 class="evg-title-xl"><?php esc_html_e( 'Diagnostic', 'evg' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Standards & Scale', 'evg' ); ?></span></h1>
			<p style="color: var(--evg-text-ash); max-width: 720px; margin: 0 auto 20px auto; font-size: 0.92rem; line-height: 1.6;">
				<?php esc_html_e( 'Every Pokémon card entrusted to Elite Vault Grading receives a rigorous, consistent, and transparent assessment from intake to tamper-evident sonic encapsulation.', 'evg' ); ?>
			</p>
			<div style="display: flex; justify-content: center; gap: 15px; align-items: center; flex-wrap: wrap;">
				<a href="#grading-form-section" class="btn-evg-executive" style="width: auto; padding: 0.8rem 1.8rem;">
					<?php esc_html_e( 'Grade Now &darr;', 'evg' ); ?>
				</a>
			</div>
		</header>

		<!-- 2. 7-STEP OPERATIONAL PIPELINE -->
		<section style="margin-bottom: 40px;">
			<div style="margin-bottom: 16px;">
				<span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 4px;"><?php esc_html_e( '01 // Certification Journey', 'evg' ); ?></span>
				<h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'The 7-Stage Grading Pipeline', 'evg' ); ?></h2>
			</div>

			<div class="evg-grid-matrix evg-pipeline-matrix">
				<div class="evg-grid-cell">
					<span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;"><?php esc_html_e( 'Stage 01', 'evg' ); ?></span>
					<h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Registry Check-In', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;"><?php esc_html_e( 'Consignments are verified, unboxed under secure surveillance, and logged into our central database.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;"><?php esc_html_e( 'Stage 02', 'evg' ); ?></span>
					<h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Authentication Check', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;"><?php esc_html_e( 'Optical verification to screen for counterfeit prints, trimming, recolouring, or pressing.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;"><?php esc_html_e( 'Stage 03', 'evg' ); ?></span>
					<h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Diagnostic Assessment', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;"><?php esc_html_e( 'Multi-point evaluation across Centring, Corners, Edges, and Surface parameters.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;"><?php esc_html_e( 'Stage 04', 'evg' ); ?></span>
					<h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Grade Determination', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;"><?php esc_html_e( 'Sub-scores and eye appeal synthesized into a whole-number grade on our strict 1–10 scale.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;"><?php esc_html_e( 'Stage 05', 'evg' ); ?></span>
					<h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Quality Control (QC)', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;"><?php esc_html_e( 'Dual-grader verification ensures label data accuracy and slab optic cleanliness.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<span class="evg-label-micro" style="color: var(--evg-text-ash); margin-bottom: 8px;"><?php esc_html_e( 'Stage 06', 'evg' ); ?></span>
					<h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Sonic Encapsulation', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;"><?php esc_html_e( 'Ultrasonic welding locks the card permanently inside a tamper-evident protective slab.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell" style="grid-column: 2 / span 1; background: rgba(212, 175, 55, 0.04);">
					<span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 8px;"><?php esc_html_e( 'Stage 07 // Finalization', 'evg' ); ?></span>
					<h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Secure UK Insured Dispatch', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.6; margin: 0;"><?php esc_html_e( 'Encapsulated slabs undergo final polish and are securely boxed for tracked return delivery.', 'evg' ); ?></p>
				</div>
			</div>
		</section>

		<!-- 3. THE 4 ASSESSMENT PILLARS -->
		<section style="margin-bottom: 40px;">
			<div style="text-align: center; margin-bottom: 20px;">
				<span class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( '02 // Core Parameters', 'evg' ); ?></span>
				<h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'The 4 Assessment Pillars', 'evg' ); ?></h2>
			</div>

			<div class="evg-grid-matrix evg-pillar-matrix" style="text-align: center;">
				<div class="evg-grid-cell">
					<svg class="evg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
					<h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Centring', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Precision border proportion measurements on both front and back artwork frames.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<svg class="evg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 20h16a2 2 0 0 0 2-2V4"/><path d="M4 4v16"/></svg>
					<h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Corners', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Microscopic review for corner whitening, edge chipping, and cut radius sharpness.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<svg class="evg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg>
					<h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Edges', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Perimeter inspection for silvering, rough cuts, stock flaking, and handling wear.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<svg class="evg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 2 7 12 22 22 7 12 2"/></svg>
					<h3 style="color: #ffffff; font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Surface', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; margin: 0; line-height: 1.5;"><?php esc_html_e( 'Glazing inspection for print lines, scratches, holo clouding, indentations, and gloss.', 'evg' ); ?></p>
				</div>
			</div>
		</section>

		<!-- 4. INTERACTIVE CENTRING DIAGNOSTIC CALCULATOR -->
		<section class="evg-calc-card">
			<span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Diagnostic Tool', 'evg' ); ?></span>
			<h3 style="color: #ffffff; font-size: 1.15rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Border Centring Diagnostic Estimator', 'evg' ); ?></h3>
			<p style="color: var(--evg-text-ash); font-size: 0.84rem; margin: 0 0 20px 0; line-height: 1.5;">
				<?php esc_html_e( 'Input border measurements (in mm or pixels) to calculate ratio splits and check minimum threshold eligibility for Grade 10 or Grade 9.', 'evg' ); ?>
			</p>

			<div class="evg-calc-grid">
				<div>
					<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Left / Top Border (A)', 'evg' ); ?></label>
					<input type="number" id="calcBorderA" class="evg-calc-input" placeholder="<?php esc_attr_e( 'e.g. 3.2', 'evg' ); ?>" step="0.1" value="3.0">
				</div>
				<div>
					<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Right / Bottom Border (B)', 'evg' ); ?></label>
					<input type="number" id="calcBorderB" class="evg-calc-input" placeholder="<?php esc_attr_e( 'e.g. 2.8', 'evg' ); ?>" step="0.1" value="3.0">
				</div>
				<div class="evg-calc-result-box">
					<span class="evg-label-micro" style="margin-bottom: 4px;"><?php esc_html_e( 'CALCULATED RATIO', 'evg' ); ?></span>
					<div id="calcRatioOutput" style="font-family: monospace; font-size: 1.2rem; font-weight: 800; color: var(--evg-gold-primary); margin-bottom: 4px;">50 / 50</div>
					<span id="calcGradeVerdict" style="font-size: 0.72rem; color: #34c759; font-weight: 700;">&#10003; <?php esc_html_e( 'GEM MINT 10 CENTRING ELIGIBLE', 'evg' ); ?></span>
				</div>
			</div>
		</section>

		<!-- 5. ELITE VAULT 1-10 GRADING SCALE -->
		<section style="margin-bottom: 40px;">
			<div class="evg-module">
				<div style="padding: 25px 20px; border-bottom: 1px solid var(--evg-border-hairline); background: #08080a;">
					<span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( '03 // The Numeric Hierarchy', 'evg' ); ?></span>
					<h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( '1–10 Whole Number Scale Examples', 'evg' ); ?></h2>
					<p style="color: var(--evg-text-ash); font-size: 0.85rem; margin: 0; line-height: 1.5;">
						<?php esc_html_e( 'We employ a strict whole-number hierarchy. No half-grades or 9.5 variations are permitted within the Elite Vault Grading system.', 'evg' ); ?>
					</p>
				</div>

				<ul class="evg-scale-registry">
					<li class="evg-scale-row">
						<div class="evg-grade-badge evg-grade-10">10</div>
						<div>
							<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 10 — Elite Mint', 'evg' ); ?></h3>
							<p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Virtually flawless; exceptional centring (60/40 or better), razor sharp corners, clean edges, and an immaculate surface free of print defects.', 'evg' ); ?></p>
						</div>
					</li>
					<li class="evg-scale-row">
						<div class="evg-grade-badge evg-grade-9">9</div>
						<div>
							<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 9 — Mint', 'evg' ); ?></h3>
							<p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Outstanding presentation with centring up to 60/40 and only very minor manufacturing nuances or microscopic handling marks visible under high magnification.', 'evg' ); ?></p>
						</div>
					</li>
					<li class="evg-scale-row">
						<div class="evg-grade-badge evg-grade-standard">8</div>
						<div>
							<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 8 — Near Mint / Mint', 'evg' ); ?></h3>
							<p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Extremely clean condition with slight edge whitening or minor centring inconsistencies allowed upon close inspection.', 'evg' ); ?></p>
						</div>
					</li>
					<li class="evg-scale-row">
						<div class="evg-grade-badge evg-grade-standard">7</div>
						<div>
							<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 7 — Near Mint', 'evg' ); ?></h3>
							<p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Good quality card showing slight corner wear, minor whitening on edges, or light surface micro-scratches.', 'evg' ); ?></p>
						</div>
					</li>
					<li class="evg-scale-row">
						<div class="evg-grade-badge evg-grade-standard">6</div>
						<div>
							<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 6 — Excellent', 'evg' ); ?></h3>
							<p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Noticeable edge whitening, corner softness, moderate handling signs, or minor printing defects across the surface.', 'evg' ); ?></p>
						</div>
					</li>
					<li class="evg-scale-row">
						<div class="evg-grade-badge evg-grade-standard">5</div>
						<div>
							<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 5 — Very Good', 'evg' ); ?></h3>
							<p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Clear evidence of handling, moderate edge wear, corner radius softness, and reduced overall eye appeal.', 'evg' ); ?></p>
						</div>
					</li>
					<li class="evg-scale-row">
						<div class="evg-grade-badge evg-grade-standard">4</div>
						<div>
							<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 4 — Good', 'evg' ); ?></h3>
							<p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Significant visible wear, multiple areas of edge whitening, light surface creases, and heavily reduced appeal.', 'evg' ); ?></p>
						</div>
					</li>
					<li class="evg-scale-row">
						<div class="evg-grade-badge evg-grade-standard">3</div>
						<div>
							<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 3 — Fair', 'evg' ); ?></h3>
							<p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Heavily worn with substantial imperfections, surface scratches, print deterioration, and notable structural flaws.', 'evg' ); ?></p>
						</div>
					</li>
					<li class="evg-scale-row">
						<div class="evg-grade-badge evg-grade-standard">2</div>
						<div>
							<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 2 — Poor', 'evg' ); ?></h3>
							<p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Major condition problems, severe surface scratches, and corner creases; card remains authentic and identifiable.', 'evg' ); ?></p>
						</div>
					</li>
					<li class="evg-scale-row">
						<div class="evg-grade-badge evg-grade-standard">1</div>
						<div>
							<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 4px 0;"><?php esc_html_e( 'Grade 1 — Damaged', 'evg' ); ?></h3>
							<p style="color: var(--evg-text-ash); font-size: 0.82rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Heavily compromised card with severe creasing, tears, pinholes, or major surface deterioration.', 'evg' ); ?></p>
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
			<span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 6px;"><?php esc_html_e( 'Submission Logistics', 'evg' ); ?></span>
			<h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Official Card Dispatch Address', 'evg' ); ?></h2>
			<p style="color: var(--evg-text-ash); font-size: 0.84rem; margin: 0 auto 20px auto; max-width: 600px; line-height: 1.5;">
				<?php esc_html_e( 'Please securely package your cards and dispatch via Royal Mail Signed For or Special Delivery directly to our secure intake facility:', 'evg' ); ?>
			</p>
			<div style="display: inline-block; background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline); border-radius: 8px; padding: 20px 35px; text-align: left; font-family: monospace; box-shadow: inset 0 2px 6px rgba(0,0,0,0.5);">
				<div style="color: var(--evg-gold-light); font-weight: 700; font-size: 0.95rem; margin-bottom: 6px; letter-spacing: 0.05em;"><?php esc_html_e( 'Elite Vault Grading', 'evg' ); ?></div>
				<div style="color: #ffffff; font-size: 0.9rem; line-height: 1.6;">PO Box 1755<br>Doncaster<br>DN1 9AS</div>
			</div>
		</section>

		<!-- 7. FULL SUBMISSION, SPEED OF SERVICE, ACE-GRADING DETAILS & LABEL CONFIGURATION FORM -->
		<section id="grading-form-section" class="evg-module" style="padding: 35px 25px;">
			<div style="text-align: center; margin-bottom: 25px;">
				<span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 6px;"><?php esc_html_e( 'Submission Portal', 'evg' ); ?></span>
				<h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'Card Declaration & Order Configuration', 'evg' ); ?></h2>
			</div>

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="evgGradingSubmissionForm" style="max-width: 900px; margin: 0 auto;">
				<input type="hidden" name="action" value="evg_process_stripe_checkout">
				<?php wp_nonce_field( 'evg_process_grading_order', 'evg_grading_submit_nonce' ); ?>

				<!-- SECTION 1: SUBMISSION STYLE SWITCHER -->
				<div class="evg-form-box-section">
					<h3 class="evg-box-title"><?php esc_html_e( 'Choose Submission Style', 'evg' ); ?></h3>
					<p class="evg-box-subtitle"><?php esc_html_e( 'Select between declaring your cards manually or sending a bulk batch for us to identify upon receipt.', 'evg' ); ?></p>

					<div class="evg-style-switcher">
						<div class="evg-style-btn active" id="btnClassicStyle" onclick="switchSubmissionStyle('classic')">
							<?php esc_html_e( 'Classic Submission (Itemized)', 'evg' ); ?>
						</div>
						<div class="evg-style-btn" id="btnSimpleStyle" onclick="switchSubmissionStyle('simple')">
							<?php esc_html_e( 'Simple Submission (Bulk Count)', 'evg' ); ?>
						</div>
					</div>
					<input type="hidden" name="submission_style" id="submissionStyleInput" value="Classic Submission">

					<!-- Classic Style: Itemized Rows -->
					<div id="classicSubmissionContainer">
						<div id="evgCardsList" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 15px;">
							<div class="evg-card-row-item" style="background: var(--evg-obsidian-elevated); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 14px; display: grid; grid-template-columns: 2fr 1.5fr 1fr 1fr auto; gap: 10px; align-items: center;">
								<input type="text" name="card_name[]" class="evg-form-control" placeholder="<?php esc_attr_e( 'Card Name (e.g. Charizard)', 'evg' ); ?>">
								<input type="text" name="card_set[]" class="evg-form-control" placeholder="<?php esc_attr_e( 'Set (e.g. Base Set)', 'evg' ); ?>">
								<input type="text" name="card_number[]" class="evg-form-control" placeholder="<?php esc_attr_e( 'Card No (e.g. 4/102)', 'evg' ); ?>">
								<input type="number" name="card_qty[]" class="evg-form-control card-qty-input" placeholder="<?php esc_attr_e( 'Qty', 'evg' ); ?>" value="1" min="1">
								<button type="button" style="background: rgba(255,69,58,0.1); border: 1px solid rgba(255,69,58,0.3); color: #ff453a; width: 36px; height: 36px; border-radius: 4px; cursor: pointer; font-weight: bold;" onclick="removeCardRow(this)">&times;</button>
							</div>
						</div>
						<button type="button" class="btn-evg-outline" onclick="addCardRow()">+ <?php esc_html_e( 'Add Another Card', 'evg' ); ?></button>
					</div>

					<!-- Simple Style: Bulk Quantity Counter -->
					<div id="simpleSubmissionContainer" style="display: none; text-align: center; padding: 15px 0;">
						<span class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Total Cards Being Sent', 'evg' ); ?></span>
						<input type="number" id="simpleCardQuantityInput" name="simple_card_quantity" class="evg-form-control" value="1" min="1" max="300" style="max-width: 180px; margin: 0 auto; text-align: center; font-size: 1.5rem; font-weight: 800; font-family: monospace;">
						<p style="color: var(--evg-text-ash); font-size: 0.8rem; margin-top: 10px;">
							<?php esc_html_e( 'Enter the amount of cards you are sending, and our expert receiving team will identify your cards once we receive them.', 'evg' ); ?>
						</p>
					</div>

					<!-- Declared Total Value / Insurance Input -->
					<div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid var(--evg-border-hairline);">
						<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Estimated Total Declared Value (£)', 'evg' ); ?></label>
						<input type="number" name="declared_insurance_value" class="evg-form-control" placeholder="<?php esc_attr_e( 'e.g. 250.00', 'evg' ); ?>" step="0.01" style="max-width: 280px;">
						<span style="font-size: 0.72rem; color: var(--evg-text-ash); display: block; margin-top: 4px;"><?php esc_html_e( 'Used for tracked return courier insurance protection.', 'evg' ); ?></span>
					</div>
				</div>

				<!-- SECTION 2: SPEED OF SERVICE -->
				<div class="evg-form-box-section">
					<h3 class="evg-box-title"><?php esc_html_e( '(Required) Service speed', 'evg' ); ?></h3>
					<p class="evg-box-subtitle"><?php esc_html_e( 'Choose how fast you want your cards back per order.', 'evg' ); ?></p>

					<div style="display: flex; flex-direction: column; gap: 12px;">
						<label class="evg-delivery-opt-label" style="margin: 0; display: flex; align-items: center; gap: 12px;">
							<input type="radio" name="speed_tier" value="standard" data-fee="0.00" data-label="<?php esc_attr_e( 'Standard (4-6 weeks)', 'evg' ); ?>" checked onchange="recalculateSummary()">
							<div>
								<strong style="color: #ffffff; font-size: 0.9rem; display: block;"><?php esc_html_e( 'Standard (4-6 weeks)', 'evg' ); ?></strong>
								<span style="color: var(--evg-text-ash); font-size: 0.75rem;">&pound;0</span>
							</div>
						</label>

						<label class="evg-delivery-opt-label" style="margin: 0; display: flex; align-items: center; gap: 12px;">
							<input type="radio" name="speed_tier" value="expedited" data-fee="2.99" data-label="<?php esc_attr_e( 'Expedited (2-4 weeks)', 'evg' ); ?>" onchange="recalculateSummary()">
							<div>
								<strong style="color: #ffffff; font-size: 0.9rem; display: block;"><?php esc_html_e( 'Expedited (2-4 weeks)', 'evg' ); ?></strong>
								<span style="color: var(--evg-gold-primary); font-size: 0.75rem;">&pound;2.99</span>
							</div>
						</label>

						<label class="evg-delivery-opt-label" style="margin: 0; display: flex; align-items: center; gap: 12px;">
							<input type="radio" name="speed_tier" value="fast_tracked" data-fee="4.99" data-label="<?php esc_attr_e( 'Fast Tracked (1-2 weeks)', 'evg' ); ?>" onchange="recalculateSummary()">
							<div>
								<strong style="color: #ffffff; font-size: 0.9rem; display: block;"><?php esc_html_e( 'Fast Tracked (1-2 weeks)', 'evg' ); ?></strong>
								<span style="color: var(--evg-gold-light); font-size: 0.75rem;">&pound;4.99</span>
							</div>
						</label>
					</div>
					<input type="hidden" name="service_speed_tier" id="serviceSpeedTierInput" value="Standard (4-6 weeks)">
				</div>

				<!-- SECTION 3: SLAB LABEL OPTIONS -->
				<div class="evg-form-box-section">
					<h3 class="evg-box-title"><?php esc_html_e( 'Select Slab Label Option', 'evg' ); ?></h3>
					<p class="evg-box-subtitle"><?php esc_html_e( 'Choose the label design for your encapsulated cards (Click image to zoom).', 'evg' ); ?></p>

					<div class="evg-label-options-grid">
						<label class="evg-label-option-card">
							<input type="radio" name="slab_label_tier" value="black_basic" data-price="0.00" checked>
							<div class="evg-label-demo-img-wrap" onclick="openEvgLightbox(this)">
								<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/black.jpeg' ) ); ?>" alt="<?php esc_attr_e( 'Black Basic Label', 'evg' ); ?>" class="evg-label-demo-img">
								<span class="evg-zoom-hint">&#128269; <?php esc_html_e( 'Zoom', 'evg' ); ?></span>
							</div>
							<div>
								<strong style="color: #fff; display: block; margin-bottom: 2px;"><?php esc_html_e( 'Black Basic label', 'evg' ); ?></strong>
								<span style="color: var(--evg-gold-primary); font-family: monospace; font-weight: 700;">&pound;0.00 / <?php esc_html_e( 'card', 'evg' ); ?></span>
							</div>
						</label>

						<label class="evg-label-option-card">
							<input type="radio" name="slab_label_tier" value="colour_match" data-price="0.99">
							<div class="evg-label-demo-img-wrap" onclick="openEvgLightbox(this)">
								<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/color.jpeg' ) ); ?>" alt="<?php esc_attr_e( 'Colour Match Label', 'evg' ); ?>" class="evg-label-demo-img">
								<span class="evg-zoom-hint">&#128269; <?php esc_html_e( 'Zoom', 'evg' ); ?></span>
							</div>
							<div>
								<strong style="color: #fff; display: block; margin-bottom: 2px;"><?php esc_html_e( 'Colour match', 'evg' ); ?></strong>
								<span style="color: var(--evg-gold-primary); font-family: monospace; font-weight: 700;">&pound;0.99 / <?php esc_html_e( 'card', 'evg' ); ?></span>
							</div>
						</label>

						<label class="evg-label-option-card">
							<input type="radio" name="slab_label_tier" value="lightening" data-price="0.99">
							<div class="evg-label-demo-img-wrap" onclick="openEvgLightbox(this)">
								<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/lighting.jpeg' ) ); ?>" alt="<?php esc_attr_e( 'Lightening Label', 'evg' ); ?>" class="evg-label-demo-img">
								<span class="evg-zoom-hint">&#128269; <?php esc_html_e( 'Zoom', 'evg' ); ?></span>
							</div>
							<div>
								<strong style="color: #fff; display: block; margin-bottom: 2px;"><?php esc_html_e( 'Lightening', 'evg' ); ?></strong>
								<span style="color: var(--evg-gold-primary); font-family: monospace; font-weight: 700;">&pound;0.99 / <?php esc_html_e( 'card', 'evg' ); ?></span>
							</div>
						</label>

						<label class="evg-label-option-card">
							<input type="radio" name="slab_label_tier" value="extended_art" data-price="2.99">
							<div class="evg-label-demo-img-wrap" onclick="openEvgLightbox(this)">
								<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/extended.jpeg' ) ); ?>" alt="<?php esc_attr_e( 'Extended Artwork Label', 'evg' ); ?>" class="evg-label-demo-img">
								<span class="evg-zoom-hint">&#128269; <?php esc_html_e( 'Zoom', 'evg' ); ?></span>
							</div>
							<div>
								<strong style="color: #fff; display: block; margin-bottom: 2px;"><?php esc_html_e( 'Extended Artwork', 'evg' ); ?></strong>
								<span style="color: var(--evg-gold-primary); font-family: monospace; font-weight: 700;">&pound;2.99 / <?php esc_html_e( 'card', 'evg' ); ?></span>
							</div>
						</label>
					</div>
				</div>

				<!-- SECTION 4: SUBMISSION NOTES -->
				<div class="evg-form-box-section">
					<h3 class="evg-box-title"><?php esc_html_e( 'Submission Notes', 'evg' ); ?></h3>
					<p class="evg-box-subtitle"><?php esc_html_e( 'Provide any specific notes, instructions or details about your submission below.', 'evg' ); ?></p>
					<textarea name="submission_notes" class="evg-form-control" rows="3" placeholder="<?php esc_attr_e( 'e.g. Please take extra care with vintage holos...', 'evg' ); ?>"></textarea>
				</div>

				<!-- SECTION 5: YOUR DETAILS -->
				<div class="evg-form-box-section">
					<h3 class="evg-box-title"><?php esc_html_e( 'Your Details', 'evg' ); ?></h3>
					<p class="evg-box-subtitle"><?php esc_html_e( 'Enter your contact info so we can communicate regarding your card consignments.', 'evg' ); ?></p>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
						<div>
							<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'First name *', 'evg' ); ?></label>
							<input type="text" name="first_name" class="evg-form-control" placeholder="<?php esc_attr_e( 'Jamie', 'evg' ); ?>" required>
						</div>
						<div>
							<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Last name *', 'evg' ); ?></label>
							<input type="text" name="last_name" class="evg-form-control" placeholder="<?php esc_attr_e( 'Brister', 'evg' ); ?>" required>
						</div>
					</div>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
						<div>
							<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Mobile Number *', 'evg' ); ?></label>
							<input type="tel" name="mobile_number" class="evg-form-control" placeholder="<?php esc_attr_e( '07445690065', 'evg' ); ?>" required>
						</div>
						<div>
							<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Email Address *', 'evg' ); ?></label>
							<input type="email" name="customer_email" class="evg-form-control" placeholder="<?php esc_attr_e( 'client@example.com', 'evg' ); ?>" required>
						</div>
					</div>
				</div>

				<!-- SECTION 6: YOUR ADDRESS -->
				<div class="evg-form-box-section">
					<h3 class="evg-box-title"><?php esc_html_e( 'Your Address', 'evg' ); ?></h3>
					<p class="evg-box-subtitle"><?php esc_html_e( 'The address you would like your cards returned to.', 'evg' ); ?></p>

					<div style="margin-bottom: 15px;">
						<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Country / Region *', 'evg' ); ?></label>
						<select name="country_region" class="evg-form-control" required style="background: var(--evg-obsidian-elevated); color: #fff;">
							<option value="United Kingdom" selected><?php esc_html_e( 'United Kingdom', 'evg' ); ?></option>
							<option value="Ireland"><?php esc_html_e( 'Ireland', 'evg' ); ?></option>
							<option value="Other"><?php esc_html_e( 'Other', 'evg' ); ?></option>
						</select>
					</div>

					<div style="margin-bottom: 15px;">
						<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Address line 1 *', 'evg' ); ?></label>
						<input type="text" name="address_line_1" class="evg-form-control" placeholder="<?php esc_attr_e( 'House number and street name', 'evg' ); ?>" required>
					</div>

					<div style="margin-bottom: 15px;">
						<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Address line 2', 'evg' ); ?></label>
						<input type="text" name="address_line_2" class="evg-form-control" placeholder="<?php esc_attr_e( 'Apartment, suite, unit, etc. (optional)', 'evg' ); ?>">
					</div>

					<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
						<div>
							<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'City *', 'evg' ); ?></label>
							<input type="text" name="city" class="evg-form-control" placeholder="<?php esc_attr_e( 'City / Town', 'evg' ); ?>" required>
						</div>
						<div>
							<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'State / County', 'evg' ); ?></label>
							<input type="text" name="county" class="evg-form-control" placeholder="<?php esc_attr_e( 'County', 'evg' ); ?>">
						</div>
						<div>
							<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'ZIP / Postal Code *', 'evg' ); ?></label>
							<input type="text" name="postcode" class="evg-form-control" placeholder="<?php esc_attr_e( 'e.g. DN1 9AS', 'evg' ); ?>" required>
						</div>
					</div>
				</div>

				<!-- SECTION 7: DELIVERY / FULFILMENT METHOD -->
				<div class="evg-delivery-selector-wrap">
					<label class="evg-label-micro" style="margin-bottom: 6px;"><?php esc_html_e( 'Fulfilment / Delivery Method *', 'evg' ); ?></label>
					<div class="evg-delivery-toggle-grid">
						<label class="evg-delivery-opt-label">
							<input type="radio" name="fulfilment_method" value="postage" checked onchange="toggleDeliveryMethod(this.value)">
							<div>
								<strong style="color: #ffffff; font-size: 0.9rem; display: block;"><?php esc_html_e( 'Royal Mail Insured Tracked Postage', 'evg' ); ?></strong>
								<span style="color: var(--evg-text-ash); font-size: 0.75rem;">
									<?php
									printf(
										/* translators: %s: Postage fee formatted as localized currency */
										esc_html__( 'Direct return courier (£%s)', 'evg' ),
										esc_html( number_format_i18n( $evg_shipping_standard, 2 ) )
									);
									?>
								</span>
							</div>
						</label>

						<label class="evg-delivery-opt-label">
							<input type="radio" name="fulfilment_method" value="collection" onchange="toggleDeliveryMethod(this.value)">
							<div>
								<strong style="color: #ffffff; font-size: 0.9rem; display: block;"><?php esc_html_e( 'Collection – Doncaster', 'evg' ); ?></strong>
								<span style="color: var(--evg-gold-primary); font-size: 0.75rem;"><?php esc_html_e( 'Free pickup (Prior agreement mandatory)', 'evg' ); ?></span>
							</div>
						</label>
					</div>

					<!-- Mandatory Collection Declaration Checkbox -->
					<div id="evgCollectionDeclarationBox" class="evg-collection-declaration-box">
						<label class="evg-declaration-check-label">
							<input type="checkbox" name="collection_agreement_confirmed" id="evgCollectionAgreementCheckbox" value="yes">
							<span>
								<strong><?php esc_html_e( 'Declaration:', 'evg' ); ?></strong> 
								<?php esc_html_e( 'I confirm that collection has been agreed with Elite Vault Grading prior to placing this order. I understand that collection is based in Doncaster and that my order will not be fulfilled via collection unless this has been agreed with the company beforehand.', 'evg' ); ?>
							</span>
						</label>
					</div>
				</div>

				<!-- SECTION 8: LIVE AUTO-UPDATING SUBMISSION SUMMARY & PROMOTIONAL CODE -->
				<div class="evg-summary-card">
					<h3 class="evg-box-title" style="border-bottom: 1px solid var(--evg-border-hairline); padding-bottom: 10px; margin-bottom: 12px;"><?php esc_html_e( 'Submission Summary', 'evg' ); ?></h3>

					<div class="evg-summary-line">
						<span><?php esc_html_e( 'Submission Type', 'evg' ); ?></span>
						<strong id="summarySubmissionStyle"><?php esc_html_e( 'Classic Submission', 'evg' ); ?></strong>
					</div>
					<div class="evg-summary-line">
						<span><?php esc_html_e( 'Service speed', 'evg' ); ?></span>
						<strong id="summarySpeedLevel"><?php esc_html_e( 'Standard (4-6 weeks)', 'evg' ); ?></strong>
					</div>
					<div class="evg-summary-line">
						<span><?php esc_html_e( 'Speed of Service Fee', 'evg' ); ?></span>
						<strong id="summarySpeedFee">&pound;0.00</strong>
					</div>
					<div class="evg-summary-line">
						<span><?php esc_html_e( 'Base Grading Price', 'evg' ); ?></span>
						<strong>&pound;<?php echo esc_html( number_format_i18n( $evg_price_standard, 2 ) ); ?> / <?php esc_html_e( 'Card', 'evg' ); ?></strong>
					</div>
					<div class="evg-summary-line">
						<span><?php esc_html_e( 'Collectible Quantity', 'evg' ); ?></span>
						<strong id="summaryQuantity"><?php esc_html_e( '1 Cards', 'evg' ); ?></strong>
					</div>
					<div class="evg-summary-line">
						<span><?php esc_html_e( 'Premium Labels Fee', 'evg' ); ?></span>
						<strong id="summaryLabelFee">&pound;0.00</strong>
					</div>
					<div class="evg-summary-line">
						<span><?php esc_html_e( 'Subtotal', 'evg' ); ?></span>
						<strong id="summarySubtotal">&pound;<?php echo esc_html( number_format_i18n( $evg_price_standard, 2 ) ); ?></strong>
					</div>
					<div class="evg-summary-line">
						<span><?php esc_html_e( 'Return Shipping', 'evg' ); ?></span>
						<strong id="summaryShipping">&pound;<?php echo esc_html( number_format_i18n( $evg_shipping_standard, 2 ) ); ?></strong>
					</div>
					<div class="evg-summary-line" id="discountRow" style="display: none; color: #34c759;">
						<span><?php esc_html_e( 'Promotional Discount', 'evg' ); ?></span>
						<strong id="summaryDiscount">-&pound;0.00</strong>
					</div>

					<div class="evg-summary-total">
						<span><?php esc_html_e( 'Estimated Total', 'evg' ); ?></span>
						<span id="summaryTotal">&pound;<?php echo esc_html( number_format_i18n( (float) ( $evg_price_standard + $evg_shipping_standard ), 2 ) ); ?></span>
					</div>

					<!-- ADD PROMOTIONAL CODE -->
					<div class="evg-promo-box">
						<label class="evg-label-micro" style="margin-bottom: 2px;"><?php esc_html_e( 'ADD PROMOTIONAL CODE', 'evg' ); ?></label>
						<span style="font-size: 0.75rem; color: var(--evg-text-ash);"><?php esc_html_e( 'Do you have a promotional code? Enter it here:', 'evg' ); ?></span>
						<div class="evg-promo-flex">
							<input type="text" id="couponCodeInput" name="promo_code" class="evg-form-control" placeholder="<?php esc_attr_e( 'e.g. EVG10', 'evg' ); ?>" style="padding: 10px 14px; font-family: monospace;">
							<button type="button" class="btn-evg-outline" onclick="applyCouponCode()" style="white-space: nowrap;"><?php esc_html_e( 'Redeem Code', 'evg' ); ?></button>
						</div>
						<div id="couponMessage" style="font-size: 0.75rem; margin-top: 6px;"></div>
					</div>
				</div>

				<input type="hidden" name="calculated_total_amount" id="calculatedTotalInput" value="<?php echo esc_attr( number_format( (float) ( $evg_price_standard + $evg_shipping_standard ), 2, '.', '' ) ); ?>">

				<button type="submit" class="btn-evg-executive" id="evgSubmitOrderBtn">
					<?php esc_html_e( 'Proceed to Secure Payment', 'evg' ); ?>
				</button>
			</form>
		</section>

	</div>
</main>

<!-- Lightbox Modal Container -->
<div id="evgImageLightbox" onclick="closeEvgLightbox()">
	<div class="evg-lightbox-content" onclick="event.stopPropagation()">
		<button class="evg-lightbox-close" onclick="closeEvgLightbox()">&times;</button>
		<img id="evgLightboxImg" src="" alt="<?php esc_attr_e( 'Enlarged Label View', 'evg' ); ?>">
		<div id="evgLightboxCaption" style="color: var(--evg-gold-light); font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;"></div>
	</div>
</div>

<script>
var basePricePerCard    = <?php echo (float) $evg_price_standard; ?>;
var baseShippingFee     = <?php echo (float) $evg_shipping_standard; ?>;
var activeDiscountPct   = 0;
var activeDiscountFixed = 0;
var activeStyle         = 'classic';

// Classic vs Simple Mode Switcher
function switchSubmissionStyle(style) {
	activeStyle = style;
	var btnClassic     = document.getElementById('btnClassicStyle');
	var btnSimple      = document.getElementById('btnSimpleStyle');
	var wrapClassic    = document.getElementById('classicSubmissionContainer');
	var wrapSimple     = document.getElementById('simpleSubmissionContainer');
	var styleInput     = document.getElementById('submissionStyleInput');
	var summaryStyleEl = document.getElementById('summarySubmissionStyle');

	if (style === 'simple') {
		btnSimple.classList.add('active');
		btnClassic.classList.remove('active');
		wrapSimple.style.display = 'block';
		wrapClassic.style.display = 'none';
		styleInput.value = 'Simple Submission';
		if (summaryStyleEl) summaryStyleEl.textContent = 'Simple Submission';
	} else {
		btnClassic.classList.add('active');
		btnSimple.classList.remove('active');
		wrapClassic.style.display = 'block';
		wrapSimple.style.display = 'none';
		styleInput.value = 'Classic Submission';
		if (summaryStyleEl) summaryStyleEl.textContent = 'Classic Submission';
	}
	recalculateSummary();
}

// Instant Live Auto Update Function
function recalculateSummary() {
	var totalCards = 0;

	if (activeStyle === 'simple') {
		var simpleInput = document.getElementById('simpleCardQuantityInput');
		totalCards = simpleInput ? (parseInt(simpleInput.value, 10) || 1) : 1;
	} else {
		var qtyInputs = document.querySelectorAll('.card-qty-input');
		qtyInputs.forEach(function(input) {
			var val = parseInt(input.value, 10);
			if (isNaN(val) || val < 1) val = 1;
			totalCards += val;
		});
	}

	if (totalCards < 1) totalCards = 1;

	// Selected Label Fee
	var selectedLabel = document.querySelector('input[name="slab_label_tier"]:checked');
	var labelPricePerCard = selectedLabel ? parseFloat(selectedLabel.getAttribute('data-price')) || 0 : 0;
	var totalLabelFee = totalCards * labelPricePerCard;

	// Selected Speed of Service Fee
	var selectedSpeed = document.querySelector('input[name="speed_tier"]:checked');
	var speedFee = selectedSpeed ? parseFloat(selectedSpeed.getAttribute('data-fee')) || 0 : 0;
	var speedLabel = selectedSpeed ? selectedSpeed.getAttribute('data-label') : 'Standard (4-6 weeks)';

	var speedInput = document.getElementById('serviceSpeedTierInput');
	if (speedInput) speedInput.value = speedLabel;

	var summarySpeedLvl = document.getElementById('summarySpeedLevel');
	if (summarySpeedLvl) summarySpeedLvl.textContent = speedLabel;

	var summarySpeedFeeEl = document.getElementById('summarySpeedFee');
	if (summarySpeedFeeEl) summarySpeedFeeEl.textContent = '£' + speedFee.toFixed(2);

	// Base Subtotal (Cards + Labels + Speed of Service)
	var baseCardsCost = totalCards * basePricePerCard;
	var subtotal = baseCardsCost + totalLabelFee + speedFee;

	// Shipping Check (£6.99 or free collection)
	var deliveryMethodInput = document.querySelector('input[name="fulfilment_method"]:checked');
	var deliveryMethod = deliveryMethodInput ? deliveryMethodInput.value : 'postage';
	var shippingFee = (deliveryMethod === 'collection') ? 0.00 : baseShippingFee;

	// Discount Calculation
	var discount = 0;
	if (activeDiscountPct > 0) {
		discount = subtotal * (activeDiscountPct / 100);
	} else if (activeDiscountFixed > 0) {
		discount = activeDiscountFixed;
	}
	if (discount > subtotal) discount = subtotal;

	var finalTotal = (subtotal - discount) + shippingFee;

	// Update DOM Elements in Real-Time
	var summaryQtyEl = document.getElementById('summaryQuantity');
	if (summaryQtyEl) summaryQtyEl.textContent = totalCards + ' Cards';

	var summaryLabelEl = document.getElementById('summaryLabelFee');
	if (summaryLabelEl) summaryLabelEl.textContent = '£' + totalLabelFee.toFixed(2);

	var summarySubtotalEl = document.getElementById('summarySubtotal');
	if (summarySubtotalEl) summarySubtotalEl.textContent = '£' + subtotal.toFixed(2);

	var summaryShippingEl = document.getElementById('summaryShipping');
	if (summaryShippingEl) summaryShippingEl.textContent = (shippingFee === 0) ? '£0.00 (Collection)' : '£' + shippingFee.toFixed(2);

	var discountRow = document.getElementById('discountRow');
	var summaryDiscountEl = document.getElementById('summaryDiscount');
	if (discount > 0) {
		if (discountRow) discountRow.style.display = 'flex';
		if (summaryDiscountEl) summaryDiscountEl.textContent = '-£' + discount.toFixed(2);
	} else {
		if (discountRow) discountRow.style.display = 'none';
	}

	var summaryTotalEl = document.getElementById('summaryTotal');
	if (summaryTotalEl) summaryTotalEl.textContent = '£' + finalTotal.toFixed(2);

	var calcTotalInput = document.getElementById('calculatedTotalInput');
	if (calcTotalInput) calcTotalInput.value = finalTotal.toFixed(2);
}

function applyCouponCode() {
	var code = document.getElementById('couponCodeInput').value.trim().toUpperCase();
	var msg = document.getElementById('couponMessage');

	if (code === 'EVG10') {
		activeDiscountPct = 10;
		activeDiscountFixed = 0;
		msg.textContent = '✓ Promotional Code EVG10 applied (10% OFF)';
		msg.style.color = '#34c759';
	} else if (code === 'VAULT5') {
		activeDiscountPct = 0;
		activeDiscountFixed = 5.00;
		msg.textContent = '✓ Promotional Code VAULT5 applied (£5.00 OFF)';
		msg.style.color = '#34c759';
	} else if (code === '') {
		activeDiscountPct = 0;
		activeDiscountFixed = 0;
		msg.textContent = 'Please enter a promotional code';
		msg.style.color = '#ff9f0a';
	} else {
		activeDiscountPct = 0;
		activeDiscountFixed = 0;
		msg.textContent = '✕ Invalid or expired promotional code';
		msg.style.color = '#ff453a';
	}
	recalculateSummary();
}

function addCardRow() {
	var container = document.getElementById('evgCardsList');
	var firstRow = container.querySelector('.evg-card-row-item');
	var newRow = firstRow.cloneNode(true);
	var inputs = newRow.querySelectorAll('input');
	inputs.forEach(function(input) {
		if (input.type === 'number') input.value = 1;
		else input.value = '';
	});
	container.appendChild(newRow);
	bindAutoUpdateEvents();
	recalculateSummary();
}

function removeCardRow(btn) {
	var container = document.getElementById('evgCardsList');
	if (container.querySelectorAll('.evg-card-row-item').length > 1) {
		btn.closest('.evg-card-row-item').remove();
		recalculateSummary();
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

function toggleDeliveryMethod(method) {
	var decBox = document.getElementById('evgCollectionDeclarationBox');
	var checkbox = document.getElementById('evgCollectionAgreementCheckbox');
	if (method === 'collection') {
		decBox.style.display = 'block';
		checkbox.required = true;
	} else {
		decBox.style.display = 'none';
		checkbox.required = false;
		checkbox.checked = false;
	}
	recalculateSummary();
}

// Attach live input listeners for continuous auto-updates
function bindAutoUpdateEvents() {
	var qtyInputs = document.querySelectorAll('.card-qty-input');
	qtyInputs.forEach(function(input) {
		input.removeEventListener('input', recalculateSummary);
		input.removeEventListener('change', recalculateSummary);
		input.addEventListener('input', recalculateSummary);
		input.addEventListener('change', recalculateSummary);
	});

	var simpleQty = document.getElementById('simpleCardQuantityInput');
	if (simpleQty) {
		simpleQty.removeEventListener('input', recalculateSummary);
		simpleQty.removeEventListener('change', recalculateSummary);
		simpleQty.addEventListener('input', recalculateSummary);
		simpleQty.addEventListener('change', recalculateSummary);
	}

	var speedRadios = document.querySelectorAll('input[name="speed_tier"]');
	speedRadios.forEach(function(radio) {
		radio.removeEventListener('change', recalculateSummary);
		radio.addEventListener('change', recalculateSummary);
	});

	var labelRadios = document.querySelectorAll('input[name="slab_label_tier"]');
	labelRadios.forEach(function(radio) {
		radio.removeEventListener('change', recalculateSummary);
		radio.addEventListener('change', recalculateSummary);
	});

	var deliveryRadios = document.querySelectorAll('input[name="fulfilment_method"]');
	deliveryRadios.forEach(function(radio) {
		radio.removeEventListener('change', recalculateSummary);
		radio.addEventListener('change', recalculateSummary);
	});
}

document.addEventListener('DOMContentLoaded', function() {
	bindAutoUpdateEvents();

	var form = document.getElementById('evgGradingSubmissionForm');
	if (form) {
		form.addEventListener('submit', function(e) {
			var selectedDelivery = form.querySelector('input[name="fulfilment_method"]:checked');
			var decCheckbox = document.getElementById('evgCollectionAgreementCheckbox');
			if (selectedDelivery && selectedDelivery.value === 'collection' && !decCheckbox.checked) {
				e.preventDefault();
				alert('You must tick the mandatory declaration confirming prior pickup agreement in Doncaster before placing a collection order.');
				decCheckbox.focus();
			}
		});
	}

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

	recalculateSummary();
});
</script>

<?php
get_footer();