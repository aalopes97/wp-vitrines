<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Vitrine_Element_Aranha2 extends Vitrine_Element {

    public function slug() {
        return 'aranha2';
    }

    public function label() {
        return 'Aranha Circular';
    }

    public function icon() {
        return 'dashicons-chart-pie';
    }

    public function defaults() {
        return array_merge( array(
            'center_image'    => '',
            'center_size'     => '160',
            'center_label'    => '',
            'center_bg_color' => '#ffffff',
            'bg_color'        => '#f8f9fa',
            'card_bg'         => '#ffffff',
            'card_border'     => '#2e7d32',
            'title_color'     => '#1d2327',
            'text_color'      => '#555555',
            'icon_size'       => '36',
            'icon_color'      => '#2e7d32',
            'radius'          => '200',
            'card_style'      => 'default',
            'card_min_height' => '190',
            'items'           => array(),
        ), self::card_preset_defaults() );
    }

    public function fields() {
        return array_merge( array(
            array( 'name' => 'center_image',    'label' => 'Imagem Central',        'type' => 'image' ),
            array( 'name' => 'center_size',     'label' => 'Tamanho central (px)',  'type' => 'number' ),
            array( 'name' => 'center_label',    'label' => 'Rótulo central',        'type' => 'text' ),
            array( 'name' => 'center_bg_color', 'label' => 'Cor de fundo centro',   'type' => 'color' ),
            array( 'name' => 'radius',          'label' => 'Raio orbital (px)',     'type' => 'number' ),
            array( 'name' => 'icon_size',       'label' => 'Tam. ícones (px)',      'type' => 'number' ),
            array( 'name' => 'icon_color',      'label' => 'Cor dos ícones',        'type' => 'color' ),
            array( 'name' => 'card_style',      'label' => 'Modelo do card',        'type' => 'select', 'options' => array(
                'default'     => 'Padrão (orbital)',
                'dark'        => 'Escuro (ícone acima)',
                'white'       => 'Branco (ícone ao lado)',
                'border-left' => 'Borda esquerda',
            ) ),
            array( 'name' => 'card_min_height', 'label' => 'Altura mínima dos cards (px)', 'type' => 'number' ),
            array( 'name' => 'card_bg',         'label' => 'Cor fundo card',        'type' => 'color' ),
            array( 'name' => 'card_border',     'label' => 'Cor destaque (bordas)', 'type' => 'color' ),
            array( 'name' => 'title_color',     'label' => 'Cor do título',         'type' => 'color' ),
            array( 'name' => 'text_color',      'label' => 'Cor do texto',          'type' => 'color' ),
            array( 'name' => 'bg_color',        'label' => 'Cor de fundo',          'type' => 'color' ),
        ), self::card_preset_fields() );
    }

    public function render( $settings, $children_html = '' ) {
        $s = wp_parse_args( $settings, $this->defaults() );

        $items       = is_array( $s['items'] ) ? array_values( $s['items'] ) : array();
        $n           = count( $items );
        $center_size = max( 60, intval( $s['center_size'] ) );
        $radius      = max( 80, intval( $s['radius'] ) );
        $icon_size   = max( 16, intval( $s['icon_size'] ) );
        $icon_color  = esc_attr( $s['icon_color'] );
        $card_bg     = esc_attr( $s['card_bg'] );
        $accent      = esc_attr( ! empty( $s['card_border'] ) ? $s['card_border'] : ( ! empty( $s['line_color'] ) ? $s['line_color'] : '#2e7d32' ) );
        $title_color = esc_attr( $s['title_color'] );
        $text_color  = esc_attr( $s['text_color'] );
        $bg_color    = esc_attr( $s['bg_color'] );
        $center_bg   = esc_attr( $s['center_bg_color'] );
        $center_lbl  = esc_html( $s['center_label'] );
        $card_style      = $this->sanitize_card_style( $s['card_style'] );
        $use_preset      = 'default' !== $card_style;
        $card_min_height = max( 80, intval( isset( $s['card_min_height'] ) ? $s['card_min_height'] : 190 ) );
        $orbit_radius    = $this->resolve_orbit_radius( $radius, $center_size, $use_preset, $card_min_height, $n );

        $stage       = $this->compute_stage_size( $center_size, $orbit_radius, $n, $use_preset, $card_min_height );
        $stage_w     = $stage['w'];
        $stage_h     = $stage['h'];
        $r_pct_w     = $stage['r_pct_w'];
        $r_pct_h     = $stage['r_pct_h'];
        $cs_pct_w    = $stage['cs_pct_w'];

        $wrap_style  = 'background:' . $bg_color
            . ';--a2-accent:' . $accent
            . ';--a2-stage-w:' . $stage_w . 'px'
            . ';--a2-stage-h:' . $stage_h . 'px'
            . ';--a2-orbit-r:' . $orbit_radius . 'px'
            . ';--a2-card-min-h:' . $card_min_height . 'px;';

        if ( $use_preset ) {
            $wrap_style .= $this->build_card_preset_style( $s, $icon_color );
        }

        $output  = '<div class="vitrine-el-aranha2 vitrine-card-style--' . esc_attr( $card_style ) . '" style="' . esc_attr( $wrap_style ) . '">';
        $output .= '<div class="vitrine-aranha2__stage">';

        $output .= '<div class="vitrine-aranha2__center"'
            . ' style="width:' . $cs_pct_w . '%;'
            . 'border-color:' . $accent . ';'
            . 'background-color:' . $center_bg . ';">';

        if ( ! empty( $s['center_image'] ) ) {
            $output .= '<img src="' . esc_url( $s['center_image'] ) . '" alt="' . esc_attr( $center_lbl ) . '" />';
        } elseif ( $center_lbl ) {
            $output .= '<span class="vitrine-aranha2__center-label" style="color:' . $text_color . ';">'
                . $center_lbl . '</span>';
        } else {
            $output .= '<span class="vitrine-aranha2__center-placeholder">'
                . '<span class="dashicons dashicons-camera"></span></span>';
        }

        $output .= '</div>'; // center

        // ── Cards ─────────────────────────────────────────────────────────
        for ( $i = 0; $i < $n; $i++ ) {
            $item  = $items[ $i ];
            $angle = - M_PI / 2 + $i * ( 2 * M_PI / max( 1, $n ) );
            $x_pct = round( 50 + $r_pct_w * cos( $angle ), 4 );
            $y_pct = round( 50 + $r_pct_h * sin( $angle ), 4 );

            $title = isset( $item['title'] ) ? wp_kses_post( $item['title'] ) : '';
            $text  = isset( $item['text'] )  ? wp_kses_post( $item['text'] )  : '';
            if ( ! $title && $text ) {
                $title = $text;
                $text  = '';
            }
            $icon  = isset( $item['icon'] ) ? $item['icon'] : '';
            $link  = isset( $item['link'] ) ? esc_url( $item['link'] ) : '';
            $delay = number_format( $i * 0.08 + 0.15, 2 );
            $card_class = 'vitrine-aranha2__card'
                . ( $link ? ' vitrine-aranha2__card--linked' : '' )
                . ( $use_preset ? ' vitrine-card-style-' . esc_attr( $card_style ) : '' );

            $card_style_attr = 'left:' . $x_pct . '%;top:' . $y_pct . '%;animation-delay:' . $delay . 's;';
            if ( ! $use_preset ) {
                $card_style_attr .= '--a2-card-bg:' . $card_bg . ';'
                    . '--a2-card-border:' . $accent . ';'
                    . '--a2-card-text:' . $text_color . ';';
            }

            $output .= '<div class="' . esc_attr( $card_class ) . '" style="' . esc_attr( $card_style_attr ) . '">';

            $inner = '';

            if ( $icon ) {
                $icon_wrap = $use_preset ? 'vitrine-card-icon' : 'vitrine-aranha2__card-icon';
                $inner .= '<span class="' . esc_attr( $icon_wrap ) . '">'
                    . $this->render_icon( $icon, $icon_size, $icon_color ) . '</span>';
            }

            if ( $title || $text ) {
                $content_class = $use_preset ? 'vitrine-card-content' : 'vitrine-aranha2__card-content';
                $inner .= '<div class="' . esc_attr( $content_class ) . '">';
                if ( $title ) {
                    $inner .= '<h3 class="vitrine-aranha2__card-title" style="color:' . $title_color . ';">' . $title . '</h3>';
                }
                if ( $text ) {
                    $inner .= '<div class="vitrine-aranha2__card-text" style="color:' . $text_color . ';">' . $text . '</div>';
                }
                $inner .= '</div>';
            }

            if ( $link ) {
                $output .= '<a href="' . $link . '" class="vitrine-aranha2__card-link">' . $inner . '</a>';
            } else {
                $output .= $inner;
            }

            $output .= '</div>'; // card
        }

        // Empty state
        if ( ! $n ) {
            $output .= '<div class="vitrine-aranha2__empty">'
                . '<span class="dashicons dashicons-chart-pie"></span>'
                . '<p>Adicione itens no painel de configurações</p>'
                . '</div>';
        }

        $output .= '</div>'; // stage
        $output .= '</div>'; // el-aranha2

        return $output;
    }

    private function resolve_orbit_radius( $radius, $center_size, $use_preset, $card_min_height = 190, $n_items = 0 ) {
        $radius = max( 80, intval( $radius ) );

        if ( ! $use_preset ) {
            return $radius;
        }

        $card_w      = 280;
        $card_h      = max( 190, intval( $card_min_height ) );
        $card_half_w = $card_w / 2;
        $card_half_h = max( 95, $card_h / 2 );
        $card_diag   = sqrt( ( $card_half_w * $card_half_w ) + ( $card_half_h * $card_half_h ) );
        $clearance   = 64;
        $min_radius  = (int) ceil( $card_diag + ( $center_size / 2 ) + $clearance );

        $n_items = max( 0, intval( $n_items ) );
        if ( $n_items > 1 ) {
            $gap_w = 36;
            $gap_h = 28;
            $by_w  = (int) ceil( ( $card_w + $gap_w ) / ( 2 * sin( M_PI / $n_items ) ) );
            $by_h  = (int) ceil( ( $card_h + $gap_h ) / ( 2 * sin( M_PI / $n_items ) ) );
            $min_radius = max( $min_radius, $by_w, $by_h );
        }

        return max( $radius, $min_radius );
    }

    private function compute_stage_size( $center_size, $radius, $n_items, $use_preset, $card_min_height = 190 ) {
        $card_max_w  = $use_preset ? 280 : 220;
        $card_half_w = $card_max_w / 2;
        $card_half_h = $use_preset ? max( 95, $card_min_height / 2 ) : 50;

        $pad_w = $use_preset ? (int) ceil( $card_half_w + 72 ) : 40;
        $pad_h = $use_preset ? (int) ceil( $card_half_h + 72 ) : 28;

        $stage_w = $center_size + 2 * $radius + 2 * $pad_w;
        $stage_h = $stage_w;

        for ( $pass = 0; $pass < 4; $pass++ ) {
            $r_pct_w  = $radius / max( 1, $stage_w ) * 100;
            $r_pct_h  = $radius / max( 1, $stage_h ) * 100;
            $cs_pct_w = $center_size / max( 1, $stage_w ) * 100;
            $cs_pct_h = $center_size / max( 1, $stage_h ) * 100;

            $half_w_pct = ( $card_half_w / max( 1, $stage_w ) ) * 100;
            $half_h_pct = ( $card_half_h / max( 1, $stage_h ) ) * 100;

            $x_min = 50 - ( $cs_pct_w / 2 );
            $x_max = 50 + ( $cs_pct_w / 2 );
            $y_min = 50 - ( $cs_pct_h / 2 );
            $y_max = 50 + ( $cs_pct_h / 2 );

            if ( $n_items > 0 ) {
                for ( $i = 0; $i < $n_items; $i++ ) {
                    $angle = - M_PI / 2 + $i * ( 2 * M_PI / max( 1, $n_items ) );
                    $x     = 50 + $r_pct_w * cos( $angle );
                    $y     = 50 + $r_pct_h * sin( $angle );
                    $x_min = min( $x_min, $x - $half_w_pct );
                    $x_max = max( $x_max, $x + $half_w_pct );
                    $y_min = min( $y_min, $y - $half_h_pct );
                    $y_max = max( $y_max, $y + $half_h_pct );
                }
            }

            $span_w = max( 58, $x_max - $x_min );
            $span_h = max( 48, $y_max - $y_min );

            $need_w = (int) ceil( $stage_w * ( $span_w / 100 ) );
            $need_h = (int) ceil( $stage_h * ( $span_h / 100 ) );

            $stage_w = max( $stage_w, $need_w );
            $stage_h = max( $stage_h, $need_h, $center_size + 2 * $pad_h + (int) ceil( $card_half_h * 0.5 ) );
        }

        return array(
            'w'        => $stage_w,
            'h'        => $stage_h,
            'r_pct_w'  => round( $radius / max( 1, $stage_w ) * 100, 4 ),
            'r_pct_h'  => round( $radius / max( 1, $stage_h ) * 100, 4 ),
            'cs_pct_w' => round( $center_size / max( 1, $stage_w ) * 100, 4 ),
        );
    }

    private function sanitize_card_style( $style ) {
        $allowed = array( 'default', 'dark', 'white', 'border-left' );
        $style   = sanitize_key( $style );
        return in_array( $style, $allowed, true ) ? $style : 'default';
    }

    private function render_icon( $icon, $icon_size, $icon_color = '' ) {
        if ( ! $icon ) {
            return '';
        }
        $color_style = $icon_color ? 'color:' . esc_attr( $icon_color ) . ';' : '';
        if ( strpos( $icon, 'dashicons-' ) === 0 ) {
            return '<span class="dashicons ' . esc_attr( $icon )
                . '" style="font-size:' . $icon_size . 'px;width:' . $icon_size . 'px;height:' . $icon_size . 'px;' . $color_style . '"></span>';
        }
        if ( preg_match( '/^fa[srlbd]?\s/', $icon ) ) {
            return '<i class="' . esc_attr( $icon ) . '" style="font-size:' . $icon_size . 'px;' . $color_style . '"></i>';
        }
        return '<img src="' . esc_url( $icon ) . '" alt=""'
            . ' style="width:' . $icon_size . 'px;height:' . $icon_size . 'px;object-fit:contain;border-radius:4px;" />';
    }
}

Vitrine_Element_Registry::register( new Vitrine_Element_Aranha2() );
