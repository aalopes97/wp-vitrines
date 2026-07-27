<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Painel admin: traduzir nomes de elementos e strings do builder por idioma.
 */
class Vitrine_Translations_Admin {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
        add_action( 'admin_init', array( __CLASS__, 'handle_save' ) );
    }

    public static function register_menu() {
        add_submenu_page(
            'edit.php?post_type=vitrine',
            Vitrine_I18n::t( 'Builder translations', 'ui.translations_page_title' ),
            Vitrine_I18n::t( 'Translations', 'ui.translations_menu' ),
            'manage_options',
            'vitrine-builder-translations',
            array( __CLASS__, 'render_page' )
        );
    }

    public static function handle_save() {
        if ( empty( $_POST['vitrine_translations_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vitrine_translations_nonce'] ) ), 'vitrine_save_translations' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $lang = isset( $_POST['vitrine_trans_lang'] ) ? sanitize_key( wp_unslash( $_POST['vitrine_trans_lang'] ) ) : '';
        if ( ! $lang ) {
            return;
        }

        $raw_values = isset( $_POST['vitrine_trans_value'] ) && is_array( $_POST['vitrine_trans_value'] )
            ? wp_unslash( $_POST['vitrine_trans_value'] )
            : array();

        $overrides = Vitrine_I18n::get_overrides();
        if ( ! isset( $overrides[ $lang ] ) || ! is_array( $overrides[ $lang ] ) ) {
            $overrides[ $lang ] = array();
        }

        $catalog = Vitrine_I18n::get_string_catalog();
        foreach ( $catalog as $key => $default ) {
            if ( ! isset( $raw_values[ $key ] ) ) {
                continue;
            }
            $val = sanitize_text_field( $raw_values[ $key ] );
            if ( '' === $val || $val === $default ) {
                unset( $overrides[ $lang ][ $key ] );
            } else {
                $overrides[ $lang ][ $key ] = $val;
            }
        }

        Vitrine_I18n::save_overrides( $overrides );

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'    => 'vitrine-builder-translations',
                    'lang'    => $lang,
                    'updated' => '1',
                ),
                admin_url( 'edit.php?post_type=vitrine' )
            )
        );
        exit;
    }

    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        Vitrine_Plugin::load_elements();
        $languages = Vitrine_I18n::get_available_languages();
        $lang_keys = array_keys( $languages );
        $current_lang = isset( $_GET['lang'] ) ? sanitize_key( wp_unslash( $_GET['lang'] ) ) : '';
        if ( ! $current_lang || ! isset( $languages[ $current_lang ] ) ) {
            $current_lang = $lang_keys ? $lang_keys[0] : get_locale();
        }

        $catalog   = Vitrine_I18n::get_string_catalog();
        $overrides = Vitrine_I18n::get_overrides();
        $saved     = isset( $_GET['updated'] ) && '1' === $_GET['updated'];
        ?>
        <div class="wrap vitrine-translations-wrap">
            <h1><?php echo esc_html( Vitrine_I18n::t( 'Builder translations', 'ui.translations_page_title' ) ); ?></h1>
            <p class="description"><?php echo esc_html( Vitrine_I18n::t( 'Override element names and builder interface strings per language. Works with Polylang and standard WordPress translations (.po/.mo).', 'ui.translations_page_intro' ) ); ?></p>
            <?php if ( Vitrine_Polylang::is_active() ) : ?>
                <p class="description"><?php echo esc_html( Vitrine_I18n::t( 'Strings are also registered in Polylang → String translations (group: Builder Vitrine).', 'ui.polylang_strings_hint' ) ); ?></p>
            <?php endif; ?>
            <?php if ( $saved ) : ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html( Vitrine_I18n::t( 'Translations saved.', 'ui.translations_saved' ) ); ?></p></div>
            <?php endif; ?>

            <form method="get" action="" style="margin:16px 0;">
                <input type="hidden" name="post_type" value="vitrine" />
                <input type="hidden" name="page" value="vitrine-builder-translations" />
                <label for="vitrine-trans-lang-select"><strong><?php echo esc_html( Vitrine_I18n::t( 'Language', 'ui.translations_language' ) ); ?></strong></label>
                <select id="vitrine-trans-lang-select" name="lang" onchange="this.form.submit()">
                    <?php foreach ( $languages as $slug => $label ) : ?>
                        <option value="<?php echo esc_attr( $slug ); ?>"<?php selected( $current_lang, $slug ); ?>><?php echo esc_html( $label . ' (' . $slug . ')' ); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <form method="post" action="">
                <?php wp_nonce_field( 'vitrine_save_translations', 'vitrine_translations_nonce' ); ?>
                <input type="hidden" name="vitrine_trans_lang" value="<?php echo esc_attr( $current_lang ); ?>" />
                <p>
                    <input type="search" id="vitrine-trans-filter" class="regular-text" placeholder="<?php echo esc_attr( Vitrine_I18n::t( 'Filter strings…', 'ui.translations_filter' ) ); ?>" />
                </p>
                <table class="widefat striped vitrine-translations-table">
                    <thead>
                        <tr>
                            <th style="width:22%"><?php echo esc_html( Vitrine_I18n::t( 'Key', 'ui.translations_key' ) ); ?></th>
                            <th style="width:28%"><?php echo esc_html( Vitrine_I18n::t( 'Default text', 'ui.translations_default' ) ); ?></th>
                            <th><?php echo esc_html( Vitrine_I18n::t( 'Translation', 'ui.translations_value' ) ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $catalog as $key => $default ) : ?>
                            <?php
                            $value = isset( $overrides[ $current_lang ][ $key ] ) ? $overrides[ $current_lang ][ $key ] : '';
                            ?>
                            <tr class="vitrine-trans-row" data-key="<?php echo esc_attr( $key ); ?>" data-default="<?php echo esc_attr( $default ); ?>">
                                <td><code><?php echo esc_html( $key ); ?></code></td>
                                <td><?php echo esc_html( $default ); ?></td>
                                <td>
                                    <input type="text" class="large-text" name="vitrine_trans_value[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $default ); ?>" />
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php echo esc_html( Vitrine_I18n::t( 'Save translations', 'ui.translations_save' ) ); ?></button>
                </p>
            </form>
        </div>
        <script>
        (function () {
            var input = document.getElementById('vitrine-trans-filter');
            if (!input) return;
            input.addEventListener('input', function () {
                var q = (input.value || '').toLowerCase();
                document.querySelectorAll('.vitrine-trans-row').forEach(function (row) {
                    var key = (row.getAttribute('data-key') || '').toLowerCase();
                    var def = (row.getAttribute('data-default') || '').toLowerCase();
                    row.style.display = (!q || key.indexOf(q) !== -1 || def.indexOf(q) !== -1) ? '' : 'none';
                });
            });
        })();
        </script>
        <?php
    }
}
