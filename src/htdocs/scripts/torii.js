$(document).ready(function() {
    $( ".column" ).sortable( {
        scroll: false,
        opacity: .7,
        connectWith: ".column"
    } );

    $( ".column" ).bind( "sortupdate", function(event, ui) {
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

