<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Assets\Collection\Filter;

use Torii\Assets\Collection;
use Torii\Assets\Collection\Filter;
use Torii\Assets\MimeTypeGuesser;

/**
 * Asset collection
 *
 * Aggregates sets of files
 *
 * @version $Revision$
 */
class MimeType extends Filter
{
    /**
     * aggregated collection
     *
     * @var Collection
     */
    protected $collection;

    /**
     * Mime type guesser
     *
     * @var MimeTypeGuesser
     */
    protected $mimeTypeGuesser;

    /**
     * Create from mime type guesser
     *
     * @param MimeTypeGuesser $mimeTypeGuesser
     * @return void
     */
    public function __construct(Collection $collection, MimeTypeGuesser $mimeTypeGuesser)
    {
        $this->collection      = $collection;
        $this->mimeTypeGuesser = $mimeTypeGuesser;
    }

    /**
     * Get files from collection
     *
     * @return Struct\File[]
     */
    public function getFiles()
    {
        $mimeTypeGuesser = $this->mimeTypeGuesser;
        return array_map(
            function ($file) use ($mimeTypeGuesser) {
                $file->mimeType = $mimeTypeGuesser->guess($file);
                return $file;
            },
            $this->collection->getFiles()
        );
    }
}
