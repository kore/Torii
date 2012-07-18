<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Assets;

/**
 * Asset collection
 *
 * Aggregates sets of files
 *
 * @version $Revision$
 */
abstract class Collection
{
    /**
     * Get files from collection
     *
     * @return Struct\File[]
     */
    abstract public function getFiles();
}

