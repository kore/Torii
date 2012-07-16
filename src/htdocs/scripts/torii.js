$(document).ready(function() {
    $( ".column" ).each( function( key, list ) {
        DragDrop.makeListContainer( list );

        list.onDragOver = function() {
            $( this ).addClass( "drag-over" );
        };

        list.onDragOut = function() {
            $( this ).removeClass( "drag-over" );
        };

        DragDrop.onDragFinished = function() {
            var configuration = [],
                column;
            $( ".column" ).each( function( columnNr, columnElement ) {
                column = [];
                $( columnElement ).children( "li" ).each( function( moduleNr, moduleElement ) {
                    column.push( moduleElement.id );
                } );
                configuration.push( column );
            } );
            
            // @TODO: Send new configuration to server.
        };
    } );
} );

