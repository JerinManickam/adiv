<?php
/**
 * The Template for displaying Categories for single course
 *
 * This template can be overridden by copying it to yourtheme/masteriyo/single-course/categories.php.
 *
 * HOWEVER, on occasion Masteriyo will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @package Masteriyo\Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( empty( $course->get_categories() ) ) {
	return;
}

do_action( 'masteriyo_before_single_course_categories' );

?>
<div class="masteriyo-course--content__category">
	<?php foreach ( $course->get_categories() as $category ) : ?>
		<a href="<?php echo esc_attr( $category->get_permalink() ); ?>" class="masteriyo-course--content__category-items masteriyo-tag">
			<?php echo esc_html( $category->get_name() ); ?>
		</a>
	<?php endforeach; ?>
</div>
<?php

do_action( 'masteriyo_after_single_course_categories' );
