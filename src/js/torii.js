/*global jQuery: false */

jQuery( document ).ready(function() {
    "use strict";

    jQuery( ".column, .trash" ).sortable( {
        scroll: false,
        delay: 200,
        opacity: 0.7,
        connectWith: ".column, .trash",
        cursor: "move"
    } );

    // Center (hiffen) trash element
    jQuery( ".trash" ).offset( {
        left: jQuery( document ).innerWidth() / 2 - 100 - jQuery( ".trash" ).offset().left
    } );

    jQuery( ".column" ).bind( "sortstart", function(event, ui) {
        jQuery( ".trash" ).fadeIn( 500 );
    } );

    jQuery( ".column, .trash" ).bind( "sortover", function(event, ui) {
        jQuery( this ).addClass( "drag-over" );
    } );

    jQuery( ".column, .trash" ).bind( "sortout", function(event, ui) {
        jQuery( this ).removeClass( "drag-over" );
    } );

    jQuery( ".trash" ).bind( "sortreceive", function(event, ui) {
        if ( !confirm( "Really remove module?" ) ) {
            jQuery( ui.sender ).sortable( 'cancel' );
        }
    } );

    jQuery( ".column, .trash" ).bind( "sortstop", function(event, ui) {
        jQuery( ".trash" ).hide();
        jQuery( ".trash" ).empty();

        var configuration = [],
            column;
        jQuery( ".column" ).each( function( columnNr, columnElement ) {
            column = [];
            jQuery( columnElement ).children( "li" ).each( function( moduleNr, moduleElement ) {
                column.push( moduleElement.id );
            } );
            configuration.push( column );
        } );

        jQuery.post(
            "/portal/resort",
            {modules: configuration},
            function ( data, textStatus, xhr ) {
                console.log( textStatus + ": " + data.ok );
            },
            "json"
        );
    } );
} );

