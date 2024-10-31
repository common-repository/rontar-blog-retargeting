<?php namespace rontar\blog_retargeting;

final class Class_Front {
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
		add_action( 'wp_footer', array( $this, 'add_pixel' ) );
	}

	public function add_pixel() {
		if (
			empty( $this->plugin->options['enabled'] )
			||
			'yes' != $this->plugin->options['enabled']
			||
			empty( $this->plugin->options['advertiser_id'] )
			||
			empty( $this->plugin->options['feed_id'] )
			||
			empty( $this->plugin->options['audience_id'] )
		) {
			return;
		}
		?>

<script>
	window.rnt=window.rnt||function(){(rnt.q=rnt.q||[]).push(arguments)};
	rnt('add_event', {advId: '<?php echo esc_js( $this->plugin->options['advertiser_id'] ); ?>'});
	//<!-- EVENTS START -->
	rnt('add_audience', {audienceId: '<?php echo esc_js( $this->plugin->options['audience_id'] ); ?>'});
	//<!-- EVENTS FINISH -->
</script>
<script async src='//uaadcodedsp.rontar.com/rontar_aud_async.js'></script>

		<?php
	}
}
