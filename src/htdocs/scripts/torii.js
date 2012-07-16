$(document).ready(function() {
    $( ".column" ).each( function( key, list ) {
        DragDrop.makeListContainer( list );

        list.onDragOver = function() {
            $( this ).addClass( "drag-over" );
        };

        list.onDragOut = function() {
            $( this ).removeClass( "drag-over" );
        };
    } );
} );

