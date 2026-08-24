<?php
/**
 * Template Name: Marketplace - Executive Tier
 * Description: Fully dynamic public marketplace catalogue for Elite Vault Grading.
 *              Fetches live inventory from wp_evg_marketplace and wp_evg_cards, supports dynamic
 *              category and grade filtering, sorting, pagination, and provides direct checkout routing.
 */

global $wpdb;

$table_marketplace = $wpdb->prefix . 'evg_marketplace';
$table_cards       = $wpdb->prefix . 'evg_cards';

// -------------------------------------------------------------------------
// 1. DYNAMIC FILTER & PAGINATION PARSING
// -------------------------------------------------------------------------
$current_page = max( 1, get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( isset( $_GET['pg'] ) ? intval( $_GET['pg'] ) : 1 ) );
$per_page     = 9;
$offset       = ( $current_page - 1 ) * $per_page;

$selected_category = isset( $_GET['cat'] ) ? sanitize_text_field( wp_unslash( $_GET['cat'] ) ) : '';
$selected_grade    = isset( $_GET['grade'] ) ? sanitize_text_field( wp_unslash( $_GET['grade'] ) ) : '';
$selected_lang     = isset( $_GET['lang'] ) ? sanitize_text_field( wp_unslash( $_GET['lang'] ) ) : '';
$selected_sort     = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : 'newest';

// Build SQL Query Clauses
$where_clauses = array( "m.status = 'Available'" );
$query_params  = array();

if ( ! empty( $selected_category ) && 'all' !== $selected_category ) {
    $where_clauses[] = "m.category = %s";
    $query_params[]  = $selected_category;
}

if ( ! empty( $selected_grade ) ) {
    if ( 'raw' === strtolower( $selected_grade ) ) {
        $where_clauses[] = "(COALESCE(m.assigned_grade, c.final_grade) IS NULL OR COALESCE(m.assigned_grade, c.final_grade) = 0)";
    } else {
        $where_clauses[] = "COALESCE(m.assigned_grade, c.final_grade) = %d";
        $query_params[]  = intval( $selected_grade );
    }
}

if ( ! empty( $selected_lang ) ) {
    $where_clauses[] = "COALESCE(NULLIF(m.language, ''), c.language, 'English') = %s";
    $query_params[]  = $selected_lang;
}

$where_sql = implode( ' AND ', $where_clauses );

// Sorting logic
$order_sql = "m.listed_date DESC";
if ( 'price-low' === $selected_sort ) {
    $order_sql = "m.price ASC";
} elseif ( 'price-high' === $selected_sort ) {
    $order_sql = "m.price DESC";
} elseif ( 'grade-high' === $selected_sort ) {
    $order_sql = "COALESCE(m.assigned_grade, c.final_grade, 0) DESC, m.listed_date DESC";
}

// Total Count Query using LEFT JOIN
$count_query = "SELECT COUNT(m.id) 
                FROM {$table_marketplace} m 
                LEFT JOIN {$table_cards} c ON m.card_id = c.id 
                WHERE {$where_sql}";

if ( ! empty( $query_params ) ) {
    $total_items = (int) $wpdb->get_var( $wpdb->prepare( $count_query, $query_params ) );
} else {
    $total_items = (int) $wpdb->get_var( $count_query );
}

$total_pages = ceil( $total_items / $per_page );

// Fetch Items Query
$items_query = "SELECT m.*, 
                       COALESCE(NULLIF(m.card_title, ''), c.card_name, 'Graded Card') as display_name,
                       COALESCE(NULLIF(m.set_name, ''), c.set_name, 'N/A') as display_set,
                       COALESCE(NULLIF(m.card_number, ''), c.card_number, 'N/A') as display_number,
                       COALESCE(NULLIF(m.language, ''), c.language, 'English') as display_lang,
                       COALESCE(m.assigned_grade, c.final_grade) as display_grade,
                       COALESCE(NULLIF(m.image_url, ''), c.front_image_url) as display_img,
                       COALESCE(NULLIF(m.slab_information, ''), 'Standard Vault Slab') as display_slab
                FROM {$table_marketplace} m 
                LEFT JOIN {$table_cards} c ON m.card_id = c.id 
                WHERE {$where_sql} 
                ORDER BY {$order_sql} 
                LIMIT %d OFFSET %d";

$query_params_with_limits   = $query_params;
$query_params_with_limits[] = $per_page;
$query_params_with_limits[] = $offset;

$listings = $wpdb->get_results( $wpdb->prepare( $items_query, $query_params_with_limits ) );

// Fetch Distinct Categories for Sidebar Filter
$categories_available = $wpdb->get_col( "SELECT DISTINCT category FROM {$table_marketplace} WHERE status = 'Available'" );

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
    max-width: 1280px;
    margin: 0 auto;
    padding: 4rem 20px 6rem 20px;
  }

  .evg-title-xl { 
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(2.2rem, 4vw, 3.2rem); 
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

  .evg-marketplace-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 28px;
    align-items: start;
  }

  .evg-form-control {
    background: var(--evg-obsidian-elevated); 
    border: 1px solid #242428;
    color: var(--evg-text-pure); 
    border-radius: 4px; 
    padding: 0.75rem 1rem; 
    font-size: 0.85rem;
    outline: none;
    transition: all 0.2s ease;
  }
  .evg-form-control:focus {
    border-color: var(--evg-gold-primary);
    box-shadow: 0 0 0 1px var(--evg-gold-primary);
  }

  .evg-filter-link {
    color: var(--evg-text-ash); 
    text-decoration: none; 
    display: block;
    padding: 0.75rem 0; 
    border-bottom: 1px solid var(--evg-border-hairline);
    font-size: 0.85rem; 
    transition: all 0.2s ease;
  }
  .evg-filter-link:last-child { border-bottom: none; }
  .evg-filter-link:hover, .evg-filter-link.active {
    color: var(--evg-gold-primary); 
    padding-left: 0.4rem; 
    border-color: var(--evg-border-gold-faint);
  }

  .evg-control-matrix {
    display: grid; 
    grid-template-columns: 1fr 1fr;
    gap: 1px;
    background: var(--evg-border-hairline); 
    border: 1px solid var(--evg-border-hairline);
    border-radius: 4px; 
    overflow: hidden;
  }
  .evg-control-cell {
    background: var(--evg-obsidian-panel);
    padding: 0.75rem 0.5rem; 
    text-align: center; 
    cursor: pointer; 
    transition: all 0.2s ease;
    display: flex; 
    align-items: center; 
    justify-content: center;
    text-decoration: none;
  }
  .evg-control-cell span { 
    font-size: 0.75rem; 
    color: var(--evg-text-ash); 
    font-family: monospace; 
    font-weight: 700; 
  }
  .evg-control-cell:hover { background: var(--evg-obsidian-elevated); }
  .evg-control-cell.active { background: rgba(212, 175, 55, 0.1); }
  .evg-control-cell.active span { color: var(--evg-gold-primary); }

  .evg-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
    gap: 20px;
  }

  .evg-product-card {
    height: 100%; 
    display: flex; 
    flex-direction: column; 
    justify-content: space-between;
    padding: 20px;
    transition: transform 0.2s ease, border-color 0.2s ease;
  }
  .evg-product-card:hover {
    transform: translateY(-4px); 
    border-color: var(--evg-gold-primary);
  }

  .evg-slab-frame {
    background: #08080a;
    border: 2px solid #2a2a2e;
    border-radius: 8px; 
    padding: 10px; 
    margin: 0 auto 1.25rem auto;
    width: 100%;
    max-width: 190px; 
    position: relative;
    box-shadow: 0 10px 25px rgba(0,0,0,0.8);
  }
  .evg-slab-label {
    background: #141416; 
    border: 1px solid #2c2c30;
    border-radius: 4px; 
    padding: 6px 8px; 
    display: flex; 
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px; 
    font-size: 0.7rem; 
    font-family: monospace; 
    color: var(--evg-text-ash);
  }
  .evg-slab-art {
    height: 220px; 
    border-radius: 4px; 
    display: flex; 
    align-items: center; 
    justify-content: center;
    background: #000000;
    overflow: hidden; 
    position: relative; 
    border: 1px solid #1f1f23;
  }
  .evg-slab-art img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  /* Executive Buy Button with Checkout Redirect */
  .btn-evg-executive {
    background: var(--evg-gold-primary); 
    color: var(--evg-text-charcoal) !important;
    font-size: 0.78rem; 
    font-weight: 800; 
    letter-spacing: 0.12em; 
    text-transform: uppercase;
    border: none; 
    border-radius: 4px; 
    padding: 0.9rem 1.25rem; 
    display: flex; 
    align-items: center; 
    justify-content: center;
    width: 100%; 
    transition: all 0.2s ease; 
    text-decoration: none; 
    cursor: pointer;
    box-sizing: border-box;
  }
  .btn-evg-executive:hover { 
    background: var(--evg-gold-light); 
    box-shadow: 0 0 20px var(--evg-gold-glow); 
  }

  .btn-evg-soldout {
    background: #1f1f23 !important;
    color: #8e8e93 !important;
    cursor: not-allowed !important;
    box-shadow: none !important;
  }

  .evg-trust-matrix {
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
    gap: 1px;
    background: var(--evg-border-hairline); 
    border: 1px solid var(--evg-border-hairline); 
    border-radius: 8px; 
    overflow: hidden;
  }
  .evg-trust-cell {
    background: var(--evg-obsidian-panel); 
    padding: 2rem 1.5rem; 
    text-align: center;
  }
  
  .evg-pagination {
    display: flex;
    gap: 6px;
    list-style: none;
    padding: 0;
    margin: 0;
  }
  .evg-pagination a, .evg-pagination span {
    background: var(--evg-obsidian-elevated); 
    border: 1px solid var(--evg-border-hairline); 
    color: var(--evg-text-ash);
    font-size: 0.85rem; 
    padding: 0.6rem 1.1rem; 
    text-decoration: none;
    border-radius: 4px;
    font-family: monospace;
    font-weight: 700;
    transition: all 0.2s ease;
  }
  .evg-pagination .current {
    background: var(--evg-gold-primary); 
    border-color: var(--evg-gold-primary); 
    color: var(--evg-text-charcoal);
  }
  .evg-pagination a:hover {
    border-color: var(--evg-gold-primary);
    color: var(--evg-gold-primary);
  }

  @media (max-width: 992px) {
    .evg-marketplace-layout { grid-template-columns: 1fr; }
  }
</style>

<main class="evg-master-wrapper">
    <div class="evg-container">

        <!-- 1. EDITORIAL HEADER -->
        <header style="text-align: center; margin-bottom: 45px; padding-bottom: 25px; border-bottom: 1px solid var(--evg-border-hairline);">
            <span class="evg-label-micro" style="margin-bottom: 10px;"><?php esc_html_e( 'Verified Inventory Access', 'evg-platform' ); ?></span>
            <h1 class="evg-title-xl"><?php esc_html_e( 'Public Slabs', 'evg-platform' ); ?> <span class="evg-text-metallic"><?php esc_html_e( 'Marketplace', 'evg-platform' ); ?></span></h1>
            <p style="color: var(--evg-text-ash); max-width: 680px; margin: 0 auto; font-size: 0.95rem; line-height: 1.6;">
                <?php esc_html_e( 'Explore our live inventory of certified Pokémon cards, permanently secured in tamper-evident Elite Vault encapsulation.', 'evg-platform' ); ?>[cite: 1]
            </p>
        </header>

        <!-- 2. MARKETPLACE CATALOGUE & FILTERS -->
        <div class="evg-marketplace-layout" style="margin-bottom: 50px;">

            <!-- SIDEBAR FILTERS -->
            <aside>
                <div class="evg-module" style="padding: 25px; position: sticky; top: 2rem;">
                    
                    <!-- CATEGORIES -->
                    <div style="margin-bottom: 30px;">
                        <span class="evg-label-micro" style="color: #ffffff; margin-bottom: 12px;"><?php esc_html_e( 'Category Index', 'evg-platform' ); ?></span>
                        <div>
                            <a href="<?php echo esc_url( remove_query_arg( array( 'cat', 'pg' ) ) ); ?>" class="evg-filter-link <?php echo empty( $selected_category ) || 'all' === $selected_category ? 'active' : ''; ?>">
                                <?php esc_html_e( 'All Inventory', 'evg-platform' ); ?>
                            </a>
                            <?php if ( ! empty( $categories_available ) ) : ?>
                                <?php foreach ( $categories_available as $cat_name ) : ?>
                                    <a href="<?php echo esc_url( add_query_arg( array( 'cat' => $cat_name, 'pg' => 1 ) ) ); ?>" class="evg-filter-link <?php echo $selected_category === $cat_name ? 'active' : ''; ?>">
                                        <?php echo esc_html( $cat_name ); ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <a href="<?php echo esc_url( add_query_arg( 'cat', 'Elite Vault Graded Cards' ) ); ?>" class="evg-filter-link">
                                    <?php esc_html_e( 'Elite Vault Graded Cards', 'evg-platform' ); ?>[cite: 1]
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- GRADE FILTER (Control Matrix) -->
                    <div style="margin-bottom: 30px;">
                        <span class="evg-label-micro" style="color: #ffffff; margin-bottom: 12px;"><?php esc_html_e( 'Certified Grade', 'evg-platform' ); ?></span>
                        <div class="evg-control-matrix">
                            <a href="<?php echo esc_url( add_query_arg( array( 'grade' => '10', 'pg' => 1 ) ) ); ?>" class="evg-control-cell <?php echo '10' === $selected_grade ? 'active' : ''; ?>">
                                <span>EVG 10</span>
                            </a>
                            <a href="<?php echo esc_url( add_query_arg( array( 'grade' => '9', 'pg' => 1 ) ) ); ?>" class="evg-control-cell <?php echo '9' === $selected_grade ? 'active' : ''; ?>">
                                <span>EVG 9</span>
                            </a>
                            <a href="<?php echo esc_url( add_query_arg( array( 'grade' => '8', 'pg' => 1 ) ) ); ?>" class="evg-control-cell <?php echo '8' === $selected_grade ? 'active' : ''; ?>">
                                <span>EVG 8</span>
                            </a>
                            <a href="<?php echo esc_url( add_query_arg( array( 'grade' => '7', 'pg' => 1 ) ) ); ?>" class="evg-control-cell <?php echo '7' === $selected_grade ? 'active' : ''; ?>">
                                <span>EVG 7</span>
                            </a>
                            <a href="<?php echo esc_url( add_query_arg( array( 'grade' => 'raw', 'pg' => 1 ) ) ); ?>" class="evg-control-cell <?php echo 'raw' === $selected_grade ? 'active' : ''; ?>" style="grid-column: span 2;">
                                <span>RAW / UNGRADED</span>
                            </a>
                        </div>
                    </div>

                    <!-- LANGUAGE FILTER -->
                    <div style="margin-bottom: 20px;">
                        <span class="evg-label-micro" style="color: #ffffff; margin-bottom: 12px;"><?php esc_html_e( 'Card Language', 'evg-platform' ); ?></span>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <a href="<?php echo esc_url( add_query_arg( array( 'lang' => 'English', 'pg' => 1 ) ) ); ?>" class="evg-filter-link <?php echo 'English' === $selected_lang ? 'active' : ''; ?>" style="padding: 4px 0;">
                                <?php esc_html_e( 'English Releases', 'evg-platform' ); ?>
                            </a>
                            <a href="<?php echo esc_url( add_query_arg( array( 'lang' => 'Japanese', 'pg' => 1 ) ) ); ?>" class="evg-filter-link <?php echo 'Japanese' === $selected_lang ? 'active' : ''; ?>" style="padding: 4px 0;">
                                <?php esc_html_e( 'Japanese Releases', 'evg-platform' ); ?>
                            </a>
                        </div>
                    </div>

                    <!-- RESET FILTERS -->
                    <?php if ( ! empty( $selected_category ) || ! empty( $selected_grade ) || ! empty( $selected_lang ) || ! empty( $selected_sort ) ) : ?>
                        <div style="padding-top: 15px; border-top: 1px solid var(--evg-border-hairline);">
                            <a href="<?php echo esc_url( get_permalink() ); ?>" style="color: #ff453a; font-size: 0.78rem; text-decoration: none; font-weight: 700;">
                                ✕ <?php esc_html_e( 'Reset All Filters', 'evg-platform' ); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                </div>
            </aside>

            <!-- PRODUCTS WORKSPACE -->
            <div>

                <!-- TOOLBAR -->
                <div class="evg-module" style="padding: 14px 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div style="font-size: 0.85rem; font-family: monospace; color: var(--evg-text-ash);">
                        <span style="color: #ffffff; font-weight: 700;"><?php esc_html_e( 'ACTIVE INVENTORY:', 'evg-platform' ); ?></span> 
                        <?php printf( esc_html__( '%d Certified Items Logged', 'evg-platform' ), $total_items ); ?>
                    </div>
                    
                    <form method="get" action="<?php echo esc_url( get_permalink() ); ?>" style="display: flex; align-items: center; gap: 10px; margin: 0;">
                        <?php if ( ! empty( $selected_category ) ) : ?>
                            <input type="hidden" name="cat" value="<?php echo esc_attr( $selected_category ); ?>">
                        <?php endif; ?>
                        <?php if ( ! empty( $selected_grade ) ) : ?>
                            <input type="hidden" name="grade" value="<?php echo esc_attr( $selected_grade ); ?>">
                        <?php endif; ?>
                        <?php if ( ! empty( $selected_lang ) ) : ?>
                            <input type="hidden" name="lang" value="<?php echo esc_attr( $selected_lang ); ?>">
                        <?php endif; ?>

                        <label for="sortBy" class="evg-label-micro" style="margin: 0; color: var(--evg-text-ash);"><?php esc_html_e( 'Sort Order', 'evg-platform' ); ?></label>
                        <select id="sortBy" name="sort" class="evg-form-control" style="padding: 6px 12px; font-size: 0.8rem; cursor: pointer;" onchange="this.form.submit()">
                            <option value="newest" <?php selected( $selected_sort, 'newest' ); ?>><?php esc_html_e( 'Newest Additions', 'evg-platform' ); ?></option>
                            <option value="price-low" <?php selected( $selected_sort, 'price-low' ); ?>><?php esc_html_e( 'Price: Low to High', 'evg-platform' ); ?></option>
                            <option value="price-high" <?php selected( $selected_sort, 'price-high' ); ?>><?php esc_html_e( 'Price: High to Low', 'evg-platform' ); ?></option>
                            <option value="grade-high" <?php selected( $selected_sort, 'grade-high' ); ?>><?php esc_html_e( 'Highest Grade First', 'evg-platform' ); ?></option>
                        </select>
                    </form>
                </div>

                <!-- CARDS GRID -->
                <?php if ( ! empty( $listings ) ) : ?>
                    <div class="evg-cards-grid">
                        <?php foreach ( $listings as $card ) : 
                            $is_in_stock = ( intval( $card->stock_quantity ) > 0 );
                            $checkout_url = add_query_arg(
                                array(
                                    'item_id'   => $card->id,
                                    'item_type' => 'marketplace',
                                ),
                                home_url( '/checkout' )
                            );
                        ?>
                            <div class="evg-module evg-product-card">
                                <div>
                                    <!-- SLAB CONTAINER -->
                                    <div class="evg-slab-frame">
                                        <div class="evg-slab-label">
                                            <span style="color: var(--evg-gold-primary); font-weight: 800;">
                                                <?php echo ! empty( $card->display_grade ) ? 'EVG ' . esc_html( $card->display_grade ) : 'RAW'; ?>
                                            </span>
                                            <span>#<?php echo esc_html( $card->display_number ); ?></span>
                                        </div>
                                        <div class="evg-slab-art">
                                            <?php if ( ! empty( $card->display_img ) ) : ?>
                                                <img src="<?php echo esc_url( $card->display_img ); ?>" alt="<?php echo esc_attr( $card->display_name ); ?>">
                                            <?php else : ?>
                                                <span style="font-size: 11px; color: var(--evg-text-ash); text-transform: uppercase; font-family: monospace; letter-spacing: 1px; text-align: center; padding: 10px;">
                                                    [ <?php echo esc_html( $card->display_name ); ?> ]
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- CARD SPECIFICATION -->
                                    <div style="margin-bottom: 20px;">
                                        <h2 style="color: #ffffff; font-size: 1rem; font-weight: 700; margin: 0 0 4px 0; line-height: 1.3;">
                                            <?php echo esc_html( $card->display_name ); ?>
                                        </h2>
                                        <p style="color: var(--evg-text-ash); font-size: 0.78rem; margin: 0 0 10px 0;">
                                            <?php echo esc_html( $card->display_set ); ?> &bull; #<?php echo esc_html( $card->display_number ); ?>
                                        </p>
                                        
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                                            <span style="background: #141416; border: 1px solid #2c2c30; color: var(--evg-gold-primary); font-size: 0.65rem; font-family: monospace; padding: 2px 6px; border-radius: 4px;">
                                                <?php echo esc_html( strtoupper( substr( $card->display_lang, 0, 3 ) ) ); ?>
                                            </span>
                                            <span style="font-size: 0.72rem; color: var(--evg-text-ash);">
                                                <?php echo esc_html( $card->display_slab ); ?>
                                            </span>
                                        </div>
                                        
                                        <div style="display: flex; justify-content: space-between; align-items: flex-end; padding-top: 10px; border-top: 1px solid var(--evg-border-hairline);">
                                            <span class="evg-label-micro" style="margin: 0;"><?php esc_html_e( 'Valuation', 'evg-platform' ); ?></span>
                                            <div style="font-family: monospace; font-size: 1.25rem; font-weight: 800; color: #ffffff;">
                                                &pound;<?php echo esc_html( number_format( $card->price, 2 ) ); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ( $is_in_stock ) : ?>
                                    <a href="<?php echo esc_url( $checkout_url ); ?>" class="btn-evg-executive">
                                        <?php esc_html_e( 'Buy It Now &bull; Checkout', 'evg-platform' ); ?>
                                    </a>
                                <?php else : ?>
                                    <button type="button" class="btn-evg-executive btn-evg-soldout" disabled>
                                        <?php esc_html_e( 'Sold Out', 'evg-platform' ); ?>[cite: 1]
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- PAGINATION CONTROLS -->
                    <?php if ( $total_pages > 1 ) : ?>
                        <div style="display: flex; justify-content: center; margin-top: 40px;">
                            <div class="evg-pagination">
                                <?php
                                echo paginate_links( array(
                                    'base'      => add_query_arg( 'pg', '%#%' ),
                                    'format'    => '',
                                    'current'   => $current_page,
                                    'total'     => $total_pages,
                                    'prev_text' => '&larr; Prev',
                                    'next_text' => 'Next &rarr;',
                                ) );
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else : ?>
                    <div class="evg-module" style="padding: 60px 20px; text-align: center;">
                        <h3 style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0 0 10px 0;"><?php esc_html_e( 'No Certified Inventory Matched', 'evg-platform' ); ?></h3>
                        <p style="color: var(--evg-text-ash); font-size: 0.9rem; max-width: 500px; margin: 0 auto 20px auto;">
                            <?php esc_html_e( 'No certified slabs match your active filter matrix. Try resetting your filters to explore available vault inventory.', 'evg-platform' ); ?>
                        </p>
                        <a href="<?php echo esc_url( get_permalink() ); ?>" class="btn-evg-executive" style="display: inline-flex; width: auto; padding: 0.75rem 2rem;">
                            <?php esc_html_e( 'Clear All Filters', 'evg-platform' ); ?>
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- 3. TRUST MATRIX STRIP -->
        <div class="evg-trust-matrix">
            <div class="evg-trust-cell">
                <span style="display: block; font-weight: 700; color: #ffffff; font-size: 0.85rem; margin-bottom: 4px;"><?php esc_html_e( '100% AUTHENTIC', 'evg-platform' ); ?></span>
                <span class="evg-label-micro" style="color: var(--evg-text-ash);"><?php esc_html_e( 'Precision Verified', 'evg-platform' ); ?></span>
            </div>
            <div class="evg-trust-cell">
                <span style="display: block; font-weight: 700; color: #ffffff; font-size: 0.85rem; margin-bottom: 4px;"><?php esc_html_e( 'SECURE VAULT', 'evg-platform' ); ?></span>
                <span class="evg-label-micro" style="color: var(--evg-text-ash);"><?php esc_html_e( 'Tamper-Evident Slabs', 'evg-platform' ); ?></span>[cite: 1]
            </div>
            <div class="evg-trust-cell">
                <span style="display: block; font-weight: 700; color: #ffffff; font-size: 0.85rem; margin-bottom: 4px;"><?php esc_html_e( 'TRACKED POST', 'evg-platform' ); ?></span>
                <span class="evg-label-micro" style="color: var(--evg-text-ash);"><?php esc_html_e( 'UK Insured Delivery', 'evg-platform' ); ?></span>[cite: 1]
            </div>
            <div class="evg-trust-cell">
                <span style="display: block; font-weight: 700; color: #ffffff; font-size: 0.85rem; margin-bottom: 4px;"><?php esc_html_e( 'REGISTRY MATCHED', 'evg-platform' ); ?></span>
                <span class="evg-label-micro" style="color: var(--evg-text-ash);"><?php esc_html_e( 'Database Logged', 'evg-platform' ); ?></span>
            </div>
        </div>

    </div>
</main>

<?php get_footer(); ?>