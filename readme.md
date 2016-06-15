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

Options
-------

A few behaviour options can be changed before embedding:

    <?php
        $pce = new \CssEmbed\CssEmbed();
        $pce->setRootDir( '/path/to/files' );
        $pce->setOptions(\CssEmbed\CssEmbed::URL_ON_ERROR|\CssEmbed\CssEmbed::EMBED_SVG);
        echo $pce->embedString( $css_content );

Available flags are:

 - `CssEmbed::URL_ON_ERROR`: if there is an error reading an asset, embed the URL
   instead of throwing an exception
 - `CssEmbed::EMBED_FONTS`: embedding fonts will usually break them in most
   browsers.  Enable this flag to force the embed.
 - `CssEmbed::EMBED_SVG`: SVG is often used as a font face; however including
   these in a stylesheet will cause it to bloat for browsers that don't use it.
   By default SVGs will be replaced with the URL to the asset; set this flag to
   force the embed of SVG files.

Mime Type Detection
-------------------

By default, the class will detect mime types using PHP's built in mime type
detection utilities. However, for more exotic file types, such as fonts, this
can often fail. To enable mime type detection that is more inline with the the
mime types that are typically sent by web servers, use the method
`enableEnhancedMimeTypes()`:

    <?php
        $pce = new \CssEmbed\CssEmbed();
        $cssEmbed->enableEnhancedMimeTypes();
        echo $pce->embedCss( $css_file );

The method accepts two parameters:

  - `$path` (string): the path to the mime.types file
  - `$create` (bool): if the file does not exist at `$path`, download and use
    the default Apache file. The directory for `$path` must be writable for this
    to work.

Note that this option is likely necessary for the `CssEmbed::EMBED_FONTS` and
`CssEmbed::EMBED_SVG` options to work properly.

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

There are a few options available for controlling how remote assets are
displayed:

    <?php
        $pce = new \CssEmbed\CssEmbed();
        $pce->enableHttp(true, $flags);
        $pce->setRootDir( '//example.com/path/to/assets' );
        echo $pce->embedString( $css_content );

Available flags are:

 - `CssEmbed::HTTP_DEFAULT_HTTPS`: for URLs with no scheme, use https to
   instead of http
 - `CssEmbed::HTTP_EMBED_SCHEME`: By default, assets that are converted
   to URLs instead of data urls have no scheme (eg, "//example.com").
   This is better for stylesheets that are maybe served over http or
   https, but it will break stylesheets served from a local HTML file.
   Set this option to force the scheme (eg, "http://example.com").
 - `CssEmbed::HTTP_EMBED_URL_ONLY`: do not convert assets to data URLs,
   only the fully qualified URL.


Unit Tests
----------

    phpunit

Thanks
------

Files structure inspired by [Geocoder](https://github.com/willdurand/Geocoder)
from [William Durand](https://github.com/willdurand)
