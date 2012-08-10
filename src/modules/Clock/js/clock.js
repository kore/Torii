/*global jQuery: false */

jQuery( document ).ready(function() {
    "use strict";

    function updateClock( target ) {
        var time = new Date(),
            data = {
                time: time.toString( "T" ),
                date: time.toString( "dddd, MMMM ddS, yyyy" )
            };

        Torii.showTemplate(
            target,
            "/templates/clock/clock.mustache",
            data
        );
    }

    jQuery( "div.clock" ).each( function( key, element ) {
        window.setInterval(
            function () {
                updateClock( element );
            },
            1000
        );
    } );
} );

