<?php
/**
 * File: class-boldgrid-backup-admin-ftp.php
 *
 * @link  https://www.boldgrid.com
 * @since 1.6.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/remote
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

// phpcs:disable WordPress.VIP

/**
 * Class: Boldgrid_Backup_Admin_Ftp
 *
 * @since 1.6.0
 */
class Boldgrid_Backup_Admin_Ftp {
	/**
	 * An FTP connection.
	 *
	 * @since 1.6.0
	 * @access private
	 * @var    Resource
	 */
	private $connection = null;

	/**
	 * The core class object.
	 *
	 * @since 1.6.0
	 * @access private
	 * @var    Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * Default folder name.
	 *
	 * Set using Boldgrid_Backup_Admin_Ftp->set_default_folder_name(), within the constructor.
	 *
	 * @since 1.9.1
	 * @var string
	 */
	public $default_folder_name;

	/**
	 * Default port numbers.
	 *
	 * @since 1.6.0
	 * @access public
	 * @var    array
	 */
	public $default_port = array(
		'ftp'  => 21,
		'sftp' => 22,
	);

	/**
	 * Default type.
	 *
	 * @since 1.6.0
	 * @access public
	 * @var    string
	 */
	public $default_type = 'sftp';

	/**
	 * Errors.
	 *
	 * @since 1.6.0
	 * @access public
	 * @var    array
	 */
	public $errors = array();

	/**
	 * The folder on the remote FTP server where backups are stored.
	 *
	 * Handled by Boldgrid_Backup_Admin_Ftp->get_folder_name().
	 *
	 * @since 1.9.1
	 * @var string
	 */
	public $folder_name;

	/**
	 * Hooks class.
	 *
	 * @since 1.6.0
	 * @access public
	 * @var    Boldgrid_Backup_Admin_Ftp_Hooks
	 */
	public $hooks;

	/**
	 * FTP host.
	 *
	 * @since 1.6.0
	 * @access private
	 * @var    string
	 */
	private $host = null;

	/**
	 * Whether or not we have logged in.
	 *
	 * @since 1.6.0
	 * @access public
	 * @var    bool
	 */
	public $logged_in = false;

	/**
	 * Nickname.
	 *
	 * So the user can refer to their ftp account as something other than ftp.
	 *
	 * @since  1.6.0
	 * @access public
	 * @var    string
	 */
	public $nickname;

	/**
	 * Our key / label for ftp.
	 *
	 * @since 1.6.0
	 * @access public
	 * @var    string
	 */
	public $key = 'ftp';

	/**
	 * FTP password.
	 *
	 * @since 1.6.0
	 * @access private
	 * @var    string
	 */
	private $pass = null;

	/**
	 * Retention count.
	 *
	 * @since 1.6.0
	 * @access public
	 * @var    int $retention_count
	 */
	public $retention_count = 5;

	/**
	 * Settings class.
	 *
	 * @since 1.7.2
	 * @access public
	 * @var Boldgrid_Backup_Admin_Remote_Settings
	 */
	public $settings;

	/**
	 * Default timeout.
	 *
	 * @since 1.6.0
	 * @access public
	 * @var    int
	 */
	public $timeout = 10;

	/**
	 * Our title / label for ftp.
	 *
	 * @since 1.6.0
	 * @access public
	 * @var    string
	 */
	public $title = 'FTP / SFTP';

	/**
	 * Title attribute.
	 *
	 * If you are using a nickname, hovering over the nickname should show this
	 * more clear title.
	 *
	 * @since  1.6.0
	 * @access public
	 * @var    string
	 */
	public $title_attr;

	/**
	 * Our FTP type, ftp or sftp.
	 *
	 * @since 1.6.0
	 * @access public
	 * @var    string
	 */
	public $type = null;

	/**
	 * FTP username.
	 *
	 * @since 1.6.0
	 * @access private
	 * @var    string
	 */
	private $user = null;

	/**
	 * Valid types.
	 *
	 * @since 1.6.0
	 * @access public
	 * @var    array
	 */
	public $valid_types = array( 'ftp', 'sftp' );

	/**
	 * Constructor.
	 *
	 * @since 1.6.0
	 *
	 * @param Boldgrid_Backup_Admin_Core $core Core class object.
	 */
	public function __construct( $core ) {
		include_once BOLDGRID_BACKUP_PATH . '/vendor/phpseclib/phpseclib/phpseclib/Net/SFTP.php';

		$this->core     = $core;
		$this->hooks    = new Boldgrid_Backup_Admin_Ftp_Hooks( $core );
		$this->page     = new Boldgrid_Backup_Admin_Ftp_Page( $core );
		$this->settings = new Boldgrid_Backup_Admin_Remote_Settings( $this->key );

		$this->set_default_folder_name();
	}

	/**
	 * Connect to our ftp server.
	 *
	 * @since 1.6.0
	 */
	public function connect() {
		if ( ! empty( $this->connection ) ) {
			return;
		}

		$this->init();

		if ( empty( $this->user ) || empty( $this->pass ) || empty( $this->host ) || empty( $this->type ) || empty( $this->port ) ) {
			return;
		}

		switch ( $this->type ) {
			case 'ftp':
				$this->connection = ftp_connect( $this->host, $this->port, $this->timeout );
				break;
			case 'sftp':
				$this->connection = new phpseclib\Net\SFTP( $this->host, $this->port );
				break;
		}
	}

	/**
	 * Create backup directory on remote host.
	 *
	 * @since 1.6.0
	 *
	 * @return bool False when we were unable to create directory.
	 */
	public function create_backup_dir() {
		$this->connect();
		$this->log_in();
		if ( ! $this->logged_in ) {
			return false;
		}

		$contents = $this->get_contents();
		if ( ! $contents || ! is_array( $contents ) ) {
			$this->errors[] = __( 'Unable to get a directory listing from FTP server.', 'boldgrid-backup' );
			return false;
		} elseif ( in_array( $this->get_folder_name(), $contents, true ) ) {
			return true;
		}

		switch ( $this->type ) {
			case 'ftp':
				$created = ftp_mkdir( $this->connection, $this->get_folder_name() );
				break;
			case 'sftp':
				$created = $this->connection->mkdir( $this->get_folder_name() );
				break;
		}

		if ( ! $created ) {
			$this->errors[] = sprintf(
				// translators: 1: Remote directory path.
				__(
					'Unable to create the following directory on FTP server: %1$s',
					'boldgrid-backup'
				),
				$this->get_folder_name()
			);
		}

		return $created;
	}

	/**
	 * Disconnect from FTP server.
	 *
	 * @since 1.6.0
	 */
	public function disconnect() {
		if ( 'ftp' === $this->type && is_resource( $this->connection ) ) {
			ftp_close( $this->connection );
			$this->connection = null;
			$this->logged_in  = false;
		}
	}

	/**
	 * Download a backup via FTP.
	 *
	 * @since 1.6.0
	 *
	 * @param  string $filename Filename.
	 * @return bool
	 */
	public function download( $filename ) {
		$this->connect();

		$local_filepath  = $this->core->backup_dir->get_path_to( $filename );
		$server_filepath = $this->get_folder_name() . '/' . $filename;
		$success         = false;

		$this->log_in();

		switch ( $this->type ) {
			case 'ftp':
				$success = ftp_get( $this->connection, $local_filepath, $server_filepath, FTP_BINARY );
				break;
			case 'sftp':
				$success = $this->connection->get( $server_filepath, $local_filepath );
				break;
		}

		if ( $success ) {
			$this->core->remote->post_download( $local_filepath );
		}

		return $success;
	}

	/**
	 * Enforce retention.
	 *
	 * @since 1.6.0
	 */
	public function enforce_retention() {
		if ( empty( $this->retention_count ) ) {
			return;
		}

		$contents = $this->get_contents( true, $this->get_folder_name() );
		$backups  = $this->format_raw_contents( $contents );

		$count_to_delete = count( $backups ) - $this->retention_count;

		if ( empty( $backups ) || $count_to_delete <= 0 ) {
			return false;
		}

		usort(
			$backups, function( $a, $b ) {
				return $a['time'] < $b['time'] ? -1 : 1;
			}
		);

		for ( $x = 0; $x < $count_to_delete; $x++ ) {
			$filename = $backups[ $x ]['filename'];
			$path     = $this->get_folder_name() . '/' . $filename;

			switch ( $this->type ) {
				case 'ftp':
					ftp_delete( $this->connection, $path );
					break;
				case 'sftp':
					$this->connection->delete( $path, false );
					break;
			}

			/**
			 * Remote file deleted due to remote retention settings.
			 *
			 * @since 1.6.0
			 */
			do_action(
				'boldgrid_backup_remote_retention_deleted',
				$this->title,
				$filename
			);
		}
	}

	/**
	 * Get our ftp folder name.
	 *
	 * @since 1.9.1
	 *
	 * @return string
	 */
	public function get_folder_name() {
		if ( ! empty( $this->folder_name ) ) {
			return $this->folder_name;
		}

		$settings       = $this->core->settings->get_settings();
		$has_settings   = isset( $settings['remote'][ $this->key ] );
		$has_folder_set = ! empty( $settings['remote'][ $this->key ]['folder_name'] );

		/*
		 * In version 1, no folder name was configurable by the user and it defaulted to
		 * "boldgrid-backup" ($this->remote_dir, which was removed in 1.9.1). In version 2, the user
		 * can set a custom folder name.
		 *
		 * This is for backwards compatibility.
		 */
		$version = $has_settings && ! $has_folder_set ? 1 : 2;

		switch ( $version ) {
			case 1:
				$this->folder_name = 'boldgrid_backup';
				break;
			case 2:
				$this->folder_name = $has_folder_set ? $settings['remote'][ $this->key ]['folder_name'] : $this->default_folder_name;
				break;
		}

		return $this->folder_name;
	}

	/**
	 * Get our settings from $_POST.
	 *
	 * For example, if we are saving our FTP settings, get all the data the user set from $_POST.
	 *
	 * @since 1.6.0
	 *
	 * @return array
	 */
	public function get_from_post() {
		$settings = $this->core->settings->get_settings();

		$values = array(
			array(
				'key'      => 'host',
				'default'  => null,
				'callback' => 'sanitize_file_name',
			),
			array(
				'key'      => 'user',
				'default'  => null,
				'callback' => 'sanitize_text_field',
			),
			array(
				'key'     => 'pass',
				'default' => null,
			),
			array(
				'key'      => 'folder_name',
				'default'  => $this->get_folder_name(),
				'callback' => 'sanitize_file_name',
			),
			array(
				'key'      => 'type',
				'default'  => $this->default_type,
				'callback' => 'sanitize_key',
			),
			array(
				'key'      => 'port',
				'default'  => $this->default_port[ $this->default_type ],
				'callback' => 'intval',
			),
			array(
				'key'      => 'retention_count',
				'default'  => $this->retention_count,
				'callback' => 'intval',
			),
			array(
				'key'      => 'nickname',
				'default'  => '',
				'callback' => 'stripslashes',
			),
		);

		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.Security.NonceVerification.NoNonceVerification

		foreach ( $values as $value ) {
			$key      = $value['key'];
			$callback = ! empty( $value['callback'] ) ? $value['callback'] : null;

			if ( ! empty( $_POST[ $key ] ) ) {
				$data[ $key ] = $_POST[ $key ];
			} elseif ( ! empty( $settings['remote'][ $this->key ][ $key ] ) ) {
				$data[ $key ] = $settings['remote'][ $this->key ][ $key ];
			} else {
				$data[ $key ] = $value['default'];
			}

			// If there is a callback function for sanitizing the value, then run it.
			if ( $callback ) {
				$data[ $key ] = $callback( $data[ $key ] );
			}
		}

		// phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.Security.NonceVerification.NoNonceVerification

		return $data;
	}

	/**
	 * Format raw contents.
	 *
	 * This method takes in raw contents and returns an array of backups, with
	 * keys defining timestamp and filename.
	 *
	 * The array of backups returned DO NOT include:
	 * # . or .. (typical when getting a directory listing).
	 * # Files / backups that do not belong to this site.
	 *   See $this->core->archive->is_site_archive().
	 *
	 * @since 1.6.0
	 *
	 * @param  array $contents Raw contents received from this->get_contents.
	 * @return array {
	 *     An array of backups.
	 *
	 *     @type int    $time     Timestamp file was uploaded to ftp server.
	 *     @type string $filename
	 * }
	 */
	public function format_raw_contents( $contents ) {
		$skips   = array( '.', '..' );
		$backups = array();

		if ( ! is_array( $contents ) ) {
			return $backups;
		}

		foreach ( $contents as $item ) {

			if ( 'sftp' === $this->type ) {
				$filename = $item['filename'];
				if ( in_array( $filename, $skips, true ) ) {
					continue;
				}

				$backups[] = array(
					'time'     => $item['mtime'],
					'filename' => $filename,
					'size'     => $item['size'],
				);
			} else {
				// Before exploding by space, replace multiple spaces with one space.
				$item = preg_replace( '!\s+!', ' ', $item );

				$exploded_item = explode( ' ', $item );
				$count         = count( $exploded_item );

				$filename = $exploded_item[ $count - 1 ];
				if ( in_array( $filename, $skips, true ) ) {
					continue;
				}

				/*
				 * Determine the format of our raw contents.
				 *
				 * There are for sure more than 2 formats (see notes at https://pastebin.com/eL5XpeYP),
				 * but for now we're currently testing for:
				 * # Windows 10-24-2018 11:12AM                       302501              boldgrid-backup-localhost_wordpress-90d7727c-20181024-175039.zip
				 * # Linux   -rw-r--r-- 1       boldgrid4s boldgrid4s 997834 Oct 24 10:36 boldgrid-backup-domain.com-b2cf0453-20181024-143320.zip
				 *
				 * Flag as a windows ftp server if first item is a date in xx-xx-xxxx format.
				 */
				$is_windows = 1 === preg_match( '/(\d{2})-(\d{2})-(\d{4})/', $exploded_item[0] );

				if ( $is_windows ) {
					$time = strtotime( $exploded_item[0] . ' ' . $exploded_item[1] );
					$size = $exploded_item[2];
				} else {
					// Get the timestamp.
					$month = $exploded_item[ $count - 4 ];
					$day   = $exploded_item[ $count - 3 ];
					$time  = $exploded_item[ $count - 2 ];
					$time  = strtotime( $month . ' ' . $day . ' ' . $time );

					$size = $exploded_item[ $count - 5 ];
				}

				$backups[] = array(
					'time'     => $time,
					'filename' => $filename,
					'size'     => $size,
				);
			}
		}

		foreach ( $backups as $key => $backup ) {
			if ( ! $this->core->archive->is_site_archive( $backup['filename'] ) ) {
				unset( $backups[ $key ] );
			}
		}
		$backups = array_values( $backups );

		return $backups;
	}

	/**
	 * Get the remote contents / listing.
	 *
	 * This method allows for both ftp / sftp AND rawlist / nlist functions. The return data can
	 * vary based on server. Example return data available here: https://pastebin.com/eL5XpeYP
	 *
	 * @since 1.6.0
	 *
	 * @param  bool   $raw   Whether to get the raw contents (ftp_rawlist) or not
	 *                       (ftp_nlist).
	 * @param  string $dir The directory to get listing of.
	 * @return mixed
	 */
	public function get_contents( $raw = false, $dir = '.' ) {
		$this->connect();
		$this->log_in();
		if ( ! $this->logged_in ) {
			$this->errors[] = __( 'Unable to log in to FTP server.', 'boldgrid-backup' );
			return array();
		}

		switch ( $this->type ) {
			case 'ftp':
				if ( $raw ) {
					$contents = ftp_rawlist( $this->connection, $dir );
				} else {
					$contents = ftp_nlist( $this->connection, $dir );
				}
				break;
			case 'sftp':
				if ( $raw ) {
					$contents = $this->connection->rawlist( $dir );
				} else {
					$contents = $this->connection->nlist( $dir );
				}
				break;
		}

		/*
		 * Some ftp servers respond with slightly different formats. In some scenarious on a Windows
		 * FTP server, the folders will be prepended with a "./" (See comment in this method's
		 * docblock). Before returning the data, remove "./" from the beginning of all items.
		 */
		$fix_windows = function( $item ) {
			if ( './' === substr( $item, 0, 2 ) ) {
				$item = substr( $item, 2 );
			}
			return $item;
		};
		if ( 'ftp' === $this->type && is_array( $contents ) ) {
			$contents = array_map( $fix_windows, $contents );
		}

		return $contents;
	}

	/**
	 * Get settings.
	 *
	 * @since 1.6.0
	 *
	 * @param bool $try_cache Whether or not to use last_login to validate the ftp account. Please
	 *                        see param definition in $this->is_setup().
	 */
	public function get_details( $try_cache = false ) {
		$is_setup = $this->is_setup( $try_cache );

		$settings = $this->core->settings->get_settings();

		return array(
			'title'     => $this->title,
			'key'       => $this->key,
			'configure' => 'admin.php?page=boldgrid-backup-ftp',
			'is_setup'  => $is_setup,
			'enabled'   => ! empty( $settings['remote'][ $this->key ]['enabled'] ) && $settings['remote'][ $this->key ]['enabled'] && $is_setup,
		);
	}

	/**
	 * Init properties.
	 *
	 * @since 1.6.0
	 */
	public function init() {
		if ( ! empty( $this->user ) || ! empty( $this->pass ) || ! empty( $this->host ) ) {
			return;
		}

		$settings = $this->core->settings->get_settings();

		$labels = array( 'user', 'pass', 'host', 'port', 'type', 'retention_count', 'nickname' );

		$configs = array(
			array(
				'property' => 'user',
				'default'  => null,
			),
			array(
				'property' => 'pass',
				'default'  => null,
			),
			array(
				'property' => 'host',
				'default'  => null,
			),
			array(
				'property' => 'port',
				'default'  => $this->default_port,
			),
			array(
				'property' => 'type',
				'default'  => $this->default_type,
			),
			array(
				'property' => 'retention_count',
				'default'  => $this->retention_count,
			),
			array(
				'property' => 'nickname',
				'default'  => $this->title,
			),
		);

		foreach ( $configs as $config ) {
			$property = $config['property'];

			if ( ! empty( $settings['remote'][ $this->key ][ $property ] ) ) {
				$this->$property = $settings['remote'][ $this->key ][ $property ];
			} else {
				$this->$property = $config['default'];
			}
		}

		if ( ! empty( $this->host ) ) {
			$this->title_attr = strtoupper( $this->type ) . ': ' . $this->host;
		}
	}

	/**
	 * Determine whether or not FTP is setup.
	 *
	 * @since 1.6.0
	 *
	 * @param  bool $try_cache Whether or not to use the last_login value to determine if we are
	 *                         setup. For example, if $try_cache and we logged in an hour ago, no
	 *                         need to try to connect and log in again, we logged in an hour ago so
	 *                         assume all is still good.
	 * @return bool
	 */
	public function is_setup( $try_cache = false ) {

		// If successfully logged in within last 24 hours, return true.
		if ( $try_cache && $this->settings->is_last_login_valid() ) {
			return true;
		}

		$this->connect();
		$this->log_in();

		$logged_in = $this->logged_in;

		return $logged_in;
	}

	/**
	 * Determine if a set of FTP credentials are valid.
	 *
	 * @since 1.6.0
	 *
	 * @param  string $host Hostname.
	 * @param  string $user Username.
	 * @param  string $pass Password.
	 * @param  int    $port Port number.
	 * @param  string $type Type.
	 * @return bool
	 */
	public function is_valid_credentials( $host, $user, $pass, $port, $type ) {
		$connection = false;
		$logged_in  = false;
		$port       = intval( $port );

		// Avoid a really long timeout.
		if ( 21 === $port && 'sftp' === $type ) {
			$this->errors[] = sprintf(
				// translators: 1: Hostname, 2: Port number.
				__(
					'Unable to connect to %1$s over port %2$u.',
					'boldgrid-backup'
				),
				$host,
				$port
			);
			return false;
		}

		switch ( $type ) {
			case 'ftp':
				$connection = @ftp_connect( $host, $port, $this->timeout ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
				break;
			case 'sftp':
				$connection = @new phpseclib\Net\SFTP( $host, $port, $this->timeout ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
				break;
		}
		if ( ! $connection ) {
			$this->errors[] = sprintf(
				// translators: 1: Hostname, 2: Port number.
				__(
					'Unable to connect to %1$s over port %2$u.',
					'boldgrid-backup'
				),
				$host,
				$port
			);
			return false;
		}

		/*
		 * Try to login.
		 *
		 * When:
		 * # Connecting over bad ports (like port FTP over port 22)
		 * # Using invalid login credentials
		 * Notices are thrown instead of catachable errors. This makes it difficult
		 * to know if a connection failed because of of a bad port number or because
		 * of bad credentials.
		 *
		 * If we have any trouble connecting, we'll use a custom error handler
		 * and throw an Exception.
		 */
		$error_caught = false;

		set_error_handler( array( 'Boldgrid_Backup_Admin_Utility', 'handle_error' ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler

		try {
			switch ( $type ) {
				case 'ftp':
					$logged_in = ftp_login( $connection, $user, $pass );
					ftp_close( $connection );
					break;
				case 'sftp':
					$logged_in = $connection->login( $user, $pass );
					break;
			}
		} catch ( Exception $e ) {
			$this->errors[] = $e->getMessage();
			$error_caught   = true;
		}
		restore_error_handler();

		if ( ! $error_caught && ! $logged_in ) {
			$this->errors[] = __( 'Invalid username / password.', 'boldgrid-backup' );
		}

		return false !== $logged_in;
	}

	/**
	 * Log into the FTP server.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public function log_in() {
		if ( $this->logged_in ) {
			return;
		}

		// If we tried to connect but don't have a connection, abort.
		$this->connect();
		if ( empty( $this->connection ) ) {
			return false;
		}

		switch ( $this->type ) {
			case 'ftp':
				$this->logged_in = @ftp_login( $this->connection, $this->user, $this->pass ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
				break;
			case 'sftp':
				$this->logged_in = $this->connection->login( $this->user, $this->pass );
				break;
		}

		if ( $this->logged_in ) {
			$this->maybe_passive();

			$this->settings->set_last_login();
		}
	}

	/**
	 * Turn on passive mode, only if needed.
	 *
	 * Turning on passive mode can only be done after a successful login. This method assumes you've
	 * already logged in.
	 *
	 * @since 1.7.0
	 */
	public function maybe_passive() {
		if ( 'ftp' === $this->type ) {
			$contents = $this->get_contents();

			if ( ! is_array( $contents ) ) {
				ftp_pasv( $this->connection, true );
			}
		}
	}

	/**
	 * Reset class properties.
	 *
	 * If the user wants to delete all FTP settings, after we clear the data from
	 * the options, run this method to clear the properties.
	 *
	 * @since 1.6.0
	 */
	public function reset() {
		$this->host = null;
		$this->user = null;
		$this->set_pass( null );
		$this->port            = $this->default_port['ftp'];
		$this->retention_count = null;
		$this->type            = $this->default_type;
	}

	/**
	 * Set our default_folder_name.
	 *
	 * @since 1.9.1
	 */
	public function set_default_folder_name() {
		$site_url = get_site_url();
		$site_url = str_replace( 'https://', '', $site_url );
		$site_url = str_replace( 'http://', '', $site_url );
		$site_url = str_replace( '/', '-', $site_url );
		$site_url = str_replace( '.', '-', $site_url );

		$this->default_folder_name = sanitize_file_name( 'boldgrid-backup-' . $site_url );
	}

	/**
	 * Set our ftp password.
	 *
	 * @since 1.6.0
	 *
	 * @param string $pass Password.
	 */
	public function set_pass( $pass ) {
		$this->pass = $pass;
	}

	/**
	 * Determine if a backup archive is uploaded to the remote server.
	 *
	 * @since 1.6.0
	 *
	 * @param string $filepath File path.
	 */
	public function is_uploaded( $filepath ) {
		$contents = $this->get_contents( false, $this->get_folder_name() );

		return ! is_array( $contents ) ? false : in_array( basename( $filepath ), $contents, true );
	}

	/**
	 * Upload a file.
	 *
	 * @since 1.6.0
	 *
	 * @param  string $filepath File path.
	 * @return bool
	 */
	public function upload( $filepath ) {
		$remote_file = $this->get_folder_name() . '/' . basename( $filepath );

		$timestamp = filemtime( $filepath );

		$this->connect();
		$this->log_in();
		if ( ! $this->logged_in ) {
			$this->errors[] = __( 'Unable to log in to ftp server.', 'boldgrid-backup' );
			return false;
		}

		$has_remote_dir = $this->create_backup_dir();
		if ( ! $has_remote_dir ) {
			return false;
		}

		switch ( $this->type ) {
			case 'ftp':
				$uploaded = ftp_put( $this->connection, $remote_file, $filepath, FTP_BINARY );

				/*
				 * Ensure the timestamp is unchanged.
				 *
				 * Not 100% accurate however. In testing, when setting a remote file's timestamp to
				 * 11am UTC, that remote server convereted the UTC time to local time.
				 */
				$cmd = 'MFMT ' . date( 'YmdHis', $timestamp ) . ' ' . $remote_file;
				ftp_raw( $this->connection, $cmd );
				break;
			case 'sftp':
				$uploaded = $this->connection->put( $remote_file, $filepath, 1 );

				// Adjust timestamp.
				$this->connection->touch( $remote_file, $timestamp );
				break;
		}

		if ( ! $uploaded ) {
			$last_error = error_get_last();

			$this->errors[] = __( 'Unable to upload file.', 'boldgrid-backup' );

			/*
			 * The last error message may be important on a failed uploaded,
			 * such as ftp_put(): Quota exceeded. Make sure the user sees the
			 * last error.
			 */
			if ( ! empty( $last_error['message'] ) && ! empty( $last_error['file'] ) && __FILE__ === $last_error['file'] ) {
				$this->errors[] = $last_error['message'];
			}

			return false;
		}

		$this->enforce_retention();

		return true;
	}
}
