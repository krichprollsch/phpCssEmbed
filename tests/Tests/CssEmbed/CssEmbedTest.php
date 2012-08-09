<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  11/05/12 17:20
 */
namespace CssEmbed\Tests;

use CssEmbed\CssEmbed;

/**
 * @author Pierre Tachoire
 */
class CssEmbedTest extends \PHPUnit_Framework_TestCase {

    public function testEmbedCss() {

        $origin = __DIR__.'/rsc/test.css';
        $expected = file_get_contents( __DIR__.'/rsc/expected.css' );

        $cssEmbed = new CssEmbed();
        $tested = $cssEmbed->embedCss( $origin );

        $this->assertEquals( $expected, $tested );
    }

    public function testEmbedString() {

        $origin = file_get_contents(__DIR__.'/rsc/test.css');
        $expected = file_get_contents( __DIR__.'/rsc/expected.css' );

        $cssEmbed = new CssEmbed();
        $cssEmbed->setRootDir(__DIR__.'/rsc'  );
        $tested = $cssEmbed->embedString( $origin );

        $this->assertEquals( $expected, $tested );
    }


}
