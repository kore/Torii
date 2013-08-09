(function( global ) {
    "use strict";

    var Weather;

    Weather = function() {
    };

    Weather.prototype.fetch = function( id, location ) {
        var query = 'SELECT * FROM weather.bylocation WHERE location="' + location + '" AND unit="c"',
            target = $( "#" + id ).find( ".body" );

        $.getJSON(
            'https://query.yahooapis.com/v1/public/yql?callback=?',
            {   q:           query,
                format:      "json",
                diagnostics: "true",
                env:         "store://datatables.org/alltableswithkeys"
            },
            function ( data ) {
                Torii.showTemplate(
                    target,
                    "/templates/weather/weather.mustache",
                    {   weather: data.query.results.weather.rss.channel,
                        module: id
                    }
                );
            }
        );

        window.setTimeout(
            function () {
                Weather.prototype.fetch( id, location );
            },
            1000 * 60 * 30
        );
    };

    // Exports
    global.Weather = Weather;

}(this));

$( document ).ready( function() {

    var weather = new Weather();

    $( 'form.weather' ).on( "submit", function( event ) {
        var id = $( event.currentTarget ).find( "input[name='module']" ).val(),
            location = $( event.currentTarget ).find( "input[name='location']" ).val();

        Torii.setConfig( id, { location: location }, null );
        weather.fetch( id, location );

        return false;
    } );

    $( '.weather-loading' ).each( function ( key, element ) {
        var id = $( element ).data( "module" ),
            location = $( element ).data( "location" );

        weather.fetch( id, location );
    } );
} );

