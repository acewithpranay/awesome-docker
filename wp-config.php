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
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'cars24_wordpoets_com_V0LcuRwo' );

/** Database username */
define( 'DB_USER', 'cars24wordpoX251' );

/** Database password */
define( 'DB_PASSWORD', 'chpxi8VlNSJu613YHCIGsmyZ' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',          'o.wnQ!LknX#]D#einlpjGP.ISm?EQ07`c5c_v<gF#AQFD>&j4_WZSc~x}Z#J[)dN' );
define( 'SECURE_AUTH_KEY',   'j_zO46M&&z$rV-#ku?L0^vA1AxZ?2ojEn_qKVEi6]@|R>o>cyPnd1@R]TG_syXk*' );
define( 'LOGGED_IN_KEY',     '9P:{<P-P_M.(Ps`{V@FY|@%)vQlM,;llN]KV!~Cf._-<#{zN=s`ufOwFp+q:v|2 ' );
define( 'NONCE_KEY',         '4pW{mNO%Gd#-ef#_jRDkDqrcbtGbnE1_`_Nhj6*w(LQ^z:(PgE`BiJHhmL#$Gr Q' );
define( 'AUTH_SALT',         '5:-Vnh0Nuv9`.~hY|}k_D,i>NgWR$vX)2rQnSz=>`3W? -E+1nhoCGi1%_A=Z3a`' );
define( 'SECURE_AUTH_SALT',  '$.#dPWUOT6c>l2q5OCve[2diaLVzp&@%*@a5_pr(~A&h9q0M<I6]=HT :RL.d{=`' );
define( 'LOGGED_IN_SALT',    'nQ[:2{l*rIPsUvPr@tEBBxAsz<=y~Fg|1XoU[D[<rKKQ=WUrz&m~m7b%m#_V)F/a' );
define( 'NONCE_SALT',        'GKPvD$J<j%pO 22$EE)yt[=j*ON+?{qL* 8bHHn!]?:*r4+ypIV_^W_okxb9Y^5b' );
define( 'WP_CACHE_KEY_SALT', 'xc?Y|`cy^S26iF>F|ll`tkRIme}HoLYXAo:6Wb!IY-u2@G&KO*a2F-<J@`th(.G{' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define('FORCE_SSL_ADMIN', true);
// in some setups HTTP_X_FORWARDED_PROTO might contain
// a comma-separated list e.g. http,https
// so check for https existence
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && strpos( $_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false ) {
   $_SERVER['HTTPS'] = 'on';
}

define( 'WP_REDIS_PREFIX', 'cars24.wordpoets.com:' );
define( 'WP_MEMORY_LIMIT', '512M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );
define( 'CONCATENATE_SCRIPTS', false );
define( 'WP_POST_REVISIONS', '100' );
define( 'MEDIA_TRASH', true );
define( 'EMPTY_TRASH_DAYS', '15' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
define( 'WP_REDIS_DISABLE_BANNERS', true );
define( 'REDIS_DATABASE_GLOBAL_CACHE', '11' );
define( 'REDIS_DATABASE_SESSION_CACHE', '12' );
define( 'REDIS_HOST', '127.0.0.1' );
define( 'REDIS_PORT', '6379' );
define( 'LOG_PATH', '/var/www/cars24.wordpoets.com/htdocs/wp-content/uploads/log' );
define( 'AWESOME_PATH', '/var/www/awesome-enterprise' );
define( 'SITE_URL', ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] );
define( 'HOME_URL', ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] );
define( 'CONNECTIONS', array('cdn_code'=> array('connection_service'=>'url_conn', 'url'=>'https://cdn.getawesomestudio.com/code', 'redis_db'=>1, 'cache_expiry'=>30000)) );
/* That's all, stop editing! Happy publishing. */

	define('DB_CONNECTIONS',
		array(
			'primary_db'=>array(
				'host'=>DB_HOST,
				'user'=>DB_USER,
				'password'=>DB_PASSWORD
			)
	
		));
	//This is required so that we can use mysqli.* as a shortcode since it is already there. In a new system this is not required
define('MYSQLI_CONNECTION','primary_db');


/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
