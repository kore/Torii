<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Assets\Collection;

use Torii\Assets\Struct\File;

/**
 * @version $Revision$
 * @covers \Torii\Assets\Collection\Simple
 * @group unittest
 */
class SimpleTest extends \PHPUnit_Framework_TestCase
{
    protected function getMockedFileSet( $files = array( 'test.css' ) )
    {
        $set = $this->getMock( '\\Torii\\Assets\\FileSet', array( 'getFiles' ), array(), '', false );
        $set
            ->expects( $this->any() )
            ->method( 'getFiles' )
            ->will( $this->returnValue(
                array_map(
                    function ( $name )
                    {
                        return new File( '/', $name );
                    },
                    $files
                )
            ) );

        return $set;
    }

    public function testGetEmptyCollection()
    {
        $collection = new Simple();

        $this->assertEquals(
            array(),
            $collection->getFiles()
        );
    }

    public function testGetUnaryCollection()
    {
        $collection = new Simple();
        $collection->addFileSet( $this->getMockedFileSet() );

        $this->assertEquals(
            array(
                new File( '/', 'test.css' ),
            ),
            $collection->getFiles()
        );
    }

    public function testGetUnaryCollectionFromConstructor()
    {
        $collection = new Simple( array(
            $this->getMockedFileSet()
        ) );

        $this->assertEquals(
            array(
                new File( '/', 'test.css' ),
            ),
            $collection->getFiles()
        );
    }

    public function testGetOrderedCollection()
    {
        $collection = new Simple( array(
            $this->getMockedFileSet()
        ) );

        $collection->addFileSet( $this->getMockedFileSet( array( 'test2.css', 'test3.css' ) ) );

        $this->assertEquals(
            array(
                new File( '/', 'test.css' ),
                new File( '/', 'test2.css' ),
                new File( '/', 'test3.css' ),
            ),
            $collection->getFiles()
        );
    }
}

