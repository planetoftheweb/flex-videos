<?php
/**
 * The main plugin class
 *
 * @package FlexVideos
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main FlexVideos Class
 */
class FlexVideos {
    
    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.0.1';
    
    /**
     * The single instance of the class
     *
     * @var FlexVideos
     */
    protected static $_instance = null;
    
    /**
     * Main FlexVideos Instance
     *
     * Ensures only one instance of FlexVideos is loaded or can be loaded.
     *
     * @static
     * @return FlexVideos - Main instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }
    
    /**
     * Define plugin constants
     */
    private function define_constants() {
        define('FLEX_VIDEOS_VERSION', $this->version);
        define('FLEX_VIDEOS_PLUGIN_FILE', __FILE__);
        define('FLEX_VIDEOS_PLUGIN_BASENAME', plugin_basename(__FILE__));
        define('FLEX_VIDEOS_PLUGIN_PATH', plugin_dir_path(__FILE__));
        define('FLEX_VIDEOS_PLUGIN_URL', plugin_dir_url(__FILE__));
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Include additional classes as needed
        // require_once FLEX_VIDEOS_PLUGIN_PATH . 'includes/class-admin.php';
        // require_once FLEX_VIDEOS_PLUGIN_PATH . 'includes/class-shortcodes.php';
        // require_once FLEX_VIDEOS_PLUGIN_PATH . 'includes/class-youtube-api.php';
    }
    
    /**
     * Hook into actions and filters
     */
    private function init_hooks() {
        // Plugin activation/deactivation hooks
        register_activation_hook(FLEX_VIDEOS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(FLEX_VIDEOS_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Initialize plugin
        add_action('init', array($this, 'init'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize plugin components
        do_action('flex_videos_init');
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Enqueue CSS
        wp_enqueue_style(
            'flex-videos-style',
            FLEX_VIDEOS_PLUGIN_URL . 'assets/css/flex-videos.css',
            array(),
            FLEX_VIDEOS_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'flex-videos-script',
            FLEX_VIDEOS_PLUGIN_URL . 'assets/js/flex-videos.js',
            array('jquery'),
            FLEX_VIDEOS_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('flex-videos-script', 'flexVideosAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('flex_videos_nonce')
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on plugin settings page
        if ($hook !== 'settings_page_itsplaitime_youtube_videos') {
            return;
        }
        
        wp_enqueue_style(
            'flex-videos-admin-style',
            FLEX_VIDEOS_PLUGIN_URL . 'assets/css/flex-videos.css',
            array(),
            FLEX_VIDEOS_VERSION
        );
        
        wp_enqueue_script(
            'flex-videos-admin-script',
            FLEX_VIDEOS_PLUGIN_URL . 'assets/js/flex-videos.js',
            array('jquery'),
            FLEX_VIDEOS_VERSION,
            true
        );
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'flex-videos',
            false,
            dirname(FLEX_VIDEOS_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create default options
        add_option('flex_videos_db_version', FLEX_VIDEOS_VERSION);
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation timestamp
        add_option('flex_videos_activated', current_time('timestamp'));
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('flex_videos_cache_cleanup');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Get plugin version
     */
    public function get_version() {
        return $this->version;
    }
}

/**
 * Main instance of FlexVideos
 *
 * @return FlexVideos
 */
function flex_videos() {
    return FlexVideos::instance();
}
