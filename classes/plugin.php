<?php

class Best_WP_Cache extends G2K_WPPS_Plugin {
    public function init () {
        require_once __DIR__ . '/module-varnish.php';
        $this->_modules['varnish'] = new BWPC_Module_Varnish($this->_pluginName, $this->_pluginSlug, $this->_pluginPath);
    }
}