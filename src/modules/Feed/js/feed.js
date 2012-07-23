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

    Feed.refresh = function( id ) {
        var target = "#" + id + "-content";

        $.get(
            "/module/" + id + "/update",
            function( data ) {
                Torii.showTemplate(
                    target,
                    "/templates/feed/entries.mustache",
                    {   entries: data,
                        module: id
                    },
                    function () {
                        $( target ).find( "li a" ).on( "mousedown", function( event ) {
                            var link = event.target;
                            $( link ).attr(
                                "href",
                                "/module/" + id + "/redirect/" + $( link ).data().id + "/" + escape( $( link ).attr( "href" ) )
                            );
                        } );

                        $( target ).find( "li a" ).on( "mouseup", function( event ) {
                            var link = event.target;
                            window.setTimeout(
                                function () {
                                    $( link ).parent().remove();
                                },
                                10
                            );
                        } );
                    }
                );
            },
            "json"
        );

        window.setTimeout(
            function() { Feed.refresh( id ) },
            1000 * 60 * 2.5
        );
    };

    // Exports
    global.Feed = Feed;

}(this));

jQuery( document ).ready( function() {
    $( ".feed-settings" ).on( "show", Feed.updateUrlList );
    $( ".feed-settings form" ).on( "submit", Feed.addUrl );
    $( ".feed-content" ).each( function( key, element ) {
        Feed.refresh( $( element ).data().id );
    } );
} );

