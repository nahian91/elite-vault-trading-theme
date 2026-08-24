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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elite Vault Grading</title>
    
</head>
<body>

    <!-- HEADER -->
    <header class="header">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="logo">
                <img src="<?php echo get_template_directory_uri();?>/assets/img/logo.png"/>
            </div>
            <?php
wp_nav_menu(array(
    'theme_location' => 'primary-menu',
    'container'      => 'nav',
    'container_class'=> 'nav-links d-none d-lg-flex',
    'menu_class'     => 'd-flex align-items-center gap-3 list-unstyled mb-0',
    'depth'          => 2,
));
?>
            <div class="nav-actions d-flex align-items-center gap-2">
    <?php if ( is_user_logged_in() ) : ?>
        <!-- LOGGED-IN STATE -->
        <a href="<?php echo esc_url( home_url( '/create-account' ) ); ?>" class="btn-custom btn-outline-gold">
            MY ACCOUNT
        </a>
        <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="btn-custom btn-gold">
            LOG OUT
        </a>
    <?php else : ?>
        <!-- GUEST / LOGGED-OUT STATE -->
        <a href="<?php echo esc_url( home_url( '/create-account' ) ); ?>" class="btn-custom btn-outline-gold">
            CREATE ACCOUNT
        </a>
        <a href="<?php echo esc_url( home_url( '/sign-in' ) ); ?>" class="btn-custom btn-gold">
            SIGN IN
        </a>
    <?php endif; ?>
</div>
        </div>
    </header>