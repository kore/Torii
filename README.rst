=====
Torii
=====

Torii is a simple but powerful portal application which easily allows writing
and adding new modules. Torii includes full API documentation and an initial
set of modules for feed aggregation, weather statistics, and more...

Installation
============

To install Torii, clone the repository and run the following commands::

    git submodule init
    git submodule update
    composer.phar install
    ant install

After that configure your webserver properly, and you should done. You might
need to adapt the database connection settings in ``build.properties`` and
``src/config/config.ini``.

Lighttpd Example
----------------

Example configuration for the lighttd webserver::

    $HTTP["host"] =~ "torii$" {
        server.document-root = "/path/to/torii/htdocs"
        server.error-handler-404 = "/index.php"
        url.rewrite-once = (
            "^(\/templates\/|\/styles\/|\/images\/|\/scripts\/).*" => "$0",
            "(?:\?(.*))?$" => "/index.php?$1"
        )
    }



..
   Local Variables:
   mode: rst
   fill-column: 79
   End: 
   vim: et syn=rst tw=79
