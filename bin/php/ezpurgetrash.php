#!/usr/bin/env php
<?php
//
// Created on: <19-Nov-2009 09:51:42 gl>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ Publish
// SOFTWARE RELEASE: 4.1.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2009 eZ Systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

$isQuiet = false;

require( 'autoload.php' );

define( 'EZ_PURGE_TRASH_LIMIT', 70 ); // Number of objects to purge in each batch
define( 'EZ_PURGE_TRASH_SLEEP', 2 ); // Number of seconds to sleep between each batch

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Empty eZ Publish trash.\n\n" .
                                                        "Permanently deletes all objects in the trash." .
                                                        "\n" .
                                                        "ezpurgetrash.php"),
                                     'use-session' => false,
                                     'use-modules' => false,
                                     'use-extensions' => true ) );

$script->startup();
$script->getOptions();
$script->initialize();

$script->setIterationData( '.', '~' );

$db = eZDB::instance();
$ini = eZINI::instance();

// Get user's ID who can remove subtrees. (Admin by default with userID = 14)
$userCreatorID = $ini->variable( "UserSettings", "UserCreatorID" );
$user = eZUser::fetch( $userCreatorID );
if ( !$user )
{
    $cli->error( "Cannot get user object with userID = '$userCreatorID'.\n(See site.ini[UserSettings].UserCreatorID)" );
    $script->shutdown( 1 );
}
eZUser::setCurrentlyLoggedInUser( $user, $userCreatorID );

$trashCount = eZContentObjectTrashNode::trashListCount( false );
if ( !$isQuiet )
    $cli->output( "Found $trashCount object(s) in trash." );
if ( $trashCount == 0 )
    $script->shutdown( 0 );
$script->resetIteration( $trashCount );

while ( $trashCount > 0 )
{
    $trashList = eZContentObjectTrashNode::trashList( array( 'Limit'  => EZ_PURGE_TRASH_LIMIT ), false );

    $db->begin();

    foreach ( $trashList as $trashNode )
    {
        $object = $trashNode->attribute( 'object' );
        $object->purge();
        $script->iterate( $cli, true );
    }

    if ( !$db->commit() )
    {
        $cli->output();
        $cli->error( 'Trash has not been emptied, impossible to commit the whole transaction' );
        $script->shutdown( 1 );
    }

    $trashCount = eZContentObjectTrashNode::trashListCount( false );
    if ( $trashCount > 0 )
        sleep( EZ_PURGE_TRASH_SLEEP );
}

if ( !$isQuiet )
    $cli->output( 'Trash successfully emptied' );

$script->shutdown();

?>
