<?php
/**
 * Compressors.
 *
 * @link  http://www.boldgrid.com
 * @since 1.5.1
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin
 * @copyright  BoldGrid.com
 * @version    $Id$
 * @author     BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Backup Admin Compressors class.
 *
 * @since 1.5.1
 */
class Boldgrid_Backup_Admin_Compressors {

	/**
	 * The core class object.
	 *
	 * @since  1.5.1
	 * @access private
	 * @var    Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * The default compressors.
	 *
	 * WordPress ships out of the box with pcl_zip.
	 *
	 * @since  1.5.1
	 * @access public
	 * @var    string
	 */
	public $default = 'pcl_zip';

	/**
	 * Constructor.
	 *
	 * @since 1.5.1
	 *
	 * @param Boldgrid_Backup_Admin_Core $core Core class object.
	 */
	public function __construct( $core ) {
		$this->core = $core;
	}

	/**
	 * Get the compressor type we will use, such as 'php_zip'.
	 *
	 * @since 1.5.1
	 *
	 * @return string
	 */
	public function get() {
		$settings = $this->core->settings->get_settings();
		$available_compressors = $this->get_available();

		/*
		 * If we have a compressor saved in our settings and it is an
		 * available compressor, then use it.
		 */
		if( ! empty( $settings['compressor'] ) && in_array( $settings['compressor'], $available_compressors, true ) ) {
			return $settings['compressor'];
		}

		return $this->default;
	}

	/**
	 * Get all available compressors.
	 *
	 * @since 1.5.1
	 *
	 * @return array
	 */
	public function get_available() {
		return $this->core->config->get_available_compressors();
	}

	/**
	 * Hook into WordPress' filter: unzip_file_use_ziparchive
	 *
	 * @since 1.5.1
	 *
	 * @return bool
	 */
	public function unzip_file_use_ziparchive() {
		$settings = $this->core->settings->get_settings();

		/*
		 * By default WordPress is set to use ZipArchive by default. Only use
		 * PclZip if we explicitly set it.
		 */
		if( ! empty( $settings['extractor'] ) && 'pcl_zip' === $settings['extractor'] ) {
			return false;
		}

		return true;
	}
}