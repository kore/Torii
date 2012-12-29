<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Assets;

use Torii\Assets\Struct\File;

/**
 * Mime type guesser
 *
 * @version $Revision$
 */
abstract class MimeTypeGuesser
{
    /**
     * Guess mime type for given file
     *
     * @param File $file
     * @return string
     */
    abstract public function guess( File $file );
}
