=====
Torii
=====

.. image::  https://secure.travis-ci.org/kore/Torii.png
   :alt:    Travis Status
   :target: https://secure.travis-ci.org/kore/Torii 
   :align:  right

Torii is a simple but powerful portal application which easily allows writing
and adding new modules. Torii includes an initial set of modules for feed
aggregation, weather statistics, and more...

Requirements
============

- Ant >= 1.8.0
- PHP >= 5.3
- MySql >= 5.1
- Composer

Installation
============

To install Torii, clone the repository and run the following commands::

    git submodule init
    git submodule update
    ant -Dcommons.env=testing install
    ant install

After that configure your webserver properly, and you should done. You might
need to adapt the database connection settings -- see `Configuration`_ for
details.

Lighttpd Example
----------------

Example configuration for the lighttpd webserver::

    $HTTP["host"] =~ "torii$" {
        server.document-root = "/path/to/torii/htdocs"
        server.error-handler-404 = "/index.php"
        url.rewrite-once = (
            "^(\/templates\/|\/styles\/|\/images\/|\/scripts\/).*" => "$0",
            "(?:\?(.*))?$" => "/index.php?$1"
        )
    }

Configuration
-------------

To configure your Torii instance copy the ``src/config/config.ini.dist`` to
``srtc/config/config.ini`` and edit the settings there. If you change the
database connection settings you might also want to do this in your
``build.properties.local`` -- see `Development`_ for details.

Development
===========

To set the application to development mode create a file
``build.properties.local`` containing ``commons.env = development`` in the
project root (just beside the ``build.properties`` file). You can set other
local build environment variables there, too.

To run the tests for Torii just execute ``ant`` in the project root (where the
``build.xml`` file resides). The first run may take a while, but subsequent
runs will be a lot faster.


..
   Local Variables:
   mode: rst
   fill-column: 79
   End: 
   vim: et syn=rst tw=79
