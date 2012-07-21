/*global jQuery: false */

jQuery( document ).ready(function() {
    "use strict";

    jQuery( "form.google" ).bind( "submit", function(event, ui) {
        window.setTimeout( function () {
            jQuery( "form.google" ).each( function() {
                this.reset();
            } );
        }, 10 );
    } );
} );

