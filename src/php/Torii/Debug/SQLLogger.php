<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Debug;

/**
 * SQL logger to debug slow queries
 *
 * @version $Revision$
 */
class SQLLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
    /**
     * Log file
     *
     * @var string
     */
    protected $logFile;

    /**
     * Seconds, after which a query is considered slow
     *
     * @var float
     */
    protected $slowSeconds;

    /**
     * Last query data
     *
     * @var array
     */
    protected $lastQuery;

    /**
     * Construct from log file and seconds of a slow query
     *
     * @param string $logFile
     * @param float $slowSeconds
     * @return void
     */
    public function __construct( $logFile, $slowSeconds = .1 )
    {
        $this->logFile = $logFile;
        $this->slowSeconds = $slowSeconds;
    }

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string $sql The SQL to be executed.
     * @param array $params The SQL parameters.
     * @param array $types The SQL parameter types.
     * @return void
     */
    public function startQuery( $sql, array $params = null, array $types = null )
    {
        $this->lastQuery = array(
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'start' => microtime( true ),
        );
    }

    /**
     * Mark the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        if ( $this->lastQuery &&
             ( $time = ( microtime( true ) - $this->lastQuery['start'] ) ) > $this->slowSeconds )
        {
            file_put_contents(
                $this->logFile,
                sprintf(
                    "[%s] Query took %.2fs seconds:\n%s\n%s\n\n",
                    date( 'r' ),
                    $time,
                    $this->lastQuery['sql'],
                    var_export( $this->lastQuery['params'], true )
                ),
                \FILE_APPEND
            );
        }
    }
}

