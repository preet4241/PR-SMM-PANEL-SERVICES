<?php
/**
 * This file belongs to the SMM Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !function_exists( 'smm_plugin_locate_template' ) ) {
    /**
     * Locate the templates and return the path of the file found
     *
     * @param string $plugin_basename
     * @param string $path
     * @param array  $var
     *
     * @return string
     * @since 2.0.0
     */
    function smm_plugin_locate_template( $plugin_basename, $path, $var = null ) {

        $template_path = '/theme/templates/' . $path;

        $located = locate_template( array(
                                        $template_path,
                                    ) );

        if ( !$located ) {
            $located = $plugin_basename . '/templates/' . $path;
        }

        return $located;
    }

}

if ( !function_exists( 'smm_plugin_get_template' ) ) {
    /**
     * Retrieve a template file.
     *
     * @param string $plugin_basename
     * @param string $path
     * @param mixed  $var
     * @param bool   $return
     *
     * @return string
     * @since 2.0.0
     */
    function smm_plugin_get_template( $plugin_basename, $path, $var = null, $return = false ) {

        $located = smm_plugin_locate_template( $plugin_basename, $path, $var );

        if ( $var && is_array( $var ) ) {
            extract( $var );
        }

        if ( $return ) {
            ob_start();
        }

        // include file located
        if ( file_exists( $located ) ) {
            include( $located );
        }

        if ( $return ) {
            return ob_get_clean();
        }
    }
}

if ( !function_exists( 'smm_plugin_content' ) ) {
    /**
     * Return post content with read more link (if needed)
     *
     * @param string     $what
     * @param int|string $limit
     * @param string     $more_text
     * @param string     $split
     * @param string     $in_paragraph
     *
     * @return string
     * @since 2.0.0
     */
    function smm_plugin_content( $what = 'content', $limit = 25, $more_text = '', $split = '[...]', $in_paragraph = 'true' ) {
        if ( $what == 'content' ) {
            $content = get_the_content( $more_text );
        } else {
            if ( $what == 'excerpt' ) {
                $content = get_the_excerpt();
            } else {
                $content = $what;
            }
        }

        if ( $limit == 0 ) {
            if ( $what == 'excerpt' ) {
                $content = apply_filters( 'the_excerpt', $content );
            } else {
                $content = preg_replace( '/<img[^>]+./', '', $content ); //remove images
                $content = apply_filters( 'the_content', $content );
                $content = str_replace( ']]>', ']]&gt;', $content );
            }

            return $content;
        }

        // remove the tag more from the content
        if ( preg_match( "/<(a)[^>]*class\s*=\s*(['\"])more-link\\2[^>]*>(.*?)<\/\\1>/", $content, $matches ) ) {

            if ( strpos( (string)$matches[ 0 ], '[button' ) ) {
                $more_link = str_replace( 'href="#"', 'href="' . get_permalink() . '"', do_shortcode( $matches[ 3 ] ) );
            } else {
                $more_link = $matches[ 0 ];
            }

            $content = str_replace( $more_link, '', $content );
            $split   = '';
        }

        if ( empty( $content ) ) {
            return;
        }
        $content = explode( ' ', $content );

        if ( !empty( $more_text ) && !isset( $more_link ) ) {
            //array_pop( $content );
            $more_link = strpos( (string)$more_text, '<a class="btn"' ) ? $more_text : '<a class="read-more' . apply_filters( 'smm_simple_read_more_classes', ' ' ) . '" href="' . get_permalink() . '">' . $more_text . '</a>';
            $split     = '';
        } elseif ( !isset( $more_link ) ) {
            $more_link = '';
        }

        // split
        if ( count( $content ) >= $limit ) {
            $split_content = '';
            for ( $i = 0; $i < $limit; $i++ ) {
                $split_content .= $content[ $i ] . ' ';
            }

            $content = $split_content . $split;
        } else {
            $content = implode( " ", $content );
        }

        // TAGS UNCLOSED
        $tags = array();
        // get all tags opened
        preg_match_all( "/(<([\w]+)[^>]*>)/", $content, $tags_opened, PREG_SET_ORDER );
        foreach ( $tags_opened as $tag ) {
            $tags[] = $tag[ 2 ];
        }

        // get all tags closed and remove it from the tags opened.. the rest will be closed at the end of the content
        preg_match_all( "/(<\/([\w]+)[^>]*>)/", $content, $tags_closed, PREG_SET_ORDER );
        foreach ( $tags_closed as $tag ) {
            unset( $tags[ array_search( $tag[ 2 ], $tags ) ] );
        }

        // close the tags
        if ( !empty( $tags ) ) {
            foreach ( $tags as $tag ) {
                $content .= "</$tag>";
            }
        }

        //$content = preg_replace( '/\[.+\]/', '', $content );
        if ( $in_paragraph == true ): $content .= $more_link; endif;
        $content = preg_replace( '/<img[^>]+./', '', $content ); //remove images
        $content = apply_filters( 'the_content', $content );
        $content = str_replace( ']]>', ']]&gt;', $content ); // echo str_replace( array( '<', '>' ), array( '&lt;', '&gt;' ), $content );
        if ( $in_paragraph == false ): $content .= $more_link; endif;

        return $content;
    }
}

if ( !function_exists( 'smm_plugin_string' ) ) {
    /**
     * Simple echo a string, with a before and after string, only if the main string is not empty.
     *
     * @param string $before What there is before the main string
     * @param string $string The main string. If it is empty or null, the functions return null.
     * @param string $after  What there is after the main string
     * @param bool   $echo   If echo or only return it
     *
     * @return string The complete string, if the main string is not empty or null
     * @since 2.0.0
     */
    function smm_plugin_string( $before = '', $string = '', $after = '', $echo = true ) {
        $html = '';

        if ( $string != '' AND !is_null( $string ) ) {
            $html = $before . $string . $after;
        }

        if ( $echo ) {
            echo wp_kses($html);
        }

        return $html;
    }
}

if ( !function_exists( 'smm_plugin_decode_title' ) ) {
    /**
     * Change some special characters to put easily html into a string
     *
     * E.G.
     * string: This is [my title] with | a new line
     * return: This is <span class="title-highlight">my title</span> with <br /> a new line
     *
     * @param  string $title The string to convert
     *
     * @return string  The html
     *
     * @since 1.0
     */
    function smm_plugin_decode_title( $title ) {
        $replaces = apply_filters( 'smm_title_special_characters', array() );

        return preg_replace( array_keys( $replaces ), array_values( $replaces ), $title );
    }
}

if ( !function_exists( 'smm_plugin_get_attachment_id' ) ) {

    /**
     * Return the ID of an attachment.
     *
     * @param string $url
     *
     * @return int
     *
     * @since 2.0.0
     */

    function smm_plugin_get_attachment_id( $url ) {

        $upload_dir = wp_upload_dir();
        $dir        = trailingslashit( $upload_dir[ 'baseurl' ] );

        if ( false === strpos( (string)$url, $dir ) ) {
            return false;
        }

        $file = basename( $url );

        $query = array(
            'post_type'  => 'attachment',
            'fields'     => 'ids',
            'meta_query' => array(
                array(
                    'value'   => $file,
                    'compare' => 'LIKE',
                ),
            ),
        );

        $query[ 'meta_query' ][ 0 ][ 'key' ] = '_wp_attached_file';
        $ids                                 = get_posts( $query );

        foreach ( $ids as $id ) {
            $attachment_image = wp_get_attachment_image_src( $id, 'full' );
            if ( $url == array_shift( $attachment_image ) || $url == str_replace( 'https://', 'http://', array_shift( $attachment_image ) ) ) {
                return $id;
            }
        }
        $query[ 'meta_query' ][ 0 ][ 'key' ] = '_wp_attachment_metadata';
        $ids                                 = get_posts( $query );

        foreach ( $ids as $id ) {

            $meta = wp_get_attachment_metadata( $id );
            if ( !isset( $meta[ 'sizes' ] ) ) {
                continue;
            }

            foreach ( (array) $meta[ 'sizes' ] as $size => $values ) {
                if ( $values[ 'file' ] == $file && $url == str_replace( 'https://', 'http://', array_shift( wp_get_attachment_image_src( $id, $size ) ) ) ) {

                    return $id;
                }
            }
        }

        return false;
    }
}

if ( !function_exists( 'smm_enqueue_script' ) ) {
    /**
     * Enqueues script.
     *
     * Registers the script if src provided (does NOT overwrite) and enqueues.
     *
     * @since  2.0.0
     * @author sam
     * @see    smm_register_script() For parameter information.
     */
    function smm_enqueue_script( $handle, $src, $deps = array(), $ver = false, $in_footer = true ) {

        if ( function_exists( 'SMM_Asset' ) && !is_admin() ) {
            $enqueue = true;
            SMM_Asset()->set( 'script', $handle, compact( 'src', 'deps', 'ver', 'in_footer', 'enqueue' ) );
        } else {
            wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
        }
    }
}

if ( !function_exists( 'smm_enqueue_style' ) ) {
    /**
     * Enqueues style.
     *
     * Registers the style if src provided (does NOT overwrite) and enqueues.
     *
     * @since  2.0.0
     * @author sam
     * @see    smm_register_style() For parameter information.
     */
    function smm_enqueue_style( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) {

        if ( function_exists( 'SMM_Asset' ) ) {
            $enqueue = true;
            $who     = SMM_Asset()->get_stylesheet_handle( get_stylesheet_uri(), 'style' );
            $where   = 'before';

            if ( false == $who ) {
                $who = '';
            }

            SMM_Asset()->set( 'style', $handle, compact( 'src', 'deps', 'ver', 'media', 'enqueue' ), $where, $who );
        } else {
            wp_enqueue_style( $handle, $src, $deps, $ver, $media );
        }
    }
}

if ( !function_exists( 'smm_get_post_meta' ) ) {
    /**
     * Retrieve the value of a metabox.
     *
     * This function retrieve the value of a metabox attached to a post. It return either a single value or an array.
     *
     * @param int    $id   Post ID.
     * @param string $meta The meta key to retrieve.
     *
     * @return mixed Single value or array
     * @since    2.0.0
     */
    function smm_get_post_meta( $id, $meta ) {
        if ( !strpos( (string)$meta, '[' ) ) {
            return get_post_meta( $id, $meta, true );
        }

        $sub_meta = explode( '[', $meta );

        $meta = get_post_meta( $id, current( $sub_meta ), true );
        for ( $i = 1; $i < count( $sub_meta ); $i++ ) {
            $current_submeta = rtrim( $sub_meta[ $i ], ']' );
            if ( !isset( $meta[ $current_submeta ] ) )
                return false;
            $meta = $meta[ $current_submeta ];
        }

        return $meta;
    }
}

if ( !function_exists( 'smm_string' ) ) {
    /**
     * Simple echo a string, with a before and after string, only if the main string is not empty.
     *
     * @param string $before What there is before the main string
     * @param string $string The main string. If it is empty or null, the functions return null.
     * @param string $after  What there is after the main string
     * @param bool   $echo   If echo or only return it
     *
     * @return string The complete string, if the main string is not empty or null
     * @since 2.0.0
     */
    function smm_string( $before = '', $string = '', $after = '', $echo = true ) {
        $html = '';

        if ( $string != '' AND !is_null( $string ) ) {
            $html = $before . $string . $after;
        }

        if ( $echo ) {
            echo wp_kses($html);
        }

        return $html;
    }
}

if ( !function_exists( 'smm_pagination' ) ) {
    /**
     * Print pagination
     *
     * @param string $pages
     * @param int    $range
     *
     * @return string
     * @since 2.0.0
     */
    function smm_pagination( $pages = '', $range = 10 ) {
        $showitems = ( $range * 2 ) + 1;

        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : false;
        if ( $paged === false ) {
            $paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : false;
        }
        if ( $paged === false ) {
            $paged = 1;
        }


        $html = '';

        if ( $pages == '' ) {
            global $wp_query;

            if ( isset( $wp_query->max_num_pages ) ) {
                $pages = $wp_query->max_num_pages;
            }

            if ( !$pages ) {
                $pages = 1;
            }
        }

        if ( 1 != $pages ) {
            $html .= "<div class='general-pagination clearfix'>";
            if ( $paged > 2 ) {
                $html .= sprintf( '<a class="%s" href="%s">&laquo;</a>', 'smm_pagination_first', get_pagenum_link( 1 ) );
            }
            if ( $paged > 1 ) {
                $html .= sprintf( '<a class="%s" href="%s">&lsaquo;</a>', 'smm_pagination_previous', get_pagenum_link( $paged - 1 ) );
            }

            for ( $i = 1; $i <= $pages; $i++ ) {
                if ( 1 != $pages && ( !( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
                    $class = ( $paged == $i ) ? " class='selected'" : '';
                    $html  .= "<a href='" . get_pagenum_link( $i ) . "'$class >$i</a>";
                }
            }

            if ( $paged < $pages ) {
                $html .= sprintf( '<a class="%s" href="%s">&rsaquo;</a>', 'smm_pagination_next', get_pagenum_link( $paged + 1 ) );
            }
            if ( $paged < $pages - 1 ) {
                $html .= sprintf( '<a class="%s" href="%s">&raquo;</a>', 'smm_pagination_last', get_pagenum_link( $pages ) );
            }

            $html .= "</div>\n";
        }

        echo wp_kses(apply_filters( 'smm_pagination_html', $html));
    }
}

if ( !function_exists( 'smm_registered_sidebars' ) ) {
    /**
     * Retrieve all registered sidebars
     *
     * @return array
     * @since 2.0.0
     */
    function smm_registered_sidebars() {
        global $wp_registered_sidebars;

        $return = array();

        if ( empty( $wp_registered_sidebars ) ) {
            $return = array( '' => '' );
        }

        foreach ( ( array ) $wp_registered_sidebars as $the_ ) {
            $return[ $the_[ 'name' ] ] = $the_[ 'name' ];
        }

        ksort( $return );

        return $return;
    }
}

if ( !function_exists( 'smm_layout_option' ) ) {
    /**
     * Retrieve a layout option
     *
     * @param        $key
     * @param bool   $id
     * @param string $type
     * @param string $model
     *
     * @return array
     * @since 2.0.0
     */
    function smm_layout_option( $key, $id = false, $type = "post", $model = "post_type" ) {

        $option = '';

        if ( defined( 'SMM' ) ) {
            $option = SMM_Layout_Panel()->get_option( $key, $id, $type, $model );
        } else {
            if ( !$id && ( is_single() || is_page() ) ) {
                global $post;
                $id = $post->ID;
            } elseif ( $id != 'all' ) {
                $option = get_post_meta( $id, $key );
            }
        }

        return $option;
    }
}

if ( !function_exists( 'smm_curPageURL' ) ) {
    /**
     * Retrieve the current complete url
     *
     * @since 1.0
     */
    function smm_curPageURL() {
		
        $pageURL = 'http';
        if ( isset( $_SERVER[ "HTTPS" ] ) AND $_SERVER[ "HTTPS" ] == "on" ) {
            $pageURL .= "s";
        }

        $pageURL .= "://";

        if ( isset( $_SERVER[ "SERVER_PORT" ] ) AND $_SERVER[ "SERVER_PORT" ] != "80" ) {
            $pageURL .= sanitize_text_field($_SERVER[ "SERVER_NAME" ]) . ":" . sanitize_text_field($_SERVER[ "SERVER_PORT" ]) . sanitize_text_field($_SERVER[ "REQUEST_URI" ]);
        } else {
            $pageURL .= sanitize_text_field($_SERVER[ "SERVER_NAME" ]) . sanitize_text_field($_SERVER[ "REQUEST_URI" ]);
        }

        return $pageURL;
    }
}

if ( !function_exists( 'smm_get_excluded_categories' ) ) {
    /**
     *
     * Retrieve the escluded categories, set on Theme Options
     *
     * @param int $k
     *
     * @return string String with all id categories excluded, separated by a comma
     *
     * @since 2.0.0
     */

    function smm_get_excluded_categories( $k = 1 ) {

        global $post;

        if ( !isset( $post->ID ) ) {
            return;
        }

        $cf_cats = get_post_meta( $post->ID, 'blog-cats', true );

        if ( !empty( $cf_cats ) ) {
            return $cf_cats;
        }

        $cats = function_exists( 'smm_get_option' ) ? smm_get_option( 'blog-excluded-cats' ) : '';


        if ( !is_array( $cats ) || empty( $cats ) || !isset( $cats[ $k ] ) ) {
            return;
        }

        $cats = array_map( 'trim', $cats[ $k ] );

        $i     = 0;
        $query = '';
        foreach ( $cats as $cat ) {
            $query .= ",-$cat";

            $i++;
        }

        ltrim( ',', $query );

        return $query;
    }
}

if ( !function_exists( 'smm_add_extra_theme_headers' ) ) {
    add_filter( 'extra_theme_headers', 'smm_add_extra_theme_headers' );

    /**
     * Check the framework core version
     *
     * @param $headers Array
     *
     * @return bool
     * @since  2.0.0
     * @author sam softnwords
     */
    function smm_add_extra_theme_headers( $headers ) {
        $headers[] = 'Core Framework Version';

        return $headers;
    }
}

if ( !function_exists( 'smm_check_plugin_support' ) ) {
    /**
     * Check the framework core version
     *
     * @return bool
     * @since  2.0.0
     * @author sam softnwords
     */
    function smm_check_plugin_support() {

        $headers[ 'core' ]   = wp_get_theme()->get( 'Core Framework Version' );
        $headers[ 'author' ] = wp_get_theme()->get( 'Author' );

        if ( !$headers[ 'core' ] && defined( 'SMM_CORE_VERSION' ) ) {
            $headers[ 'core' ] = SMM_CORE_VERSION;
        }

        if ( ( !empty( $headers[ 'core' ] ) && version_compare( $headers[ 'core' ], '2.0.0', '<=' ) ) || $headers[ 'author' ] != 'Softnwords Themes' ) {
            return true;
        } else {
            return false;
        }
    }
}

if ( !function_exists( 'smm_ie_version' ) ) {
    /**
     * Retrieve IE version.
     *
     * @return int|float
     * @since  1.0.0
     * @author sam softnwords, sam
     */
    function smm_ie_version() {
	
        if ( !isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) {
            return -1;
        }
        preg_match( '/MSIE ([0-9]+\.[0-9])/', sanitize_text_field($_SERVER[ 'HTTP_USER_AGENT' ]), $reg );

        if ( !isset( $reg[ 1 ] ) ) // IE 11 FIX
        {
            preg_match( '/rv:([0-9]+\.[0-9])/', sanitize_text_field($_SERVER[ 'HTTP_USER_AGENT' ]), $reg );
            if ( !isset( $reg[ 1 ] ) ) {
                return -1;
            } else {
                return floatval( $reg[ 1 ] );
            }
        } else {
            return floatval( $reg[ 1 ] );
        }
    }
}

if ( !function_exists( 'smm_avoid_duplicate' ) ) {
    /**
     * Check if something exists. If yes, add a -N to the value where N is a number.
     *
     * @param mixed  $value
     * @param array  $array
     * @param string $check
     *
     * @return mixed
     * @since  2.0.0
     * @author sam
     */
    function smm_avoid_duplicate( $value, $array, $check = 'value' ) {
        $match = array();

        if ( !is_array( $array ) ) {
            return $value;
        }

        if ( ( $check == 'value' && !in_array( $value, $array ) ) || ( $check == 'key' && !isset( $array[ $value ] ) ) ) {
            return $value;
        } else {
            if ( !preg_match( '/([a-z]+)-([0-9]+)/', $value, $match ) ) {
                $i = 2;
            } else {
                $i     = intval( $match[ 2 ] ) + 1;
                $value = $match[ 1 ];
            }

            return smm_avoid_duplicate( $value . '-' . $i, $array, $check );
        }
    }
}

if ( !function_exists( 'smm_title_special_characters' ) ) {
    /**
     * The chars used in smm_decode_title() and smm_encode_title()
     *
     * E.G.
     * string: This is [my title] with | a new line
     * return: This is <span class="highlight">my title</span> with <br /> a new line
     *
     * @param  string $title The string to convert
     *
     * @return string  The html
     *
     * @since 1.0
     */
    function smm_title_special_characters( $chars ) {
        return array_merge( $chars, array(
            '/[=\[](.*?)[=\]]/' => '<span class="title-highlight">$1</span>',
            '/\|/'              => '<br />',
        ) );
    }

    add_filter( 'smm_title_special_characters', 'smm_title_special_characters' );
}

if ( !function_exists( 'smm_decode_title' ) ) {
    /**
     * Change some special characters to put easily html into a string
     *
     * E.G.
     * string: This is [my title] with | a new line
     * return: This is <span class="title-highlight">my title</span> with <br /> a new line
     *
     * @param  string $title The string to convert
     *
     * @return string  The html
     *
     * @since 1.0
     */
    function smm_decode_title( $title ) {
        $replaces = apply_filters( 'smm_title_special_characters', array() );

        return preg_replace( array_keys( $replaces ), array_values( $replaces ), $title );
    }
}

if ( !function_exists( 'smm_encode_title' ) ) {
    /**
     * Change some special characters to put easily html into a string
     *
     * E.G.
     * string: This is [my title] with | a new line
     * return: This is <span class="title-highlight">my title</span> with <br /> a new line
     *
     * @param  string $title The string to convert
     *
     * @return string  The html
     *
     * @since 1.0
     */
    function smm_encode_title( $title ) {
        $replaces = apply_filters( 'smm_title_special_characters', array() );

        return preg_replace( array_values( $replaces ), array_keys( $replaces ), $title );
    }
}

if ( !function_exists( 'smm_remove_chars_title' ) ) {
    /**
     * Change some special characters to put easily html into a string
     *
     * E.G.
     * string: This is [my title] with | a new line
     * return: This is <span class="title-highlight">my title</span> with <br /> a new line
     *
     * @param  string $title The string to convert
     *
     * @return string  The html
     *
     * @since 1.0
     */
    function smm_remove_chars_title( $title ) {
        $replaces = apply_filters( 'smm_title_special_characters', array() );

        return preg_replace( array_keys( $replaces ), '$1', $title );
    }
}




if ( !function_exists( 'smm_load_js_file' ) ) {
    /**
     * Load .min.js file if WP_Debug is not defined
     *
     * @param string $filename The file name
     *
     * @return string The file path
     * @since  2.0.0
     * @author sam softnwords
     */
    function smm_load_js_file( $filename ) {

        if ( !( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || isset( $_GET[ 'smms_script_debug' ] ) ) ) {
            $filename = str_replace( '.js', '.min.js', $filename );
        }

        return $filename;
    }
}

if ( !function_exists( 'smm_wpml_register_string' ) ) {
    /**
     * Register a string in wpml trnslation
     *
     * @param $contenxt context name
     * @param $name     string name
     * @param $value    value to translate
     *
     * @since  2.0.0
     * @author sam
     */
    function smm_wpml_register_string( $contenxt, $name, $value ) {
        // wpml string translation
        do_action( 'wpml_register_single_string', $contenxt, $name, $value );
    }
}

if ( !function_exists( 'smm_wpml_string_translate' ) ) {
    /**
     * Get a string translation
     *
     * @param $contenxt         context name
     * @param $name             string name
     * @param $default_value    value to translate
     *
     * @return string the string translated
     * @since  2.0.0
     * @author sam
     */
    function smm_wpml_string_translate( $contenxt, $name, $default_value ) {
        return apply_filters( 'wpml_translate_single_string', $default_value, $contenxt, $name );
    }

}

if ( !function_exists( 'smm_wpml_object_id' ) ) {
    /**
     * Get id of post translation in current language
     *
     * @param int         $element_id
     * @param string      $element_type
     * @param bool        $return_original_if_missing
     * @param null|string $ulanguage_code
     *
     * @return int the translation id
     * @since  2.0.0
     * @author sam
     */
    function smm_wpml_object_id( $element_id, $element_type = 'post', $return_original_if_missing = false, $ulanguage_code = null ) {
        if ( function_exists( 'wpml_object_id_filter' ) ) {
            return wpml_object_id_filter( $element_id, $element_type, $return_original_if_missing, $ulanguage_code );
        } elseif ( function_exists( 'icl_object_id' ) ) {
            return icl_object_id( $element_id, $element_type, $return_original_if_missing, $ulanguage_code );
        } else {
            return $element_id;
        }
    }

}

if ( !function_exists( 'smms_get_formatted_price' ) ) {
    /**
     * Format the price with a currency symbol.
     *
     * @param float $price
     * @param array $args (default: array())
     *
     * @return string
     */
    function smms_get_formatted_price( $price, $args = array() ) {
        extract( apply_filters( 'wc_price_args', wp_parse_args( $args, array(
            'ex_tax_label'       => false,
            'currency'           => '',
            'decimal_separator'  => wc_get_price_decimal_separator(),
            'thousand_separator' => wc_get_price_thousand_separator(),
            'decimals'           => wc_get_price_decimals(),
            'price_format'       => get_woocommerce_price_format(),
        ) ) ) );

        $negative = $price < 0;
        $price    = apply_filters( 'raw_woocommerce_price', floatval( $negative ? $price * -1 : $price ) );
        $price    = apply_filters( 'formatted_woocommerce_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator, $thousand_separator );

        if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $decimals > 0 ) {
            $price = wc_trim_zeros( $price );
        }

        $formatted_price = ( $negative ? '-' : '' ) . sprintf( $price_format, get_woocommerce_currency_symbol( $currency ), $price );
        $return          = $formatted_price;

        return apply_filters( 'wc_price', $return, $price, $args );
    }
}

if ( !function_exists( 'smms_get_terms' ) ) {
    /**
     * Get terms
     *
     * @param $args
     *
     * @return array|int|WP_Error
     */
    function smms_get_terms( $args ) {
        global $wp_version;
        if ( version_compare( $wp_version, '4.5', '>=' ) ) {
            $terms = get_terms( $args );
        } else {
           // $terms = get_terms( $args[ 'taxonomy' ], $args );
        }

        return $terms;
    }
}

if ( !function_exists( 'smms_field_deps_data' ) ) {
    function smms_field_deps_data( $args ) {
        $deps_data = '';
        if ( isset( $args[ 'deps' ] ) && ( isset( $args[ 'deps' ][ 'ids' ] ) || isset( $args[ 'deps' ][ 'id' ] ) ) && ( isset( $args[ 'deps' ][ 'values' ] ) || isset( $args[ 'deps' ][ 'value' ] ) ) ) {
            $deps       = $args[ 'deps' ];
            $id         = isset( $deps[ 'target-id' ] ) ? $deps[ 'target-id' ] : $args[ 'id' ];
            $dep_id     = isset( $deps[ 'id' ] ) ? $deps[ 'id' ] : $deps[ 'ids' ];
            $dep_values = isset( $deps[ 'value' ] ) ? $deps[ 'value' ] : $deps[ 'values' ];
            $dep_type   = isset( $deps[ 'type' ] ) ? $deps[ 'type' ] : 'hide'; // possible values: hide|disable

            $deps_data = "data-dep-target='$id' data-dep-id='$dep_id' data-dep-value='$dep_values' data-dep-type='$dep_type'";
        }

        return $deps_data;
    }
}

if ( !function_exists( 'smms_panel_field_deps_data' ) ) {
    /**
     * @param                                               $option
     * @param SMM_Plugin_Panel|SMM_Plugin_Panel_WooCommerce $panel
     *
     * @return string
     */
    function smms_panel_field_deps_data( $option, $panel ) {
        $deps_data = '';
        if ( isset( $option[ 'deps' ] ) && ( isset( $option[ 'deps' ][ 'ids' ] ) || isset( $option[ 'deps' ][ 'id' ] ) ) && isset( $option[ 'deps' ][ 'values' ] ) ) {
            $dep_id                    = isset( $option[ 'deps' ][ 'id' ] ) ? $option[ 'deps' ][ 'id' ] : $option[ 'deps' ][ 'ids' ];
            $option[ 'deps' ][ 'ids' ] = $option[ 'deps' ][ 'id' ] = $panel->get_id_field( $dep_id );
            $option[ 'id' ]            = $panel->get_id_field( $option[ 'id' ] );

            $deps_data = smms_field_deps_data( $option );
        }

        return $deps_data;
    }
}

if ( !function_exists( 'smms_plugin_fw_get_field' ) ) {
    /**
     * @param array $field
     * @param bool  $echo
     * @param bool  $show_container
     *
     * @return string|void
     */
    function smms_plugin_fw_get_field( $field, $echo = false, $show_container = true ) {
        if ( empty( $field[ 'type' ] ) )
            return '';

        if ( !isset( $field[ 'value' ] ) )
            $field[ 'value' ] = '';

        if ( !isset( $field[ 'name' ] ) )
            $field[ 'name' ] = '';

        if ( !isset( $field[ 'custom_attributes' ] ) )
            $field[ 'custom_attributes' ] = '';

        if ( !isset( $field[ 'default' ] ) && isset( $field[ 'std' ] ) )
            $field[ 'default' ] = $field[ 'std' ];


        $field_template = smms_plugin_fw_get_field_template_path( $field );

        if ( $field_template ) {
            if ( !$echo )
                ob_start();

            if ( $show_container ) echo '<div class="smms-plugin-fw-field-wrapper smms-plugin-fw-' . esc_attr($field[ 'type' ]) . '-field-wrapper">';

            do_action( 'smms_plugin_fw_get_field_before', $field );
            do_action( 'smms_plugin_fw_get_field_' . $field[ 'type' ] . '_before', $field );

            include( $field_template );

            do_action( 'smms_plugin_fw_get_field_after', $field );
            do_action( 'smms_plugin_fw_get_field_' . $field[ 'type' ] . '_after', $field );

            if ( $show_container ) echo '</div>';

            if ( !$echo )
                return ob_get_clean();
        }
    }
}

if ( !function_exists( 'smms_plugin_fw_get_field_template_path' ) ) {
    function smms_plugin_fw_get_field_template_path( $field ) {
        if ( empty( $field[ 'type' ] ) )
            return false;

        $field_template = SMM_CORE_PLUGIN_TEMPLATE_PATH . '/fields/' . sanitize_title( $field[ 'type' ] ) . '.php';

        $field_template = apply_filters( 'smms_plugin_fw_get_field_template_path', $field_template, $field );

        return file_exists( $field_template ) ? $field_template : false;
    }
}

if ( !function_exists( 'smms_plugin_fw_html_data_to_string' ) ) {
    function smms_plugin_fw_html_data_to_string( $data = array(), $echo = false ) {
        $html_data = '';

        if ( is_array( $data ) ) {
            foreach ( $data as $key => $value ) {
                $current_value = !is_array( $value ) ? $value : implode( ',', $value );
                $html_data     .= " data-$key='$current_value'";
            }
            $html_data .= ' ';
        }

        if ( $echo )
            echo wp_kses($html_data);
        else
            return $html_data;
    }
}

if ( !function_exists( 'smms_plugin_fw_get_icon' ) ) {
    function smms_plugin_fw_get_icon( $icon = '', $args = array() ) {
        return SMM_Icons()->get_icon( $icon, $args );
    }
}

if ( !function_exists( 'smms_plugin_fw_is_true' ) ) {
    function smms_plugin_fw_is_true( $value ) {
        return true === $value || 1 === $value || '1' === $value || 'yes' === $value || 'true' === $value;
    }
}

if ( !function_exists( 'smms_plugin_fw_enqueue_enhanced_select' ) ) {
    function smms_plugin_fw_enqueue_enhanced_select() {
        wp_enqueue_script( 'smms-enhanced-select' );
        $select2_style_to_enqueue = function_exists( 'WC' ) ? 'woocommerce_admin_styles' : 'smms-select2-no-wc';
        wp_enqueue_style( $select2_style_to_enqueue );
    }
}

if ( !function_exists( 'smm_add_select2_fields' ) ) {
    /**
     * Add select 2
     *
     * @param array $args
     */
    function smm_add_select2_fields( $args = array() ) {
        $default = array(
            'type'              => 'hidden',
            'class'             => '',
            'id'                => '',
            'name'              => '',
            'data-placeholder'  => '',
            'data-allow_clear'  => false,
            'data-selected'     => '',
            'data-multiple'     => false,
            'data-action'       => '',
            'value'             => '',
            'style'             => '',
            'custom-attributes' => array()
        );

        $args = wp_parse_args( $args, $default );

        $custom_attributes = array();
        foreach ( $args[ 'custom-attributes' ] as $attribute => $attribute_value ) {
            $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
        }
        $custom_attributes = implode( ' ', $custom_attributes );

        if ( !function_exists( 'WC' ) || version_compare( WC()->version, '2.7.0', '>=' ) ) {
            if ( $args[ 'data-multiple' ] === true && substr( $args[ 'name' ], -2 ) != '[]' ) {
                $args[ 'name' ] = $args[ 'name' ] . '[]';
            }
            $select2_template_name = 'select2.php';

        } else {
            if ( $args[ 'data-multiple' ] === false && is_array( $args[ 'data-selected' ] ) ) {
                $args[ 'data-selected' ] = current( $args[ 'data-selected' ] );
            }
            $select2_template_name = 'select2-wc-2.6.php';
        }

        $template = SMM_CORE_PLUGIN_TEMPLATE_PATH . '/fields/resources/' . $select2_template_name;
        if ( file_exists( $template ) ) {
            include $template;
        }
    }
}

if ( !function_exists( 'smms_plugin_fw_get_version' ) ) {
    function smms_plugin_fw_get_version() {
        $plugin_fw_data_smm = get_file_data( trailingslashit( SMM_CORE_PLUGIN_PATH ) . 'init.php', array( 'Version' => 'Version' ) );
        return $plugin_fw_data_smm[ 'Version' ];
    }
}

if ( !function_exists( 'smms_get_premium_support_url' ) ) {
    //@TODO: To Remove
    /**
     * Return the url for My Account > Support dashboard
     *
     * @return string The complete string, if the main string is not empty or null
     * @since 2.0.0
     */
    function smms_get_premium_support_url() {
        return 'https://softnwords.com/my-account/support/dashboard/';
    }
}

if ( !function_exists( 'smms_plugin_fw_is_panel' ) ) {
    function smms_plugin_fw_is_panel() {
        $panel_screen_id = 'smms-plugins_page';
        $screen          = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

        return $screen instanceof WP_Screen && strpos( (string)$screen->id, $panel_screen_id ) !== false;
    }
}



/* === Gutenberg Support === */

if( ! function_exists( 'smms_plugin_fw_is_gutenberg_enabled' ) ){
	function smms_plugin_fw_is_gutenberg_enabled(){
		return function_exists( 'SMMS_Gutenberg' );
	}
}

if( ! function_exists( 'smms_plugin_fw_gutenberg_add_blocks' ) ){
	/**
	 * Add new blocks to Gutenberg
	 *
	 * @param $blocks string|array new blocks
	 * @return bool true if add a new blocks, false otherwise
	 *
	 * @author sam softnwords
	 */
	function smms_plugin_fw_gutenberg_add_blocks( $blocks ){
		$added = false;
		if( smms_plugin_fw_is_gutenberg_enabled() ) {
			// ADD Blocks
			$added = SMMS_Gutenberg()->add_blocks( $blocks );

			//ADD Blocks arguments
			if( $added ){
				SMMS_Gutenberg()->set_block_args( $blocks );
			}
		}

		return $added;
	}
}

if( ! function_exists( 'smms_plugin_fw_gutenberg_get_registered_blocks' ) ){
	/**
	 * Return an array with the registered blocks
	 *
	 * @return array
	 */
	function smms_plugin_fw_gutenberg_get_registered_blocks(){
		return smms_plugin_fw_is_gutenberg_enabled() ? SMMS_Gutenberg()->get_registered_blocks() : array();
	}
}

if( ! function_exists( 'smms_plugin_fw_gutenberg_get_to_register_blocks' ) ){
	/**
	 * Return an array with the blocks to register
	 *
	 * @return array
	 */
	function smms_plugin_fw_gutenberg_get_to_register_blocks(){
		return smms_plugin_fw_is_gutenberg_enabled() ? SMMS_Gutenberg()->get_to_register_blocks() : array();
	}
}

if( ! function_exists( 'smms_plugin_fw_get_default_logo' ) ){
	/**
	 * Get the default SVG logo
	 *
	 * @return string default logo image url
	 */
	function smms_plugin_fw_get_default_logo(){
		return SMM_CORE_PLUGIN_URL . '/assets/images/smms-icon.svg';
	}
}

if ( ! function_exists( 'smms_set_wrapper_class' ) ) {
	/**
	 * Return the class for the new plugin panel style.
	 *
	 * @param $class array|string the list of additional classes to add inside the panel wrapper.
	 *
	 * @return string
	 *
	 * @author sam
	 */
	function smms_set_wrapper_class( $class = '' ) {
		$new_class = 'smms-plugin-ui';
		$class     = ( ! empty( $class ) && is_array( $class ) ) ? implode( ' ', $class ) : $class;

		return $new_class . ' ' . $class;
	}
}

if( ! function_exists('smms_get_date_format') ){
	/**
	 * get all available date format
	 * @since 3.1
	 * @author Salvatore Strano
	 * @return array
	 */

	function smms_get_date_format( $js = true ){

		$date_formats = array(
			'F j, Y' => 'F j, Y',
			'Y-m-d'  => 'Y-m-d',
			'm/d/Y'  => 'm/d/Y',
			'd/m/Y'  => 'd/m/Y',
		);

		if( $js  ){
			$date_formats = array(
				'MM d, yy'     => 'F j, Y',
				'yy-mm-dd'     => 'Y-m-d',
				'mm/dd/yy'     => 'm/d/Y',
				'dd/mm/yy'     => 'd/m/Y',
			);
		}

		return apply_filters( 'smms_plugin_fw_date_formats', $date_formats, $js) ;
	}
}


if( ! function_exists('smms_format_toggle_title') ) {
	/**
	 * replace the placeholders with the values of the element id
	 * for toggle element field.
	 *
	 * @return array
	 * @author Salvatore Strano
	 * @since 3.1
	 */

	function smms_format_toggle_title( $title, $value ) {
		preg_match_all( '/(?<=\%%).+?(?=\%%)/', $title, $matches );
		if ( isset( $matches[0] ) ) {
			foreach ( $matches[0] as $element_id ) {
				if ( isset( $value[ $element_id ] ) ) {
					$title = str_replace( '%%' . $element_id . '%%', $value[ $element_id ], $title );
				}
			}
		}

		return $title;
	}
}

if( ! function_exists( 'smms_plugin_fw_load_update_and_licence_files' ) ){
	/**
	 * Load premium file for license and update system
	 *
	 * @author sam softnwords
	 *
	 * @return void
	 */
	function smms_plugin_fw_load_update_and_licence_files(){
		global $plugin_upgrade_fw_data_smm;

		/**
		 * If the init.php was load by old plugin-fw version
		 * load the upgrade and license key from local folder
		 */
		if( empty( $plugin_upgrade_fw_data_smm ) ){
			$plugin_upgrade_path = plugin_dir_path( __DIR__ ) . 'plugin-upgrade';
			if( file_exists( $plugin_upgrade_path ) ){
				$required_files = array(
					$plugin_upgrade_path . '/lib/smm-licence.php',
					$plugin_upgrade_path . '/lib/smm-plugin-licence.php',
					$plugin_upgrade_path . '/lib/smm-theme-licence.php',
					$plugin_upgrade_path . '/lib/smm-plugin-upgrade.php'
				);

				$plugin_upgrade_fw_data_smm = array( '1.0' => $required_files );
			}
		}

		if( ! empty( $plugin_upgrade_fw_data_smm ) && is_array( $plugin_upgrade_fw_data_smm ) ){
			foreach ( $plugin_upgrade_fw_data_smm as $fw_version=> $core_files ){
				foreach ( $core_files as $core_file ){
					if( file_exists( $core_file ) ){
						include_once $core_file;
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'smms_plugin_fw_remove_duplicate_classes' ) ) {
	/**
	 * Remove the duplicate classes from a string.
	 *
	 * @param  $classes string
	 *
	 * @return string
	 *
	 * @since 3.2.2
	 * @author sam softnwords
	 */
	function smms_plugin_fw_remove_duplicate_classes( $classes ) {
		$class_array  = explode( ' ', $classes );
		$class_unique = array_unique( array_filter( $class_array ) );
		if ( $class_unique ) {
			$classes = implode( ' ', $class_unique );
		}

		return $classes;
	}
}

if ( ! function_exists( 'smms_plugin_fw_add_requirements' ) ) {

	function smms_plugin_fw_add_requirements( $plugin_name, $requirements ) {
		if ( ! empty( $requirements ) ) {
			SMMS_System_Status()->add_requirements( $plugin_name, $requirements );


		}
	}
}
if ( ! function_exists( 'smms_getHost' ) ) {
    function smms_getHost($Address) {
    						$parseUrl = wp_parse_url(trim($Address));
    				$host = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
                            $h='';
    						$parts = explode( '.', $host );
    						$num_parts = count($parts);

    						if ($parts[0] == "www") {
        					for ($i=1; $i < $num_parts; $i++) {
            					$h .= $parts[$i] . '.';
        					}
    						}else {
        					for ($i=0; $i < $num_parts; $i++) {
            					$h .= $parts[$i] . '.';
        					}
    						}
    		return substr($h,0,-1);
		}
}

if ( ! function_exists( 'smms_splitUrl' ) ) {
function smms_splitUrl($fullUrl) {
    $parts = wp_parse_url($fullUrl);

    // Validate
    if (!isset($parts['scheme']) || !isset($parts['host'])) {
        return false; // invalid URL
    }

    // Build base URL
    $baseUrl = $parts['scheme'] . '://' . $parts['host'];
    if (isset($parts['port'])) {
        $baseUrl .= ':' . $parts['port'];
    }

    // Build remaining parts
    $path = isset($parts['path']) ? $parts['path'] : '';
    $query = isset($parts['query']) ? '?' . $parts['query'] : '';
    $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

    $remaining = $path . $query . $fragment;

    return [
        'base_url' 	=> esc_url_raw($baseUrl), //Base URL: https://api.example.com:8080
        'path'     	=> esc_url_raw($path),    //Path: /v1/users
        'query'    	=> esc_url_raw($query),   //Query: ?id=123
        'fragment' 	=> esc_url_raw($fragment),//Fragment: #section
        'remaining' => esc_url_raw($remaining)//Remaining: /v1/users?id=123#section
    ];
	}
}

if ( ! function_exists( 'smms_decode_string' ) ) {
    function smms_decode_string($string_data, $string_item ) {
           
    		$smm_string_item = json_decode($string_data,true);
    		(!empty($string_item)) ?
    		$smm_string_item_return = $smm_string_item[$string_item]
    		:
    		$smm_string_item_return = $smm_string_item;
    		return $smm_string_item_return;
		}
}
// ADDING API Order COLUMNS WITH THEIR TITLES In order listing page
add_filter( 'manage_edit-shop_order_columns', 'smm_shop_order_column', 20 );
function smm_shop_order_column($columns)
        {
                $reordered_columns = array();

                // Inserting columns to a specific location
                foreach( $columns as $key => $column){
                $reordered_columns[$key] = $column;
                if( $key ==  'order_status' ){
                    // Inserting after "Status" column
                $reordered_columns['smm-api-order'] = __( 'API Order','smm-api');
            
                }
                }
        return $reordered_columns;
        }
// Adding Api order meta data for each api order
add_action( 'manage_shop_order_posts_custom_column' , 'smmapi_custom_orders_list_column_content', 20, 2 );
function smmapi_custom_orders_list_column_content( $column, $post_id )
{   global $the_order;
    switch ( $column )
    {
        case 'smm-api-order' :
            // Get custom post meta data
            $my_var_one = get_post_meta( $post_id, 'Response', true );
            if(!empty($my_var_one)){
            $only_numbers = filter_var($my_var_one, FILTER_SANITIZE_NUMBER_INT);
                echo '<ul class="orders-list-items-smmapi">
                <li>'.
                esc_html($only_numbers)
                .'</li>';
            
                echo '</ul>';
            }
            // Testing (to be removed) - Empty value case
            else
                echo '<small>(<em>NA</em>)</small>';

            break;

        
    }
}
add_action( 'woocommerce_after_checkout_validation', 'smmapi_remove_item_cart_session_expired' );
function smmapi_remove_item_cart_session_expired(){
  global $woocommerce;
  
  $actual_cart = WC()->session->get( 'cart' );
  foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		//$_product =  wc_get_product( $cart_item['data']->get_id()); 
		$product_id = $cart_item['data']->get_id();
		$product = wc_get_product( $product_id );
		$title = $product->get_title();
		if($product->is_type( 'simple' )){
 	    $smm_api_checked   = smm_get_prop( $product, '_smapi_api' );   
        $input_text_box_radio_saved   = smm_get_prop( $product, 'locate_input_box' );        
		}
		if(  $smm_api_checked =='yes' && $input_text_box_radio_saved == 'checkout'){
		$product->get_meta( 'smm_custom_text_field_title' ) ? $product->get_meta( 'smm_custom_text_field_title' ):'title is empty';
		// $variation_id = $cart_item['variation_id'];
	  if( ! isset( $cart_item['smm-cfwc-title-field'] ))
    //$woocommerce->cart->empty_cart();
    wc_add_notice( __("<strong>ERROR:</strong> Customer input is missing ", "smm-api"), "error" );
  }}
}
// Add product new column in administration
add_filter( 'manage_edit-product_columns', 'smm_product_api_id_column', 20 );
function smm_product_api_id_column( $columns ) {

    $columns['smm-id'] = esc_html__( 'SMM ID', 'smm-api' );
        return array_slice( $columns, 0, 3, true ) + array( 'smm-id' => 'SMM ID' ) + array_slice( $columns, 3, count( $columns ) - 3, true );


}
// Populate weight column
add_action( 'manage_product_posts_custom_column', 'smm_product_api_id_column_data', 2 );
function smm_product_api_id_column_data( $column ) {
    global $post;

    if ( $column == 'smm-id' ) {
        $product = wc_get_product($post->ID);
                
				$api_item_list_options_saved_simple = 
			     smm_get_prop( $product, '_smapi_service_id_option' );
				 $items = explode("_item_",$api_item_list_options_saved_simple);
				 $_smapi_server_name_option_simple =
			    smm_get_prop( $product, '_smapi_server_name_option' );
        if ( $api_item_list_options_saved_simple > 0 )
            print "ID-".esc_attr($items[1])."<br>S-".esc_html($_smapi_server_name_option_simple);
        else print 'N/A';
    }
}