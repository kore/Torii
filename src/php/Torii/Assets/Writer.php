<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Assets;

/**
 * Asset writer
 *
 * @version $Revision$
 */
class Writer
{
    /**
     * Write collection files to target path
     *
     * @param Collection $collection
     * @param string $target
     * @return void
     */
    public function write( Collection $collection, $target )
    {
        foreach ( $collection->getFiles() as $file ) {
            $targetPath = $target . '/' . $file->localPath;

            if ( !file_exists( $targetPath ) ||
                 ( filemtime( $targetPath ) < $file->modificationTime ) )
            {
                $this->ensureParentDirectory( $targetPath );
                copy( $file->basePath . $file->localPath, $targetPath );
            }
        }
    }

    /**
     * Esnusres parent directory of provided path exists
     *
     * @param string $file
     * @return void
     */
    protected function ensureParentDirectory( $file )
    {
        $dir = dirname( $file );
        if ( is_dir( $dir ) ) {
            return;
        }

        if ( !@mkdir( $dir, 0777, true ) ) {
            throw new \RuntimeException( "Could not create directory $dir" );
        }
    }
}
