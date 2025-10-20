<?php


if ( ! function_exists( 'wd_get_box_shadow_control_attrs' ) ) {
	function wd_get_box_shadow_control_attrs( $attr, $attrs_prefix = '' ) {
		$attr->add_attr(
			array(
				'position'   => array(
					'type'    => 'string',
					'default' => 'outline',
				),
				'horizontal' => array(
					'type'       => 'number',
					'default'    => 0,
					'responsive' => true,
				),
				'vertical'   => array(
					'type'       => 'number',
					'default'    => 0,
					'responsive' => true,
				),
				'blur'       => array(
					'type'       => 'number',
					'default'    => 10,
					'responsive' => true,
				),
				'spread'     => array(
					'type'       => 'number',
					'default'    => 0,
					'responsive' => true,
				),

			),
			$attrs_prefix
		);

		$attr->add_attr( wd_get_color_control_attrs( 'color' ), $attrs_prefix );
	}
}
