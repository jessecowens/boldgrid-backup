<?php
/**
 * File: class-boldgrid-backup.php
 *
 * A class definition that includes attributes and functions used across the admin area.
 *
 * @link https://www.boldgrid.com
 * @since 1.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/includes
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

// phpcs:disable WordPress.VIP

/**
 * Class: Boldgrid_Backup
 *
 * The core plugin class.
 * This is used to define internationalization and admin-specific hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since 1.0
 */
class Boldgrid_Backup {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since 1.0
	 * @access protected
	 * @var Boldgrid_Backup_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0
	 * @access protected
	 * @var string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since 1.0
	 * @access protected
	 * @var string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area of the site.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->plugin_name = 'boldgrid-backup';
		$this->version     = ( defined( 'BOLDGRID_BACKUP_VERSION' ) ? BOLDGRID_BACKUP_VERSION : '' );

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Boldgrid_Backup_Loader. Orchestrates the hooks of the plugin.
	 * - Boldgrid_Backup_I18n. Defines internationalization functionality.
	 * - Boldgrid_Backup_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since 1.0
	 * @access private
	 */
	private function load_dependencies() {
		require_once BOLDGRID_BACKUP_PATH . '/vendor/autoload.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once BOLDGRID_BACKUP_PATH . '/includes/class-boldgrid-backup-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once BOLDGRID_BACKUP_PATH . '/includes/class-boldgrid-backup-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin.php';

		/**
		 * Include a utility class.
		 */
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-utility.php';

		/**
		 * The class responsible for the configuration of the plugin.
		 */
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-config.php';

		/**
		 * The class responsible for the functionality test for the plugin.
		 */
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-test.php';

		/**
		 * The class responsible for the admin notices for the plugin.
		 */
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-notice.php';

		/**
		 * The class responsible for the cron functionality in the admin area.
		 */
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-cron.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-cron-test.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-cron-log.php';

		/**
		 * The class responsible for the core backup functionality in the admin area.
		 */
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-core.php';

		/**
		 * The class responsible for the backup  in the admin area.
		 */
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-settings.php';

		/**
		 * The class responsible for the PHP profiling functionality using XHProf.
		 */
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-xhprof.php';

		/**
		 * The class responsible for the plugin file upload functionality in the admin area.
		 */
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-upload.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-restore-helper.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-restore-git.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-filelist.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-compressor.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-compressors.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/compressor/class-boldgrid-backup-admin-compressor-php-zip.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/compressor/class-boldgrid-backup-admin-compressor-pcl-zip.php';

		include BOLDGRID_BACKUP_PATH . '/vendor/ifsnop/mysqldump-php/src/Ifsnop/Mysqldump/Mysqldump.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-db-dump.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-db-get.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-db-import.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-db-omit.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-backup-dir.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-archive.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-archive-actions.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-archives.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-archives-all.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-archive-browser.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-archive-log.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-archive-details.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-archive-fail.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-archiver-utility.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-wp-cron.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-scheduler.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-home-dir.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-auto-rollback.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-jobs.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-remote.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/storage/class-boldgrid-backup-admin-storage-local.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-email.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-folder-exclusion.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-core-files.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-in-progress.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-in-progress-data.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/remote/class-boldgrid-backup-admin-ftp.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/remote/class-boldgrid-backup-admin-ftp-hooks.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/remote/class-boldgrid-backup-admin-ftp-page.php';
		require_once BOLDGRID_BACKUP_PATH . '/admin/remote/class-boldgrid-backup-admin-remote-settings.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-go-pro.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-tools.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-time.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-crypt.php';

		require_once BOLDGRID_BACKUP_PATH . '/includes/class-boldgrid-backup-authentication.php';
		require_once BOLDGRID_BACKUP_PATH . '/includes/class-boldgrid-backup-download.php';
		require_once BOLDGRID_BACKUP_PATH . '/includes/class-boldgrid-backup-file.php';

		require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-cli.php';

		// WP-CLI support.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once BOLDGRID_BACKUP_PATH . '/admin/class-boldgrid-backup-admin-wpcli.php';
		}

		$this->loader = new Boldgrid_Backup_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Boldgrid_Backup_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since 1.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new Boldgrid_Backup_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since 1.0
	 * @access private
	 */
	private function define_admin_hooks() {
		// Instantiate a Boldgrid_Backup_Admin class object.
		$plugin_admin = new Boldgrid_Backup_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );

		// Instantiate the admin core.
		$plugin_admin_core = new Boldgrid_Backup_Admin_Core();

		// WP-CLI support.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			Boldgrid_Backup_Admin_Wpcli::$core = $plugin_admin_core;
		}

		// Add nav menu items.
		$this->loader->add_action(
			'admin_menu', $plugin_admin_core,
			'add_menu_items'
		);

		// Add a custom action for admin notices.
		$this->loader->add_action(
			'boldgrid_backup_notice', $plugin_admin_core->notice,
			'boldgrid_backup_notice', 10, 2
		);

		$this->loader->add_action( 'admin_notices', $plugin_admin_core->notice, 'display_user_notice' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_get_countdown_notice', $plugin_admin_core->auto_rollback, 'wp_ajax_get_countdown_notice' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_get_protect_notice', $plugin_admin_core->auto_rollback, 'wp_ajax_get_protect_notice' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_get_progress_notice', $plugin_admin_core->in_progress, 'wp_ajax_get_progress_notice' );
		$this->loader->add_action( 'core_upgrade_preamble', $plugin_admin_core->notice, 'display_autoupdate_notice' );

		// Add a custom action to handle AJAX callback for creating a backup archive file.
		$this->loader->add_action(
			'wp_ajax_boldgrid_backup_now', $plugin_admin_core,
			'boldgrid_backup_now_callback'
		);

		// Add a custom action to handle AJAX callback for archive file download buttons.
		$this->loader->add_action(
			'wp_ajax_download_archive_file', $plugin_admin_core,
			'download_archive_file_callback'
		);

		// Add an action to perform an auto-backup before an auto-update occurs.
		$this->loader->add_action(
			'pre_auto_update', $plugin_admin_core,
			'boldgrid_backup_now_auto'
		);

		// Add an action to display an admin notice for a pending rollback.
		$this->loader->add_action(
			'admin_notices', $plugin_admin_core->auto_rollback,
			'notice_countdown_show'
		);

		// Add a custom action to handle AJAX callback for canceling a pending rollback.
		$this->loader->add_action(
			'wp_ajax_boldgrid_cancel_rollback', $plugin_admin_core->auto_rollback,
			'wp_ajax_cancel'
		);

		if ( $plugin_admin_core->test->run_functionality_tests() ) {
			$this->loader->add_action( 'admin_notices', $plugin_admin_core->auto_rollback, 'notice_backup_show' );
		}

		// Add an action to add a cron job to restore after WordPress Updates, unless canceled.
		$this->loader->add_action(
			'upgrader_process_complete', $plugin_admin_core->auto_rollback,
			'notice_deadline_show', 10, 2
		);

		// Add a custom action to handle AJAX callback for getting the rollback deadline.
		$this->loader->add_action(
			'wp_ajax_boldgrid_backup_deadline', $plugin_admin_core->auto_rollback,
			'wp_ajax_get_deadline'
		);

		$this->loader->add_action( 'boldgrid_backup_pre_restore', $plugin_admin_core->restore_helper, 'pre_restore' );
		$this->loader->add_action( 'boldgrid_backup_post_restore', $plugin_admin_core->restore_helper, 'post_restore' );
		$this->loader->add_filter( 'boldgrid_backup_post_restore', $plugin_admin_core->archive_log, 'post_restore' );
		$this->loader->add_action( 'boldgrid_backup_post_restore_htaccess', $plugin_admin_core->restore_helper, 'post_restore_htaccess' );
		$this->loader->add_action( 'boldgrid_backup_post_restore_wpconfig', $plugin_admin_core->restore_helper, 'post_restore_wpconfig' );
		$this->loader->add_filter( 'boldgrid_backup_restore_fail', $plugin_admin_core->restore_helper, 'restore_fail' );

		$this->loader->add_filter( 'boldgrid_backup_cannnot_restore_git_objects', $plugin_admin_core->restore_git, 'chmod_objects' );

		$this->loader->add_filter( 'boldgrid_backup_file_in_dir', $plugin_admin_core->backup_dir, 'file_in_dir' );

		$this->loader->add_filter( 'unzip_file_use_ziparchive', $plugin_admin_core->compressors, 'unzip_file_use_ziparchive' );

		$this->loader->add_filter( 'cron_schedules', $plugin_admin_core->wp_cron, 'cron_schedules' );
		$this->loader->add_action( 'boldgrid_backup_wp_cron_backup', $plugin_admin_core->wp_cron, 'backup' );
		$this->loader->add_action( 'boldgrid_backup_wp_cron_restore', $plugin_admin_core->wp_cron, 'restore' );

		$this->loader->add_action( 'boldgrid_backup_archive_files_init', $plugin_admin_core->archive_fail, 'archive_files_init' );

		$this->loader->add_action( 'boldgrid_backup_wp_cron_run_jobs', $plugin_admin_core->jobs, 'run' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin_core, 'admin_enqueue_scripts' );

		$this->loader->add_filter( 'plugins_loaded', $plugin_admin_core, 'init_premium' );

		$this->loader->add_action( 'boldgrid_backup_delete_local', $plugin_admin_core->local, 'delete_local' );

		$this->loader->add_action( 'boldgrid_backup_post_archive_files', $plugin_admin_core->local, 'post_archive_files', 100 );
		$this->loader->add_action( 'boldgrid_backup_post_archive_files', $plugin_admin_core->jobs, 'post_archive_files', 200 );
		$this->loader->add_action( 'boldgrid_backup_post_jobs_email', $plugin_admin_core->jobs, 'post_jobs_email' );

		$this->loader->add_action( 'boldgrid_backup_cron_fail_email', $plugin_admin_core->archive_fail, 'cron_fail_email' );

		$this->loader->add_action( 'wp_ajax_boldgrid_backup_browse_archive', $plugin_admin_core->archive_browser, 'wp_ajax_browse_archive' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_browse_archive_file_actions', $plugin_admin_core->archive_browser, 'wp_ajax_file_actions' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_browse_archive_restore_db', $plugin_admin_core->archive_browser, 'wp_ajax_restore_db' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_browse_archive_view_db', $plugin_admin_core->archive_browser, 'wp_ajax_view_db' );

		$this->loader->add_action( 'wp_ajax_boldgrid_backup_restore_archive', $plugin_admin_core, 'wp_ajax_restore' );

		$this->loader->add_action( 'wp_ajax_boldgrid_backup_exclude_folders_preview', $plugin_admin_core->folder_exclusion, 'wp_ajax_preview' );

		$this->loader->add_action( 'admin_init', $plugin_admin_core->config, 'admin_init' );

		$this->loader->add_action( 'admin_init', $plugin_admin_core->auto_rollback, 'enqueue_update_selectors' );

		$this->loader->add_action( 'admin_init', $plugin_admin_core->cron, 'upgrade_crontab_entries' );

		$this->loader->add_action( 'wp_ajax_boldgrid_backup_generate_download_link', $plugin_admin_core->archive_actions, 'wp_ajax_generate_download_link' );

		/* FTP */

		// Allow one click upload.
		$this->loader->add_action( 'boldgrid_backup_single_archive_remote_options', $plugin_admin_core->ftp->hooks, 'single_archive_remote_option' );
		// Process upload via ajax.
		$this->loader->add_filter( 'wp_ajax_boldgrid_backup_remote_storage_upload_ftp', $plugin_admin_core->ftp->hooks, 'wp_ajax_upload' );
		// Add to the settings page.
		$this->loader->add_filter( 'boldgrid_backup_register_storage_location', $plugin_admin_core->ftp->hooks, 'register_storage_location' );
		// Add our "configure ftp" page.
		$this->loader->add_action( 'admin_menu', $plugin_admin_core->ftp->hooks, 'add_menu_items' );
		// After updating settings on the settings page, check if we have valid credentials.
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_is_setup_ftp', $plugin_admin_core->ftp->hooks, 'is_setup_ajax' );
		// After a backup file has been created, add remote provider to jobs queue.
		$this->loader->add_action( 'boldgrid_backup_post_archive_files', $plugin_admin_core->ftp->hooks, 'post_archive_files' );
		// This is the filter executed by the jobs queue.
		$this->loader->add_filter( 'boldgrid_backup_ftp_upload_post_archive', $plugin_admin_core->ftp->hooks, 'upload_post_archiving' );
		// Add ftp backups to the "Backups" tab.
		$this->loader->add_action( 'boldgrid_backup_get_all', $plugin_admin_core->ftp->hooks, 'filter_get_all' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_remote_storage_download_ftp', $plugin_admin_core->ftp->hooks, 'wp_ajax_download' );
		// Styles and Scripts for FTP settings page.
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin_core->ftp->page, 'enqueue_scripts' );
		$this->loader->add_filter( 'shutdown', $plugin_admin_core->ftp->hooks, 'shutdown' );

		$this->loader->add_action( 'admin_notices', $plugin_admin_core->go_pro, 'admin_notice_setup' );

		$this->loader->add_action( 'boldgrid_backup_pre_dump', $plugin_admin_core->in_progress, 'pre_dump' );
		$this->loader->add_action( 'boldgrid_backup_post_dump', $plugin_admin_core->in_progress, 'post_dump' );
		$this->loader->add_filter( 'heartbeat_received', $plugin_admin_core->in_progress, 'heartbeat_received', 10, 2 );

		$this->loader->add_action( 'customize_controls_enqueue_scripts', $plugin_admin_core->auto_rollback, 'enqueue_customize_controls' );

		add_filter( 'pre_update_option_boldgrid_backup_settings', array( 'Boldgrid_Backup_Admin_Crypt', 'pre_update_settings' ), 10, 3 );
		add_filter( 'option_boldgrid_backup_settings', array( 'Boldgrid_Backup_Admin_Crypt', 'option_settings' ), 10, 2 );

		// Actions run from crontab calls; unauthenticated.
		$this->loader->add_action( 'wp_ajax_nopriv_boldgrid_backup_run_jobs', $plugin_admin_core->jobs, 'run' );
		$this->loader->add_action( 'wp_ajax_nopriv_boldgrid_backup_run_backup', $plugin_admin_core->cron, 'backup' );
		$this->loader->add_action( 'wp_ajax_nopriv_boldgrid_backup_run_restore', $plugin_admin_core->cron, 'restore' );

		// For public downloads.
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_download', $plugin_admin_core->download, 'public_download' );
		$this->loader->add_action( 'wp_ajax_nopriv_boldgrid_backup_download', $plugin_admin_core->download, 'public_download' );

		// Admin notices from cron log.
		$this->loader->add_action( 'admin_notices', $plugin_admin_core->cron_log, 'admin_notice' );

		// For Ajax URL import.
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_url_upload', $plugin_admin_core->upload, 'ajax_url_import' );

		// Filter the boldgrid_backup_pending_rollback site option.
		$this->loader->add_filter( 'site_option_boldgrid_backup_pending_rollback', $plugin_admin_core->auto_rollback, 'validate_rollback_option', 10, 2 );

		// Enable updating feature in the BoldGrid Library.
		add_filter( 'Boldgrid\Library\Update\isEnalbed', '__return_true' );

		$this->loader->add_filter( 'wp_ajax_boldgrid_backup_update_archive_details', $plugin_admin_core->archive_details, 'wp_ajax_update' );

		$this->loader->add_action( 'admin_menu', $plugin_admin_core->local, 'add_submenus' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_is_setup_local', $plugin_admin_core->local, 'is_setup_ajax' );

		$this->loader->add_filter( 'boldgrid_backup_get_core', $plugin_admin_core, 'get_core' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since 1.0
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since 1.0
	 * @return Boldgrid_Backup_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since 1.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
