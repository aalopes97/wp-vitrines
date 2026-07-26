<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Traduções do builder: WordPress i18n, overrides por idioma e Polylang.
 */
class Vitrine_I18n {

    const TEXT_DOMAIN = 'builder-vitrine';
    const OPTION_OVERRIDES = 'vitrine_builder_translations';

    /** @var bool */
    private static $polylang_strings_registered = false;

    public static function init() {
        add_action( 'init', array( __CLASS__, 'load_textdomain' ), 0 );
        add_action( 'init', array( __CLASS__, 'register_polylang_strings' ), 20 );
    }

    public static function load_textdomain() {
        load_plugin_textdomain(
            self::TEXT_DOMAIN,
            false,
            dirname( plugin_basename( VITRINE_PATH . 'builder-vitrine.php' ) ) . '/languages'
        );
    }

    /**
     * Idioma ativo no admin do builder (Polylang admin > usuário > site).
     */
    public static function get_admin_language() {
        if ( function_exists( 'pll_current_language' ) ) {
            $lang = pll_current_language( 'slug' );
            if ( $lang ) {
                return $lang;
            }
        }
        $locale = get_user_locale();
        if ( function_exists( 'pll_languages_list' ) ) {
            $map = pll_languages_list( array( 'fields' => 'locale' ) );
            $slugs = pll_languages_list( array( 'fields' => 'slug' ) );
            if ( is_array( $map ) && is_array( $slugs ) ) {
                $idx = array_search( $locale, $map, true );
                if ( false !== $idx && isset( $slugs[ $idx ] ) ) {
                    return $slugs[ $idx ];
                }
            }
        }
        return $locale;
    }

    /**
     * Lista idiomas para o painel (slug => nome).
     */
    public static function get_available_languages() {
        if ( function_exists( 'pll_languages_list' ) ) {
            $slugs = pll_languages_list( array( 'fields' => 'slug' ) );
            $names = pll_languages_list( array( 'fields' => 'name' ) );
            $out   = array();
            if ( is_array( $slugs ) ) {
                foreach ( $slugs as $i => $slug ) {
                    $out[ $slug ] = isset( $names[ $i ] ) ? $names[ $i ] : $slug;
                }
            }
            if ( $out ) {
                return $out;
            }
        }
        return array( get_locale() => self::t( 'Default language', 'ui' ) );
    }

    /**
     * Traduz string do builder.
     *
     * @param string      $default Texto padrão (msgid).
     * @param string      $name    Chave estável (ex: ui.canvas_placeholder).
     * @param string|null $lang    Slug Polylang ou locale; null = admin atual.
     */
    public static function translate( $default, $name, $lang = null ) {
        $default = (string) $default;
        $name    = (string) $name;
        if ( null === $lang ) {
            $lang = self::get_admin_language();
        }

        $overrides = self::get_overrides();
        if ( isset( $overrides[ $lang ][ $name ] ) && '' !== $overrides[ $lang ][ $name ] ) {
            return $overrides[ $lang ][ $name ];
        }

        if ( function_exists( 'pll_translate_string' ) ) {
            $pll = pll_translate_string( $default, $lang );
            if ( is_string( $pll ) && $pll !== $default ) {
                return $pll;
            }
        }

        return __( $default, self::TEXT_DOMAIN );
    }

    /** Atalho para UI do builder. */
    public static function t( $default, $name = '' ) {
        if ( '' === $name ) {
            $name = 'ui.' . sanitize_key( $default );
        }
        return self::translate( $default, $name );
    }

    public static function element_label( $slug, $default_label ) {
        return self::translate( $default_label, 'element.' . sanitize_key( $slug ) . '.label' );
    }

    public static function field_label( $slug, $field_name, $default_label ) {
        return self::translate(
            $default_label,
            'element.' . sanitize_key( $slug ) . '.field.' . sanitize_key( $field_name )
        );
    }

    public static function field_option_label( $slug, $field_name, $option_value, $default_label ) {
        return self::translate(
            $default_label,
            'element.' . sanitize_key( $slug ) . '.field.' . sanitize_key( $field_name ) . '.option.' . sanitize_key( $option_value )
        );
    }

    public static function get_overrides() {
        $stored = get_option( self::OPTION_OVERRIDES, array() );
        return is_array( $stored ) ? $stored : array();
    }

    public static function save_overrides( array $overrides ) {
        update_option( self::OPTION_OVERRIDES, $overrides, false );
    }

    /**
     * Catálogo completo de strings traduzíveis (chave => default).
     */
    public static function get_string_catalog() {
        $catalog = self::get_ui_string_catalog();

        $elements = Vitrine_Plugin::load_elements();
        foreach ( $elements as $slug => $el ) {
            $catalog[ 'element.' . $slug . '.label' ] = $el->label();
            foreach ( $el->fields() as $field ) {
                if ( empty( $field['name'] ) ) {
                    continue;
                }
                $fname = $field['name'];
                $catalog[ 'element.' . $slug . '.field.' . $fname ] = isset( $field['label'] ) ? $field['label'] : $fname;
                if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
                    foreach ( $field['options'] as $opt_val => $opt_label ) {
                        $catalog[ 'element.' . $slug . '.field.' . $fname . '.option.' . $opt_val ] = $opt_label;
                    }
                }
            }
        }

        return apply_filters( 'vitrine_builder_string_catalog', $catalog );
    }

    /**
     * Strings da interface do editor (sidebar, canvas, abas).
     */
    public static function get_ui_string_catalog() {
        return array(
            'ui.elements'                      => 'Elements',
            'ui.search_elements'               => 'Search element...',
            'ui.no_elements_found'             => 'No elements found.',
            'ui.settings'                      => 'Settings',
            'ui.settings_empty'                => 'Click an element on the canvas to edit its settings.',
            'ui.content_tab'                   => 'Content',
            'ui.style_tab'                     => 'Styles',
            'ui.canvas_placeholder'            => 'Drag elements here',
            'ui.collapse_elements_panel'       => 'Collapse elements panel',
            'ui.show_elements_panel'           => 'Show elements panel',
            'ui.collapse_settings_panel'       => 'Collapse settings panel',
            'ui.show_settings_panel'           => 'Show settings panel',
            'ui.close_settings_panel'          => 'Close panel',
            'ui.builder_title'                 => 'Vitrine Builder',
            'ui.page_custom_css'               => 'Custom vitrine CSS',
            'ui.clone_to_language'             => 'Clone to language',
            'ui.clone_to_language_help'        => 'Creates or updates the Polylang translation with the same layout and content.',
            'ui.clone_success'                 => 'Vitrine cloned. Opening translation…',
            'ui.clone_error'                   => 'Could not clone vitrine.',
            'ui.translations_page_title'       => 'Builder translations',
            'ui.translations_menu'             => 'Translations',
            'ui.container_default'             => 'Container',
            'ui.translations_page_intro'       => 'Override element names and builder interface strings per language. Works with Polylang and standard WordPress translations (.po/.mo).',
            'ui.translations_save'             => 'Save translations',
            'ui.translations_saved'            => 'Translations saved.',
            'ui.translations_language'         => 'Language',
            'ui.translations_key'              => 'Key',
            'ui.translations_default'          => 'Default text',
            'ui.translations_value'            => 'Translation',
            'ui.translations_filter'           => 'Filter strings…',
            'ui.polylang_strings_hint'         => 'Strings are also registered in Polylang → String translations (group: Builder Vitrine).',
        );
    }

    /**
     * Strings passadas ao editor.js (valores já traduzidos para o idioma admin).
     */
    public static function get_editor_js_strings() {
        $catalog = self::get_string_catalog();
        $out     = array();
        foreach ( $catalog as $key => $default ) {
            if ( 0 === strpos( $key, 'ui.' ) ) {
                $out[ $key ] = self::translate( $default, $key );
            }
        }
        $out['container_default'] = self::t( 'Container', 'ui.container_default' );
        return $out;
    }

    public static function localize_elements_for_editor( array $elements_raw ) {
        $elements_js = array();
        foreach ( $elements_raw as $slug => $el ) {
            $fields_data = array();
            foreach ( $el->fields() as $field ) {
                $f = $field;
                if ( isset( $f['label'] ) ) {
                    $f['label'] = self::field_label( $slug, $f['name'], $f['label'] );
                }
                if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
                    $opts = array();
                    foreach ( $field['options'] as $opt_key => $opt_label ) {
                        $opts[ $opt_key ] = self::field_option_label( $slug, $field['name'], $opt_key, $opt_label );
                    }
                    $f['options'] = $opts;
                }
                $fields_data[] = $f;
            }
            $elements_js[ $slug ] = array(
                'slug'     => $slug,
                'label'    => self::element_label( $slug, $el->label() ),
                'icon'     => $el->icon(),
                'defaults' => $el->defaults(),
                'fields'   => $fields_data,
            );
        }
        return $elements_js;
    }

    public static function register_polylang_strings() {
        if ( ! function_exists( 'pll_register_string' ) || self::$polylang_strings_registered ) {
            return;
        }
        self::$polylang_strings_registered = true;

        Vitrine_Plugin::load_elements();
        $catalog = self::get_string_catalog();
        foreach ( $catalog as $name => $string ) {
            pll_register_string( $name, $string, 'Builder Vitrine', false );
        }
    }
}
