<?php

class Best_WP_Cache extends G2K_WPPS_Plugin {
    public function register_hooks () {
        parent::register_hooks();

        add_action(	'edit_post', array( $this, 'purgePost' ), 99 );
        add_action(	'edit_post', array(  $this, 'purgeCommon' ), 99 );
        add_action(	'deleted_post', array( $this, 'purgePost' ), 99 );
        add_action(	'deleted_post', array( $this, 'purgeCommon' ), 99 );

        add_action( 'wp_before_admin_bar_render', array( $this, 'adminBar' ) );
    }

    public function purgeAll () {
        return $this->_purgeVarnish('(.*)');
    }

    public function purgeCommon () {
        return $this->_purgeVarnish(array(
            '/',
            '(.*)/feed/(.*)',
            '(.*)/trackback/(.*)',
            '/page/(.*)',
        ));
    }

    public function purgePost ($post_id) {
        $url  = get_permalink($post_id);
        $link = str_replace(home_url(), '', $url);

        return $this->_purgeVarnish(array(
            $link,
            $link . 'page/(.*)',
        ));
    }

    protected function _purgeVarnish ($target) {
        @ini_set( 'auto_detect_line_endings', true );

        $host = parse_url( get_site_url(), PHP_URL_HOST );

        $out  = 'PURGE ' . $target . ' HTTP/1.0' . PHP_EOL;
        $out .= 'Host: ' . $host . PHP_EOL;
        $out .= 'Connection: Close' . PHP_EOL . PHP_EOL;

        $sock = @fsockopen( '127.0.0.1', 80, $errno, $errstr, 5 );

        if ( $sock ) {
            @fwrite( $sock, $out );
            $result = @fread( $sock, 256 );
            @fclose( $sock );

            if ( preg_match( '/200 OK/', $result ) || preg_match( '/200 Purged/', $result ) ) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
} 