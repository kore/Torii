<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Assets\Struct;

use Torii\Struct;

/**
 * Struct representing a file
 *
 * @version $Revision$
 */
class File extends Struct
{
    /**
     * Base path
     *
     * @var string
     */
    public $basePath;

    /**
     * Local file path
     *
     * @var string
     */
    public $localPath;

    /**
     * File mime type
     *
     * @var string
     */
    public $mimeType;

    /**
     * File modification time
     *
     * Stored as a unix timestamp (UTC)
     *
     * @var int
     */
    protected $modificationTime;

    /**
     * Construct
     *
     * @param string $basePath
     * @param string $localPath
     * @return void
     */
    public function __construct( $basePath = null, $localPath = null, $mimeType = null )
    {
        $this->basePath  = $basePath;
        $this->localPath = $localPath;
        $this->mimeType  = $mimeType;
    }

    /**
     * Handle special magic property modificationTime
     *
     * @param string $property
     * @return void
     */
    public function __get( $property )
    {
        if ( $property === 'modificationTime' ) {
            if ( $this->modificationTime !== null ) {
                return $this->modificationTime;
            }

            return $this->modificationTime = filemtime( $this->basePath . $this->localPath );
        }

        return parent::__get( $property );
    }
}
