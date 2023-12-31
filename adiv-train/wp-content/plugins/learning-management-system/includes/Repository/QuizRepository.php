<?php
/**
 * Quiz repository.
 *
 * @since 1.0.0
 *
 * @package Repository
 */

namespace Masteriyo\Repository;

use Masteriyo\Database\Model;
use Masteriyo\Models\Quiz;

/**
 * Quiz repository class.
 *
 * @since 1.0.0
 */
class QuizRepository extends AbstractRepository implements RepositoryInterface {

	/**
	 * Data stored in meta keys, but not considered "meta".
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array(
		'course_id'                  => '_course_id',
		'pass_mark'                  => '_pass_mark',
		'full_mark'                  => '_full_mark',
		'duration'                   => '_duration',
		'attempts_allowed'           => '_attempts_allowed',
		'questions_display_per_page' => '_questions_display_per_page',
	);

	/**
	 * Create a quiz in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param Masteriyo\Models\Quiz $quiz Quiz object.
	 */
	public function create( Model &$quiz ) {
		if ( ! $quiz->get_date_created( 'edit' ) ) {
			$quiz->set_date_created( current_time( 'mysql', true ) );
		}

		// Author of the quiz should be same as that of course, because quizzes are children of courses.
		if ( $quiz->get_course_id() ) {
			$quiz->set_author_id( masteriyo_get_course_author_id( $quiz->get_course_id() ) );
		}

		// Set the author of the quiz to the current user id, if the quiz doesn't have a author.
		if ( empty( $quiz->get_course_id() ) ) {
			$quiz->set_author_id( get_current_user_id() );
		}

		$id = wp_insert_post(
			apply_filters(
				'masteriyo_new_quiz_data',
				array(
					'post_type'     => 'mto-quiz',
					'post_status'   => $quiz->get_status() ? $quiz->get_status() : 'publish',
					'post_author'   => $quiz->get_author_id( 'edit' ),
					'post_title'    => $quiz->get_name() ? $quiz->get_name() : __( 'Quiz', 'masteriyo' ),
					'post_content'  => $quiz->get_description(),
					'post_excerpt'  => $quiz->get_short_description(),
					'post_parent'   => $quiz->get_parent_id(),
					'ping_status'   => 'closed',
					'menu_order'    => $quiz->get_menu_order(),
					'post_date'     => $quiz->get_date_created( 'edit' ),
					'post_date_gmt' => $quiz->get_date_created( 'edit' ),
					'post_name'     => $quiz->get_slug( 'edit' ),
				),
				$quiz
			)
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$quiz->set_id( $id );
			$this->update_post_meta( $quiz, true );
			// TODO Invalidate caches.

			$quiz->save_meta_data();
			$quiz->apply_changes();

			do_action( 'masteriyo_new_quiz', $id, $quiz );
		}

	}

	/**
	 * Read a quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param Masteriyo\Models\Quiz $quiz Course object.
	 * @throws Exception If invalid quiz.
	 */
	public function read( Model &$quiz ) {
		$quiz_post = get_post( $quiz->get_id() );

		if ( ! $quiz->get_id() || ! $quiz_post || 'mto-quiz' !== $quiz_post->post_type ) {
			throw new \Exception( __( 'Invalid quiz.', 'masteriyo' ) );
		}

		$quiz->set_props(
			array(
				'name'              => $quiz_post->post_title,
				'slug'              => $quiz_post->post_name,
				'date_created'      => $quiz_post->post_date_gmt,
				'date_modified'     => $quiz_post->post_modified_gmt,
				'status'            => $quiz_post->post_status,
				'parent_id'         => $quiz_post->post_parent,
				'menu_order'        => $quiz_post->menu_order,
				'description'       => $quiz_post->post_content,
				'short_description' => $quiz_post->post_excerpt,
			)
		);

		$this->read_quiz_data( $quiz );
		$this->read_extra_data( $quiz );
		$quiz->set_object_read( true );

		do_action( 'masteriyo_quiz_read', $quiz->get_id(), $quiz );
	}

	/**
	 * Update a quiz in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param Masteriyo\Models\Quiz $quiz Quiz object.
	 *
	 * @return void
	 */
	public function update( Model &$quiz ) {
		$changes = $quiz->get_changes();

		$post_data_keys = array(
			'description',
			'short_description',
			'name',
			'parent_id',
			'status',
			'date_created',
			'date_modified',
			'slug',
		);

		// Only update the post when the post data changes.
		if ( array_intersect( $post_data_keys, array_keys( $changes ) ) ) {
			$post_data = array(
				'post_content' => $quiz->get_description( 'edit' ),
				'post_excerpt' => $quiz->get_short_description( 'edit' ),
				'post_title'   => $quiz->get_name( 'edit' ),
				'post_status'  => $quiz->get_status( 'edit' ) ? $quiz->get_status( 'edit' ) : 'publish',
				'post_name'    => $quiz->get_slug( 'edit' ),
				'post_parent'  => $quiz->get_parent_id(),
				'post_type'    => 'mto-quiz',
			);

			/**
			 * When updating this object, to prevent infinite loops, use $wpdb
			 * to update data, since wp_update_post spawns more calls to the
			 * save_post action.
			 *
			 * This ensures hooks are fired by either WP itself (admin screen save),
			 * or an update purely from CRUD.
			 */
			if ( doing_action( 'save_post' ) ) {
				// TODO Abstract the $wpdb WordPress class.
				$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $quiz->get_id() ) );
				clean_post_cache( $quiz->get_id() );
			} else {
				wp_update_post( array_merge( array( 'ID' => $quiz->get_id() ), $post_data ) );
			}
			$quiz->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
		} else { // Only update post modified time to record this save event.
			$GLOBALS['wpdb']->update(
				$GLOBALS['wpdb']->posts,
				array(
					'post_modified'     => current_time( 'mysql' ),
					'post_modified_gmt' => current_time( 'mysql', true ),
				),
				array(
					'ID' => $quiz->get_id(),
				)
			);
			clean_post_cache( $quiz->get_id() );
		}

		$this->update_post_meta( $quiz );

		$quiz->apply_changes();

		do_action( 'masteriyo_update_quiz', $quiz->get_id(), $quiz );
	}

	/**
	 * Delete a quiz from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param Masteriyo\Models\Quiz $quiz Quiz object.
	 * @param array $args   Array of args to pass.
	 */
	public function delete( Model &$quiz, $args = array() ) {
		$id          = $quiz->get_id();
		$object_type = $quiz->get_object_type();

		$args = array_merge(
			array(
				'force_delete' => false,
				'children'     => false,
			),
			$args
		);

		if ( ! $id ) {
			return;
		}

		if ( $args['children'] ) {
			$this->delete_questions( $quiz );
		}

		if ( $args['force_delete'] ) {
			do_action( 'masteriyo_before_delete_' . $object_type, $id, $quiz );
			wp_delete_post( $id, true );
			$quiz->set_id( 0 );
			do_action( 'masteriyo_after_delete_' . $object_type, $id, $quiz );
		} else {
			do_action( 'masteriyo_before_trash_' . $object_type, $id, $quiz );
			wp_trash_post( $id );
			$quiz->set_status( 'trash' );
			do_action( 'masteriyo_before_trash_' . $object_type, $id, $quiz );
		}
	}

	/**
	 * Read quiz data. Can be overridden by child classes to load other props.
	 *
	 * @since 1.0.0
	 *
	 * @param Masteriyo\Models\Quiz $quiz quiz object.
	 */
	protected function read_quiz_data( &$quiz ) {
		$id          = $quiz->get_id();
		$meta_values = $this->read_meta( $quiz );

		$set_props = array();

		$meta_values = array_reduce(
			$meta_values,
			function( $result, $meta_value ) {
				$result[ $meta_value->key ][] = $meta_value->value;
				return $result;
			},
			array()
		);

		foreach ( $this->internal_meta_keys as $prop => $meta_key ) {
			$meta_value         = isset( $meta_values[ $meta_key ][0] ) ? $meta_values[ $meta_key ][0] : null;
			$set_props[ $prop ] = maybe_unserialize( $meta_value ); // get_post_meta only unserializes single values.
		}

		$quiz->set_props( $set_props );
	}

	/**
	 * Read extra data associated with the quiz, like button text or quiz URL for external quizs.
	 *
	 * @since 1.0.0
	 *
	 * @param Masteriyo\Models\Quiz $quiz quiz object.
	 */
	protected function read_extra_data( &$quiz ) {
		$meta_values = $this->read_meta( $quiz );

		foreach ( $quiz->get_extra_data_keys() as $key ) {
			$function = 'set_' . $key;
			if ( is_callable( array( $quiz, $function ) )
				&& isset( $meta_values[ '_' . $key ] ) ) {
				$quiz->{$function}( $meta_values[ '_' . $key ] );
			}
		}
	}

	/**
	 * Fetch quizzes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query_vars Query vars.
	 * @return Masteriyo\Models\Quiz[]
	 */
	public function query( $query_vars ) {
		$args = $this->get_wp_query_args( $query_vars );

		if ( ! empty( $args['errors'] ) ) {
			$query = (object) array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			);
		} else {
			$query = new \WP_Query( $args );
		}

		if ( isset( $query_vars['return'] ) && 'objects' === $query_vars['return'] && ! empty( $query->posts ) ) {
			// Prime caches before grabbing objects.
			update_post_caches( $query->posts, array( 'mto-quiz' ) );
		}

		$quizzes = ( isset( $query_vars['return'] ) && 'ids' === $query_vars['return'] ) ? $query->posts : array_filter( array_map( 'masteriyo_get_quiz', $query->posts ) );

		if ( isset( $query_vars['paginate'] ) && $query_vars['paginate'] ) {
			return (object) array(
				'quizes'        => $quizzes,
				'total'         => $query->found_posts,
				'max_num_pages' => $query->max_num_pages,
			);
		}

		return $quizzes;
	}

	/**
	 * Get valid WP_Query args from a QuizQuery's query variables.
	 *
	 * @since 1.0.0
	 * @param array $query_vars Query vars from a QuizQuery.
	 * @return array
	 */
	protected function get_wp_query_args( $query_vars ) {
		// Map query vars to ones that get_wp_query_args or WP_Query recognize.
		$key_mapping = array(
			'status'    => 'post_status',
			'page'      => 'paged',
			'parent_id' => 'post_parent',
		);

		foreach ( $key_mapping as $query_key => $db_key ) {
			if ( isset( $query_vars[ $query_key ] ) ) {
				$query_vars[ $db_key ] = $query_vars[ $query_key ];
				unset( $query_vars[ $query_key ] );
			}
		}

		$query_vars['post_type'] = 'mto-quiz';

		$wp_query_args = parent::get_wp_query_args( $query_vars );

		if ( ! isset( $wp_query_args['date_query'] ) ) {
			$wp_query_args['date_query'] = array();
		}
		if ( ! isset( $wp_query_args['meta_query'] ) ) {
			$wp_query_args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		// Handle date queries.
		$date_queries = array(
			'date_created'  => 'post_date',
			'date_modified' => 'post_modified',
		);
		foreach ( $date_queries as $query_var_key => $db_key ) {
			if ( isset( $query_vars[ $query_var_key ] ) && '' !== $query_vars[ $query_var_key ] ) {

				// Remove any existing meta queries for the same keys to prevent conflicts.
				$existing_queries = wp_list_pluck( $wp_query_args['meta_query'], 'key', true );
				foreach ( $existing_queries as $query_index => $query_contents ) {
					unset( $wp_query_args['meta_query'][ $query_index ] );
				}

				$wp_query_args = $this->parse_date_for_wp_query( $query_vars[ $query_var_key ], $db_key, $wp_query_args );
			}
		}

		// Handle paginate.
		if ( ! isset( $query_vars['paginate'] ) || ! $query_vars['paginate'] ) {
			$wp_query_args['no_found_rows'] = true;
		}

		// Handle orderby.
		if ( isset( $query_vars['orderby'] ) && 'include' === $query_vars['orderby'] ) {
			$wp_query_args['orderby'] = 'post__in';
		}

		return apply_filters( 'masteriyo_quiz_data_store_cpt_get_quizes_query', $wp_query_args, $query_vars, $this );
	}

	/**
	 * Delete questions under the quiz.
	 *
	 * @since 1.3.10
	 *
	 * @param Masteriyo\Models\Quiz $quiz Quiz object.
	 */
	protected function delete_questions( $quiz ) {
		$questions = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => 'mto-question',
				'post_status' => 'any',
				'post_parent' => $quiz->get_id(),
				'fields'      => 'ids',
			)
		);

		foreach ( $questions as $question ) {
			wp_delete_post( $question, true );
		}
	}
}
