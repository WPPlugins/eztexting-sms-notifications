<?php
class WP_Functions {
    
    public function wp_redirect($url, $exit = false) {
        wp_redirect( $url );
        if( $exit )
            exit;
    }

    /**
     * Makes sure that a user was referred from another admin page.
     *
     * @param string $action Action nonce
     * @param string $query_arg where to look for nonce in $_REQUEST (since 2.5)
     * @see /wp-includes/pluggable.php
     */
    public function check_admin_referer($action = -1, $query_arg = '_wpnonce') {
        return check_admin_referer($action, $query_arg);
    }

    /**
     * Kill WordPress execution and display HTML message with error message.
     *
     *
     * @param string $message Error message.
     * @param string $title Error title.
     * @param string|array $args Optional arguments to control behavior.
     * @see /wp-includes/functions.php
     */
    public function wp_die($message, $title = '', $args = array()) {
        return wp_die($message, $title, $args);
    }

    /**
     * Deactivate a single plugin or multiple plugins.
     *
     * The deactivation hook is disabled by the plugin upgrader by using the $silent
     * parameter.
     *
     * @since 2.5.0
     *
     * @param string|array $plugins Single plugin or list of plugins to deactivate.
     * @param bool $silent Prevent calling deactivation hooks. Default is false.
     * @see /wp-includes/plugin.php
     */
    public function deactivate_plugins($plugins, $silent = false) {
        return deactivate_plugins($plugins, $silent = false);
    }

    /**
     * Whether current user has capability or role.
     *
     * @since 2.0.0
     *
     * @param string $capability Capability or role name.
     * @return bool
     * @see /wp-includes/capabilities.php
     */
    public function current_user_can($capability) {
        return current_user_can($capability);
    }
}

