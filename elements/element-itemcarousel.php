<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Vitrine_Element_Itemcarousel extends Vitrine_Element {

    public function slug() {
        return 'itemcarousel';
    }

    public function label() {
        return 'Carrossel de Itens';
    }

    public function icon() {
        return 'dashicons-slides';
    }

    public function defaults() {
        return array(
            'slides_per_view_desktop' => '3',
            'slides_per_view_tablet'  => '2',
            'slides_per_view_mobile'  => '1',
            'gap'             => '20',
            'autoplay'        => '0',
            'autoplay_speed'  => '5000',
            'show_arrows'     => '1',
            'show_dots'       => '1',
            'card_bg'         => '#ffffff',
            'card_radius'     => '12',
            'card_padding'    => '20',
            'image_height'    => '160',
            'image_fit'       => 'cover',
            'image_radius'    => '8',
            'icon_size'       => '40',
            'icon_color'      => '#2271b1',
            'icon_bg'         => '#eef5fc',
            'icon_radius'     => '50',
            'title_color'     => '#1a1a1a',
            'title_size'      => '18',
            'title_weight'    => '700',
            'text_color'      => '#555555',
            'text_size'       => '14',
            'text_weight'     => '400',
            'text_align'      => 'center',
            'items'           => array(),
        );
    }

    public function fields() {
        return array(
            array( 'name' => '_section_slides', 'label' => 'Slides visíveis por dispositivo', 'type' => 'heading' ),
            array( 'name' => 'slides_per_view_desktop', 'label' => 'Desktop (≥901px)', 'type' => 'number' ),
            array( 'name' => 'slides_per_view_tablet',  'label' => 'Tablet (562–900px)', 'type' => 'number' ),
            array( 'name' => 'slides_per_view_mobile',  'label' => 'Mobile (≤561px)',  'type' => 'number' ),
            array( 'name' => 'gap',             'label' => 'Espaçamento (px)',         'type' => 'number' ),
            array( 'name' => 'autoplay',        'label' => 'Autoplay',                 'type' => 'select', 'options' => array( '0' => 'Desligado', '1' => 'Ligado' ) ),
            array( 'name' => 'autoplay_speed',  'label' => 'Intervalo autoplay (ms)',  'type' => 'number' ),
            array( 'name' => 'show_arrows',     'label' => 'Setas de navegação',       'type' => 'select', 'options' => array( '1' => 'Sim', '0' => 'Não' ) ),
            array( 'name' => 'show_dots',       'label' => 'Indicadores (dots)',       'type' => 'select', 'options' => array( '1' => 'Sim', '0' => 'Não' ) ),
            array( 'name' => 'card_bg',         'label' => 'Fundo do slide',           'type' => 'color' ),
            array( 'name' => 'card_radius',     'label' => 'Arredondamento (px)',      'type' => 'number' ),
            array( 'name' => 'card_padding',    'label' => 'Padding interno (px)',     'type' => 'number' ),
            array( 'name' => 'image_height',    'label' => 'Altura da imagem (px)',    'type' => 'number' ),
            array( 'name' => 'image_fit',       'label' => 'Ajuste da imagem',         'type' => 'select', 'options' => array( 'cover' => 'Cover', 'contain' => 'Contain' ) ),
            array( 'name' => 'image_radius',    'label' => 'Arred. imagem (px)',       'type' => 'number' ),
            array( 'name' => 'icon_size',       'label' => 'Tam. ícone (px)',          'type' => 'number' ),
            array( 'name' => 'icon_color',      'label' => 'Cor do ícone',             'type' => 'color' ),
            array( 'name' => 'icon_bg',         'label' => 'Fundo do ícone',           'type' => 'color' ),
            array( 'name' => 'icon_radius',     'label' => 'Arred. ícone (px)',        'type' => 'number' ),
            array( 'name' => 'title_color',     'label' => 'Cor do título',            'type' => 'color' ),
            array( 'name' => 'title_size',      'label' => 'Tam. título (px)',         'type' => 'number' ),
            array( 'name' => 'title_weight',    'label' => 'Peso do título',           'type' => 'select', 'options' => $this->weight_options() ),
            array( 'name' => 'text_color',      'label' => 'Cor do texto',             'type' => 'color' ),
            array( 'name' => 'text_size',       'label' => 'Tam. texto (px)',          'type' => 'number' ),
            array( 'name' => 'text_weight',     'label' => 'Peso do texto',            'type' => 'select', 'options' => $this->weight_options() ),
            array( 'name' => 'text_align',      'label' => 'Alinhamento',              'type' => 'select', 'options' => array( 'left' => 'Esquerda', 'center' => 'Centro', 'right' => 'Direita' ) ),
        );
    }

    public function render( $settings, $children_html = '' ) {
        $s = wp_parse_args( $settings, $this->defaults() );

        $slides_desktop = $this->clamp_slides_per_view(
            isset( $s['slides_per_view_desktop'] ) ? $s['slides_per_view_desktop'] : ( isset( $s['slides_per_view'] ) ? $s['slides_per_view'] : 3 ),
            3
        );
        $slides_tablet  = $this->clamp_slides_per_view( isset( $s['slides_per_view_tablet'] ) ? $s['slides_per_view_tablet'] : 2, 2 );
        $slides_mobile  = $this->clamp_slides_per_view( isset( $s['slides_per_view_mobile'] ) ? $s['slides_per_view_mobile'] : 1, 1 );
        $slides_min     = min( $slides_desktop, $slides_tablet, $slides_mobile );
        $gap             = max( 0, intval( $s['gap'] ) );
        $autoplay        = ! empty( $s['autoplay'] ) && '1' === (string) $s['autoplay'];
        $autoplay_speed  = max( 2000, intval( $s['autoplay_speed'] ) );
        $show_arrows     = ! isset( $s['show_arrows'] ) || '1' === (string) $s['show_arrows'];
        $show_dots       = ! isset( $s['show_dots'] ) || '1' === (string) $s['show_dots'];
        $card_bg         = esc_attr( $s['card_bg'] );
        $card_radius     = max( 0, intval( $s['card_radius'] ) );
        $card_padding    = max( 0, intval( $s['card_padding'] ) );
        $image_height    = max( 0, intval( $s['image_height'] ) );
        $image_fit       = ( 'contain' === $s['image_fit'] ) ? 'contain' : 'cover';
        $image_radius    = max( 0, intval( $s['image_radius'] ) );
        $icon_size       = max( 16, intval( $s['icon_size'] ) );
        $icon_color      = esc_attr( $s['icon_color'] );
        $icon_bg         = esc_attr( $s['icon_bg'] );
        $icon_radius     = max( 0, intval( $s['icon_radius'] ) );
        $icon_radius_css = $icon_radius >= 50 ? '50%' : $icon_radius . 'px';
        $title_color     = esc_attr( $s['title_color'] );
        $title_size      = max( 10, intval( $s['title_size'] ) );
        $title_weight    = $this->sanitize_weight( $s['title_weight'] );
        $text_color      = esc_attr( $s['text_color'] );
        $text_size       = max( 10, intval( $s['text_size'] ) );
        $text_weight     = $this->sanitize_weight( $s['text_weight'] );
        $text_align      = in_array( $s['text_align'], array( 'left', 'center', 'right' ), true ) ? $s['text_align'] : 'center';

        $items = is_array( $s['items'] ) ? array_values( $s['items'] ) : array();
        if ( empty( $items ) ) {
            return '';
        }

        $wrap_style = '--ic-slides-desktop:' . $slides_desktop . ';'
            . '--ic-slides-tablet:' . $slides_tablet . ';'
            . '--ic-slides-mobile:' . $slides_mobile . ';'
            . '--ic-gap:' . $gap . 'px;'
            . '--ic-card-bg:' . $card_bg . ';'
            . '--ic-card-radius:' . $card_radius . 'px;'
            . '--ic-card-padding:' . $card_padding . 'px;'
            . '--ic-img-h:' . $image_height . 'px;'
            . '--ic-img-fit:' . $image_fit . ';'
            . '--ic-img-radius:' . $image_radius . 'px;'
            . '--ic-icon-size:' . $icon_size . 'px;'
            . '--ic-icon-color:' . $icon_color . ';'
            . '--ic-icon-bg:' . $icon_bg . ';'
            . '--ic-icon-radius:' . $icon_radius_css . ';'
            . '--ic-title-color:' . $title_color . ';'
            . '--ic-title-size:' . $title_size . 'px;'
            . '--ic-title-weight:' . $title_weight . ';'
            . '--ic-text-color:' . $text_color . ';'
            . '--ic-text-size:' . $text_size . 'px;'
            . '--ic-text-weight:' . $text_weight . ';'
            . '--ic-text-align:' . $text_align . ';';

        $data_attrs = ' data-slides-desktop="' . esc_attr( $slides_desktop ) . '"'
            . ' data-slides-tablet="' . esc_attr( $slides_tablet ) . '"'
            . ' data-slides-mobile="' . esc_attr( $slides_mobile ) . '"'
            . ' data-autoplay="' . ( $autoplay ? '1' : '0' ) . '"'
            . ' data-autoplay-speed="' . esc_attr( $autoplay_speed ) . '"';

        $output  = '<div class="vitrine-el-itemcarousel"' . $data_attrs . ' style="' . esc_attr( $wrap_style ) . '">';
        $output .= '<div class="vitrine-ic-viewport">';
        $output .= '<div class="vitrine-ic-track">';

        foreach ( $items as $it ) {
            $output .= $this->render_slide( $it, $image_height, $image_fit, $image_radius, $icon_size, $icon_color );
        }

        $output .= '</div></div>';

        if ( $show_arrows && count( $items ) > $slides_min ) {
            $output .= '<button type="button" class="vitrine-ic-arrow vitrine-ic-arrow--prev" aria-label="Anterior"><span class="dashicons dashicons-arrow-left-alt2"></span></button>';
            $output .= '<button type="button" class="vitrine-ic-arrow vitrine-ic-arrow--next" aria-label="Próximo"><span class="dashicons dashicons-arrow-right-alt2"></span></button>';
        }

        if ( $show_dots && count( $items ) > $slides_min ) {
            $output .= '<div class="vitrine-ic-dots" role="tablist">';
            $max_index = max( 0, count( $items ) - $slides_min );
            for ( $d = 0; $d <= $max_index; $d++ ) {
                $active = 0 === $d ? ' is-active' : '';
                $output .= '<button type="button" class="vitrine-ic-dot' . $active . '" data-index="' . $d . '" aria-label="Slide ' . ( $d + 1 ) . '"></button>';
            }
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    private function render_slide( $it, $image_height, $image_fit, $image_radius, $icon_size, $icon_color ) {
        $item_type = ( isset( $it['item_type'] ) && 'icon' === $it['item_type'] ) ? 'icon' : 'image';
        $image     = ! empty( $it['image'] ) ? esc_url( $it['image'] ) : '';
        $icon      = ! empty( $it['icon'] ) ? $it['icon'] : '';
        $title     = isset( $it['title'] ) ? esc_html( $it['title'] ) : '';
        $text      = isset( $it['text'] ) ? wp_kses_post( $it['text'] ) : '';
        $link      = ! empty( $it['link'] ) ? esc_url( $it['link'] ) : '';
        $new_tab   = ! empty( $it['link_new_tab'] ) && '1' === (string) $it['link_new_tab'];

        $inner = '';

        if ( 'icon' === $item_type && $icon ) {
            $inner .= '<div class="vitrine-ic-slide-icon-wrap"><span class="vitrine-ic-slide-icon">'
                . $this->render_icon( $icon, $icon_size, $icon_color ) . '</span></div>';
        } elseif ( 'image' === $item_type && $image && $image_height > 0 ) {
            $img_style = 'object-fit:' . $image_fit . ';border-radius:' . intval( $image_radius ) . 'px;';
            $inner    .= '<div class="vitrine-ic-slide-media" style="height:' . intval( $image_height ) . 'px;">'
                . '<img src="' . $image . '" alt="" class="vitrine-ic-slide-img" style="' . esc_attr( $img_style ) . '" />'
                . '</div>';
        }

        $inner .= '<div class="vitrine-ic-slide-body">';
        if ( $title ) {
            $inner .= '<h3 class="vitrine-ic-slide-title">' . $title . '</h3>';
        }
        if ( $text ) {
            $inner .= '<div class="vitrine-ic-slide-text">' . $text . '</div>';
        }
        $inner .= '</div>';

        if ( ! $title && ! $text
            && ( ( 'image' === $item_type && ! $image ) || ( 'icon' === $item_type && ! $icon ) ) ) {
            return '';
        }

        $slide_inner = '<div class="vitrine-ic-slide-inner">' . $inner . '</div>';

        if ( $link ) {
            $target = $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
            $slide_inner = '<a href="' . $link . '" class="vitrine-ic-slide-link"' . $target . '>' . $inner . '</a>';
        }

        return '<div class="vitrine-ic-slide vitrine-ic-slide--' . esc_attr( $item_type ) . '">' . $slide_inner . '</div>';
    }

    private function render_icon( $icon, $icon_size, $icon_color = '' ) {
        if ( ! $icon ) {
            return '';
        }
        $color_style = $icon_color ? 'color:' . esc_attr( $icon_color ) . ';' : '';
        if ( strpos( $icon, 'dashicons-' ) === 0 ) {
            return '<span class="dashicons ' . esc_attr( $icon ) . '" style="font-size:' . intval( $icon_size ) . 'px;width:' . intval( $icon_size ) . 'px;height:' . intval( $icon_size ) . 'px;' . $color_style . '"></span>';
        }
        if ( preg_match( '/^fa[srlbd]?\s/', $icon ) ) {
            return '<i class="' . esc_attr( $icon ) . '" style="font-size:' . intval( $icon_size ) . 'px;' . $color_style . '"></i>';
        }
        return '<img src="' . esc_url( $icon ) . '" alt="" style="width:' . intval( $icon_size ) . 'px;height:' . intval( $icon_size ) . 'px;object-fit:contain;" />';
    }

    private function weight_options() {
        return array(
            '300' => 'Leve (300)',
            '400' => 'Normal (400)',
            '500' => 'Médio (500)',
            '600' => 'Semi-negrito (600)',
            '700' => 'Negrito (700)',
            '800' => 'Extra-negrito (800)',
        );
    }

    private function sanitize_weight( $weight ) {
        $allowed = array( '300', '400', '500', '600', '700', '800' );
        $weight  = (string) $weight;
        return in_array( $weight, $allowed, true ) ? $weight : '400';
    }

    private function clamp_slides_per_view( $value, $fallback = 1 ) {
        $n = intval( $value );
        if ( $n < 1 ) {
            $n = intval( $fallback );
        }
        return max( 1, min( 4, $n ) );
    }
}

Vitrine_Element_Registry::register( new Vitrine_Element_Itemcarousel() );
