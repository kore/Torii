<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Assets\Collection;

use Torii\Assets\Collection,
    Torii\Assets\FileSet;

/**
 * Asset collection
 *
 * Aggregates sets of files, keeps them in the order as they were provided to
 * the collection.
 *
 * @version $Revision$
 */
class Simple extends Collection
{
    /**
     * File sets
     *
     * @var FileSet[]
     */
    protected $fileSets = array();

    /**
     * Construct from optional file sets
     *
     * @param FileSet[] $fileSets
     */
    public function __construct( array $fileSets = array() )
    {
        foreach ( $fileSets as $fileSet ) {
            $this->addFileSet( $fileSet );
        }
    }

    /**
     * Add another file sets
     *
     * @param FileSet $fileSet
     * @return void
     */
    public function addFileSet( FileSet $fileSet )
    {
        $this->fileSets[] = $fileSet;
    }

    /**
     * Get files from collection
     *
     * @return Struct\File[]
     */
    public function getFiles()
    {
        $files = array();
        foreach ( $this->fileSets as $fileSet ) {
            $files = array_merge( $files, $fileSet->getFiles() );
        }

        return $files;
    }
}
