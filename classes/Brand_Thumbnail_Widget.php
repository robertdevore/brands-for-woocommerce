<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class Brand_Thumbnails_Widget
 * 
 * Defines the brand thumbnails widget for displaying brands with thumbnails, names, and descriptions.
 * 
 * @since  1.0.0
 */
class Brand_Thumbnails_Widget extends WP_Widget {

    /**
     * Constructor for the widget.
     * 
     * @since  1.0.0
     * @return void
     */
    public function __construct() {
        parent::__construct(
            'brand_thumbnails_widget',
            esc_html__( 'Brand Thumbnails Widget', 'brands-for-woocommerce' ),
            [
                'description' => esc_html__( 'Displays brand thumbnails with options to show/hide brand names and descriptions, limit results, and randomize output.', 'brands-for-woocommerce' )
            ]
        );
    }

    /**
     * Render the widget output on the frontend.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Widget instance settings.
     * 
     * @since  1.0.0
     * @return void
     */
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
    
        if ( ! empty( $args['before_title'] ) && ! empty( $args['after_title'] ) ) {
            echo $args['before_title'] . esc_html__( 'Brands', 'brands-for-woocommerce' ) . $args['after_title'];
        }
    
        // Fetch widget settings.
        $show_name        = ! empty( $instance['show_name'] );
        $show_description = ! empty( $instance['show_description'] );
        $limit            = ! empty( $instance['limit'] ) ? absint( $instance['limit'] ) : 5;
        $randomize        = ! empty( $instance['randomize'] );
    
        // Prepare arguments for retrieving brands.
        $brands_args = [
            'taxonomy'   => 'product_brand',
            'hide_empty' => false,
        ];
    
        $brands = get_terms( $brands_args );
    
        // Randomize terms if needed.
        if ( $randomize && ! is_wp_error( $brands ) ) {
            shuffle( $brands );
        }
    
        // Limit terms after randomization.
        if ( ! is_wp_error( $brands ) ) {
            $brands = array_slice( $brands, 0, $limit );
        }
    
        if ( ! empty( $brands ) && ! is_wp_error( $brands ) ) {
            echo '<div class="widget-brand-thumbnails">';
    
            foreach ( $brands as $brand ) {
                $image      = get_term_meta( $brand->term_id, 'brand_image', true );
                $brand_link = get_term_link( $brand );
    
                echo '<div class="brand-thumbnail">';
    
                if ( $image ) {
                    echo '<a href="' . esc_url( $brand_link ) . '">';
                    echo '<img src="' . esc_url( $image ) . '" class="brand-thumbnail-image" alt="' . esc_attr( $brand->name ) . '">';
                    echo '</a>';
                }
    
                if ( $show_name ) {
                    echo '<a href="' . esc_url( $brand_link ) . '">';
                    echo '<p class="brand-thumbnail-name">' . esc_html( $brand->name ) . '</p>';
                    echo '</a>';
                }
    
                if ( $show_description && ! empty( $brand->description ) ) {
                    echo '<p class="brand-thumbnail-description">' . esc_html( $brand->description ) . '</p>';
                }
    
                echo '</div>';
            }
    
            echo '</div>';
        } else {
            echo '<p>' . esc_html__( 'No brands available.', 'brands-for-woocommerce' ) . '</p>';
        }
    
        echo $args['after_widget'];
    }    

    /**
     * Render the widget settings form in the admin.
     *
     * @param array $instance Current widget instance settings.
     * 
     * @since  1.0.0
     * @return void
     */
    public function form( $instance ) {
        // Define widget settings.
        $show_name        = isset( $instance['show_name'] ) ? (bool) $instance['show_name'] : true;
        $show_description = isset( $instance['show_description'] ) ? (bool) $instance['show_description'] : false;
        $limit            = isset( $instance['limit'] ) ? absint( $instance['limit'] ) : 5;
        $randomize        = isset( $instance['randomize'] ) ? (bool) $instance['randomize'] : false;
        ?>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $show_name ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_name' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_name' ) ); ?>" />
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_name' ) ); ?>"><?php esc_html_e( 'Show Brand Name', 'brands-for-woocommerce' ); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $show_description ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_description' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_description' ) ); ?>" />
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_description' ) ); ?>"><?php esc_html_e( 'Show Brand Description', 'brands-for-woocommerce' ); ?></label>
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Number of Brands to Display:', 'brands-for-woocommerce' ); ?></label>
            <input class="small-text" type="number" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" value="<?php echo esc_attr( $limit ); ?>" min="1" />
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $randomize ); ?> id="<?php echo esc_attr( $this->get_field_id( 'randomize' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'randomize' ) ); ?>" />
            <label for="<?php echo esc_attr( $this->get_field_id( 'randomize' ) ); ?>"><?php esc_html_e( 'Randomize Brands Display Order', 'brands-for-woocommerce' ); ?></label>
        </p>
        <?php
    }

    /**
     * Save widget settings.
     *
     * @param array $new_instance New settings.
     * @param array $old_instance Old settings.
     * 
     * @since  1.0.0
     * @return array Updated settings.
     */
    public function update( $new_instance, $old_instance ) {
        $instance                     = $old_instance;
        $instance['show_name']        = ! empty( $new_instance['show_name'] );
        $instance['show_description'] = ! empty( $new_instance['show_description'] );
        $instance['limit']            = ! empty( $new_instance['limit'] ) ? absint( $new_instance['limit'] ) : 5;
        $instance['randomize']        = ! empty( $new_instance['randomize'] );

        return $instance;
    }
}
