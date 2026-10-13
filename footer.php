<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package evg
 */

?>

	<!-- FOOTER -->
	<footer class="footer">
		<div class="container">
			<div class="row g-4 text-center text-md-start">

				<!-- FOOTER LOGO & INFO -->
				<div class="col-12 col-lg-4 footer-logo-col d-flex flex-column align-items-center align-items-md-start">
					<div class="logo mb-2">
						<div class="logo-footer">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.png' ); ?>" alt="<?php echo esc_attr__( 'Elite Vault Grading', 'evg' ); ?>" style="max-height: 45px;" />
						</div>
					</div>
					<p class="text-center text-md-start mb-3"><?php esc_html_e( 'The premier grading service for Pokémon cards.', 'evg' ); ?></p>

					<!-- SOCIAL MEDIA ICONS (FB, Twitter, Instagram, YouTube, TikTok, LinkedIn) -->
					<div class="footer-social-icons d-flex align-items-center flex-wrap gap-3">
						<!-- Facebook -->
						<a href="https://www.facebook.com/profile.php?id=61594981504968" target="_blank" rel="noopener noreferrer" class="text-gold" aria-label="<?php echo esc_attr__( 'Facebook', 'evg' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
							</svg>
						</a>

						<!-- Instagram -->
						<a href="https://www.instagram.com/elitevaultgrading" target="_blank" rel="noopener noreferrer" class="text-gold" aria-label="<?php echo esc_attr__( 'Instagram', 'evg' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
								<path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
								<line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
							</svg>
						</a>

						<!-- YouTube -->
						<a href="https://youtube.com/@elitevaultgrading?si=_wv2IClmpB9YMLl0" target="_blank" rel="noopener noreferrer" class="text-gold" aria-label="<?php echo esc_attr__( 'YouTube', 'evg' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58 2.78 2.78 0 0 0 1.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.96A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"></path>
								<polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="currentColor"></polygon>
							</svg>
						</a>

						<!-- TikTok -->
						<a href="https://www.tiktok.com/@elitevaultgrading" target="_blank" rel="noopener noreferrer" class="text-gold" aria-label="<?php echo esc_attr__( 'TikTok', 'evg' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
							</svg>
						</a>

					</div>
				</div>

				<!-- FOOTER 1: SERVICES -->
				<div class="col-6 col-sm-4 col-lg-2">
					<h5 class="footer-title">
						<?php
						$menu_obj_1 = get_nav_menu_locations();
						if ( isset( $menu_obj_1['footer-1'] ) ) {
							$menu = wp_get_nav_menu_object( $menu_obj_1['footer-1'] );
							echo $menu ? esc_html( $menu->name ) : esc_html__( 'SERVICES', 'evg' );
						} else {
							esc_html_e( 'SERVICES', 'evg' );
						}
						?>
					</h5>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-1',
							'container'      => false,
							'menu_class'     => 'footer-links list-unstyled mb-0',
							'depth'          => 1,
							'fallback_cb'    => function () {
								?>
								<ul class="footer-links list-unstyled mb-0">
									<li><a href="<?php echo esc_url( home_url( '/grading' ) ); ?>"><?php esc_html_e( 'Grading', 'evg' ); ?></a></li>
									<li><a href="<?php echo esc_url( home_url( '/buy-it-now' ) ); ?>"><?php esc_html_e( 'Buy Graded Cards', 'evg' ); ?></a></li>
									<li><a href="<?php echo esc_url( home_url( '/pre-order' ) ); ?>"><?php esc_html_e( 'Pre-Order', 'evg' ); ?></a></li>
								</ul>
								<?php
							},
						)
					);
					?>
				</div>

				<!-- FOOTER 2: ACCOUNT -->
				<div class="col-6 col-sm-4 col-lg-2">
					<h5 class="footer-title">
						<?php
						$menu_obj_2 = get_nav_menu_locations();
						if ( isset( $menu_obj_2['footer-2'] ) ) {
							$menu = wp_get_nav_menu_object( $menu_obj_2['footer-2'] );
							echo $menu ? esc_html( $menu->name ) : esc_html__( 'ACCOUNT', 'evg' );
						} else {
							esc_html_e( 'ACCOUNT', 'evg' );
						}
						?>
					</h5>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-2',
							'container'      => false,
							'menu_class'     => 'footer-links list-unstyled mb-0',
							'depth'          => 1,
							'fallback_cb'    => function () {
								?>
								<ul class="footer-links list-unstyled mb-0">
									<li><a href="<?php echo esc_url( home_url( '/create-account' ) ); ?>"><?php esc_html_e( 'Create Account', 'evg' ); ?></a></li>
									<li><a href="<?php echo esc_url( home_url( '/sign-in' ) ); ?>"><?php esc_html_e( 'Sign In', 'evg' ); ?></a></li>
									<li><a href="<?php echo esc_url( home_url( '/create-account' ) ); ?>"><?php esc_html_e( 'My Submissions', 'evg' ); ?></a></li>
								</ul>
								<?php
							},
						)
					);
					?>
				</div>

				<!-- FOOTER 3: SUPPORT -->
				<div class="col-6 col-sm-4 col-lg-2">
					<h5 class="footer-title">
						<?php
						$menu_obj_3 = get_nav_menu_locations();
						if ( isset( $menu_obj_3['footer-3'] ) ) {
							$menu = wp_get_nav_menu_object( $menu_obj_3['footer-3'] );
							echo $menu ? esc_html( $menu->name ) : esc_html__( 'SUPPORT', 'evg' );
						} else {
							esc_html_e( 'SUPPORT', 'evg' );
						}
						?>
					</h5>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-3',
							'container'      => false,
							'menu_class'     => 'footer-links list-unstyled mb-0',
							'depth'          => 1,
							'fallback_cb'    => function () {
								?>
								<ul class="footer-links list-unstyled mb-0">
									<li><a href="<?php echo esc_url( home_url( '/faq' ) ); ?>"><?php esc_html_e( 'FAQ', 'evg' ); ?></a></li>
									<li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Contact Us', 'evg' ); ?></a></li>
									<li><a href="<?php echo esc_url( home_url( '/shipping-guidelines' ) ); ?>"><?php esc_html_e( 'Shipping Guidelines', 'evg' ); ?></a></li>
								</ul>
								<?php
							},
						)
					);
					?>
				</div>

				<!-- FOOTER 4: CONTACT US -->
				<div class="col-6 col-sm-6 col-lg-2 footer-contact d-flex flex-column align-items-center align-items-md-start">
					<h5 class="footer-title"><?php esc_html_e( 'CONTACT US', 'evg' ); ?></h5>
					<p class="d-flex align-items-center gap-2 mb-2">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9a050" stroke-width="2">
							<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
							<polyline points="22,6 12,13 2,6"/>
						</svg>
						<a href="mailto:info@elitevaultgrading.com" class="text-reset text-decoration-none">
							<?php echo esc_html( 'info@elitevaultgrading.com' ); ?>
						</a>
					</p>
					<p class="d-flex align-items-center gap-2 mb-0">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9a050" stroke-width="2">
							<circle cx="12" cy="12" r="10"/>
							<polyline points="12 6 12 12 16 14"/>
						</svg>
						<?php esc_html_e( 'Response time: 24-48 hours', 'evg' ); ?>
					</p>
				</div>

			</div>

			<!-- COPYRIGHT BAR -->
			<div class="row mt-4 pt-4 border-top border-secondary">
				<div class="col-12 text-center">
					<p class="footer-copyright mb-0" style="font-size: 11px;">
						<?php
						printf(
							/* translators: 1: Current year, 2: Site name, 3: Creator link */
							esc_html__( '&copy; %1$s %2$s. All rights reserved. | Created By %3$s', 'evg' ),
							esc_html( gmdate( 'Y' ) ),
							esc_html( get_bloginfo( 'name' ) ),
							'<a href="https://www.social-splash.com" target="_blank" rel="noopener noreferrer" class="text-gold">' . esc_html__( 'Social Splash', 'evg' ) . '</a>'
						);
						?>
					</p>
				</div>
			</div>

		</div>
	</footer>

	<?php wp_footer(); ?>
</body>
</html>