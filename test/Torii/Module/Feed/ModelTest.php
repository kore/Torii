<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Feed;

use Torii\DatabaseTest;

/**
 * @version $Revision$
 */
class ModelTest extends DatabaseTest
{
    public function testGetNoUrls()
    {
        $model = new Model( $this->getDbal() );

        $this->assertEquals(
            array(),
            $model->getUrlList( "module_1" )
        );
    }

    public function testGetNoData()
    {
        $model = new Model( $this->getDbal() );

        $this->assertEquals(
            array(),
            $model->getUnread( "module_1" )
        );
    }

    /**
     * @depends testGetNoUrls
     */
    public function testAddUrl()
    {
        $model = new Model( $this->getDbal() );
        $model->addUrl( "module_1", "http://example.com" );

        $this->assertEquals(
            array(
                new Struct\Url( 1,"http://example.com" ),
            ),
            $model->getUrlList( "module_1" )
        );
    }

    public function testAddTwoUrls()
    {
        $model = new Model( $this->getDbal() );
        $model->addUrl( "module_1", "http://example.com/1" );
        $model->addUrl( "module_1", "http://example.com/2" );

        $this->assertEquals(
            array(
                new Struct\Url( 1, "http://example.com/1" ),
                new Struct\Url( 2, "http://example.com/2" ),
            ),
            $model->getUrlList( "module_1" )
        );
    }

    /**
     * @expectedException \PDOException
     */
    public function testAddSameUrl()
    {
        $model = new Model( $this->getDbal() );
        $model->addUrl( "module_1", "http://example.com" );
        $model->addUrl( "module_1", "http://example.com" );
    }

    /**
     * @depends testAddUrl
     */
    public function testRemoveUrl()
    {
        $model = new Model( $this->getDbal() );
        $model->addUrl( "module_1", "http://example.com" );
        $model->removeurl( "module_1", 1 );

        $this->assertEquals(
            array(),
            $model->getUrlList( "module_1" )
        );
    }

    public function testRemoveUnknownUrl()
    {
        $model = new Model( $this->getDbal() );
        $model->removeurl( "module_1", 42 );

        $this->assertEquals(
            array(),
            $model->getUrlList( "module_1" )
        );
    }

    /**
     * @depends testAddUrl
     */
    public function testGetPendingUrl()
    {
        $model = new Model( $this->getDbal() );
        $model->addUrl( "module_1", "http://example.com" );

        $this->assertEquals(
            array( 'http://example.com' ),
            $model->getPending( 300 )
        );
    }
}

