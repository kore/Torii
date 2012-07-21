/**
 * jquery form protect plugin
 *
 * This file is part of jquery form protect plugin.
 *
 * Author: Jakob Westhoff <jakob@westhoffswelt.de>
 *
 * jquery form protect plugin is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 of the License.
 *
 * jquery form protect plugin is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * jquery form protect plugin; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

(function($) {
    $.fn.protect = function( message ) {
        return this.each( function() {
            $(this).find( "input, textarea" )
                .bind( "change", function( e ) {
                    $.fn.protect.changed = true;
                });

            $(window).bind( "beforeunload", function() {
                if ( $.fn.protect.changed ) {
                    return message;
                }
            });
            
            $(this).bind( 'submit', function( e ) {
                $.fn.protect.changed = false;
            });          
        });
    };

    $.fn.protect.changed = false;
})( jQuery );
