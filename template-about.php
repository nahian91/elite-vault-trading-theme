<?php
/**
 * Template Name: About Us - Executive Tier
 * Description: Ultra-premium company profile for Elite Vault Grading.
 *              Features corporate identity, 8-pillar operational advantages, customer demographic scope,
 *              live transparency portfolio info (dynamic unlock fee), 5-10 business day turnaround,
 *              dynamic system options, and a clean luxury dark UI with centered alignment.
 *
 * @package evg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -------------------------------------------------------------------------
// 1. DYNAMIC SYSTEM SETTINGS RESOLUTION
// -------------------------------------------------------------------------
$evg_support_email       = get_option( 'evg_support_email', 'info@elitevaultgrading.com' );
$evg_turnaround_time     = get_option( 'evg_turnaround_time', '5-10 Business Days' );
$evg_price_standard      = (float) get_option( 'evg_price_standard', 9.99 );
$evg_unlock_fee          = (float) get_option( 'evg_portfolio_unlock_fee', 0.99 );
$evg_accept_submissions  = get_option( 'evg_accept_submissions', 'yes' );

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

	/* Two Column Identity Layout */
	.evg-identity-grid {
		display: grid;
		grid-template-columns: repeat(2, 1fr);
		gap: 24px;
		align-items: stretch;
	}

	/* Grid Matrices */
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
		padding: 2rem 1.5rem; 
		transition: background 0.3s ease;
	}
	.evg-grid-cell:hover { background: var(--evg-obsidian-elevated); }

	.evg-feature-matrix { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }

	/* Centered Flex Matrix for Audience / Bottom Grid to align remaining items perfectly */
	.evg-flex-center-matrix {
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		gap: 1px;
		background: var(--evg-border-hairline);
		border: 1px solid var(--evg-border-hairline);
		border-radius: 8px;
		overflow: hidden;
	}
	.evg-flex-center-cell {
		background: var(--evg-obsidian-panel);
		padding: 1.5rem 1.25rem;
		flex: 1 1 220px;
		max-width: 280px;
		transition: background 0.3s ease;
		text-align: center;
	}
	.evg-flex-center-cell:hover {
		background: var(--evg-obsidian-elevated);
	}

	/* Icons */
	.evg-icon { color: var(--evg-text-ash); margin-bottom: 1.25rem; transition: all 0.3s ease; }
	.evg-grid-cell:hover .evg-icon { color: var(--evg-gold-primary); transform: translateY(-2px); }

	.evg-telemetry-split {
		display: grid;
		grid-template-columns: 1.4fr 1fr;
		gap: 28px;
		align-items: center;
	}

	/* Buttons */
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
	.btn-evg-executive.disabled {
		background: #333336;
		color: #88888e !important;
		cursor: not-allowed;
		box-shadow: none;
	}

	.btn-evg-outline {
		background: transparent; 
		color: var(--evg-gold-primary) !important;
		font-size: 0.82rem; 
		font-weight: 800; 
		letter-spacing: 0.15em; 
		text-transform: uppercase;
		border: 1px solid var(--evg-gold-primary); 
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
	.btn-evg-outline:hover { 
		background: rgba(212, 175, 55, 0.1); 
		color: var(--evg-gold-light) !important; 
		border-color: var(--evg-gold-light); 
	}

	/* Responsive Media Queries */
	@media (max-width: 991.98px) {
		.evg-identity-grid,
		.evg-telemetry-split { grid-template-columns: 1fr; }
	}

	@media (max-width: 767.98px) {
		.evg-container { padding: 2rem 15px 4rem 15px; }
		.evg-module { padding: 25px 20px !important; }
		.btn-evg-executive, .btn-evg-outline { width: 100%; max-width: 320px; }
	}
</style>

<main class="evg-master-wrapper">
	<div class="evg-container">

		<!-- 1. EDITORIAL HEADER -->
		<header style="text-align: center; margin-bottom: 35px; padding-bottom: 20px; border-bottom: 1px solid var(--evg-border-hairline);">
			<span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Corporate Overview', 'evg' ); ?></span>
			<h1 class="evg-title-xl"><?php esc_html_e( 'About', 'evg' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Elite Vault Grading', 'evg' ); ?></span></h1>
			<p style="color: var(--evg-text-ash); max-width: 720px; margin: 0 auto 15px auto; font-size: 0.92rem; line-height: 1.6;">
				<?php
				printf(
					/* translators: %s: Standard pricing formatted as float */
					esc_html__( 'At Elite Vault Grading, we provide collectors with professional, reliable, and consistent card certification starting from £%s. Whether protecting a treasured personal collection, authenticating rare inventory, or preparing cards for market liquidity, our standard is precision you can trust.', 'evg' ),
					esc_html( number_format_i18n( $evg_price_standard, 2 ) )
				);
				?>
			</p>
			<div style="display: inline-flex; align-items: center; gap: 10px; font-family: monospace; font-size: 0.75rem; color: var(--evg-gold-light); background: var(--evg-obsidian-elevated); padding: 5px 16px; border-radius: 4px; border: 1px solid var(--evg-border-gold-faint);">
				<span>
					<?php
					printf(
						/* translators: %s: Turnaround time string */
						esc_html__( 'CURRENT UK TURNAROUND: %s', 'evg' ),
						esc_html( $evg_turnaround_time )
					);
					?>
				</span>
			</div>
		</header>

		<!-- 2. WHO WE ARE & MISSION MODULES -->
		<section class="evg-identity-grid" style="margin-bottom: 40px;">
			<!-- Column 1: Identity -->
			<div class="evg-module" style="padding: 30px 25px;">
				<span class="evg-label-micro" style="color: #ffffff; margin-bottom: 12px;"><?php esc_html_e( '01 // Identity', 'evg' ); ?></span>
				<h2 style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0 0 14px 0;"><?php esc_html_e( 'Infrastructure & Heritage', 'evg' ); ?></h2>
				<p style="color: var(--evg-text-ash); font-size: 0.86rem; line-height: 1.65; margin: 0 0 14px 0;">
					<?php esc_html_e( 'Elite Vault Grading is a dedicated UK-based trading card certification company. We are committed to helping collectors protect, preserve, and showcase their Pokémon TCG collections with uncompromising integrity.', 'evg' ); ?>
				</p>
				<p style="color: var(--evg-text-ash); font-size: 0.86rem; line-height: 1.65; margin: 0;">
					<?php
					printf(
						/* translators: %s: Turnaround time string */
						esc_html__( 'Our experienced team inspects every card across 4 diagnostic pillars before sonic encapsulation in high-clarity protective slabs. With turnaround times from %s, whether submitting a single card or bulk batches, we deliver a grading experience that is transparent, highly responsive, and strictly professional.', 'evg' ),
						esc_html( $evg_turnaround_time )
					);
					?>
				</p>
			</div>

			<!-- Column 2: Mission & Philosophy -->
			<div class="evg-module" style="padding: 30px 25px; display: flex; flex-direction: column; justify-content: space-between;">
				<div>
					<span class="evg-label-micro" style="color: #ffffff; margin-bottom: 12px;"><?php esc_html_e( '02 // Philosophy', 'evg' ); ?></span>
					<h2 style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0 0 14px 0;"><?php esc_html_e( 'Core Mission Directive', 'evg' ); ?></h2>
					<blockquote style="margin: 0 0 14px 0; padding-left: 15px; border-left: 2px solid var(--evg-gold-primary); color: var(--evg-gold-light); font-style: italic; font-size: 0.92rem; line-height: 1.6;">
						<?php esc_html_e( '“To engineer a trusted grading service offering premium protection and outstanding customer service, rendering professional card certification accessible and transparent for all collectors.”', 'evg' ); ?>
					</blockquote>
					<p style="color: var(--evg-text-ash); font-size: 0.86rem; line-height: 1.65; margin: 0;">
						<?php esc_html_e( 'We are passionate about safeguarding the physical condition, verifiable authenticity, and long-term collectible value of your collection.', 'evg' ); ?>
					</p>
				</div>

				<div style="background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-hairline); border-radius: 6px; padding: 14px; margin-top: 20px;">
					<span class="evg-label-micro" style="color: #ffffff; margin-bottom: 6px;"><?php esc_html_e( 'UK Operations & Support Hub', 'evg' ); ?></span>
					<p style="color: var(--evg-text-ash); font-size: 0.78rem; line-height: 1.5; margin: 0;">
						<?php
						printf(
							/* translators: %s: Support email link */
							esc_html__( 'Processing English and Japanese TCG submissions via Royal Mail Special Delivery. Contact our desk at %s.', 'evg' ),
							'<a href="' . esc_url( 'mailto:' . antispambot( $evg_support_email ) ) . '" style="color: var(--evg-gold-primary); text-decoration: none;"><strong>' . esc_html( antispambot( $evg_support_email ) ) . '</strong></a>'
						);
						?>
					</p>
				</div>
			</div>
		</section>

		<!-- 3. WHY CHOOSE ELITE VAULT (8 PILLAR MATRIX) -->
		<section style="margin-bottom: 40px;">
			<div style="text-align: center; margin-bottom: 20px;">
				<span class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( '03 // Standard of Excellence', 'evg' ); ?></span>
				<h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'Why Choose Elite Vault Grading?', 'evg' ); ?></h2>
			</div>

			<div class="evg-grid-matrix evg-feature-matrix" style="text-align: center;">
				<div class="evg-grid-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 2 7 12 22 22 7 12 2"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Diagnostic Integrity', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Strict 1–10 whole-number parameter scaling applied to every evaluated card.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Premium Encapsulation', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'High-clarity, tamper-evident sonic-sealed protective slabs.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Secure Processing', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Complete vault security protocols and climate-controlled storage throughout.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Data Transparency', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Open diagnostic criteria, free preview flaw evidence, and full portfolio unlock.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Dedicated Support', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Fast customer service desk embedded within our UK facility.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Live Telemetry', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Monitor multi-stage order progression directly via your member account.', 'evg' ); ?></p>
				</div>
				<div class="evg-grid-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Inclusive Access', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;">
						<?php
						printf(
							/* translators: %s: Standard pricing formatted as float */
							esc_html__( 'Scalable grading service starting from £%s for collectors of all levels.', 'evg' ),
							esc_html( number_format_i18n( $evg_price_standard, 2 ) )
						);
						?>
					</p>
				</div>
				<div class="evg-grid-cell">
					<svg class="evg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
					<h3 style="color: #ffffff; font-size: 0.95rem; font-weight: 700; margin: 0 0 6px 0;"><?php esc_html_e( 'Specialized Focus', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.8rem; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Expertise dedicated exclusively to English and Japanese Pokémon TCG.', 'evg' ); ?></p>
				</div>
			</div>
		</section>

		<!-- 4. WHO WE GRADE FOR (Centered Flex Layout for bottom row alignment) -->
		<section style="margin-bottom: 40px;">
			<div style="text-align: center; margin-bottom: 20px;">
				<span class="evg-label-micro" style="margin-bottom: 8px;"><?php esc_html_e( '04 // Service Scope', 'evg' ); ?></span>
				<h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php esc_html_e( 'Who We Grade For', 'evg' ); ?></h2>
			</div>

			<div class="evg-flex-center-matrix">
				<div class="evg-flex-center-cell">
					<span style="display: block; font-weight: 700; color: var(--evg-gold-light); font-size: 0.95rem; margin-bottom: 6px;"><?php esc_html_e( 'Casual Collectors', 'evg' ); ?></span>
					<p style="color: var(--evg-text-ash); font-size: 0.78rem; line-height: 1.4; margin: 0;"><?php esc_html_e( 'Protecting childhood favorites and personal binder collections.', 'evg' ); ?></p>
				</div>
				<div class="evg-flex-center-cell">
					<span style="display: block; font-weight: 700; color: var(--evg-gold-light); font-size: 0.95rem; margin-bottom: 6px;"><?php esc_html_e( 'Competitive Players', 'evg' ); ?></span>
					<p style="color: var(--evg-text-ash); font-size: 0.78rem; line-height: 1.4; margin: 0;"><?php esc_html_e( 'Preserving tournament trophies, worlds promos, and deck prizes.', 'evg' ); ?></p>
				</div>
				<div class="evg-flex-center-cell">
					<span style="display: block; font-weight: 700; color: var(--evg-gold-light); font-size: 0.95rem; margin-bottom: 6px;"><?php esc_html_e( 'Capital Investors', 'evg' ); ?></span>
					<p style="color: var(--evg-text-ash); font-size: 0.78rem; line-height: 1.4; margin: 0;"><?php esc_html_e( 'Maximizing verified card condition and market confidence.', 'evg' ); ?></p>
				</div>
				<div class="evg-flex-center-cell">
					<span style="display: block; font-weight: 700; color: var(--evg-gold-light); font-size: 0.95rem; margin-bottom: 6px;"><?php esc_html_e( 'Hobby Enthusiasts', 'evg' ); ?></span>
					<p style="color: var(--evg-text-ash); font-size: 0.78rem; line-height: 1.4; margin: 0;"><?php esc_html_e( 'Showcasing master sets in uniform, matching protective slabs.', 'evg' ); ?></p>
				</div>
				<div class="evg-flex-center-cell">
					<span style="display: block; font-weight: 700; color: var(--evg-gold-light); font-size: 0.95rem; margin-bottom: 6px;"><?php esc_html_e( 'Retailers & Stores', 'evg' ); ?></span>
					<p style="color: var(--evg-text-ash); font-size: 0.78rem; line-height: 1.4; margin: 0;"><?php esc_html_e( 'Bulk grading allocations for shop showcases and customer displays.', 'evg' ); ?></p>
				</div>
				<div class="evg-flex-center-cell">
					<span style="display: block; font-weight: 700; color: var(--evg-gold-light); font-size: 0.95rem; margin-bottom: 6px;"><?php esc_html_e( 'Marketplace Sellers', 'evg' ); ?></span>
					<p style="color: var(--evg-text-ash); font-size: 0.78rem; line-height: 1.4; margin: 0;"><?php esc_html_e( 'Boosting buyer trust on online secondary sales channels.', 'evg' ); ?></p>
				</div>
				<div class="evg-flex-center-cell">
					<span style="display: block; font-weight: 700; color: var(--evg-gold-light); font-size: 0.95rem; margin-bottom: 6px;"><?php esc_html_e( 'Archivists', 'evg' ); ?></span>
					<p style="color: var(--evg-text-ash); font-size: 0.78rem; line-height: 1.4; margin: 0;"><?php esc_html_e( 'Archival preservation for rare, historic, and vintage cards.', 'evg' ); ?></p>
				</div>
				<div class="evg-flex-center-cell">
					<span style="display: block; font-weight: 700; color: var(--evg-gold-light); font-size: 0.95rem; margin-bottom: 6px;"><?php esc_html_e( 'Content Creators', 'evg' ); ?></span>
					<p style="color: var(--evg-text-ash); font-size: 0.78rem; line-height: 1.4; margin: 0;"><?php esc_html_e( 'High-end slab returns for live reveals, unboxings, and breaks.', 'evg' ); ?></p>
				</div>
			</div>
		</section>

		<!-- 5. TRANSPARENCY & DAMAGE PORTFOLIO -->
		<section class="evg-module" style="padding: 30px 25px; margin-bottom: 40px;">
			<div class="evg-telemetry-split">
				<div>
					<span class="evg-label-micro" style="color: #ffffff; margin-bottom: 10px;"><?php esc_html_e( '05 // Diagnostic Telemetry', 'evg' ); ?></span>
					<h2 style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0 0 14px 0;"><?php esc_html_e( 'Optical Transparency: Microscopic Fault Portfolios', 'evg' ); ?></h2>
					<p style="color: var(--evg-text-ash); font-size: 0.86rem; line-height: 1.65; margin: 0 0 12px 0;">
						<?php esc_html_e( 'Elite Vault Grading provides direct diagnostic evidence for completed assessments. Every certified slab lookup includes up to 3 complimentary defect scans for instant inspection.', 'evg' ); ?>
					</p>
					<p style="color: var(--evg-text-ash); font-size: 0.86rem; line-height: 1.65; margin: 0;">
						<?php
						printf(
							/* translators: %s: Portfolio unlock fee formatted as float */
							esc_html__( 'Collectors and secondary-market buyers can unlock the full high-resolution microscopic damage portfolio—complete with all flaw angles, coordinate mapping, and sub-score rationale—for a one-time unlock fee of £%s per card.', 'evg' ),
							esc_html( number_format_i18n( $evg_unlock_fee, 2 ) )
						);
						?>
					</p>
				</div>
				<div style="background: var(--evg-obsidian-base); border: 1px solid var(--evg-border-gold-faint); border-radius: 6px; padding: 20px; text-align: center; margin-top: 15px;">
					<span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 8px;"><?php esc_html_e( 'ACTIVE PLATFORM FEATURE', 'evg' ); ?></span>
					<h3 style="color: #ffffff; font-size: 1.05rem; font-weight: 700; margin: 0 0 8px 0;"><?php esc_html_e( 'Fault Evidence Telemetry', 'evg' ); ?></h3>
					<p style="color: var(--evg-text-ash); font-size: 0.78rem; line-height: 1.5; margin: 0;">
						<?php
						printf(
							/* translators: %s: Portfolio unlock fee formatted as float */
							esc_html__( '3 free previews on all verifications, with comprehensive high-res fault scans unlockable for just £%s per card.', 'evg' ),
							esc_html( number_format_i18n( $evg_unlock_fee, 2 ) )
						);
						?>
					</p>
				</div>
			</div>
		</section>

		<!-- 6. FINAL CTA -->
		<section class="evg-module" style="padding: 35px 20px; text-align: center;">
			<span class="evg-label-micro" style="color: var(--evg-gold-light); margin-bottom: 10px;"><?php esc_html_e( 'Initialize Asset Intake', 'evg' ); ?></span>
			<h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 700; margin: 0 0 10px 0;"><?php esc_html_e( 'Ready to Protect Your Collection?', 'evg' ); ?></h2>
			<p style="color: var(--evg-text-ash); max-width: 600px; margin: 0 auto 25px auto; font-size: 0.88rem; line-height: 1.6;">
				<?php
				printf(
					/* translators: 1: Standard pricing formatted as float, 2: Turnaround time string */
					esc_html__( 'Submit your Pokémon cards today from £%1$s per card for professional grading and secure encapsulation with %2$s turnaround.', 'evg' ),
					esc_html( number_format_i18n( $evg_price_standard, 2 ) ),
					esc_html( $evg_turnaround_time )
				);
				?>
			</p>
			<div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
				<?php if ( 'yes' === $evg_accept_submissions ) : ?>
					<a href="<?php echo esc_url( home_url( '/grading' ) ); ?>" class="btn-evg-executive">
						<?php esc_html_e( 'Grade Now', 'evg' ); ?>
					</a>
				<?php else : ?>
					<span class="btn-evg-executive disabled">
						<?php esc_html_e( 'Submissions Sold Out', 'evg' ); ?>
					</span>
				<?php endif; ?>

				<a href="<?php echo esc_url( home_url( '/buy-it-now' ) ); ?>" class="btn-evg-outline">
					<?php esc_html_e( 'Browse Marketplace', 'evg' ); ?>
				</a>
			</div>
		</section>

	</div>
</main>

<?php
get_footer();