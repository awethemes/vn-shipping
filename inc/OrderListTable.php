<?php

namespace VNShipping;

use VNShipping\Courier\Couriers;
use VNShipping\Courier\ShippingStatus;
use VNShipping\Traits\SingletonTrait;

class OrderListTable {
	use SingletonTrait;

	/**
	 * @var ShippingData[]
	 */
	protected $shipping_data;

	/**
	 * Init the list table.
	 */
	public function init() {
		add_action( 'current_screen', [ $this, 'setup_screen' ], 100 );
	}

	/**
	 * Run the hooks in edit-shop_order screen.
	 */
	public function setup_screen() {
		if ( $this->is_order_list_screen() ) {
			add_action( 'parse_query', [ $this, 'set_query_vars' ] );
			add_filter( 'posts_clauses', [ $this, 'query_posts_clauses' ], 10, 2 );
			add_action( 'restrict_manage_posts', [ $this, 'show_filters' ] );

			add_filter( 'the_posts', [ $this, 'eager_load_shipping_data' ], 10, 2 );

			add_filter( 'manage_edit-shop_order_columns', [ $this, 'register_column' ] );
			add_action( 'manage_shop_order_posts_custom_column', [ $this, 'output_column' ], 10, 2 );
		}
	}

	/**
	 * @return bool
	 */
	public function is_order_list_screen() {
		$screen = get_current_screen();

		return $screen && 'edit-shop_order' === $screen->id;
	}

	/**
	 * @param array $columns
	 */
	public function register_column( $columns ) {
		$add_column = [
			'vn_shipping_info' => esc_html__( 'Shipping', 'vn-shipping' ),
		];

		$offset = array_search( 'order_total', array_keys( $columns ), true );

		if ( $offset === false ) {
			$columns = array_merge( $columns, $add_column );
		} else {
			$columns = array_merge(
				array_slice( $columns, 0, $offset ),
				$add_column,
				array_slice( $columns, $offset, null )
			);
		}

		return $columns;
	}

	/**
	 * @param string $colname
	 * @param int    $post_Id
	 * @return void
	 */
	public function output_column( $colname, $post_Id ) {
		if ( $colname !== 'vn_shipping_info' ) {
			return;
		}

		$shipping = $this->shipping_data[ $post_Id ] ?? null;
		if ( $shipping === null ) {
			return;
		}

		echo sprintf(
			'<strong class="order-shipping-courier">%s</strong> - ',
			esc_html( $shipping->get_courier_name() )
		);

		echo sprintf(
			'<strong class="order-tracking-number">%s</strong> - ',
			esc_html( $shipping->tracking_number )
		);

		echo sprintf(
			'<span class="order-status is-%s">%s</span>',
			esc_attr( $shipping->status ),
			esc_html( $shipping->get_status_name() )
		);
	}

	/**
	 * @param \WP_Post[] $posts
	 * @param \WP_Query  $wp_query
	 */
	public function eager_load_shipping_data( $posts, $wp_query ) {
		global $wpdb;

		if ( ! $this->is_order_list_screen() || ! $wp_query->is_main_query() || empty( $posts ) ) {
			return $posts;
		}

		$ids = array_map( 'absint', wp_list_pluck( $posts, 'ID' ) );

		$items = $wpdb->get_results(
			"SELECT * FROM `{$wpdb->prefix}vn_shipping_data` WHERE `order_id` IN (" . implode( ',', $ids ) . ");"
		);

		// Mapping by order ID.
		foreach ( $items as $item ) {
			$this->shipping_data[ $item->order_id ] = new ShippingData( $item );
		}

		return $posts;
	}

	/**
	 * Show the filters.
	 */
	public function show_filters() {
		global $typenow, $wpdb;

		if ( 'shop_order' !== $typenow ) {
			return;
		}

		$current_courier = wc_clean( wp_unslash( $_GET['_shipping_courier'] ?? null ) );
		$current_status = wc_clean( wp_unslash( $_GET['_shipping_status'] ?? null ) );

		$statuses = wp_list_pluck(
			$wpdb->get_results( "SELECT DISTINCT status FROM `{$wpdb->prefix}vn_shipping_data`" ),
			'status'
		);

		?>
		<select name="_shipping_courier">
			<option value=""><?php echo esc_html__( 'Shipping courier', 'vn-shipping' ); ?></option>

			<?php foreach ( Couriers::getCouriers() as $courier ) : ?>
				<option value="<?php echo esc_attr( $courier['id'] ) ?>" <?php selected( $courier['id'],
					$current_courier ); ?>>
					<?php echo esc_html( $courier['name'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php

		if ( ! empty( $statuses ) ) { ?>
			<select name="_shipping_status">
				<option value=""><?php echo esc_html__( 'Shipping status', 'vn-shipping' ); ?></option>

				<?php foreach ( $statuses as $status ) : ?>
					<option value="<?php echo esc_attr( $status ) ?>" <?php selected( $status, $current_status ); ?>>
						<?php echo esc_html( ShippingStatus::get_status_name( $status ) ) ?: $status ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php
		}
	}

	/**
	 * @param \WP_Query $wp Query object.
	 */
	public function set_query_vars( $wp ) {
		if ( ! $this->is_order_list_screen() ) {
			return;
		}

		if ( ! empty( $_GET['_shipping_status'] ) ) {
			$wp->query_vars['_shipping_status'] = wc_clean( wp_unslash( $_GET['_shipping_status'] ) );
		}

		if ( ! empty( $_GET['_shipping_courier'] ) ) {
			$wp->query_vars['_shipping_courier'] = wc_clean( wp_unslash( $_GET['_shipping_courier'] ) );
		}
	}

	/**
	 * @param array     $clauses
	 * @param \WP_Query $query
	 * @return array
	 */
	public function query_posts_clauses( $clauses, $query ) {
		global $wpdb;

		if ( $query->query_vars['post_type'] !== 'shop_order' ) {
			return $clauses;
		}

		if ( ! empty( $query->query_vars['_shipping_status'] ) || ! empty( $query->query_vars['_shipping_courier'] ) ) {
			if ( strpos( $clauses['join'], 'vn_shipping_data' ) === false ) {
				$join_clause = " INNER JOIN `{$wpdb->prefix}vn_shipping_data` vn_shipping_data ON $wpdb->posts.ID = vn_shipping_data.order_id ";

				if ( ! empty( $query->query_vars['_shipping_status'] ) ) {
					$join_clause .= $wpdb->prepare(
						' AND vn_shipping_data.status = %s ',
						$query->query_vars['_shipping_status']
					);
				}

				if ( ! empty( $query->query_vars['_shipping_courier'] ) ) {
					$join_clause .= $wpdb->prepare(
						' AND vn_shipping_data.courier = %s ',
						$query->query_vars['_shipping_courier']
					);
				}

				$clauses['join'] .= $join_clause;
			}
		}

		return $clauses;
	}
}
