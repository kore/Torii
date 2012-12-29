<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Controller;

use Qafoo\RMF;
use Torii\Assets\Collection;

/**
 * Asset controller
 *
 * Controller for delivering static files outside of the base dir. Should not
 * be used in production. Will be slow. But is a nice thing for development.
 *
 * @version $Revision$
 */
class Assets
{
    /**
     * Mapping of local paths to target collections
     *
     * @var array(string => Collection)
     */
    protected $assets = array();

    /**
     * Construct from asset collections
     *
     * @param array $assets
     * @return void
     */
    public function __construct( array $assets )
    {
        $this->assets = $assets;
    }

    /**
     * Deliver asset
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @return Struct\Response
     */
    public function deliver( RMF\Request $request )
    {
        foreach ( $this->assets as $regexp => $collection ) {
            if ( !preg_match( $regexp, $request->path, $matches ) ) {
                continue;
            }

            if ( !isset( $matches['path'] ) ) {
                throw new \RuntimeException( "No match value 'path'." );
            }

            foreach ( $collection->getFiles() as $file ) {
                if ( $file->localPath !== $matches['path'] ) {
                    continue;
                }

                // @TODO: ARRRGS!
                header( "Content-Type: " . $file->mimeType );

                header( "Last-Modified: " . gmdate( "D, d M Y H:i:s", $file->modificationTime ) . " GMT" );
                header( "Etag: " . md5( $file->modificationTime ) );
                header( "Cache-Control: public" );

                readfile( $file->basePath . $file->localPath );
                exit( 0 );
            }
        }

        throw new \Exception( $request->path . " not found." );
    }
}
