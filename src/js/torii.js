/*global jQuery: false, Mustache: false */

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
    if ( jQuery( ".trash" ).offset() ) {
        jQuery( ".trash" ).offset( {
            left: jQuery( document ).innerWidth() / 2 - 100 - jQuery( ".trash" ).offset().left
        } );
    }

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

(function( global ) {
    "use strict";

    var Torii,
        templateCache = {};

    Torii = function() {
    };

    Torii.showTemplate = function( target, template, data, success ) {
        ( function() {
            if ( templateCache[template] ) {
                var deferred = new jQuery.Deferred();
                deferred.resolve( templateCache[template] );
                return deferred.promise();
            }

            return jQuery.get( template );
        }() ).then( function( templateData ) {
            templateCache[template] = templateData;

            jQuery( target ).html(
                Mustache.to_html( templateData, data )
            );

            // Call optional success function after completion
            if ( success ) {
                success();
            }
        } );
    };

    Torii.getConfig = function( module, callback ) {
        $.get(
            "/portal/config/" + module,
            callback,
            "json"
        );
    };

    Torii.setConfig = function( module, config, callback ) {
        $.post(
            "/portal/config/" + module,
            config,
            callback,
            "json"
        );
    };

    // Exports
    global.Torii = Torii;

}(this));

