$(document).ready(function() {
    $( ".column, .trash" ).sortable( {
        scroll: false,
        opacity: .7,
        connectWith: ".column, .trash",
        cursor: "move"
    } );

    // Center (hiffen) trash element
    $( ".trash" ).offset( {
        left: $( document ).innerWidth() / 2 - 100 - $( ".trash" ).offset().left
    } );

    $( ".column" ).bind( "sortstart", function(event, ui) {
        $( ".trash" ).fadeIn( 500 );
    } );

    $( ".column, .trash" ).bind( "sortover", function(event, ui) {
        $( this ).addClass( "drag-over" );
    } );

    $( ".column, .trash" ).bind( "sortout", function(event, ui) {
        $( this ).removeClass( "drag-over" );
    } );

    $( ".trash" ).bind( "sortreceive", function(event, ui) {
        if ( !confirm( "Really remove module?" ) ) {
            $( ui.sender ).sortable( 'cancel' );
        }
    } );

    $( ".column, .trash" ).bind( "sortstop", function(event, ui) {
        $( ".trash" ).hide();
        $( ".trash" ).empty();

        var configuration = [],
            column;
        $( ".column" ).each( function( columnNr, columnElement ) {
            column = [];
            $( columnElement ).children( "li" ).each( function( moduleNr, moduleElement ) {
                column.push( moduleElement.id );
            } );
            configuration.push( column );
        } );

        $.post(
            "/portal/resort",
            {modules: configuration},
            function ( data, textStatus, xhr ) {
                console.log( textStatus + ": " + data.ok );
            },
            "json"
        );
    } );
} );

