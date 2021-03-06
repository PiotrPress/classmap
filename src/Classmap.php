<?php declare(strict_types=1);

namespace PiotrPress;

class Classmap {
    protected string $directory = '';
    protected string $extension = '.php';

    public function __construct( string $directory, string $extension = '.php' ) {
        $this->directory = \realpath( $directory );
        $this->extension = $extension;
    }

    public function dump( string $file = '' ) : bool {
        $path = $file ?: $this->directory . '/classmap.php';
        return (bool)\file_put_contents( $path, \sprintf( '<?php return %s;', \var_export( $this->get(), true ) ) );
    }

    public function get() : array {
        $map = [];

        foreach ( new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $this->directory ) ) as $file ) {
            if ( ! $file->isFile() ) continue;

            $path = $file->getRealPath() ?: $file->getPathname();

            if ( \ltrim( $this->extension, '.' ) !== \pathinfo( $path, \PATHINFO_EXTENSION ) ) continue;

            foreach ( $this->search( $path ) as $class ) {
                if ( 0 === \strpos( $path, $this->directory ) ) $path = \substr( $path, \strlen( $this->directory ) );
                $map[ $class ] = \str_replace( '\\', '/', $path );
            }
        }

        return $map;
    }

    protected function search( string $path ) : array {
        $contents = \file_get_contents( $path );
        $tokens = \token_get_all( $contents );

        $nsTokens = [ \T_STRING => true, \T_NS_SEPARATOR => true ];
        if ( \defined( 'T_NAME_QUALIFIED' ) ) $nsTokens[ T_NAME_QUALIFIED ] = true;

        $classes = [];

        $namespace = '';
        for ( $i = 0; isset( $tokens[ $i ] ); ++$i ) {
            $token = $tokens[ $i ];

            if ( ! isset( $token[1] ) ) continue;

            $class = '';

            switch ( $token[ 0 ] ) {
                case \T_NAMESPACE :
                    $namespace = '';
                    while ( isset( $tokens[ ++$i ][ 1 ] ) )
                        if ( isset( $nsTokens[ $tokens[ $i ][ 0 ] ] ) )
                            $namespace .= $tokens[ $i ][ 1 ];
                    $namespace .= '\\';
                    break;
                case \T_CLASS :
                case \T_INTERFACE :
                case \T_TRAIT :
                    $isClassConstant = false;
                    for ( $j = $i - 1; $j > 0; --$j ) {
                        if ( ! isset( $tokens[ $j ][ 1 ] ) )
                            break;
                        if ( \T_DOUBLE_COLON === $tokens[ $j ][ 0 ] ) {
                            $isClassConstant = true;
                            break;
                        } elseif ( ! \in_array( $tokens[ $j ][ 0 ], [ \T_WHITESPACE, \T_DOC_COMMENT, \T_COMMENT ] ) )
                            break;
                    }

                    if ( $isClassConstant ) break;

                    while ( isset( $tokens[ ++$i ][ 1 ] ) ) {
                        $t = $tokens[ $i ];
                        if ( \T_STRING === $t[ 0 ] )
                            $class .= $t[ 1 ];
                        elseif ( '' !== $class && \T_WHITESPACE === $t[ 0 ] )
                            break;
                    }

                    $classes[] = \ltrim( $namespace . $class, '\\' );
                    break;
                default:
                    break;
            }
        }

        return $classes;
    }
}