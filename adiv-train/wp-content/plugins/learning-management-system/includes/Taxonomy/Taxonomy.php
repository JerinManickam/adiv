<?php
/**
 * Abstract taxonomy class.
 */

namespace Masteriyo\Taxonomy;

abstract class Taxonomy {
	/**
	 * Taxonomy.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * Post type the taxonomy belongs to.
	 *
	 * @since 1.0.0
	 */
	protected $post_type = 'post';

	/**
	 * Register taxonomy.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		register_taxonomy(
			$this->taxonomy,
			apply_filters( "masteriyo_taxonomy_objects_{$this->taxonomy}", array( $this->post_type ) ),
			$this->get_args()
		);
	}

	/**
	 * Get all labels.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_labels() {
		$args = $this->get_args();

		$labels = isset( $args['labels'] ) ? $args['labels'] : array();

		return apply_filters( "masteriyo_taxonomy_{$taxonomy}_labels", $labels );
	}

	/**
	 * Get label.
	 *
	 * @since 1.0.0
	 *
	 * @param string $label Label. (e.g. name, singular_name, menu_name, etc )
	 *
	 * @return mixed|null
	 */
	public function get_label( $label ) {
		$labels = $this->get_labels();

		$value = isset( $labels[ $label ] ) ? $value = $labels[ $label ] : '';

		return apply_filters( "masteriyo_taxonomy_{$taxonomy}_label", $value, $label );
	}

	/**
	 * Set label.
	 *
	 * @since 1.0.0
	 *
	 * @param string $label Label. (e.g. name, singular_name, menu_name, etc )
	 * @param string $value Label text/value.
	 * @param bool $strict  Strict check the label.(Default: true)
	 *
	 * @return Masteriyo\Masteriyo\Taxonomy
	 */
	public function set_label( $label, $value, $strict = true ) {
		$labels = $this->get_labels();

		if ( $strict && ! isset( $labels[ $label ] ) ) {
			throw new \Exception( 'Invalid label name.' );
		}

		$labels[ $label ] = $value;
		$args['labels']   = $labels;
	}



	/**
	 * Get arg.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Arg name. (e.g. label, args, menu_position, etc )
	 *
	 * @return mixed|null
	 */
	public function get_arg( $name ) {
		$args = $this->get_args();

		$value = ( isset( $arg[ $name ] ) ) ? $arg[ $name ] : null;

		return apply_filters( "masteriyo_taxonomy_{$taxonomy}_arg", $value, $name );
	}


	/**
	 * Get taxonomy args which includes labels and other args.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_args() {
		return new \WP_Error(
			'invalid-method',
			// translators: %s: Class method name.
			sprintf( __( "Method '%s' not implemented. Must be overridden in subclass.", 'masteriyo' ), __METHOD__ ),
			array( 'status' => 405 )
		);
	}
}
