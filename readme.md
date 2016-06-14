PhpCssEmbed
====

**PhpCssEmbed** embed data uri in css part

[![Build Status](https://travis-ci.org/krichprollsch/phpCssEmbed.png?branch=master)](https://travis-ci.org/krichprollsch/phpCssEmbed)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/krichprollsch/phpCssEmbed/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/krichprollsch/phpCssEmbed/?branch=master)

Usage
-----

Use embed css with a file

    <?php
        $pce = new \CssEmbed\CssEmbed();
        echo $pce->embedCss( $css_file );

Or directly with css content

    <?php
        $pce = new \CssEmbed\CssEmbed();
        $pce->setRootDir( '/path/to/files' );
        echo $pce->embedString( $css_content );

Working with HTTP Assets
------------------------

To embed online assets, such as images, enable the HTTP functions with
`enableHttp`:

    // in style.css
    #my-selector {
        background: url('http://example.com/path/to/image.jpeg');
    }

    // in php
    $pce = new \CssEmbed\CssEmbed();
    $pce->enableHttp();
    echo $pce->embedCss('/path/to/style.css');

This also works for embedding assets in a remote stylesheet:

    <?php
        $pce = new \CssEmbed\CssEmbed();
        $pce->enableHttp();
        echo $pce->embedCss('http://example.com/path/to/style.css');

Or when working with CSS directly:

    <?php
        $pce = new \CssEmbed\CssEmbed();
        $pce->enableHttp();
        $pce->setRootDir( '//example.com/path/to/assets' );
        echo $pce->embedString( $css_content );

Control of HTTP behaviour can be set via bitwise flags that can be set
as an argument for `enableHttp` or `setHttpFlag`. They can be unset with
`unsetHttpFlag`:

    <?php
        $pce = new \CssEmbed\CssEmbed();
        $pce->enableHttp(\CssEmbed::HTTP_ENABLED|\CssEmbed::HTTP_URL_ON_ERROR);
        $pce->setHttpFlag(\CssEmbed::HTTP_EMBED_URL_ONLY);
        $pce->unsetHttpFlag(\CssEmbed::HTTP_URL_ON_ERROR);
        // ...

Available flags are:

 - CssEmbed::HTTP_ENABLED: enable embedding over http;
 - CssEmbed::HTTP_DEFAULT_HTTPS: for URLs with no scheme, use https to
   instead of http
 - CssEmbed::HTTP_URL_ON_ERROR: if there is an error fetching a remote
   asset, embed the URL instead of throwing an exception
 - CssEmbed::HTTP_EMBED_FONTS: embedding fonts will usually break them
   in most browsers.  Enable this flag to force the embed.
 - CssEmbed::HTTP_EMBED_SVG: SVG is often used as a font face; however
   including these in a stylesheet will cause it to bloat for browsers
   that don't use it.  By default SVGs will be replaced with the URL
   to the asset; set this flag to force the embed of SVG files.
 - CssEmbed::HTTP_EMBED_SCHEME: By default, assets that are converted
   to URLs instead of data urls have no scheme (eg, "//example.com").
   This is better for stylesheets that are maybe served over http or
   https, but it will break stylesheets served from a local HTML file.
   Set this option to force the scheme (eg, "http://example.com").
 - CssEmbed::HTTP_EMBED_URL_ONLY: do not convert assets to data URLs,
   only the fully qualified URL.


Unit Tests
----------

    phpunit

Thanks
------

Files structure inspired by [Geocoder](https://github.com/willdurand/Geocoder)
from [William Durand](https://github.com/willdurand)
