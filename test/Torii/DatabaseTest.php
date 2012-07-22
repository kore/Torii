<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii;

/**
 * @version $Revision$
 */
abstract class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get correctly confiigured Doctrine DBAL instance
     *
     * @return void
     */
    protected function getDbal()
    {
        $dic = new DIC\Base();
        $dic->environment = 'testing';
        $dbal = $dic->dbal;

        // Truncate all out tables
        $schema = $dbal->getSchemaManager();
        foreach ( $schema->listTables() as $table )
        {
            if ( $table->getName() === 'changelog' )
            {
                // Special table by DBDeploy
                continue;
            }

            $dbal->query( "TRUNCATE TABLE " . $table->getName() );
        }

        return $dbal;
    }
}
