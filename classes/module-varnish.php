<?php

class BWPC_Module_Varnish extends G2K_WPPS_Base {
    protected $_postHooks = array(
        'save_post',
        'deleted_post',
        'trashed_post',
        'edit_post',
        'delete_attachment',
        'switch_theme',
    );
    protected $_commentHooks = array(
        'edit_post',
        'comment_post',
        'edit_comment',
        'trashed_comment',
        'untrashed_comment',
        'deleted_comment',
    );

    protected $_validPostStatus = array(
        'publish',
        'trash',
    );

    protected $_url2purge = array();

    public function register_hooks () {
        parent::register_hooks();

        foreach ($this->_postHooks as $hook) {
            add_action($hook, array(&$this, 'purgePost'));
        }
        foreach ($this->_commentHooks as $hook) {
            add_action($hook, array(&$this, 'purgeComment'));
        }
        add_action('shutdown', array(&$this, 'executePurge'));

        add_action( 'wp_before_admin_bar_render', array( $this, 'adminBar' ) );
    }

    public function purgeAll () {
        $this->_url2purge[] = '(.*)';
    }

    public function purgeCommon () {
        $this->_url2purge[] = '/';
        $this->_url2purge[] = '(.*)/feed/(.*)';
        $this->_url2purge[] = '(.*)/trackback/(.*)';
        $this->_url2purge[] = '/page/(.*)';
    }

    public function purgePost ($post_id) {
        if ( $url = get_permalink($post_id) && in_array(get_post_status($post_id), $this->_validPostStatus) ) {
            // Category & Tag purge based on Donnacha's work in WP Super Cache
            $categories = get_the_category($post_id);
            if ( $categories ) {
                $category_base = get_option('category_base');
                if ( $category_base == '' )
                    $category_base = '/category/';
                $category_base = trailingslashit( $category_base );
                foreach ($categories as $cat) {
                    $this->_url2purge[] = $category_base . $cat->slug . '/';
                }
            }

            $tags = get_the_tags($post_id);
            if ( $tags ) {
                $tag_base = get_option( 'tag_base' );
                if ( $tag_base == '' )
                    $tag_base = '/tag/';
                $tag_base = trailingslashit( str_replace( '..', '', $tag_base ) );
                foreach ($tags as $tag) {
                    $this->_url2purge[] = $tag_base . $tag->slug . '/';
                }
            }

            $link = str_replace(home_url(), '', $url);

            $this->_url2purge[] = $link;
            $this->_url2purge[] = $link . 'page/(.*)';
        }
    }

    public function purgeComment ($comment_id) {
        $comment = get_comment($comment_id);
        $approved = $comment->comment_approved;

        if ($approved || $approved == 'trash') {
            $approved = $comment->comment_post_ID;

            $this->_url2purge[] = '/\\\?comments_popup=' . $approved;
            $this->_url2purge[] = '/\\\?comments_popup=' . $approved . '&(.*)';
        }
    }

    public function executePurge () {
        $homeurl = trim(home_url(), '/');
        foreach (array_unique($this->_url2purge) as $url) {
            $this->_purgeUrl($homeurl . $url);
        }
    }

    protected function _purgeUrl ($url) {
        $url = parse_url($url);

        $varnishIPs = get_option('bwpc_varnish_ip');
        if (defined('BWPC_VARNISH_IP') and BWPC_VARNISH_IP) {
            $varnishIPs = BWPC_VARNISH_IP;
        }

        if (isset($varnishIPs)) {
            foreach (explode(',', $varnishIPs) as $varnishIP) {
                $this->_doPurge($url['scheme'] . '://' . $varnishIP . $url['path'], $url['host']);
            }
        } else {
            $this->_doPurge($url['scheme'] . '://' . $url['host'] . $url['path'], $url['host']);
        }
    }

    protected function _doPurge ($url4purge, $host) {
        return wp_remote_request($url4purge, array(
            'method'  => 'PURGE',
            'headers' => array(
                'host'           => $host,
                'X-Purge-Method' => 'regex',
            ),
        ));
    }
} 