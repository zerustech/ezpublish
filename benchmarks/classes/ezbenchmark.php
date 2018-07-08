<?php
/**
 * File containing the eZBenchmark class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 * @package kernel
 */

/*!
  \class eZBenchmark ezbenchmark.php
  \brief eZBenchmark provides a framework for doing benchmarks

*/

require_once( 'lib/ezutils/classes/ezdebug.php' );

class eZBenchmark extends eZBenchmarkUnit
{
    function __construct( $name = false )
    {
        parent::__construct( $name );
    }

    function addMark( &$mark )
    {
        if ( is_subclass_of( $mark, 'ezbenchmarkunit' ) )
        {
            $markList = $mark->markList();
            foreach ( $markList as $entry )
            {
                $entry['name'] = $mark->name() . '::' . $entry['name'];
                $this->addEntry( $entry );
            }
        }
        else
        {
            eZDebug::writeWarning( "Tried to add mark unit for an object which is not subclassed from eZBenchmarkUnit", __METHOD__ );
        }
    }
}

?>
