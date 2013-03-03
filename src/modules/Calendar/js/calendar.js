(function( global ) {
    "use strict";

    var Calendar;

    Calendar = function() {
    };

    Calendar.config = {};

    Calendar.statusMap = {
        0:   "Not Fetched",
        100: "Continue",
        101: "Switching Protocols",
        102: "Processing",
        118: "Connection timed out",
        200: "OK",
        201: "Created",
        202: "Accepted",
        203: "Non-Authoritative Information",
        204: "No Content",
        205: "Reset Content",
        206: "Partial Content",
        207: "Multi-Status",
        300: "Multiple Choices",
        301: "Moved Permanently",
        302: "Found",
        303: "See Other",
        304: "Not Modified",
        305: "Use Proxy",
        306: "(reserviert)",
        307: "Temporary Redirect",
        400: "Bad Request",
        401: "Unauthorized",
        402: "Payment Required",
        403: "Forbidden",
        404: "Not Found",
        405: "Method Not Allowed",
        406: "Not Acceptable",
        407: "Proxy Authentication Required",
        408: "Request Time-out",
        409: "Conflict",
        410: "Gone",
        411: "Length Required",
        412: "Precondition Failed",
        413: "Request Entity Too Large",
        414: "Request-URL Too Long",
        415: "Unsupported Media Type",
        416: "Requested range not satisfiable",
        417: "Expectation Failed",
        418: "I'm a teapot",
        421: "There are too many connections from your internet address",
        422: "Unprocessable Entity",
        423: "Locked",
        424: "Failed Dependency",
        425: "Unordered Collection",
        426: "Upgrade Required",
        451: "Unavailable For Legal Reasons",
        500: "Internal Server Error",
        501: "Not Implemented",
        502: "Bad Gateway",
        503: "Service Unavailable",
        504: "Gateway Time-out",
        505: "HTTP Version not supported",
        506: "Variant Also Negotiates",
        507: "Insufficient Storage",
        509: "Bandwidth Limit Exceeded",
        510: "Not Extended",
    };

    Calendar.addUrl = function( event ) {
        var name = $( event.target ).find( "input[name=name]" ).val(),
            url = $( event.target ).find( "input[name=url]" ).val(),
            id = $( event.target ).find( "input[name=id]" ).val();

        event.stopPropagation( true );

        $.post(
            "/module/" + id + "/add",
            {url: url, name: name},
            function () {
                Calendar.updateUrlList( null, id );
            },
            "json"
        );

        event.target.reset();
        return false;
    };

    Calendar.updateUrlList = function( event, id ) {
        var id = id || $( event.target ).find( "input[name=id]" ).val(),
            target = $( "#calendar-list-" + id ).find( "tbody" );
        $.get(
            "/module/" + id + "/getList",
            function( data ) {

                $.each( data, function( key, value ) {
                    value.textStatus = Calendar.statusMap[value.status];
                    
                    if ( value.status < 300 ) {
                        value.statusClass = "ok";
                    } else if ( value.status < 400 ) {
                        value.statusClass = "redirect";
                    } else if ( value.status < 500 ) {
                        value.statusClass = "fault";
                    } else {
                        value.statusClass = "error";
                    }
                } );

                Torii.showTemplate(
                    target,
                    "/templates/calendar/urls.mustache",
                    {   urls: data,
                        module: id
                    },
                    function () {
                        $( target ).find( "a.remove" ).on( "click", Calendar.removeUrl );
                    }
                );
            },
            "json"
        );
    };

    Calendar.removeUrl = function( event ) {
        var data = $( event.delegateTarget ).data();

        $.post(
            "/module/" + data.module + "/remove",
            data,
            function () {
                Calendar.updateUrlList( null, data.module );
            },
            "json"
        );
    };

    Calendar.refresh = function( id ) {
        var target = "#" + id + "-content";

        $.get(
            "/module/" + id + "/update?timezone=" + jstz.determine().name(),
            function( data ) {
                var dates = [];
                
                $.each( data, function( key, element ) {
                    dates.push( {
                        day: key,
                        entries: element
                    } );
                } );

                Torii.showTemplate(
                    target,
                    "/templates/calendar/entries.mustache",
                    {   dates: dates,
                        module: id,
                        scriber: Calendar.config[id].scriber === "true"
                    }
                );
            },
            "json"
        );

        window.setTimeout(
            function() { Calendar.refresh( id ) },
            1000 * 60 * 2.5
        );
    };

    // Exports
    global.Calendar = Calendar;

}(this));

jQuery( document ).ready( function() {
    $( ".calendar-list" ).on( "show", Calendar.updateUrlList );
    $( ".calendar-list form" ).on( "submit", Calendar.addUrl );

    $( ".calendar-content" ).each( function( key, element ) {
        var id = $( element ).data().id;
        Torii.getConfig( id, function( data ) {
            Calendar.config[id] = data;
            Calendar.refresh( id );
        } );
    } );
} );

