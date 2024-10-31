<?php namespace rontar\blog_retargeting;

final class Class_Admin {
	/** Singleton *************************************************************/
	public static function instance() {

		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been ran previously
		if ( null === $instance ) {
			$class_name = get_class();
			$instance = new $class_name;
			$instance->initialise();
		}

		// Always return the instance
		return $instance;
	}

	/** Magic Methods *********************************************************/
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	private $plugin;

	private function initialise() {
		$this->setup_globals();
		$this->load_dependencies();
		$this->define_hooks();
	}

	private function setup_globals() {
		$this->plugin = Class_Plugin::instance();
	}

	private function load_dependencies() {}

	private function define_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );

		add_action( 'init', array( $this, 'add_feed' ) );
		add_filter( 'plugin_action_links', array( $this, 'plugin_add_settings_link' ), 10, 2);
	}

	public function plugin_add_settings_link( $links, $plugin_file ) {
		if ( 'rontar-blog-retargeting/main.php' == $plugin_file ) {
			$settings_link = '<a href="edit.php?page=rontar_blog_retargeting">' . __( 'Settings' ) . '</a>';
			array_push( $links, $settings_link );
		}
		return $links;
	}	
	
	public function add_admin_menu() {
		add_submenu_page(
			'edit.php',
			'Rontar Blog Retargeting',
			'Rontar Blog Retargeting',
			'manage_options',
			$this->plugin->name,
			array( $this, 'options_page' )
		);
	}

	public function options_page() {
		echo '<form class="form-table" action="options.php" method="post">';
		echo '<br />Get started by using our <a href="https://b.rontar.com/Signup?utm_source=wordpress&utm_medium=listing" target="_blank">sign up wizard</a>. The wizard will provide you with your Advertiser ID, Product feed ID, and Audience ID.<br />';
			settings_fields( $this->plugin->name );
			do_settings_sections( $this->plugin->name );
			submit_button();

		echo '</form>';
	}

	public function settings_init() {
		$options = get_option( $this->plugin->name . '_settings' );

		register_setting( $this->plugin->name, $this->plugin->name . '_settings', array( $this, 'sanitize_setting' ) );

		add_settings_section(
			$this->plugin->name . '_section',
			__( 'Rontar Blog Retargeting', $this->plugin->name ),
			null,
			$this->plugin->name
		);

		add_settings_field(
			'enabled',
			__( 'Enable', $this->plugin->name ),
			array( $this, 'enabled_field_render' ),
			$this->plugin->name,
			$this->plugin->name . '_section',
			array(
				'value' => $options['enabled'],
			)
		);

		add_settings_field(
			'advertiser_id',
			__( 'Advertiser ID', $this->plugin->name ),
			array( $this, 'text_field_render' ),
			$this->plugin->name,
			$this->plugin->name . '_section',
			array(
				'id'    => 'advertiser_id',
				'label' => __( 'Advertiser ID', $this->plugin->name ),
				'value' => $options['advertiser_id'],
			)
		);

		add_settings_field(
			'feed_id',
			__( 'Feed ID', $this->plugin->name ),
			array( $this, 'text_field_render' ),
			$this->plugin->name,
			$this->plugin->name . '_section',
			array(
				'id'    => 'feed_id',
				'label' => __( 'Feed ID', $this->plugin->name ),
				'value' => $options['feed_id'],
			)
		);

		add_settings_field(
			'audience_id',
			__( 'Audience ID', $this->plugin->name ),
			array( $this, 'text_field_render' ),
			$this->plugin->name,
			$this->plugin->name . '_section',
			array(
				'id'    => 'audience_id',
				'label' => __( 'Audience ID', $this->plugin->name ),
				'value' => $options['audience_id'],
			)
		);

		add_settings_field(
			'feed_url',
			__( 'Blog Feed URL', $this->plugin->name ),
			array( $this, 'feed_url_field_render' ),
			$this->plugin->name,
			$this->plugin->name . '_section',
			array(
				'id'    => 'feed_url',
				'label' => __( 'Blog Feed URL', $this->plugin->name ),
				'value' => site_url( 'rontarblogfeed.xml' ),
			)
		);
	}

	public function enabled_field_render( $args ) {
		?>
			<fieldset>
				<legend class="screen-reader-text"><span><?php _e( 'Enable', $this->plugin->name ); ?></span></legend>
				<label for="rontar_blog_retargeting_enabled">
					<input
						class=""
						type="checkbox"
						name="<?php echo esc_attr( "{$this->plugin->name}_settings[enabled]" ); ?>"
						id="rontar_blog_retargeting_enabled"
						style=""
						value="yes"
						<?php checked( $args['value'], 'yes' ); ?> />
					<?php _e( 'Enable Rontar Blog Retargeting', $this->plugin->name ); ?></label><br>
			</fieldset>
		<?php
	}

	public function text_field_render( $args ) {
		?>
			<fieldset>
				<legend class="screen-reader-text"><span><?php echo $args['label']; ?></span></legend>
				<input
					class="input-text regular-input "
					type="text"
					name="<?php echo esc_attr( "{$this->plugin->name}_settings[{$args['id']}]" ); ?>"
					id="<?php echo esc_attr( "{$this->plugin->name}_{$args['id']}" ); ?>"
					style="min-width:50%;"
					value="<?php echo esc_attr( $args['value'] ); ?>"
					placeholder=""
					required="required"
					oninvalid="this.setCustomValidity('<?php echo esc_attr( $args['label'] ); ?> required')"
					oninput="setCustomValidity('')" />
			</fieldset>
		<?php
	}

	public function feed_url_field_render( $args ) {
		?>
			<fieldset>
				<legend class="screen-reader-text"><span><?php echo $args['label']; ?></span></legend>
				<input
					class="input-text regular-input "
					type="text"
					style="min-width:50%;"
					value="<?php echo esc_attr( $args['value'] ); ?>"
					placeholder=""
					readonly="readonly" />
			</fieldset>
		<?php
	}

	public function sanitize_setting( $settings ) {
		foreach ( $this->plugin->options as $key => $value ) {
			if ( ! isset( $settings[ $key ] ) ) {
				$settings[ $key ] = 'enabled' == $key ? 'no' : 'yes';
			}
		}

		return $settings;
	}

	public function add_feed() {
		add_feed( 'rontarblogfeed.xml', array( $this, 'render_feed' ) );

		foreach ( array_keys( get_option( 'rewrite_rules', array() ) ) as $rule ) {
			if ( stristr( $rule, 'rontarblogfeed.xml' ) ) {
				flush_rewrite_rules();
				break;
			}
		}
	}

	public function render_feed() {
		echo '<shop>';
			$this->render_currencies();
			$this->render_categories();
			$this->render_offers();
		echo '</shop>';
	}

	private function render_currencies() {
		echo '<currencies>';
		echo '<currency id="USD" rate="1"/>';
		echo '</currencies>';
	}

	private function render_categories() {
		$category_query = array(
			'hide_empty' => false,
			'orderby'    => 'term_id',
		);

		echo '<categories>';
			foreach ( get_categories( $category_query ) as $category ) {
				printf(
					'<category id="%d" %s>%s</category>',
					$category->cat_ID,
					$category->parent == 0 ? '' : ( 'parentId="' . $category->parent . '"' ),
					wp_strip_all_tags( $category->name )
				);
			}
		echo '</categories>';
	}

	private function render_offers() {
		global $wpdb;

		echo '<offers>';
//			$post_query = "SELECT ID, post_title, post_content, post_excerpt FROM {$wpdb->posts} WHERE post_type='post' AND post_status='publish' ORDER BY post_modified DESC LIMIT 20";

//			if ( $posts = $wpdb->get_results( $post_query ) ) {
			if ( $posts = get_posts( array( 'numberposts' => 20 ) ) ) {
				foreach ( $posts as $post ) {
					$picture = get_the_post_thumbnail_url( $post->ID, 'full' );
					if ( empty( $picture ) ) {
						if (
							1 === preg_match( '/<img\s+[^>]*src="([^"]*)"[^>]*>/', $post->post_excerpt, $matches )
							&&
							isset( $matches[1] )
						) {
							$picture = $matches[1];
						}
						;
					}
					if ( empty( $picture ) ) {
						if (
							1 === preg_match( '/<img\s+[^>]*src="([^"]*)"[^>]*>/', $post->post_content, $matches )
							&&
							isset( $matches[1] )
						) {
							$picture = $matches[1];
						}
					}
					if ( empty( $picture ) ) {
						$picture = 'https://www.rontar.com/zero_pixel.png';
					}

					$category_id = null;
					$categories  = wp_get_post_categories( $post->ID, array( 'fields' => 'id=>parent' ) );
					if ( $categories ) {
						$category_ids = array_diff( array_keys( $categories ), array_values( $categories ) );
						$category_id = reset( $category_ids );
					}

					printf(
						'<offer id="%d"><url>%s</url><price>%s</price><categoryId>%d</categoryId><picture>%s</picture><name>%s</name><description>%s</description></offer>',
						$post->ID,
						get_permalink( $post->ID ),
						1,
						$category_id,
						esc_url( $picture ),
						'<![CDATA[' . $post->post_title . ']]>',
						'<![CDATA[' . html_entity_decode( $post->post_excerpt, ENT_COMPAT, "UTF-8" ) . ']]>'
					);
				}
			}

		echo '</offers>';
	}
}
