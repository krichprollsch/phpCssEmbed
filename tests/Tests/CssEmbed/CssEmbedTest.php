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

    public function mimeTypeProvider()
    {
        return array(
          array('application/octet-stream', 'binary.file'),
          array('image/gif', 'php.gif')
        );
    }

    /**
     * @dataProvider mimeTypeProvider
     */
    public function testMimeType($expected, $file)
    {
        $cssEmbed = new CssEmbedTestable();
        $file = __DIR__.'/rsc/'.$file;
        $this->assertEquals($expected, $cssEmbed->mimeType($file));
    }

    public function testHttpEnabledEmbedCss()
    {
        $origin = __DIR__.'/rsc/test-local-with-http.css';
        $expected = file_get_contents(__DIR__.'/rsc/expected-local-with-http.css');

        $cssEmbed = new CssEmbed();
        $cssEmbed->enableHttp();
        $tested = $cssEmbed->embedCss($origin);

        $this->assertEquals($expected, $tested);
    }

    public function testHttpEnabledEmbedString()
    {
        $origin = file_get_contents(__DIR__.'/rsc/test-http.css');
        $expected = file_get_contents(__DIR__.'/rsc/expected-http.css');

        $cssEmbed = new CssEmbed();
        $cssEmbed->enableHttp();
        $cssEmbed->setRootDir('//httpbin.org/media/hypothetical-css-dir');
        $tested = $cssEmbed->embedString($origin);
        $this->assertEquals($expected, $tested);
    }

    public function testSetHttpFlag()
    {
        $origin = file_get_contents(__DIR__.'/rsc/test-http.css');
        $expected = file_get_contents(__DIR__.'/rsc/expected-http-options.css');

        $cssEmbed = new CssEmbed();

        $cssEmbed->enableHttp();
        $cssEmbed->setHttpFlag(CssEmbed::HTTP_DEFAULT_HTTPS);
        $cssEmbed->setHttpFlag(CssEmbed::HTTP_EMBED_SVG);
        $cssEmbed->setHttpFlag(CssEmbed::HTTP_EMBED_SCHEME);

        $cssEmbed->setRootDir('//httpbin.org/media/hypothetical-css-dir');
        $tested = $cssEmbed->embedString($origin);

        $this->assertEquals($expected, $tested);

        $expected = file_get_contents(__DIR__.'/rsc/expected-http.css');

        $cssEmbed->unsetHttpFlag(CssEmbed::HTTP_DEFAULT_HTTPS);
        $cssEmbed->unsetHttpFlag(CssEmbed::HTTP_EMBED_SVG);
        $cssEmbed->unsetHttpFlag(CssEmbed::HTTP_EMBED_SCHEME);
        $tested = $cssEmbed->embedString($origin);
        $this->assertEquals($expected, $tested);
    }
    
    public function testHttpEmbedUrl()
    {
        $origin = file_get_contents(__DIR__.'/rsc/test-http-url-only.css');
        $expected = file_get_contents(__DIR__.'/rsc/expected-http-url-only.css');

        $cssEmbed = new CssEmbed();
        $cssEmbed->enableHttp();
        $cssEmbed->setHttpFlag(CssEmbed::HTTP_EMBED_URL_ONLY);
        $cssEmbed->setRootDir('//httpbin.org/media/hypothetical-css-dir');
        $tested = $cssEmbed->embedString($origin);
        $this->assertEquals($expected, $tested);
    }
}

class CssEmbedTestable extends CssEmbed
{
    public function mimeType($file)
    {
        return parent::mimeType($file);
    }
}
