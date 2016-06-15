<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  11/05/12 17:20
 */
namespace CssEmbed\Tests;

use CssEmbed\CssEmbed;

/**
 * @author Pierre Tachoire <pierre.tachoire@gmail.com>
 */
class CssEmbedTest extends \PHPUnit_Framework_TestCase
{
    public function testEmbedCss()
    {
        $origin = __DIR__.'/rsc/test.css';
        $expected = file_get_contents(__DIR__.'/rsc/expected.css');

        $cssEmbed = new CssEmbed();
        $tested = $cssEmbed->embedCss($origin);

        $this->assertEquals($expected, $tested);
    }

    public function testEmbedString()
    {
        $origin = file_get_contents(__DIR__.'/rsc/test.css');
        $expected = file_get_contents(__DIR__.'/rsc/expected.css');

        $cssEmbed = new CssEmbed();
        $cssEmbed->setRootDir(__DIR__.'/rsc');
        $tested = $cssEmbed->embedString($origin);

        $this->assertEquals($expected, $tested);
    }

    public function testMimeTypes()
    {
        $origin = file_get_contents(__DIR__.'/rsc/test-mime.css');
        $expected = file_get_contents(__DIR__.'/rsc/expected-mime.css');

        $cssEmbed = new CssEmbed();
        $cssEmbed->setRootDir(__DIR__.'/rsc');
        $cssEmbed->enableEnhancedMimeTypes();
        $tested = $cssEmbed->embedString($origin);

        $this->assertEquals($expected, $tested);
    }

    public function testSetOptions()
    {
        $origin = file_get_contents(__DIR__.'/rsc/test-options.css');
        $expected = file_get_contents(__DIR__.'/rsc/expected-options.css');

        $cssEmbed = new CssEmbed();
        $cssEmbed->enableEnhancedMimeTypes();
        $cssEmbed->setOptions(CssEmbed::URL_ON_ERROR|CssEmbed::EMBED_FONTS|CssEmbed::EMBED_SVG);
        
        $cssEmbed->setRootDir(__DIR__.'/rsc');
        $tested = $cssEmbed->embedString($origin);

        $this->assertEquals($expected, $tested);
    }

    public function testHttpEnabledEmbedCss()
    { 
        $origin = __DIR__.'/rsc/test-http-enabled.css';
        $expected = file_get_contents(__DIR__.'/rsc/expected-http-enabled.css');

        $cssEmbed = new CssEmbed();
        $cssEmbed->enableHttp();
        $tested = $cssEmbed->embedCss($origin);

        $this->assertEquals($expected, $tested);
    }

    public function testHttpEnabledEmbedString()
    {
        $origin = file_get_contents(__DIR__.'/rsc/test-http-remote.css');
        $expected = file_get_contents(__DIR__.'/rsc/expected-http-remote.css');

        $cssEmbed = new CssEmbed();
        $cssEmbed->enableHttp();
        $cssEmbed->setRootDir('//httpbin.org/media/hypothetical-css-dir');
        $tested = $cssEmbed->embedString($origin);
        $this->assertEquals($expected, $tested);
    }

    public function testHttpEnabledEnableOptions()
    {
        $origin = file_get_contents(__DIR__.'/rsc/test-http-options.css');
        $expected = file_get_contents(__DIR__.'/rsc/expected-http-options.css');

        $cssEmbed = new CssEmbed();

        $cssEmbed->enableHttp(
            true,
            CssEmbed::HTTP_DEFAULT_HTTPS|CssEmbed::HTTP_EMBED_SCHEME|CssEmbed::HTTP_EMBED_URL_ONLY
        );
        $cssEmbed->setRootDir('//httpbin.org/media/hypothetical-css-dir');
        $tested = $cssEmbed->embedString($origin);

        $this->assertEquals($expected, $tested);

    }
}


