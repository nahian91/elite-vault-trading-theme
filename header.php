<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package evg
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

    <!-- HEADER -->
    <header class="header">
        <div class="container d-flex align-items-center justify-content-between">
            
            <!-- LOGO -->
            <div class="logo">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.png' ); ?>" alt="Elite Vault Grading" style="max-height: 45px;" />
                </a>
            </div>

            <!-- DESKTOP NAVIGATION -->
            <?php
            wp_nav_menu( array(
                'theme_location' => 'primary-menu',
                'container'      => 'nav',
                'container_class'=> 'nav-links d-none d-lg-flex',
                'menu_class'     => 'd-flex align-items-center gap-3 list-unstyled mb-0',
                'depth'          => 2,
            ) );
            ?>

            <!-- DESKTOP ACTIONS -->
            <div class="nav-actions d-none d-lg-flex align-items-center gap-2">
                <?php if ( is_user_logged_in() ) : ?>
                    <a href="<?php echo esc_url( home_url( '/create-account' ) ); ?>" class="btn-custom btn-outline-gold">
                        MY ACCOUNT
                    </a>
                    <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="btn-custom btn-gold">
                        LOG OUT
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url( home_url( '/create-account' ) ); ?>" class="btn-custom btn-outline-gold">
                        CREATE ACCOUNT
                    </a>
                    <a href="<?php echo esc_url( home_url( '/sign-in' ) ); ?>" class="btn-custom btn-gold">
                        SIGN IN
                    </a>
                <?php endif; ?>
            </div>

            <!-- MOBILE MENU TOGGLE BUTTON (HAMBURGER) -->
            <button class="navbar-toggler d-lg-none bg-transparent border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#evgMobileMenu" aria-controls="evgMobileMenu" aria-expanded="false" aria-label="Toggle navigation">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--gold, #c9a050)" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>

        <!-- MOBILE COLLAPSIBLE MENU CONTAINER -->
        <div class="collapse d-lg-none mt-3 px-3 pb-3 bg-dark border-bottom border-secondary" id="evgMobileMenu">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'primary-menu',
                'container'      => 'nav',
                'container_class'=> 'mobile-nav-links py-2',
                'menu_class'     => 'list-unstyled d-flex flex-column gap-3 mb-3',
                'depth'          => 2,
            ) );
            ?>
            <div class="mobile-nav-actions d-flex flex-column gap-2 pt-2 border-top border-secondary">
                <?php if ( is_user_logged_in() ) : ?>
                    <a href="<?php echo esc_url( home_url( '/create-account' ) ); ?>" class="btn-custom btn-outline-gold text-center w-100">
                        MY ACCOUNT
                    </a>
                    <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="btn-custom btn-gold text-center w-100">
                        LOG OUT
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url( home_url( '/create-account' ) ); ?>" class="btn-custom btn-outline-gold text-center w-100">
                        CREATE ACCOUNT
                    </a>
                    <a href="<?php echo esc_url( home_url( '/sign-in' ) ); ?>" class="btn-custom btn-gold text-center w-100">
                        SIGN IN
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>