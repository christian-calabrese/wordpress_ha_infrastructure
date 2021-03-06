<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * This has been slightly modified (to read environment variables) for use in Docker.
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// IMPORTANT: this file needs to stay in-sync with https://github.com/WordPress/WordPress/blob/master/wp-config-sample.php
// (it gets parsed by the upstream wizard in https://github.com/WordPress/WordPress/blob/f27cb65e1ef25d11b535695a660e7282b98eb742/wp-admin/setup-config.php#L356-L392)

// a helper function to lookup "env_FILE", "env", then fallback
if (!function_exists('getenv_docker')) {
	// https://github.com/docker-library/wordpress/issues/588 (WP-CLI will load this file 2x)
	function getenv_docker($env, $default) {
		if ($fileEnv = getenv($env . '_FILE')) {
			return rtrim(file_get_contents($fileEnv), "\r\n");
		}
		else if (($val = getenv($env)) !== false) {
			return $val;
		}
		else {
			return $default;
		}
	}
}

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', getenv_docker('WORDPRESS_DB_NAME', 'wordpress') );

/** MySQL database username */
define( 'DB_USER', getenv_docker('WORDPRESS_DB_USER', 'example username') );

/** MySQL database password */
define( 'DB_PASSWORD', getenv_docker('WORDPRESS_DB_PASSWORD', 'example password') );

/**
 * Docker image fallback values above are sourced from the official WordPress installation wizard:
 * https://github.com/WordPress/WordPress/blob/f9cc35ebad82753e9c86de322ea5c76a9001c7e2/wp-admin/setup-config.php#L216-L230
 * (However, using "example username" and "example password" in your database is strongly discouraged.  Please use strong, random credentials!)
 */

/** MySQL hostname */
define( 'DB_HOST', getenv_docker('WORDPRESS_DB_HOST', 'mysql') );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', getenv_docker('WORDPRESS_DB_CHARSET', 'utf8') );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', getenv_docker('WORDPRESS_DB_COLLATE', '') );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         getenv_docker('WORDPRESS_AUTH_KEY',         'put your unique phrase here') );
define( 'SECURE_AUTH_KEY',  getenv_docker('WORDPRESS_SECURE_AUTH_KEY',  'put your unique phrase here') );
define( 'LOGGED_IN_KEY',    getenv_docker('WORDPRESS_LOGGED_IN_KEY',    'put your unique phrase here') );
define( 'NONCE_KEY',        getenv_docker('WORDPRESS_NONCE_KEY',        'put your unique phrase here') );
define( 'AUTH_SALT',        getenv_docker('WORDPRESS_AUTH_SALT',        'put your unique phrase here') );
define( 'SECURE_AUTH_SALT', getenv_docker('WORDPRESS_SECURE_AUTH_SALT', 'put your unique phrase here') );
define( 'LOGGED_IN_SALT',   getenv_docker('WORDPRESS_LOGGED_IN_SALT',   'put your unique phrase here') );
define( 'NONCE_SALT',       getenv_docker('WORDPRESS_NONCE_SALT',       'put your unique phrase here') );
// (See also https://wordpress.stackexchange.com/a/152905/199287)

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = getenv_docker('WORDPRESS_TABLE_PREFIX', 'wp_');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', !!getenv_docker('WORDPRESS_DEBUG', '') );

/* Add any custom values between this line and the "stop editing" line. */

// If we're behind a proxy server and using HTTPS, we need to alert WordPress of that fact
// see also https://wordpress.org/support/article/administration-over-ssl/#using-a-reverse-proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
	$_SERVER['HTTPS'] = 'on';
}
// (we include this by default because reverse proxying is extremely common in container environments)

if ($configExtra = getenv_docker('WORDPRESS_CONFIG_EXTRA', '')) {
	eval($configExtra);
}

define( 'AS3CF_SETTINGS', serialize( array(
    // Storage Provider ('aws', 'do', 'gcp')
    //'provider' => 'aws',
    // Access Key ID for Storage Provider (aws and do only, replace '*')
    'access-key-id' => '********************',
    // Secret Access Key for Storage Providers (aws and do only, replace '*')
    //'secret-access-key' => '**************************************',
    // GCP Key File Path (gcp only, absolute file path, not URL)
    // Make sure hidden from public website, i.e. outside site's document root.
    //'key-file-path' => '/path/to/key/file.json',
    // Use IAM Roles on Amazon Elastic Compute Cloud (EC2) or Google Compute Engine (GCE)
    'use-server-roles' => true,
    // Bucket to upload files to
    'bucket' => getenv('MEDIA_S3_BUCKET'),
    // Bucket region (e.g. 'us-west-1' - leave blank for default region)
    'region' => 'eu-west-1',
    // Automatically copy files to bucket on upload
    'copy-to-s3' => true,
    // Enable object prefix, useful if you use your bucket for other files
    'enable-object-prefix' => true,
    // Object prefix to use if 'enable-object-prefix' is 'true'
    'object-prefix' => 'wp-content/uploads/',
    // Organize bucket files into YYYY/MM directories matching Media Library upload date
    'use-yearmonth-folders' => true,
    // Append a timestamped folder to path of files offloaded to bucket to avoid filename clashes and bust CDN cache if updated
    'object-versioning' => true,
    // Delivery Provider ('storage', 'aws', 'do', 'gcp', 'cloudflare', 'keycdn', 'stackpath', 'other')
    //'delivery-provider' => 'storage',
    // Custom name to display when using 'other' Delivery Provider
    //'delivery-provider-name' => 'Akamai',
    // Rewrite file URLs to bucket
    'serve-from-s3' => true,
    // Use a custom domain (CNAME), not supported when using 'storage' Delivery Provider
    'enable-delivery-domain' => false,
    // Custom domain (CNAME), not supported when using 'storage' Delivery Provider
    // 'delivery-domain' => 'cdn.example.com',
    // Enable signed URLs for Delivery Provider that uses separate key pair (currently only 'aws' supported, a.k.a. CloudFront)
    'enable-signed-urls' => false,
    // Access Key ID for signed URLs (aws only, replace '*')
    // 'signed-urls-key-id' => '********************',
    // Key File Path for signed URLs (aws only, absolute file path, not URL)
    // Make sure hidden from public website, i.e. outside site's document root.
    // 'signed-urls-key-file-path' => '/path/to/key/file.pem',
    // Private Prefix for signed URLs (aws only, relative directory, no wildcards)
    // 'signed-urls-object-prefix' => 'private/',
    // Serve files over HTTPS
    'force-https' => false,
    // Remove the local file version once offloaded to bucket
    'remove-local-file' => false,
    // DEPRECATED (use enable-delivery-domain): Bucket URL format to use ('path', 'cloudfront')
    //'domain' => 'path',
    // DEPRECATED (use delivery-domain): Custom domain if 'domain' set to 'cloudfront'
    //'cloudfront' => 'cdn.exmple.com',
) ) );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';