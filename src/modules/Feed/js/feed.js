(function( global ) {
    "use strict";

    var Feed;

    Feed = function() {
    };

    Feed.addUrl = function( event ) {
        var input = $( ".feed-settings form input[name=url]" );
        event.stopPropagation( true );

        $.post(
            "/module/Feed/add",
            {url: input.val() },
            Feed.updateUrlList,
            "json"
        );
        
        input.val( null );
        return false;   
    };

    Feed.updateUrlList = function( event ) {
        // @TODO: Implement
    };

    // Exports
    global.Feed = Feed;

}(this));

jQuery( document ).ready( function() {
    $( ".feed-settings" ).on( "show", Feed.updateUrlList );
    $( ".feed-settings form" ).on( "submit", Feed.addUrl );
} );

