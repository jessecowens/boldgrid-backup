<?php
/**
 * File: config.plugin.php
 *
 * Plugin configuration file.
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

// Prevent direct calls.
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

return array(
	'urls'                 => array(
		'compatibility'       => 'https://www.boldgrid.com/support/advanced-tutorials/backup-compatibility-guide',
		'possible_issues'     => 'https://www.boldgrid.com/support/advanced-tutorials/backup-userguide#possible-issues',
		'reduce_size_warning' => 'https://www.boldgrid.com/support/advanced-tutorials/backup-userguide#reduce-size-warning',
		'resource_usage'      => 'https://www.boldgrid.com/support/advanced-tutorials/backup-userguide#resource-usage',
		'upgrade'             => 'https://www.boldgrid.com/support/advanced-tutorials/backup-userguide#upgrade',
		'user_guide'          => 'https://www.boldgrid.com/support/advanced-tutorials/backup-userguide',
		'restore'             => 'https://www.boldgrid.com/support/advanced-tutorials/restoring-boldgrid-backup/',
		'setting_directory'   => 'https://www.boldgrid.com/support/advanced-tutorials/backup-userguide#setting-backup-directory',
	),
	'lang'                 => array(
		// translators: 1: Number of seconds.
		'est_pause' => esc_html__( 'Estimated Pause: %s seconds', 'boldgrid-backup' ),
	),
	'public_link_lifetime' => '1 HOUR',
	'url_regex'            => '^https?:\/\/[a-z0-9\-\.]+(\.[a-z]{2,5})?(:[0-9]{1,5})?(\/.*)?$',
	/*
	 * When we login to a remote storage provider, we log the utc timestamp of that login. Sometimes
	 * we want to know if a remote storage provider is setup, and usually we check by trying to log
	 * in successfully. To skip having to log in, we can simply check the last time we logged in.
	 * For example, if we logged in 2 hours ago, usually we can say that the remote storage is setup
	 * correctly because we logged in successfully just 2 hours prior. last_login_lifetime specifies
	 * this time limit. If we logged in within 'last_login_lifetime' ago, assume the remote storage
	 * is still setup successfully. This is not across the board though, each storage provider must
	 * setup this last login cache and check against it.
	 */
	'last_login_lifetime'  => DAY_IN_SECONDS,
);
