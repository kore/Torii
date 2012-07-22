<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Assets\MimeTypeGuesser;

use Torii\Assets\Struct\File;

/**
 * @version $Revision$
 * @covers \Torii\Assets\MimeTypeGuesser\Extension
 * @group unittest
 */
class ExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function getFiles()
    {
        return array(
            array( 'no_extension', 'application/octet-stream' ),
            array( 'unknown.extension', 'application/octet-stream' ),
            array( 'css.css', 'text/css' ),
            array( 'css.min.css', 'text/css' ),
            array( 'js.js', 'text/javascript' ),
            array( 'js.min.js', 'text/javascript' ),
            array( 'png.png', 'image/png' ),
            array( 'jpeg.jpeg', 'image/jpeg' ),
            array( 'jpeg.jpg', 'image/jpeg' ),
            array( 'mustache.mustache', 'text/mustache' ),
        );
    }

    /**
     * @dataProvider getFiles
     */
    public function testGuessMimeType( $name, $mimeType )
    {
        $guesser = new Extension();

        $this->assertEquals(
            $mimeType,
            $guesser->guess( new File( '/', $name ) )
        );
    }
}

