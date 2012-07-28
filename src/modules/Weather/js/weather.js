(function( global ) {
    "use strict";

    var Weather;

    Weather = function() {
    };

    Weather.prototype.fetch = function( id, location ) {
        var query = 'SELECT * FROM weather.bylocation WHERE location="' + location + '" AND unit="c"',
            target = $( "#" + id ).find( ".body" );

        $.getJSON(
            'http://query.yahooapis.com/v1/public/yql?callback=?',
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
    };

    // Exports
    global.Weather = Weather;

}(this));

$( document ).ready( function() {

    var weather = new Weather();

    $( 'form.weather' ).on( "submit", function( event ) {
        weather.fetch(
            $( event.currentTarget ).find( "input[name='module']" ).val(),
            $( event.currentTarget ).find( "input[name='location']" ).val()
        );
        return false;
    } );
} );
