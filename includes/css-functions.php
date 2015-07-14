<?php

if ( ! function_exists( 'hex2rgb' ) ) {
	function hex2rgb($hex) {
		$hex = str_replace("#", "", $hex);

		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		$rgb = array($r, $g, $b);
		//return implode(",", $rgb); // returns the rgb values separated by commas
		return $rgb; // returns an array with the rgb values
	}
}

function popmake_get_rgba_value( $hex, $opacity = 100 ) {
	return 'rgba( '. implode( ', ', hex2rgb( $hex ) ) . ', ' . ($opacity / 100) . ' )';
}

function popmake_get_border_style( $w, $s, $c ) {
	return "{$w}px {$s} {$c}";
}

function popmake_get_box_shadow_style( $h, $v, $b, $s, $c, $o = 100, $inset = 'no' ) {
	return "{$h}px {$v}px {$b}px {$s}px " . popmake_get_rgba_value( $c, $o ) . ($inset == 'yes' ? ' inset' : '');
}

function popmake_get_text_shadow_style( $h, $v, $b, $c, $o = 100 ) {
	return "{$h}px {$v}px {$b}px " . popmake_get_rgba_value( $c, $o );
}

function popmake_get_font_style( $s, $w, $lh, $f, $st = null, $v = null ) {
	return str_replace('  ', ' ', trim( "$st $v $w {$s}px/{$lh}px \"$f\"" ) );
}

function popmake_generate_theme_styles( $popup_theme_id ) {

	$styles = array();

	$theme = popmake_get_popup_theme_data_attr( $popup_theme_id );

	extract( $theme );

	$styles['overlay'] = array(
		'background-color' => popmake_get_rgba_value( $overlay['background_color'], $overlay['background_opacity'] ),
	);

	$styles['container'] = array(
		'padding' => "{$container['padding']}px",
		'background-color' => popmake_get_rgba_value( $container['background_color'], $container['background_opacity'] ),
		'border-radius' => "{$container['border_radius']}px",
		'border' => popmake_get_border_style( $container['border_width'], $container['border_style'], $container['border_color'] ),
		'box-shadow' => popmake_get_box_shadow_style( $container['boxshadow_horizontal'], $container['boxshadow_vertical'], $container['boxshadow_blur'], $container['boxshadow_spread'], $container['boxshadow_color'], $container['boxshadow_opacity'], $container['boxshadow_inset'] ),
	);

	$styles['title'] = array(
		'color' => $title['font_color'],
		'text-align' => $title['text_align'],
		'text-shadow' => popmake_get_text_shadow_style( $title['textshadow_horizontal'], $title['textshadow_vertical'], $title['textshadow_blur'], $title['textshadow_color'], $title['textshadow_opacity'] ),
		'font' => popmake_get_font_style( $title['font_size'], $title['font_weight'], $title['line_height'], $title['font_family'], $title['font_style'] )
	);

	$styles['content'] = array(
		'color' => $content['font_color'],
		'font-family' => $content['font_family'],
		'font-weight' => $content['font_weight'],
		'font-style'  => $content['font_style'],
	);

	$styles['close'] = array(
		'height' => empty( $close['height'] ) ? 'auto' : "{$close['height']}px",
		'width' => empty( $close['width'] ) ? 'auto' : "{$close['width']}px",
		'left' => 'auto',
        'right' => 'auto',
        'bottom' => 'auto',
        'top' => 'auto',
		'padding' => "{$close['padding']}px",
		'background-color' => popmake_get_rgba_value( $close['background_color'], $close['background_opacity'] ),
		'color' => $close['font_color'],
		'font' => popmake_get_font_style( $close['font_size'], $close['font_weight'], $close['line_height'], $close['font_family'], $close['font_style'] ),
		'border' => popmake_get_border_style( $close['border_width'], $close['border_style'], $close['border_color'] ),
		'border-radius' => "{$close['border_radius']}px",
		'box-shadow' => popmake_get_box_shadow_style( $close['boxshadow_horizontal'], $close['boxshadow_vertical'], $close['boxshadow_blur'], $close['boxshadow_spread'], $close['boxshadow_color'], $close['boxshadow_opacity'], $close['boxshadow_inset'] ),
		'text-shadow' => popmake_get_text_shadow_style( $close['textshadow_horizontal'], $close['textshadow_vertical'], $close['textshadow_blur'], $close['textshadow_color'], $close['textshadow_opacity'] ),
	);

	switch ( $close['location'] ) {
		case "topleft":
			$styles['close']['top'] = "{$close['position_top']}px";
			$styles['close']['left'] = "{$close['position_left']}px";
			break;
		case "topright":
			$styles['close']['top'] = "{$close['position_top']}px";
			$styles['close']['right'] = "{$close['position_right']}px";
            break;
		case "bottomleft":
			$styles['close']['bottom'] = "{$close['position_bottom']}px";
			$styles['close']['left'] = "{$close['position_left']}px";
            break;
        case "bottomright":
	        $styles['close']['bottom'] = "{$close['position_bottom']}px";
	        $styles['close']['right'] = "{$close['position_right']}px";
            break;
    }

	return apply_filters( 'popmake_generate_theme_styles', $styles, $popup_theme_id );
}

function popmake_render_theme_styles( $popup_theme_id ) {
	$styles = '';
	foreach ( popmake_generate_theme_styles( $popup_theme_id ) as $element => $rules ) {
		switch ( $element ) {
			case 'overlay':
				$rule = ".popmake-overlay.theme-{$popup_theme_id}";
				break;
			case 'container':
				$rule = ".popmake.theme-{$popup_theme_id}";
				break;
			default:
				$rule = ".popmake.theme-{$popup_theme_id} .popmake-{$element}";
				break;
		}

		$rule_set = $sep = '';
		foreach( $rules as $key => $value ) {
			if ( ! empty( $value ) ) {
				$rule_set .= $sep . $key . ': ' . $value;
				$sep = '; ';
			}
		}

		$styles .= "$rule { $rule_set } \r\n";
	}

	return $styles;
}