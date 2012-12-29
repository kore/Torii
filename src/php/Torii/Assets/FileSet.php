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
class FileSet
{
    /**
     * File set base path
     *
     * @var string
     */
    protected $basePath;

    /**
     * Pattern for included files
     *
     * @var string
     */
    protected $filePattern;

    /**
     * Pattern for excluded files
     *
     * @var string
     */
    protected $fileIgnorePattern;

    /**
     * Construct from base path and file pattern
     *
     * Optionally provide an ignore pattern to include certain files
     *
     * @param string $basePath
     * @param string $filePattern
     * @param string $fileIgnorePattern
     * @return void
     */
    public function __construct($basePath, $filePattern, $fileIgnorePattern = null)
    {
        $this->basePath          = $basePath;
        $this->filePattern       = $filePattern;
        $this->fileIgnorePattern = $fileIgnorePattern;
    }

    /**
     * Get files
     *
     * @return File[]
     */
    public function getFiles()
    {
        $basePath = realpath($this->basePath) . '/';
        $include  = glob($basePath . $this->filePattern);
        $exclude  = $this->fileIgnorePattern ?
            glob($basePath . $this->fileIgnorePattern) :
            array();

        return array_values(array_map(
            function ($file) use ($basePath) {
                return new Struct\File($basePath, str_replace($basePath, '', $file));
            },
            array_diff(
                $include,
                array_intersect($include, $exclude)
            )
        ));
    }
}
