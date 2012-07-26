$( document ).ready( function() {
    $( '#findweather' ).on( "click", function() {
        var query = 'SELECT * FROM weather.bylocation WHERE location="' + $( '#placename' ).val() + '" AND unit="c"';

        $.getJSON(
            'http://query.yahooapis.com/v1/public/yql?callback=?',
            {   q:           query,
                format:      "json",
                diagnostics: "true",
                env:         "store://datatables.org/alltableswithkeys"
            },
            function ( data ) {

                console.log(data);

                var weather = data.query.results.weather.rss.channel;

                $('#weathertext').show();
                $('#placetitle').html($('#placename').val());
                $('#weatherimage').attr('src','/images/'+weather.item.condition.code+'.png');
                $('#temperature').html(weather.item.condition.temp+' &deg;C');
                $('#condition').html(weather.item.condition.text);
                var winddirection=parseInt(weather.wind.direction);
                var direction='';
                switch(true)
                {
                    case (winddirection==0):
                        direction='N';
                        break;
                    case (winddirection<90):
                        direction='NE';
                        break;
                    case (winddirection==90):
                        direction='E';
                        break;
                    case (winddirection<180):
                        direction='SE';
                        break;
                    case (winddirection==180):
                        direction='S';
                        break;
                    case (winddirection<270):
                        direction='SW';
                        break;
                    case (winddirection==270):
                        direction='W';
                        break;
                    case (winddirection<360):
                        direction='NW';
                        break;
                    case (winddirection==360):
                        direction='N';
                        break;
                }
                $('#dirspeed').html('Wind: '+direction+' at '+weather.wind.speed+' km/h');
                $('#humidity').html('Humidity: '+weather.atmosphere.humidity+'%');
            }
        );
    } );
} );
