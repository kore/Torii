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

    public function testAddUrl()
    {
        $model = new Model( $this->getDbal() );
        $model->addUrl( "module_1", "http://example.com" );

        $this->assertEquals(
            array(
                new Struct\Url( "http://example.com" ),
            ),
            $model->getUrlList( "module_1" )
        );
    }
}

