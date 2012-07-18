<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Assets;

/**
 * @version $Revision$
 * @covers \Torii\FileSet
 * @group unittest
 */
class FileSetTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStaticFileSet()
    {
        $set = new FileSet(
            __DIR__ . '/_data',
            'foo.css'
        );

        $this->assertEquals(
            array(
                new Struct\File( __DIR__ . '/_data/', 'foo.css' ),
            ),
            $set->getFiles()
        );
    }

    public function testGetStaticFileSetTrainlingSlash()
    {
        $set = new FileSet(
            __DIR__ . '/_data/',
            'foo.css'
        );

        $this->assertEquals(
            array(
                new Struct\File( __DIR__ . '/_data/', 'foo.css' ),
            ),
            $set->getFiles()
        );
    }

    public function testGetInvalidFileSet()
    {
        $set = new FileSet(
            __DIR__ . '/_data/',
            'unknown'
        );

        $this->assertEquals(
            array(),
            $set->getFiles()
        );
    }

    public function testGetPatternFileSet()
    {
        $set = new FileSet(
            __DIR__ . '/_data/',
            '*.css'
        );

        $this->assertEquals(
            array(
                new Struct\File( __DIR__ . '/_data/', 'bar.css' ),
                new Struct\File( __DIR__ . '/_data/', 'foo.css' ),
            ),
            $set->getFiles()
        );
    }

    public function testGetDeepPatternFileSet()
    {
        $set = new FileSet(
            __DIR__,
            '_data/*.css'
        );

        $this->assertEquals(
            array(
                new Struct\File( __DIR__ . '/', '_data/bar.css' ),
                new Struct\File( __DIR__ . '/', '_data/foo.css' ),
            ),
            $set->getFiles()
        );
    }

    public function testGetIgnorePattern()
    {
        $set = new FileSet(
            __DIR__ . '/_data/',
            '*',
            'bar.*'
        );

        $this->assertEquals(
            array(
                new Struct\File( __DIR__ . '/_data/', 'blubb.js' ),
                new Struct\File( __DIR__ . '/_data/', 'foo.css' ),
            ),
            $set->getFiles()
        );
    }
}
