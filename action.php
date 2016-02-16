<?php
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();
class action_plugin_autheid extends DokuWiki_Action_Plugin {
    /**
     * Registers the event handlers.
     */
    function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('HTML_LOGINFORM_OUTPUT', 'BEFORE',  $this, 'hook_html_loginform_output', array());
    }
    /**
     * Handles the login form rendering.
     */
    function hook_html_loginform_output(&$event, $param) {

        //$url = DOKU_URL . '/lib/plugins/autheid/auth/';
        //$url = 'https://jkoolitus.eu/eid/?id=arvi';
        $url = $this->getConf('login_url');

        ?>
            <h3>ID kaardiga sisselogimine</h3>
            <p>
                <a href="<?php echo $url; ?>"><img src="<?php echo DOKU_URL . '/lib/plugins/autheid/images/idkaart.gif'; ?>"></img></a>
            </p>

        <?php
    }
}
?>
