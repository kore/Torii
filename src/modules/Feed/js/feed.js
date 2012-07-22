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
                Feed.updateUrlList( null, id );
            },
            "json"
        );

        input.val( null );
        return false;
    };

    Feed.updateUrlList = function( event, id ) {
        var id = id || $( event.target ).find( "input[name=id]" ).val(),
            target = $( "#feed-settings-" + id ).find( "tbody" );

        $.get(
            "/module/" + id + "/getList",
            function( data ) {
                Torii.showTemplate(
                    target,
                    "/templates/feed/urls.mustache",
                    {   urls: data,
                        module: id
                    },
                    function () {
                        $( target ).find( "button" ).on( "click", Feed.removeUrl );
                    }
                );
            },
            "json"
        );
    };

    Feed.removeUrl = function( event ) {
        var data = $( event.target ).data();

        $.post(
            "/module/" + data.module + "/remove",
            data,
            function () {
                Feed.updateUrlList( null, data.module );
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

