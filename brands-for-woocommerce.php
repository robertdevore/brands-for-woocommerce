<?php

/**
  * The plugin bootstrap file
  *
  * @link              https://robertdevore.com
  * @since             1.0.0
  * @package           Brands_For_WooCommerce
  *
  * @wordpress-plugin
  *
  * Plugin Name: Brands for WooCommerce®
  * Description: Allows you to create and manage brands in WooCommerce®, with options to display brands as lists, thumbnails, or sidebar widgets.
  * Plugin URI:  https://github.com/robertdevore/brands-for-woocommerce/
  * Version:     1.0.0
  * Author:      Robert DeVore
  * Author URI:  https://robertdevore.com/
  * License:     GPL-2.0+
  * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
  * Text Domain: brands-for-woocommerce
  * Domain Path: /languages
  * Update URI:  https://github.com/robertdevore/brands-for-woocommerce/
  */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Add the widget class.
require 'classes/Brand_Thumbnail_Widget.php';

// Add the Plugin Update Checker.
require 'vendor/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/robertdevore/brands-for-woocommerce/',
    __FILE__,
    'brands-for-woocommerce'
);

// Set the branch that contains the stable release.
$myUpdateChecker->setBranch( 'main' );

// Check if Composer's autoloader is already registered globally.
if ( ! class_exists( 'RobertDevore\WPComCheck\WPComPluginHandler' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use RobertDevore\WPComCheck\WPComPluginHandler;

new WPComPluginHandler( plugin_basename( __FILE__ ), 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' );

// Define constants.
define('BRANDS_FOR_WOOCOMMERCE_VERSION', '1.0.0' );

/**
 * Load plugin text domain for translations
 * 
 * @since  1.0.1
 * @return void
 */
function brands_woocommerce_load_textdomain() {
    load_plugin_textdomain( 
        'brands-for-woocommerce',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}
add_action( 'plugins_loaded', 'brands_woocommerce_load_textdomain' );

/**
 * Main Brands_For_WooCommerce class.
 * 
 * @since 1.0.0
 */
class Brands_For_WooCommerce {

    /**
     * Constructor: Hooks and shortcodes registration.
     */
    public function __construct() {
        // Register hooks.
        add_action( 'init', [ $this, 'register_brand_taxonomy' ] );
        add_action( 'product_brand_add_form_fields', [ $this, 'add_brand_image_and_url_fields' ], 10, 2 );
        add_action( 'product_brand_edit_form_fields', [ $this, 'edit_brand_image_and_url_fields' ], 10, 2 );
        add_action( 'created_product_brand', [ $this, 'save_brand_meta' ], 10, 2 );
        add_action( 'edited_product_brand', [ $this, 'save_brand_meta' ], 10, 2 );
        add_action( 'woocommerce_single_product_summary', [ $this, 'display_product_brand_image' ], 1 );
        add_action( 'widgets_init', [ $this, 'register_brand_widget' ] );
        add_action( 'rest_api_init', [ $this, 'register_brands_api_route' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_media_uploader' ] );
        add_action( 'admin_footer', [ $this, 'brand_image_upload_script' ] );
        add_action( 'woocommerce_archive_description', [ $this, 'display_brand_archive_header' ], 10 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_styles' ] );

        // Shortcodes.
        add_shortcode( 'product_brand', [ $this, 'product_brand_shortcode' ] );
        add_shortcode( 'brand_products', [ $this, 'brand_products_shortcode' ] );
        add_shortcode( 'brands_list', [ $this, 'brands_list_shortcode' ] );
    }

    /**
     * Enqueue public styles for the plugin.
     * 
     * @since  1.0.0
     * @return void
     */
    public function enqueue_public_styles() {
        wp_enqueue_style(
            'brands-for-woocommerce-styles',
            plugin_dir_url( __FILE__ ) . 'assets/css/brands-for-woocommerce.css',
            [],
            BRANDS_FOR_WOOCOMMERCE_VERSION
        );
    }    

    /**
     * Enqueue media uploader scripts on taxonomy pages.
     *
     * @param string $hook The current admin page hook.
     * 
     * @since  1.0.0
     * @return void
     */
    public function enqueue_media_uploader( $hook ) {
        if (
            ( 'edit-tags.php' === $hook && isset( $_GET['taxonomy'] ) && 'product_brand' === $_GET['taxonomy'] ) ||
            ( 'term.php' === $hook && isset( $_GET['taxonomy'] ) && 'product_brand' === $_GET['taxonomy'] )
        ) {
            wp_enqueue_media();
        }
    }

    /**
     * Output JavaScript for the media uploader on taxonomy pages.
     * 
     * @since  1.0.0
     * @return void
     */
    public function brand_image_upload_script() {
        if ( isset( $_GET['taxonomy'] ) && 'product_brand' === $_GET['taxonomy'] ) :
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    function openMediaUploader(inputField, previewElement) {
                        const customUploader = wp.media({
                            title: '<?php echo esc_js( __( "Select Brand Image", "brands-for-woocommerce" ) ); ?>',
                            button: {
                                text: '<?php echo esc_js( __( "Use this image", "brands-for-woocommerce" ) ); ?>'
                            },
                            multiple: false
                        }).on('select', function() {
                            const attachment = customUploader.state().get('selection').first().toJSON();
                            inputField.val(attachment.url);
                            previewElement.html('<img src="' + attachment.url + '" style="max-width:300px;">');
                        }).open();
                    }

                    $('.brand-image-upload').on('click', function(e) {
                        e.preventDefault();
                        const inputField = $(this).siblings('input[name="brand_image"]');
                        const previewElement = $(this).siblings('.brand-image-preview');
                        openMediaUploader(inputField, previewElement);
                    });
                });
            </script>
            <?php
        endif;
    }

    /**
     * Add custom fields for the brand image and URL.
     * 
     * @since  1.0.0
     * @return void
     */
    public function add_brand_image_and_url_fields() {
        ?>
        <div class="form-field">
            <label for="brand_image"><?php echo esc_html_e( 'Brand Image', 'brands-for-woocommerce' ); ?></label>
            <button type="button" class="button brand-image-upload"><?php esc_html_e( 'Upload Image', 'brands-for-woocommerce' ); ?></button>
            <div class="brand-image-preview"></div>
            <input type="hidden" name="brand_image" id="brand_image" value="" />
            <p class="description"><?php esc_html_e( 'Upload an image for this brand.', 'brands-for-woocommerce' ); ?></p>
        </div>
        <div class="form-field">
            <label for="brand_website_url"><?php esc_html_e( 'Brand Website URL', 'brands-for-woocommerce' ); ?></label>
            <input type="url" name="brand_website_url" id="brand_website_url" value="">
            <p class="description"><?php esc_html_e( 'Enter the URL for this brand\'s website.', 'brands-for-woocommerce' ); ?></p>
        </div>
        <?php
    }

    /**
     * Edit custom fields for the brand image and URL.
     *
     * @param WP_Term $term The current term object.
     * 
     * @since  1.0.0
     * @return void
     */
    public function edit_brand_image_and_url_fields( $term ) {
        $image       = get_term_meta( $term->term_id, 'brand_image', true );
        $website_url = get_term_meta( $term->term_id, 'brand_website_url', true );

        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="brand_image"><?php esc_html_e( 'Brand Image', 'brands-for-woocommerce' ); ?></label></th>
            <td>
                <div class="brand-image-preview">
                    <?php if ( $image ) : ?>
                        <img src="<?php echo esc_url( $image ); ?>" style="max-width: 300px;">
                    <?php endif; ?>
                </div>
                <button type="button" class="button brand-image-upload"><?php esc_html_e( 'Upload Image', 'brands-for-woocommerce' ); ?></button>
                <input type="hidden" name="brand_image" id="brand_image" value="<?php echo esc_attr( $image ); ?>">
                <p class="description"><?php esc_html_e( 'Upload an image for this brand.', 'brands-for-woocommerce' ); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="brand_website_url"><?php esc_html_e( 'Brand Website URL', 'brands-for-woocommerce' ); ?></label></th>
            <td>
                <input type="url" name="brand_website_url" id="brand_website_url" value="<?php echo esc_attr( $website_url ); ?>">
                <p class="description"><?php esc_html_e( 'Enter the URL for this brand\'s website.', 'brands-for-woocommerce' ); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save custom metadata for brand taxonomy.
     *
     * @param int $term_id The term ID.
     * 
     * @since  1.0.0
     * @return void
     */
    public function save_brand_meta( $term_id ) {
        if ( isset( $_POST['brand_image'] ) ) {
            update_term_meta( $term_id, 'brand_image', sanitize_text_field( wp_unslash( $_POST['brand_image'] ) ) );
        }
        if ( isset( $_POST['brand_website_url'] ) ) {
            update_term_meta( $term_id, 'brand_website_url', esc_url_raw( wp_unslash( $_POST['brand_website_url'] ) ) );
        }
    }

    /**
     * Method to display brand image with link to website URL, title, and description on archive pages.
     * 
     * @since  1.0.0
     * @return void
     */
    public function display_brand_archive_header() {
        if ( is_tax( 'product_brand' ) ) {
            $term        = get_queried_object();
            $image_url   = get_term_meta( $term->term_id, 'brand_image', true );
            $website_url = get_term_meta( $term->term_id, 'brand_website_url', true );

            // Prepare image HTML.
            $image_html = '';
            if ( $image_url ) {
                $image_html .= $website_url
                    ? '<a href="' . esc_url( $website_url ) . '" target="_blank">'
                    : '';
                $image_html .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $term->name ) . '" class="brand-archive-image" style="max-width: 300px; height: auto; margin-bottom: 20px;">';
                $image_html .= $website_url ? '</a>' : '';
            }

            // Output header.
            echo '<div class="brand-archive-header">';
            echo $image_html;
            echo '</div>';
        }
    }

    /**
     * Register the 'product_brand' taxonomy.
     * 
     * @since  1.0.0
     * @return void
     */
    public function register_brand_taxonomy() {
        $labels = [
            'name'          => _x( 'Brands', 'taxonomy general name', 'brands-for-woocommerce' ),
            'singular_name' => _x( 'Brand', 'taxonomy singular name', 'brands-for-woocommerce' ),
            'search_items'  => esc_html__( 'Search Brands', 'brands-for-woocommerce' ),
            'all_items'     => esc_html__( 'All Brands', 'brands-for-woocommerce' ),
            'edit_item'     => esc_html__( 'Edit Brand', 'brands-for-woocommerce' ),
            'add_new_item'  => esc_html__( 'Add New Brand', 'brands-for-woocommerce' ),
        ];

        $args = [
            'labels'            => $labels,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_in_nav_menus' => true,
            'rewrite'           => [ 'slug' => 'brand' ],
        ];

        register_taxonomy( 'product_brand', 'product', $args );
    }

    /**
     * Shortcode to display product brands in a grid layout.
     *
     * @param array $atts Shortcode attributes.
     * 
     * @since  1.0.0
     * @return string HTML output of the product brands grid.
     */
    public function product_brand_shortcode( $atts ) {
        // Define and sanitize attributes.
        $atts = shortcode_atts(
            [
                'columns'          => 4,
                'show_title'       => 'true',
                'link_image'       => 'true',
                'show_description' => 'false',
            ],
            $atts,
            'product_brand'
        );

        $show_title       = filter_var( $atts['show_title'], FILTER_VALIDATE_BOOLEAN );
        $link_image       = filter_var( $atts['link_image'], FILTER_VALIDATE_BOOLEAN );
        $show_description = filter_var( $atts['show_description'], FILTER_VALIDATE_BOOLEAN );

        // Fetch brands.
        $brands = get_terms(
            [
                'taxonomy'   => 'product_brand',
                'hide_empty' => false,
            ]
        );

        // Check for errors or empty results.
        if ( empty( $brands ) || is_wp_error( $brands ) ) {
            return '<p>' . esc_html__( 'No brands available.', 'brands-for-woocommerce' ) . '</p>';
        }

        // Prepare grid HTML.
        $columns = (int) $atts['columns'];
        $output  = '<div class="product-brand-grid" data-columns="' . esc_attr( $columns ) . '">';

        foreach ( $brands as $brand ) {
            $image       = get_term_meta( $brand->term_id, 'brand_image', true );
            $brand_link  = get_term_link( $brand );
            $description = $brand->description;

            $output .= '<div class="product-brand-item">';

            // Display image with optional link.
            if ( $image ) {
                if ( $link_image && ! is_wp_error( $brand_link ) ) {
                    $output .= '<a href="' . esc_url( $brand_link ) . '">';
                }
                $output .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $brand->name ) . '" class="brand-image">';
                if ( $link_image && ! is_wp_error( $brand_link ) ) {
                    $output .= '</a>';
                }
            }

            // Display title if enabled.
            if ( $show_title ) {
                $output .= '<p class="brand-name">' . esc_html( $brand->name ) . '</p>';
            }

            // Display description if enabled.
            if ( $show_description && $description ) {
                $output .= '<p class="brand-description">' . esc_html( $description ) . '</p>';
            }

            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Shortcode to display products from a specific brand.
     *
     * @param array $atts Shortcode attributes.
     * 
     * @since  1.0.0
     * @return string HTML output of the products grid.
     */
    public function brand_products_shortcode( $atts ) {
        // Define and sanitize shortcode attributes.
        $atts = shortcode_atts(
            [
                'brand'    => '',
                'per_page' => '12',
                'columns'  => '4',
                'orderby'  => 'title',
                'order'    => 'asc',
            ],
            $atts,
            'brand_products'
        );

        $query_args = [
            'post_type'      => 'product',
            'posts_per_page' => (int) $atts['per_page'],
            'tax_query'      => [
                [
                    'taxonomy' => 'product_brand',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field( $atts['brand'] ),
                ],
            ],
            'orderby' => sanitize_text_field( $atts['orderby'] ),
            'order'   => sanitize_text_field( $atts['order'] ),
        ];

        $query = new WP_Query( $query_args );

        ob_start();

        if ( $query->have_posts() ) {
            // Add WooCommerce® grid wrapper with the specified columns.
            echo '<ul class="products columns-' . esc_attr( $atts['columns'] ) . '">';

            while ( $query->have_posts() ) {
                $query->the_post();

                // Use WooCommerce's built-in template for product grid items.
                wc_get_template_part( 'content', 'product' );
            }

            echo '</ul>';
        } else {
            // Show a message if no products are found.
            echo '<p>' . esc_html__( 'No products found for this brand.', 'brands-for-woocommerce' ) . '</p>';
        }

        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Shortcode to display an A-Z indexed list of brands.
     * 
     * Generates an A-Z menu for quick scrolling and lists brands alphabetically by first letter.
     *
     * @since  1.0.0
     * @return string
     */
    public function brands_list_shortcode( $atts ) {
        // Parse shortcode attributes with default values
        $atts = shortcode_atts( [
            'show_images' => 'false',
            'show_title'  => 'true',
        ], $atts, 'brands_list' );

        // Convert the 'show_images' attribute to a boolean.
        $show_images = filter_var( $atts['show_images'], FILTER_VALIDATE_BOOLEAN );

        // Convert the 'show_title' attribute to a boolean.
        $show_title = filter_var( $atts['show_title'], FILTER_VALIDATE_BOOLEAN );

        $brands = get_terms( [
            'taxonomy'   => 'product_brand',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ] );

        if ( empty( $brands ) || is_wp_error( $brands ) ) {
            return '<p>' . esc_html__( 'No brands available.', 'brands-for-woocommerce' ) . '</p>';
        }

        // Build A-Z index menu.
        $output = '<div class="brand-index-menu">';
        foreach ( range( 'A', 'Z' ) as $letter ) {
            $output .= '<a href="#brand-' . esc_attr( $letter ) . '">' . esc_html( $letter ) . '</a> ';
        }
        $output .= '</div>';

        // Group brands by their first letter.
        $grouped_brands = [];
        foreach ( $brands as $brand ) {
            $first_letter = strtoupper( substr( $brand->name, 0, 1 ) );
            if ( ! isset( $grouped_brands[ $first_letter ] ) ) {
                $grouped_brands[ $first_letter ] = [];
            }
            $grouped_brands[ $first_letter ][] = $brand;
        }

        // Output brands by letter.
        $output .= '<div class="brands-list">';
        foreach ( $grouped_brands as $letter => $brands_group ) {
            $output .= '<h2 id="brand-' . esc_attr( $letter ) . '">' . esc_html( $letter ) . '</h2>';
            $output .= '<ul>';
            foreach ( $brands_group as $brand ) {
                $brand_link = get_term_link( $brand );
                $image      = get_term_meta( $brand->term_id, 'brand_image', true );

                $output .= '<li>';
                if ( $show_images && $image ) {
                    $output .= '<a href="' . esc_url( $brand_link ) . '"><img src="' . esc_url( $image ) . '" alt="' . esc_attr( $brand->name ) . '" class="brand-image" style="max-width: 140px; margin-right: 10px; vertical-align: middle;"></a>';
                }
                if ( $show_title ) {
                    $output .= '<a href="' . esc_url( $brand_link ) . '">' . esc_html( $brand->name ) . '</a>';
                }
                $output .= '</li>';
            }
            $output .= '</ul>';
        }
        $output .= '</div>';

        return $output;
    }

    /**
     * Display brand on single product page.
     * 
     * @since  1.0.0
     * @return void
     */
    public function display_product_brand_image() {
        global $post;

        $brand = get_the_terms( $post->ID, 'product_brand' );

        if ( $brand && ! is_wp_error( $brand ) ) {
            $term_id    = $brand[0]->term_id;
            $image      = get_term_meta( $term_id, 'brand_image', true );
            $brand_link = get_term_link( $brand[0] );

            // Validate brand link.
            $link = ( ! is_wp_error( $brand_link ) ) ? $brand_link : '';

            // Display the image with optional link.
            if ( $image ) {
                $output = '<div class="product-brand">';
                if ( $link ) {
                    $output .= '<a href="' . esc_url( $link ) . '">';
                }
                $output .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $brand[0]->name ) . '" class="brand-image" style="max-width: 100px; margin-bottom: 10px;">';
                if ( $link ) {
                    $output .= '</a>';
                }
                $output .= '</div>';

                echo wp_kses_post( $output );
            }
        }
    }

    /**
     * Register the brand thumbnails widget.
     * 
     * @since  1.0.0
     * @return void
     */
    public function register_brand_widget() {
        register_widget( 'Brand_Thumbnails_Widget' );
    }

    /**
     * Register REST API route for fetching brands.
     * 
     * @since  1.0.0
     * @return void
     */
    public function register_brands_api_route() {
        register_rest_route(
            'wc/v3',
            '/brands',
            [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_all_brands' ],
            ]
        );
    }

    /**
     * Retrieve all brands via REST API.
     *
     * @since  1.0.0
     * @return WP_REST_Response API response with brand data.
     */
    public function get_all_brands() {
        // Fetch all terms in the 'product_brand' taxonomy.
        $brands = get_terms(
            [
                'taxonomy'   => 'product_brand',
                'hide_empty' => false,
            ]
        );

        // Check for errors and prepare the response.
        if ( ! is_wp_error( $brands ) ) {
            $brands_data = [];

            foreach ( $brands as $brand ) {
                $brand_image = get_term_meta( $brand->term_id, 'brand_image', true );
                $brand_link  = get_term_link( $brand );

                $brands_data[] = [
                    'id'          => $brand->term_id,
                    'name'        => $brand->name,
                    'description' => $brand->description,
                    'slug'        => $brand->slug,
                    'count'       => $brand->count,
                    'image'       => $brand_image ? esc_url( $brand_image ) : null,
                    'url'         => ! is_wp_error( $brand_link ) ? esc_url( $brand_link ) : null,
                ];
            }

            return rest_ensure_response( $brands_data );
        }

        return rest_ensure_response( [] );
    }
}

new Brands_For_WooCommerce();
