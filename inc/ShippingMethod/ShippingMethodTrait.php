<?php

namespace VNShipping\ShippingMethod;

use InvalidArgumentException;
use VNShipping\Courier\Exception\RequestException;
use VNShipping\Courier\Factory;

trait ShippingMethodTrait {
	/**
	 * @var \VNShipping\Courier\AbstractCourier|null
	 */
	protected $courier;

	/**
	 * @return \VNShipping\Courier\AbstractCourier
	 */
	public function get_courier() {
		if ( ! $this->courier ) {
			$this->courier = Factory::createFromShippingMethod( $this );
		}

		return $this->courier;
	}

	/**
	 * @return \VNShipping\Courier\Response\CollectionResponseData|null
	 */
	public function get_stores() {
		try {
			if ( isset( $_REQUEST['invalidate'] ) && 1 === (int) $_REQUEST['invalidate'] ) {
				return $this->get_courier()->get_stores( [] );
			}

			return $this->get_cache_value(
				'stores',
				function () {
					return $this->get_courier()->get_stores( [] );
				},
				30
			);
		} catch ( RequestException | InvalidArgumentException $e ) {
			return null;
		}
	}

	/**
	 * @param string   $key
	 * @param callable $callback
	 * @param int      $lifetime
	 * @return mixed
	 */
	protected function get_cache_value( $key, $callback, $lifetime = 0 ) {
		$transient_key = $this->get_field_key( $key );

		if ( $value = get_transient( $transient_key ) ) {
			return $value;
		}

		$value = $callback();

		if ( $value !== null ) {
			set_transient( $transient_key, $value, $lifetime * MINUTE_IN_SECONDS );
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process_admin_options() {
		if ( $this->instance_id ) {
			return parent::process_admin_options();
		}

		if ( empty( $this->settings ) ) {
			$this->init_settings();
		}

		$olds = $this->settings;
		$saved = parent::process_admin_options();
		$dirty = array_diff( $olds, $this->settings );

		if ( $saved && ! empty( $dirty ) ) {
			$this->settings_changed( $dirty );

			if ( isset( $dirty['access_token'] ) ||
				 isset( $dirty['api_token'] ) ||
				 isset( $dirty['username'] ) ||
				 isset( $dirty['password'] )
			) {
				delete_transient( $this->get_field_key( 'stores' ) );

				$this->api_token_changed();
			}
		}

		return $saved;
	}

	/**
	 * Perform actions when setting changes.
	 *
	 * @param array $dirty
	 */
	protected function settings_changed( $dirty ) {
		// Sub-class implements this!
	}

	/**
	 * Perform actions when API token changed.
	 */
	protected function api_token_changed() {
		// Sub-class implements this!
	}

	/**
	 * Generate Select HTML.
	 *
	 * @param string $key  Field key.
	 * @param array  $data Field data.
	 * @return string
	 */
	public function generate_radio_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );

		$defaults = [
			'title' => '',
			'disabled' => false,
			'class' => '',
			'css' => '',
			'placeholder' => '',
			'type' => 'text',
			'desc_tip' => false,
			'description' => '',
			'custom_attributes' => [],
			'options' => [],
		];

		$data = wp_parse_args( $data, $defaults );

		$options = $data['options'] ?? [];
		if ( isset( $data['options_callback'] ) && is_callable( $data['options_callback'] ) ) {
			$options = call_user_func( $data['options_callback'], $key, $data );
		}

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo wp_kses_post( $data['title'] ); ?>
				<?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?>
			</th>

			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text">
						<span><?php echo wp_kses_post( $data['title'] ); ?></span>
					</legend>

					<?php foreach ( (array) $options as $option_key => $option_value ) : ?>
						<label for="<?php echo esc_attr( $field_key . '_' . $option_key ); ?>"
							   style="display: inline-block; margin-bottom: 1.5rem;">
							<input
								type="radio"
								id="<?php echo esc_attr( $field_key . '_' . $option_key ); ?>"
								name="<?php echo esc_attr( $field_key ); ?>"
								value="<?php echo esc_attr( $option_key ); ?>"
								class="radio <?php echo esc_attr( $data['class'] ); ?>"
								style="<?php echo esc_attr( $data['css'] ); ?>"
								<?php disabled( $data['disabled'], true ); ?>
								<?php checked( (string) $option_key, esc_attr( $this->get_option( $key ) ) ); ?>
								<?php echo $this->get_custom_attribute_html( $data ); // WPCS: XSS ok. ?>
							/>

							<span style="display: inline-block; vertical-align: top;">
								<?php echo wp_kses_post( $option_value ); ?>
							</span>
						</label> <br />
					<?php endforeach; ?>

					<?php echo $this->get_description_html( $data ); // WPCS: XSS ok. ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}
}
