<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Polylang: CPT traduzível e clonagem de vitrine entre idiomas.
 */
class Vitrine_Polylang {

    public static function init() {
        add_filter( 'pll_get_post_types', array( __CLASS__, 'register_post_type' ), 10, 2 );
        add_action( 'wp_ajax_vitrine_clone_to_language', array( __CLASS__, 'ajax_clone_to_language' ) );
    }

    public static function is_active() {
        return function_exists( 'pll_set_post_language' ) && function_exists( 'pll_get_post' );
    }

    /**
     * @param array $post_types
     * @param bool  $is_settings
     */
    public static function register_post_type( $post_types, $is_settings ) {
        if ( $is_settings ) {
            $post_types['vitrine'] = 'vitrine';
        } else {
            $post_types['vitrine'] = 'vitrine';
        }
        return $post_types;
    }

    /**
     * Idiomas Polylang exceto o da vitrine atual.
     */
    public static function get_clone_target_languages( $post_id ) {
        if ( ! self::is_active() ) {
            return array();
        }
        $current = pll_get_post_language( $post_id, 'slug' );
        if ( ! $current ) {
            $current = pll_default_language( 'slug' );
        }
        $slugs = pll_languages_list( array( 'fields' => 'slug' ) );
        $names = pll_languages_list( array( 'fields' => 'name' ) );
        $out   = array();
        if ( ! is_array( $slugs ) ) {
            return $out;
        }
        foreach ( $slugs as $i => $slug ) {
            if ( $slug === $current ) {
                continue;
            }
            $out[] = array(
                'slug'    => $slug,
                'name'    => isset( $names[ $i ] ) ? $names[ $i ] : $slug,
                'post_id' => (int) pll_get_post( $post_id, $slug ),
            );
        }
        return $out;
    }

    public static function ajax_clone_to_language() {
        check_ajax_referer( 'vitrine_save', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'builder-vitrine' ) ) );
        }

        if ( ! self::is_active() ) {
            wp_send_json_error( array( 'message' => __( 'Polylang is not active.', 'builder-vitrine' ) ) );
        }

        $source_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $lang      = isset( $_POST['lang'] ) ? sanitize_key( wp_unslash( $_POST['lang'] ) ) : '';

        if ( ! $source_id || ! $lang ) {
            wp_send_json_error( array( 'message' => __( 'Invalid request.', 'builder-vitrine' ) ) );
        }

        $source = get_post( $source_id );
        if ( ! $source || 'vitrine' !== $source->post_type ) {
            wp_send_json_error( array( 'message' => __( 'Invalid vitrine.', 'builder-vitrine' ) ) );
        }

        if ( ! current_user_can( 'edit_post', $source_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'builder-vitrine' ) ) );
        }

        $result = self::clone_to_language( $source_id, $lang );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success(
            array(
                'post_id'  => $result,
                'edit_url' => get_edit_post_link( $result, 'raw' ),
                'message'  => __( 'Vitrine cloned successfully.', 'builder-vitrine' ),
            )
        );
    }

    /**
     * Copia layout e meta para tradução Polylang (cria ou atualiza).
     *
     * @return int|WP_Error ID da vitrine de destino.
     */
    public static function clone_to_language( $source_id, $lang ) {
        $source = get_post( $source_id );
        if ( ! $source || 'vitrine' !== $source->post_type ) {
            return new WP_Error( 'invalid_source', __( 'Invalid source vitrine.', 'builder-vitrine' ) );
        }

        $translations = pll_get_post_translations( $source_id );
        if ( ! is_array( $translations ) ) {
            $translations = array();
        }

        $source_lang = pll_get_post_language( $source_id, 'slug' );
        if ( $source_lang ) {
            $translations[ $source_lang ] = (int) $source_id;
        }

        $target_id = isset( $translations[ $lang ] ) ? (int) $translations[ $lang ] : 0;
        if ( ! $target_id ) {
            $existing = pll_get_post( $source_id, $lang );
            if ( $existing ) {
                $target_id = (int) $existing;
            }
        }

        if ( $target_id ) {
            if ( ! current_user_can( 'edit_post', $target_id ) ) {
                return new WP_Error( 'forbidden', __( 'Cannot edit target translation.', 'builder-vitrine' ) );
            }
        } else {
            $target_id = wp_insert_post(
                array(
                    'post_type'   => 'vitrine',
                    'post_status' => 'draft',
                    'post_title'  => $source->post_title,
                ),
                true
            );
            if ( is_wp_error( $target_id ) ) {
                return $target_id;
            }
            pll_set_post_language( $target_id, $lang );
        }

        self::copy_vitrine_data( $source_id, $target_id );

        $translations[ $lang ] = (int) $target_id;
        pll_save_post_translations( $translations );

        return (int) $target_id;
    }

    /**
     * Duplica layout, hero e configurações da página.
     */
    public static function copy_vitrine_data( $from_id, $to_id ) {
        $from_id = (int) $from_id;
        $to_id   = (int) $to_id;

        $layout = get_post_meta( $from_id, '_vitrine_layout', true );
        if ( is_array( $layout ) ) {
            update_post_meta( $to_id, '_vitrine_layout', $layout );
        }

        $settings = get_post_meta( $from_id, Vitrine_Hero_Meta::META_KEY, true );
        if ( is_array( $settings ) ) {
            update_post_meta( $to_id, Vitrine_Hero_Meta::META_KEY, $settings );
        }

        $source = get_post( $from_id );
        if ( $source ) {
            wp_update_post(
                array(
                    'ID'           => $to_id,
                    'post_title'   => $source->post_title,
                    'post_content' => $source->post_content,
                    'post_excerpt' => $source->post_excerpt,
                )
            );
        }

        do_action( 'vitrine_clone_copied', $to_id, $from_id );
    }
}
