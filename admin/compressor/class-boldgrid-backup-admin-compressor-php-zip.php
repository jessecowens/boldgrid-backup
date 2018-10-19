<?php
/**
 * File: class-boldgrid-backup-admin-compressor-php-zip.php
 *
 * PHP Zip Compressor.
 *
 * @link  https://www.boldgrid.com
 * @since 1.5.1
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/compressor
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Class: Boldgrid_Backup_Admin_Compressor_Php_Zip
 *
 * @since 1.5.1
 */
class Boldgrid_Backup_Admin_Compressor_Php_Zip extends Boldgrid_Backup_Admin_Compressor {
	/**
	 * An array of directories we've added to the zip.
	 *
	 * @since 1.6.0
	 * @access public
	 * @var    array
	 */
	public $dirs = array();

	/**
	 * The status of our test result.
	 *
	 * @since  1.5.1
	 * @access public
	 * @var    mixed|bool|null
	 */
	public static $test_result = null;

	/**
	 * An array of any errors received while testing.
	 *
	 * @since  1.5.1
	 * @access public
	 * @var    array
	 */
	public $test_errors = array();

	/**
	 * An instance of ZipArchive.
	 *
	 * @since 1.5.1
	 * @var   ZipArchive
	 */
	public $zip;

	/**
	 * Add a file's directories to the zip.
	 *
	 * When you add a file, the parent directories are not always explicitly
	 * created. For example, if you add wp-content/themes/pavilion/index.php the
	 * wp-content directory (and so forth) is not explicity added to the zip.
	 *
	 * @since 1.6.0
	 *
	 * @param string $file A file path.
	 */
	public function add_dir( $file ) {
		$add_directory = '';
		$dirs          = explode( DIRECTORY_SEPARATOR, dirname( $file ) );

		foreach ( $dirs as $key => $dir ) {
			if ( 0 === $key ) {
				$add_directory = $dir;
			} else {
				$add_directory .= '/' . $dir;
			}

			if ( ! in_array( $add_directory, $this->dirs, true ) ) {
				$this->zip->addEmptyDir( $add_directory );
				$this->dirs[] = $add_directory;
			}
		}
	}

	/**
	 * Archive files.
	 *
	 * @since 1.5.1
	 *
	 * @see Boldgrid_Backup_Admin_Filelist::get_total_size()
	 *
	 * @param array $filelist File list.
	 * @param array $info {
	 *     An array of data about the backup archive we are generating.
	 *
	 *     @type string mode       backup
	 *     @type bool   dryrun
	 *     @type string compressor php_zip
	 *     @type int    filesize   0
	 *     @type bool   save       1
	 *     @type int    total_size 0
	 * }
	 */
	public function archive_files( $filelist, &$info ) {
		$info['filepath'] = $this->core->generate_archive_path( 'zip' );

		// Init vars used for our "in progress" bar.
		$number_files_archived = 0;
		$total_size_archived   = 0;
		$number_files_todo     = count( $filelist );
		$last_x_files          = array();

		if ( $info['dryrun'] ) {
			return true;
		}

		$this->zip = new ZipArchive();

		$status = $this->zip->open( $info['filepath'], ZipArchive::CREATE );

		if ( ! $status ) {
			return array(
				'error'         => 'Cannot open ZIP archive file "' . $info['filepath'] . '".',
				'error_code'    => $status,
				'error_message' => Boldgrid_Backup_Admin_Utility::translate_zip_error( $status ),
			);
		}

		foreach ( $filelist as $fileinfo ) {
			$is_dir = ! empty( $fileinfo[3] ) && 'd' === $fileinfo[3];

			if ( $is_dir ) {
				$this->zip->addEmptyDir( $fileinfo[1] );
			} else {
				$this->zip->addFile( $fileinfo[0], $fileinfo[1] );
				$this->add_dir( $fileinfo[1] );
			}

			$number_files_archived++;
			$total_size_archived += empty( $fileinfo[2] ) ? 0 : $fileinfo[2];

			/*
			 * If applicable, add this file to the list of files archived that we show the user.
			 *
			 * To give the user a more broad sense of the files being added, our list only contains
			 * every 20th file.
			 *
			 * Our list is only 5 long because we make hook into the heartbeat every 5 seconds to grab
			 * the last 5 files, and we display each file for 1 second.
			 */
			if ( 0 === $number_files_archived % 20 ) {
				$last_x_files[] = $fileinfo[1];
				if ( count( $last_x_files ) > 5 ) {
					array_shift( $last_x_files );
				}
			}

			/*
			 * Update our "in progress" data.
			 *
			 * To prevent excessive calls to update options, we only update our in progress data every
			 * 100 files.
			 */
			$all_files_archived = $number_files_archived >= $number_files_todo;
			if ( 0 === $number_files_archived % 100 || $all_files_archived ) {
				Boldgrid_Backup_Admin_In_Progress_Data::set_arg( 'total_files_done', $number_files_archived );
				Boldgrid_Backup_Admin_In_Progress_Data::set_arg( 'last_files', $last_x_files );
				Boldgrid_Backup_Admin_In_Progress_Data::set_arg( 'total_size_archived', $total_size_archived );
				Boldgrid_Backup_Admin_In_Progress_Data::set_arg( 'total_size_archived_size_format', size_format( $total_size_archived ) );
				Boldgrid_Backup_Admin_In_Progress_Data::set_arg( 'step', 2 );
			}
		}

		/*
		 * We're done archiving all files.
		 *
		 * Empty out the "last files archived" data, and set an appropriate status.
		 */
		Boldgrid_Backup_Admin_In_Progress_Data::delete_arg( 'last_files' );
		Boldgrid_Backup_Admin_In_Progress_Data::set_arg( 'step', 3 );

		$close = $this->zip->close();

		Boldgrid_Backup_Admin_In_Progress_Data::delete_arg( 'step' );

		if ( ! $close ) {
			return array(
				'error' => 'Cannot close ZIP archive file "' . $info['filepath'] . '".',
			);
		}

		return true;
	}

	/**
	 * Determine if ZipArchive is available.
	 *
	 * @since 1.5.2
	 */
	public static function is_available() {
		return extension_loaded( 'zip' ) && class_exists( 'ZipArchive' );
	}

	/**
	 * Test the functionality of php_zip.
	 *
	 * @since 1.5
	 *
	 * @return bool
	 */
	public function test() {
		if ( null !== self::$test_result ) {
			return self::$test_result;
		}

		$backup_dir = $this->core->backup_dir->get();

		$test_file_contents = __(
			'This is a test file from BoldGrid Backup. You can delete this file.',
			'boldgrid-backup'
		);

		// translators: 1: A filename.
		$cannot_open_zip = __( 'Unable to create zip file: %1$s', 'boldgrid-backup' );

		// translators: 1: Backup directory path.
		$cannot_close_zip = __( 'When testing ZipArchive functionality, we are able to create a zip file and add files to it, but we were unable to close the zip file.<br /><strong>Please be sure the following backup directory has modify permissions</strong>:<br />%1$s', 'boldgrid-backup' );
		$safe_to_delete   = __( 'safe-to-delete', 'boldgrid-backup' );
		$test_zip_file    = $this->core->test->test_prefix . '-zip';
		$test_filename    = sprintf( '%1$s%5$s%2$s-%3$s-%4$s', $backup_dir, $test_zip_file, mt_rand(), $safe_to_delete, DIRECTORY_SEPARATOR );
		$zip_filepath     = $test_filename . '.zip';
		$random_filename  = $test_filename . '.txt';

		$zip    = new ZipArchive();
		$status = $zip->open( $zip_filepath, ZipArchive::CREATE );

		if ( ! $status ) {
			$this->test_errors[] = sprintf( $cannot_open_zip, $zip_filepath );
			self::$test_result   = false;

			return false;
		}

		$this->core->wp_filesystem->touch( $random_filename );
		$this->core->wp_filesystem->put_contents( $random_filename, $test_file_contents );

		$zip->addFile( $random_filename, 'test.txt' );

		$zip_closed = @$zip->close(); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged

		$this->core->test->delete_test_files( $backup_dir );

		if ( ! $zip_closed ) {
			$this->test_errors[] = sprintf( $cannot_close_zip, $backup_dir );
			self::$test_result   = false;

			return false;
		}

		self::$test_result = true;

		return true;
	}
}
