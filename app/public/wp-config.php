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
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',          '+3alUm,4A4jI>:&xQG}FI5*m=6I u>mvIU+dG!<n>Kqa[/%7pej!H/uCx3B6mad6' );
define( 'SECURE_AUTH_KEY',   '>k8X+3ZxmCm?xU3W,~0|:ZY!jQt_Eu)DzeD&B[$7qfNG5)vIn_!R5D:zv_&ub+AL' );
define( 'LOGGED_IN_KEY',     'Ju.H48% y{EN$s~sNiX@$2,@sNb+5_bT[U:^#k :Xp@QDm6@5n-L7#jjJ-)_B,aZ' );
define( 'NONCE_KEY',         'a<uJ1.jKiBLbq04y(S*.m(K_(01TMs-*CO;8j;I&J97,08l(%dI474u~PV%8[{vn' );
define( 'AUTH_SALT',         '+*V(z50]*dW|os{$P?}PI7!{yyF5eM+iD];b<_.RJ5/t6,[9p{SFVdy^&W-LCjp6' );
define( 'SECURE_AUTH_SALT',  ' 1Rhqg=92D)PVl8GtmqV$b-L|%;By4&r~n2486Iblo>1{07ng!mvj.kro6x$(!.(' );
define( 'LOGGED_IN_SALT',    'zVe`#RL&21JxJUEN%GnD1{[=lTK.ym|p7v?_-#=I{xbf}*G/UFo<@vE@ZqS{n1Am' );
define( 'NONCE_SALT',        'zQE>Z&E6Of=*kSHtt)JpM-`X[u/B^|Kv6 H1F*0%j)l:U}9T^r8,gU$h1.,m)M)c' );
define( 'WP_CACHE_KEY_SALT', 'N@9DdiU6`#`Njj^]QcIiboUiFm-|w[@3:RR[$C_chXZQ<eb4gE_$dL[ScP/l.G;F' );


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

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
