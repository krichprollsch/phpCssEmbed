PhpCssEmbed
====

**PhpCssEmbed** embed data uri in css part

[![Build Status](https://secure.travis-ci.org/krichprollsch/Tac.png)](http://travis-ci.org/krichprollsch/Tac)

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

Unit Tests
----------

    phpunit

Thanks
------

Files structure inspired by [Geocoder](https://github.com/willdurand/Geocoder)
from [William Durand](https://github.com/willdurand)