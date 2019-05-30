<?php

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../includes/simple_html_dom.php';

class EAE_DOM_Encoder
{
    const SHORTCODE_REGEXP = '~\[encode.*\].+\[/encode\]~U';

    const EMAIL_REGEXP = '{(?:mailto:)?(?:[-!#$%&*+/=?^_`.{|}~\w\x80-\xFF]+|".*?")\@(?:[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+|\[[\d.a-fA-F:]+\])}xi';

    public $jsName;

    public $cssName;

    protected $dom;

    protected $method;

    protected $message;

    protected $technique;

    protected $emails = [];

    protected $shortcodes = [];

    private static $instance = null;

    public function __construct()
    {
        $this->technique = get_option( 'eae_technique' );
        $this->method = apply_filters( 'eae_method', 'eae_encode_str' );

        $this->jsName = $this->str_random_name();
        $this->cssName = $this->str_random_name();
    }

    public static function instance()
    {
        if ( ! self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function parse( $html )
    {
        if ( empty( $html ) ) {
            throw new Exception('No HTML to parse.');
        }

        $max = defined( 'MAX_FILE_SIZE' ) ? MAX_FILE_SIZE : 600000;
        $size = strlen( $html );

        if ( $size > $max ) {
            throw new Exception(sprintf(
                'HTML too large (%s KB). Maximum: %s KB',
                round( $size / 1024 ),
                round( $max / 1024 )
            ));
        }

        $this->dom = simplehtmldom_1_5\str_get_html(
            $html, false, true, get_bloginfo( 'charset' ), false
        );

        if ( $this->dom && $this->dom->root ) {
            $this->loop( $this->dom->root );
        } else {
            throw new Exception( 'Unable to parse HTML :/' );
        }

        return $this;
    }

    public function output()
    {
        $html = $this->dom->root->innertext();

        if ( $this->message ) {
            $html .= static::message( $this->message );
        }

        $html = str_replace( array_keys( $this->emails ), array_values( $this->emails ), $html );

        $html = str_replace( array_keys( $this->shortcodes ), array_values( $this->shortcodes ), $html );

        $html = preg_replace_callback( '/{eae=(.+?)}/i', function ( $matches ) {
            return str_replace( '\\\u', '\u', $matches[ 1 ] );
        }, $html );

        return $html;
    }

    protected function loop( $node )
    {
        if ( $node->nodes ) {
            foreach ( $node->nodes as $child ) {
                $this->loop( $child );
            }
        }

        if ( $node->nodetype === HDOM_TYPE_ELEMENT ) {
            $this->replace_emails_in_attributes( $node );
        }

        if ( $node->nodetype === HDOM_TYPE_TEXT ) {
            $this->replace_shortcodes_in_text( $node );
            $this->replace_emails_in_text( $node );
        }

        if ( $node->nodetype === HDOM_TYPE_COMMENT ) {
            $this->replace_emails_in_comment( $node );
        }
    }

    protected function find_emails( $text )
    {
        if ( apply_filters( 'eae_at_sign_check', true ) && strpos( $text, '@' ) === false ) {
            return;
        }

        preg_match_all( self::EMAIL_REGEXP, $text, $matches );

        if ( empty( $matches[ 0 ] ) ) {
            return;
        }

        $emails = array_filter( $matches[ 0 ], function ( $email ) {
            return strpos( $email, '/' ) === false
                && strpos( $email, '=' ) === false
                && ! preg_match( '/@\d{1,2}(.\d)?x\./', $email );
        });

        if ( empty( $emails ) ) {
            return;
        }

        return array_unique( $emails );
    }

    protected function find_shortcodes( $text )
    {
        if ( strpos( $text, '[/encode]' ) === false ) {
            return;
        }

        preg_match_all( self::SHORTCODE_REGEXP, $text, $matches );

        if ( empty( $matches[ 0 ] ) ) {
            return;
        }

        return array_unique( $matches[ 0 ] );
    }

    protected function replace_emails_in_inner_text( $node, $text, $emails, $type )
    {
        foreach ( $emails as $email ) {
            $key = $this->str_random();

            $this->emails[ $key ] = $this->obfuscate_email( $type, $email );

            $text = str_replace( $email, $key, $text );
        }

        $node->innertext = $text;
    }

    protected function replace_shortcodes_in_inner_text( $node, $text, $shortcodes, $type )
    {
        foreach ( $shortcodes as $shortcode ) {
            $key = $this->str_random();

            $this->shortcodes[ $key ] = do_shortcode( $shortcode );

            $text = str_replace( $shortcode, $key, $text );
        }

        $node->innertext = $text;
    }

    protected function replace_emails_in_attributes( $node )
    {
        $in_head = $this->is_inside_head( $node );

        foreach ( $node->attr as $name => $value ) {
            $trimmed = trim( $value );

            if ( empty( $trimmed ) ) {
                continue;
            }

            if ( ! $emails = $this->find_emails( $value ) ) {
                continue;
            }

            foreach ( $emails as $email ) {
                $key = $this->str_random();

                $this->emails[ $key ] = $in_head
                    ? $this->obfuscate_email( 'head', $email )
                    : $this->obfuscate_attribute( $node, $name, $email );

                if ( $this->is_mailto_link( $node, $name, $email ) ) {
                    $node->attr[ $name ] = $key;
                } else {
                    $node->attr[ $name ] = str_replace( $email, $key, $value );
                }
            }
        }
    }

    protected function replace_emails_in_comment( $node )
    {
        $text = $node->innertext();
        $trimmed = trim( $text );

        if ( empty( $trimmed ) ) {
            return;
        }

        if ( ! $emails = $this->find_emails( $text ) ) {
            return;
        }

        foreach ( $emails as $email ) {
            $key = $this->str_random();

            $this->emails[ $key ] = $this->obfuscate_email( 'comment', $email );

            $text = str_replace( $email, $key, $text );
        }

        $node->innertext = $text;
    }

    protected function replace_emails_in_text( $node )
    {
        $text = $node->innertext();
        $trimmed = trim( $text );

        if ( empty( $trimmed ) ) {
            return;
        }

        if ( in_array( $node->parent->tag, [ 'script', 'style' ] ) ) {
            return;
        }

        if ( ! $emails = $this->find_emails( $text ) ) {
            return;
        }

        if ( in_array( $node->parent->tag, [ 'textarea', 'xmp', 'noscript' ] ) ) {
            $this->replace_emails_in_inner_text( $node->parent, $text, $emails, $node->parent->tag );
        } elseif ( $this->is_inside_head( $node ) ) {
            $this->replace_emails_in_inner_text( $node->parent, $text, $emails, 'head' );
        } elseif ( $node->nodetype === HDOM_TYPE_TEXT ) {
            $this->replace_emails_in_inner_text( $node, $text, $emails, 'text' );
        } else {
            $this->replace_emails_in_inner_text( $node->parent, $text, $emails, 'text' );
        }
    }

    protected function replace_shortcodes_in_text( $node )
    {
        $text = $node->innertext();
        $trimmed = trim( $text );

        if ( empty( $trimmed ) ) {
            return;
        }

        if ( in_array( $node->parent->tag, [ 'textarea', 'xmp', 'style', 'script', 'noscript' ] ) ) {
            return;
        }

        if ( $this->is_inside_head( $node ) ) {
            return;
        }

        if ( ! $shortcodes = $this->find_shortcodes( $text ) ) {
            return;
        }

        $this->replace_shortcodes_in_inner_text(
            $node->nodetype === HDOM_TYPE_TEXT ? $node : $node->parent,
            $text, $shortcodes, 'text'
        );
    }

    protected function obfuscate_email( $type, $email )
    {
        $user = strstr( $email, '@', true );
        $domain = strstr( $email, '@' );

        if ( in_array( $type, [ 'head', 'textarea', 'noscript' ] ) ) {
            return $this->str_encode( $email );
        }

        if ( in_array( $type, [ 'comment', 'xmp' ] ) ) {
            return $user . str_replace(
                [ '@', '.' ],
                [ ' ⟨at⟩ ', ' ⟨dot⟩ ' ],
                $domain
            );
        }

        if ( $type === 'text' ) {
            if ( $this->technique === 'css-direction' ) {
                return sprintf( '<span class="__eae_cssd">%s</span>', $this->str_encode( eae_reverse( $email ) ) );
            }

            if ( $this->technique === 'rot13' ) {
                return sprintf( '<span class="__eae_r13">%s</span>', str_rot13( $email ) );
            }

            if ( $this->technique === 'rot47' ) {
                return sprintf(
                    '<span class="%s">%s</span>',
                    $this->cssName,
                    str_replace( [ '.', '@' ], [ '&#x2e;', '&#64;' ], eae_reverse( $email ) )
                );
            }

            return $this->str_encode( $email );
        }

        return $email;
    }

    protected function obfuscate_attribute( $node, $attribute, $email )
    {
        if ( ! $this->is_mailto_link( $node, $attribute, $email ) ) {
            if ( has_filter( 'eae_obfuscate_attribute' ) ) {
                return apply_filters( 'eae_obfuscate_attribute', $email, $attribute, $node );
            }

            return $this->str_encode( $email );
        }

        if ( in_array( $this->technique, [ 'entities', 'css-direction' ] ) ) {
            $link = $this->str_encode( $email );

            $query = parse_url( $node->attr[ 'href' ], PHP_URL_QUERY );

            if ( $query ) {
                $link .= '?' . eae_encode_emails( $query );
            }

            return $link;
        }

        if ( $this->technique === 'rot13' ) {
            preg_match( '/href\h*=\h*("|\')/i', $node->outertext(), $matches );

            $outer = empty( $matches ) ? '"' : '';
            $inner = isset( $matches[ 1 ] ) && $matches[ 1 ] === "'" ? '"' : "'";
            $email = str_rot13( htmlspecialchars_decode( $node->attr[ 'href' ] ) );

            return "{$outer}javascript:window.location.href=__eae_decode({$inner}{$email}{$inner});{$outer}";
        }

        if ( $this->technique === 'rot47' ) {
            preg_match( '/href\h*=\h*("|\')/i', $node->outertext(), $matches );

            $outer = empty( $matches ) ? '"' : '';
            $inner = isset( $matches[ 1 ] ) && $matches[ 1 ] === "'" ? '"' : "'";
            $email = self::str_rot47( htmlspecialchars_decode( $node->attr[ 'href' ] ) );

            return "{$outer}javascript:{$this->jsName}({$inner}{$email}{$inner});{$outer}";
        }
    }

    protected function is_inside_head( $node )
    {
        if ( $node->tag === 'head' ) {
            return true;
        }

        if ( $node->parent ) {
            return $this->is_inside_head( $node->parent );
        }

        return false;
    }

    protected function is_mailto_link( $node, $attribute, $value )
    {
        return $node->tag === 'a'
            && $attribute === 'href'
            && stripos ( $value, 'mailto:' ) === 0;
    }

    protected function str_encode( $string )
    {
        $method = $this->method;

        return $method( $string );
    }

    protected function str_random()
    {
        return sprintf( '__eae_%s__', mt_rand() );
    }

    protected function str_random_name()
    {
        $parts = array_map( function ( $part ) {
            $random = str_shuffle( '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' );

            return substr( $random, 0, mt_rand( 3, 7 ) ) . str_repeat( '_', mt_rand( 0, 2 ) );
        }, range( 0, mt_rand( 0, 2 ) ) );

        $name = implode( '', $parts );

        if ( ctype_digit( $name[ 0 ] ) ) {
            $name = "_{$name}";
        }

        return $name;
    }

    public static function str_rot47( $string )
    {
        return strtr( $string,
            '!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~',
            'PQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNO'
        );
    }

    public static function message( $message )
    {
        return sprintf(
            "\n<!--\n%s\n\n%s\n-->\n",
            '[Email Address Encoder]',
            $message
        );
    }
}
