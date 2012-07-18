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
}

