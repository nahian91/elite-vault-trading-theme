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
            <div class="row g-4">
                <div class="col-lg-4 footer-logo-col">
                    <div class="logo">
                        <div class="logo-footer">
                            <img src="<?php echo get_template_directory_uri();?>/assets/img/logo.png"/>
                        </div>
                    </div>
                    <p>The premier grading service for Pokémon cards.</p>
                </div>
                <!-- FOOTER 1: SERVICES -->
<div class="col-6 col-sm-4 col-lg-2">
    <h5 class="footer-title">
        <?php
        $menu_obj_1 = get_nav_menu_locations();
        if (isset($menu_obj_1['footer-1'])) {
            $menu = wp_get_nav_menu_object($menu_obj_1['footer-1']);
            echo $menu ? esc_html($menu->name) : 'SERVICES';
        } else {
            echo 'SERVICES';
        }
        ?>
    </h5>
    <?php
    wp_nav_menu(array(
        'theme_location' => 'footer-1',
        'container'      => false,
        'menu_class'     => 'footer-links list-unstyled mb-0',
        'depth'          => 1,
        'fallback_cb'    => function() { ?>
            <ul class="footer-links list-unstyled mb-0">
                <li><a href="<?php echo esc_url(home_url('/grading')); ?>">Grading</a></li>
                <li><a href="<?php echo esc_url(home_url('/marketplace')); ?>">Buy Graded Cards</a></li>
                <li><a href="<?php echo esc_url(home_url('/pre-order')); ?>">Pre-Order</a></li>
            </ul>
        <?php },
    ));
    ?>
</div>

<!-- FOOTER 2: ACCOUNT -->
<div class="col-6 col-sm-4 col-lg-2">
    <h5 class="footer-title">
        <?php
        $menu_obj_2 = get_nav_menu_locations();
        if (isset($menu_obj_2['footer-2'])) {
            $menu = wp_get_nav_menu_object($menu_obj_2['footer-2']);
            echo $menu ? esc_html($menu->name) : 'ACCOUNT';
        } else {
            echo 'ACCOUNT';
        }
        ?>
    </h5>
    <?php
    wp_nav_menu(array(
        'theme_location' => 'footer-2',
        'container'      => false,
        'menu_class'     => 'footer-links list-unstyled mb-0',
        'depth'          => 1,
        'fallback_cb'    => function() { ?>
            <ul class="footer-links list-unstyled mb-0">
                <li><a href="<?php echo esc_url(home_url('/create-account')); ?>">Create Account</a></li>
                <li><a href="<?php echo esc_url(home_url('/sign-in')); ?>">Sign In</a></li>
                <li><a href="<?php echo esc_url(home_url('/create-account')); ?>">My Submissions</a></li>
            </ul>
        <?php },
    ));
    ?>
</div>

<!-- FOOTER 3: SUPPORT -->
<div class="col-6 col-sm-4 col-lg-2">
    <h5 class="footer-title">
        <?php
        $menu_obj_3 = get_nav_menu_locations();
        if (isset($menu_obj_3['footer-3'])) {
            $menu = wp_get_nav_menu_object($menu_obj_3['footer-3']);
            echo $menu ? esc_html($menu->name) : 'SUPPORT';
        } else {
            echo 'SUPPORT';
        }
        ?>
    </h5>
    <?php
    wp_nav_menu(array(
        'theme_location' => 'footer-3',
        'container'      => false,
        'menu_class'     => 'footer-links list-unstyled mb-0',
        'depth'          => 1,
        'fallback_cb'    => function() { ?>
            <ul class="footer-links list-unstyled mb-0">
                <li><a href="<?php echo esc_url(home_url('/faq')); ?>">FAQ</a></li>
                <li><a href="<?php echo esc_url(home_url('/contact')); ?>">Contact Us</a></li>
                <li><a href="<?php echo esc_url(home_url('/shipping-guidelines')); ?>">Shipping Guidelines</a></li>
            </ul>
        <?php },
    ));
    ?>
</div>
                <div class="col-12 col-lg-2 footer-contact">
                    <h5 class="footer-title">CONTACT US</h5>
                    <p>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9a050" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        support@elitevaultgrading.com
                    </p>
                    <p>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9a050" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        Response time: 24-48 hours
                    </p>
                </div>
            </div>
        </div>
    </footer>
	<?php wp_footer();?>
</body>
</html>