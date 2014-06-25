<?php

class BWPC_Module_AdminBar extends G2K_WPPS_Base {
    public function register_hooks () {
        parent::register_hooks();

        add_action('wp_before_admin_bar_render', array($this, 'adminBar'));
    }

    public function adminBar () {
        global $wp_admin_bar, $post;

        if (current_user_can('manage_options')) {
            $nonce = wp_create_nonce(strtolower($this->_pluginSlug) . '-nonce');

            $wp_admin_bar->add_menu(array(
                'parent' => false,
                'id'     => strtolower($this->_pluginSlug),
                'title'  => $this->_pluginName,
                'href'   => '#',
                'meta'   => false,
            ));

            $wp_admin_bar->add_menu(array(
                'parent' => strtolower($this->_pluginSlug),
                'id'     => strtolower($this->_pluginSlug) . '-ca',
                'title'  => __('Clear All', strtolower($this->_pluginSlug)),
                'href'   => '#?_wpnonce=' . $nonce,
                'meta'   => false,
            ));

            if (isset($post->ID) and $post->ID) {
                $wp_admin_bar->add_menu(array(
                    'parent' => strtolower($this->_pluginSlug),
                    'id'     => strtolower($this->_pluginSlug) . '-cp',
                    'title'  => __('Clear This Post', strtolower($this->_pluginSlug)),
                    'href'   => '#?_wpnonce=' . $nonce,
                    'meta'   => false,
                ));
            }
        }
    }
} 