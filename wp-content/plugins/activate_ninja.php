<?
require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( ! file_exists( ABSPATH .'wp-content/nfwlog/ninjafirewall.php' ) ) {
    activate_plugin('ninjafirewall/ninjafirewall.php');
}
?>
