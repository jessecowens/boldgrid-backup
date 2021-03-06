<?php
/**
 * File: class-boldgrid-backup-admin-remote.php
 *
 * @link  https://www.boldgrid.com
 * @since 1.5.2
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Class: Boldgrid_Backup_Admin_Remote
 *
 * @since 1.5.2
 */
class Boldgrid_Backup_Admin_Remote {
	/**
	 * The core class object.
	 *
	 * @since  1.5.2
	 * @access private
	 * @var    Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * Constructor.
	 *
	 * @since 1.5.2
	 *
	 * @param Boldgrid_Backup_Admin_Core $core Boldgrid_Backup_Admin_Core object.
	 */
	public function __construct( $core ) {
		$this->core = $core;
	}

	/**
	 * Determine if any storage locations are enabled.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public function any_enabled() {
		$settings = $this->core->settings->get_settings();

		if ( empty( $settings ) || empty( $settings['remote'] ) ) {
			return false;
		}

		foreach ( $settings['remote'] as $remote ) {
			if ( isset( $remote['enabled'] ) && true === $remote['enabled'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return whether or not a remote storage provider is enabled.
	 *
	 * @since 1.5.2
	 *
	 * @param  string $id A remote storage id, such as "amazon_s3".
	 * @return bool
	 */
	public function is_enabled( $id ) {
		$settings = $this->core->settings->get_settings();

		return ! empty( $settings['remote'][ $id ]['enabled'] ) && true === $settings['remote'][ $id ]['enabled'];
	}

	/**
	 * Take action after a backup has been downloaded remotely.
	 *
	 * @since 1.6.0
	 *
	 * @see Boldgrid_Backup_Admin_Archive::init()
	 * @see Boldgrid_Backup_Admin_Archive::update_timestamp()
	 *
	 * @param string $filepath A file path.
	 */
	public function post_download( $filepath ) {
		// Update the archive's timestamp based upon time last modified time in the log.
		$this->core->archive->reset();
		$this->core->archive->init( $filepath );
		$this->core->archive->update_timestamp();
	}
}
