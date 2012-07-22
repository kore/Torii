<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Assets\Collection\Filter;

use Torii\Assets\Struct\File;

/**
 * @version $Revision$
 * @covers \Torii\Assets\Collection\Filter\MimeType
 * @group unittest
 */
class MimeTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStaticFileSet()
    {
        $mockedCollection = $this->getMock( '\\Torii\\Assets\\Collection' );
        $mockedCollection
            ->expects( $this->once() )
            ->method( 'getFiles' )
            ->will( $this->returnValue( array(
                new File( '/', 'foo' )
            ) ) );

        $mockedGuesser = $this->getMock( '\\Torii\\Assets\\MimeTypeGuesser' );
        $mockedGuesser
            ->expects( $this->once() )
            ->method( 'guess' )
            ->will( $this->returnValue( 'mime/type' ) );

        $filter = new MimeType( $mockedCollection, $mockedGuesser );
        $this->assertEquals(
            array(
                new File( '/', 'foo', 'mime/type' )
            ),
            $filter->getFiles()
        );
    }
}

