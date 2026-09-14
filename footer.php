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
                            <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/logo.png" alt="Elite Vault Grading" style="max-height: 45px;" />
                        </div>
                    </div>
                    <p class="text-center text-md-start mb-3">The premier grading service for Pokémon cards.</p>
                    
                    <!-- SOCIAL MEDIA ICONS (FB, Twitter, Instagram, YouTube, TikTok, LinkedIn) -->
                    <div class="footer-social-icons d-flex align-items-center flex-wrap gap-3">
                        <!-- Facebook -->
                        <a href="#" target="_blank" rel="noopener noreferrer" class="text-gold" aria-label="Facebook">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                        </a>
                        <!-- Twitter / X -->
                        <a href="#" target="_blank" rel="noopener noreferrer" class="text-gold" aria-label="Twitter">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                            </svg>
                        </a>
                        <!-- Instagram -->
                        <a href="#" target="_blank" rel="noopener noreferrer" class="text-gold" aria-label="Instagram">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                        </a>
                        <!-- YouTube -->
                        <a href="#" target="_blank" rel="noopener noreferrer" class="text-gold" aria-label="YouTube">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58 2.78 2.78 0 0 0 1.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.96A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"></path>
                                <polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="currentColor"></polygon>
                            </svg>
                        </a>
                        <!-- TikTok -->
                        <a href="#" target="_blank" rel="noopener noreferrer" class="text-gold" aria-label="TikTok">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
                            </svg>
                        </a>
                        <!-- LinkedIn -->
                        <a href="#" target="_blank" rel="noopener noreferrer" class="text-gold" aria-label="LinkedIn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                <rect x="2" y="9" width="4" height="12"></rect>
                                <circle cx="4" cy="4" r="2"></circle>
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
                            echo $menu ? esc_html( $menu->name ) : 'SERVICES';
                        } else {
                            echo 'SERVICES';
                        }
                        ?>
                    </h5>
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'footer-1',
                        'container'      => false,
                        'menu_class'     => 'footer-links list-unstyled mb-0',
                        'depth'          => 1,
                        'fallback_cb'    => function() { ?>
                            <ul class="footer-links list-unstyled mb-0">
                                <li><a href="<?php echo esc_url( home_url( '/grading' ) ); ?>">Grading</a></li>
                                <li><a href="<?php echo esc_url( home_url( '/buy-it-now' ) ); ?>">Buy Graded Cards</a></li>
                                <li><a href="<?php echo esc_url( home_url( '/pre-order' ) ); ?>">Pre-Order</a></li>
                            </ul>
                        <?php },
                    ) );
                    ?>
                </div>

                <!-- FOOTER 2: ACCOUNT -->
                <div class="col-6 col-sm-4 col-lg-2">
                    <h5 class="footer-title">
                        <?php
                        $menu_obj_2 = get_nav_menu_locations();
                        if ( isset( $menu_obj_2['footer-2'] ) ) {
                            $menu = wp_get_nav_menu_object( $menu_obj_2['footer-2'] );
                            echo $menu ? esc_html( $menu->name ) : 'ACCOUNT';
                        } else {
                            echo 'ACCOUNT';
                        }
                        ?>
                    </h5>
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'footer-2',
                        'container'      => false,
                        'menu_class'     => 'footer-links list-unstyled mb-0',
                        'depth'          => 1,
                        'fallback_cb'    => function() { ?>
                            <ul class="footer-links list-unstyled mb-0">
                                <li><a href="<?php echo esc_url( home_url( '/create-account' ) ); ?>">Create Account</a></li>
                                <li><a href="<?php echo esc_url( home_url( '/sign-in' ) ); ?>">Sign In</a></li>
                                <li><a href="<?php echo esc_url( home_url( '/create-account' ) ); ?>">My Submissions</a></li>
                            </ul>
                        <?php },
                    ) );
                    ?>
                </div>

                <!-- FOOTER 3: SUPPORT -->
                <div class="col-6 col-sm-4 col-lg-2">
                    <h5 class="footer-title">
                        <?php
                        $menu_obj_3 = get_nav_menu_locations();
                        if ( isset( $menu_obj_3['footer-3'] ) ) {
                            $menu = wp_get_nav_menu_object( $menu_obj_3['footer-3'] );
                            echo $menu ? esc_html( $menu->name ) : 'SUPPORT';
                        } else {
                            echo 'SUPPORT';
                        }
                        ?>
                    </h5>
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'footer-3',
                        'container'      => false,
                        'menu_class'     => 'footer-links list-unstyled mb-0',
                        'depth'          => 1,
                        'fallback_cb'    => function() { ?>
                            <ul class="footer-links list-unstyled mb-0">
                                <li><a href="<?php echo esc_url( home_url( '/faq' ) ); ?>">FAQ</a></li>
                                <li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>">Contact Us</a></li>
                                <li><a href="<?php echo esc_url( home_url( '/shipping-guidelines' ) ); ?>">Shipping Guidelines</a></li>
                            </ul>
                        <?php },
                    ) );
                    ?>
                </div>

                <!-- FOOTER 4: CONTACT US -->
                <div class="col-12 col-sm-6 col-lg-2 footer-contact d-flex flex-column align-items-center align-items-md-start">
                    <h5 class="footer-title">CONTACT US</h5>
                    <p class="d-flex align-items-center gap-2 mb-2">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9a050" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        info@elitevaultgrading.com
                    </p>
                    <p class="d-flex align-items-center gap-2 mb-0">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9a050" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        Response time: 24-48 hours
                    </p>
                </div>

            </div>

            <!-- COPYRIGHT BAR -->
            <div class="row mt-4 pt-4 border-top border-secondary">
                <div class="col-12 text-center">
                    <p class="footer-copyright mb-0" style="font-size: 11px;">
                        &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved. | Created By <a href="https://www.social-splash.com" target="_blank" rel="noopener noreferrer" class="text-gold">Social Splash</a>
                    </p>
                </div>
            </div>

        </div>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>