(function( global ) {
    "use strict";

    var Account;

    Account = function() {
    };

    Account.config = {};

    Account.addAccount = function( event ) {
        var name = $( event.target ).find( "input[name=name]" ).val(),
            blz = $( event.target ).find( "input[name=blz]" ).val(),
            knr = $( event.target ).find( "input[name=knr]" ).val(),
            pin = $( event.target ).find( "input[name=pin]" ).val(),
            id = $( event.target ).find( "input[name=id]" ).val();

        event.stopPropagation( true );

        $.post(
            "/module/" + id + "/add",
            {name: name, blz: blz, knr: knr, pin: pin},
            function () {
                Account.updateAccountList( null, id );
            },
            "json"
        );

        event.target.reset();
        return false;
    };

    Account.updateAccountList = function( event, id ) {
        var id = id || $( event.target ).find( "input[name=id]" ).val(),
            target = $( "#account-list-" + id ).find( "tbody" );
        $.get(
            "/module/" + id + "/getList",
            function( data ) {
                Torii.showTemplate(
                    target,
                    "/templates/account/accounts.mustache",
                    {   accounts: data,
                        module: id
                    },
                    function () {
                        $( target ).find( "a.remove" ).on( "click", Account.removeAccount );
                    }
                );
            },
            "json"
        );
    };

    Account.removeAccount = function( event ) {
        var data = $( event.delegateTarget ).data();

        $.post(
            "/module/" + data.module + "/remove",
            data,
            function () {
                Account.updateAccountList( null, data.module );
            },
            "json"
        );
    };

    Account.refresh = function( id ) {
        var target = "#" + id + "-content";

        $.get(
            "/module/" + id + "/update",
            function( data ) {
                Torii.showTemplate(
                    target,
                    "/templates/account/entries.mustache",
                    {   entries: data,
                        module: id
                    }
                );
            },
            "json"
        );

        window.setTimeout(
            function() { Account.refresh( id ) },
            1000 * 60 * 2.5
        );
    };

    // Exports
    global.Account = Account;

}(this));

jQuery( document ).ready( function() {
    $( ".account-list" ).on( "show", Account.updateAccountList );
    $( ".account-list form" ).on( "submit", Account.addAccount );

    $( ".account-content" ).each( function( key, element ) {
        var id = $( element ).data().id;
        Torii.getConfig( id, function( data ) {
            Account.config[id] = data;
            Account.refresh( id );
        } );
    } );
} );

