<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Assets\Collection;

use Torii\Assets\Collection;

/**
 * Asset collection
 *
 * Aggregates sets of files. Files sets are named, and may contain dependencies 
 * on other named file sets. Result will be topologically sorted.
 *
 * @version $Revision$
 */
class Dependency extends Collection
{
    /**
     * File sets
     *
     * @var FileSet[]
     */
    protected $fileSets = array();

    /**
     * file set dependencies
     *
     * @var string[]
     */
    protected $dependencies = array();

    /**
     * Add another file sets
     *
     * Each file set is identified by a name. You may add multiple file sets
     * with the same name.
     *
     * Optionally you may pass an array of names of file sets, on which the new 
     * file set depends on.
     *
     * @param string $name
     * @param FileSet $fileSet
     * @param string[] $dependencies
     * @return void
     */
    public function addFileSet( $name, FileSet $fileSet, array $dependencies = array() )
    {
        $this->fileSets[$name][]   = $fileSet;
        $this->dependencies[$name] = array_merge(
            $dependencies,
            $this->dependencies[$name]
        );
    }

    /**
     * Get files from collection
     *
     * @return Struct\File[]
     */
    public function getFiles()
    {
        throw new \RuntimeException( '@TODO: Implement' );
    }
}

