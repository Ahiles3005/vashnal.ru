<?php

namespace Vashnal;

use WP_Post;
use WP_Widget_Custom_HTML;

/**
 * Custom widget for homepage categories.
 *
 * @package Vashnal
 */
class HTML_Widget extends WP_Widget_Custom_HTML {

    /**
     * Outputs the content for the current Custom HTML widget instance.
     *
     * @param array $args Display arguments including 'before_title', 'after_title',
     *                        'before_widget', and 'after_widget'.
     * @param array $instance Settings for the current Custom HTML widget instance.
     *
     * @since 4.8.1
     *
     * @global WP_Post $post Global post object.
     *
     */
    public function widget( $args, $instance ) {
        global $post;

        // Override global $post so filters (and shortcodes) apply in a consistent context.
        $original_post = $post;
        if ( is_singular() ) {
            // Make sure post is always the queried object on singular queries (not from another sub-query that failed to clean up the global $post).
            $post = get_queried_object();
        } else {
            // Nullify the $post global during widget rendering to prevent shortcodes from running with the unexpected context on archive queries.
            $post = null;
        }

        // Prevent dumping out all attachments from the media library.
        add_filter( 'shortcode_atts_gallery', array( $this, '_filter_gallery_shortcode_attrs' ) );

        $instance = array_merge( $this->default_instance, $instance );

        /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
        $title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

        // Prepare instance data that looks like a normal Text widget.
        $simulated_text_widget_instance = array_merge(
            $instance,
            array(
                'text'   => isset( $instance['content'] ) ? $instance['content'] : '',
                'filter' => false, // Because wpautop is not applied.
                'visual' => false, // Because it wasn't created in TinyMCE.
            )
        );
        unset( $simulated_text_widget_instance['content'] ); // Was moved to 'text' prop.

        /** This filter is documented in wp-includes/widgets/class-wp-widget-text.php */
        $content = apply_filters( 'widget_text', $instance['content'], $simulated_text_widget_instance, $this );

        // Adds 'noopener' relationship, without duplicating values, to all HTML A elements that have a target.
        $content = wp_targeted_link_rel( $content );

        /**
         * Filters the content of the Custom HTML widget.
         *
         * @param string $content The widget content.
         * @param array $instance Array of settings for the current widget.
         * @param WP_Widget_Custom_HTML $this Current Custom HTML widget instance.
         *
         * @since 4.8.1
         *
         */
        $content = apply_filters( 'widget_custom_html_content', $content, $instance, $this );

        // Restore post global.
        $post = $original_post;
        remove_filter( 'shortcode_atts_gallery', array( $this, '_filter_gallery_shortcode_attrs' ) );

        // Inject the Text widget's container class name alongside this widget's class name for theme styling compatibility.
        $args['before_widget'] = preg_replace( '/(?<=\sclass=["\'])/', 'widget_text ', $args['before_widget'] );

        echo $args['before_widget'];
        if ( ! empty( $title ) ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        echo $content;
        echo $args['after_widget'];
    }

}