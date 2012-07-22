(function( global ) {
    "use strict";

    var Feed;

    Feed = function() {
    };

    Feed.addUrl = function( event ) {
        var input = $( event.target ).find( "input[name=url]" ),
            id = $( event.target ).find( "input[name=id]" ).val();

        event.stopPropagation( true );

        $.post(
            "/module/" + id + "/add",
            {url: input.val() },
            function () {
                Feed.updateUrlList( event );
            },
            "json"
        );
        
        input.val( null );
        return false;   
    };

    Feed.updateUrlList = function( event ) {
        var id = $( event.target ).find( "input[name=id]" ).val(),
            target = $( "#feed-settings-" + id ).find( "tbody" );

        $.get(
            "/module/" + id + "/getList",
            function( data ) {
                Torii.showTemplate(
                    target,
                    "/templates/feed/urls.mustache",
                    {urls: data}
                );
            },
            "json"
        );
    };

    // Exports
    global.Feed = Feed;

}(this));

jQuery( document ).ready( function() {
    $( ".feed-settings" ).on( "show", Feed.updateUrlList );
    $( ".feed-settings form" ).on( "submit", Feed.addUrl );
} );

