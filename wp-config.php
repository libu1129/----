<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'formax1' );

/** MySQL database username */
define( 'DB_USER', 'formax1' );

/** MySQL database password */
define( 'DB_PASSWORD', 'A123654a*' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '8~.o;jCQyf%328E+=buW^;BC_o1a;zrDl*TZgsF9O]^]SECV{P-@^yqJ^.J<9lP+' );
define( 'SECURE_AUTH_KEY',  '[6Mz`1u?o+*psI`>hsj ;lh7G*5jiAx,EjXp@HP {keG*;h$Sh(~KoPlL[mb)wa.' );
define( 'LOGGED_IN_KEY',    '8U?Ahae8ia3llj%u-&O-H6lNn-KxxSrq~Q>M0m)^5VsnM;lFC,gg%`[hg7Q4UvtC' );
define( 'NONCE_KEY',        'PQToa6p=]PyN:D,Qdq$ZyP#wx.i%E|AUY5xpc-E$t/^xJiYs+ 2$o?z`g$Gp>vmU' );
define( 'AUTH_SALT',        ';KeyT}GAU}B}1I9+pyabppOc):ee%QsO;=V$J=~$dRqb7=+<U_hoDpBCbSN%E%[o' );
define( 'SECURE_AUTH_SALT', 'laevQwrW)k<mx5Rkrj}(Sw.+?<pW]NQn+4~LEBR~tfh;hJh!1p*H8Uw4mmd[U>(~' );
define( 'LOGGED_IN_SALT',   'Fk!2?:)X-m.f`fgOAB3icN9|{/2m~F]zU}RA4rGx?~N0^;>`HAgFfgZ8vdWDk^N5' );
define( 'NONCE_SALT',       'C{%`x7b8T:3= m**6QQj|E]3d0j@Wr*y2h[Vyl=2PKTR1CO.no!~% @a#w0JuOa4' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
/* custom security setting */
define('DISALLOW_FILE_EDIT',true);
define('WP_POST_REVISIONS',7);
define('IMAGE_EDIT_OVERWRITE',true);
define('DISABLE_WP_CRON',true);
define('EMPTY_TRASH_DAYS',7);
