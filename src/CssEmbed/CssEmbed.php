<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  08/08/12 15:22
 */

namespace CssEmbed;

/**
 * CssEmbed
 *
 * @author pierre
 */
class CssEmbed
{

    const SEARCH_PATTERN = "/^(.*url\\(['\" ]*)([^'\" ]+)(['\" ]*\\).*)$/U";
    const URI_PATTERN = "data:%s;base64,%s";

    /**
     * @param string $css_file
     *
     * @return string
     */
    public function embed($css_file) {
        $base = dirname($css_file);
        $return = null;
        $handle = fopen($css_file, "r");
        if($handle === false) {
            throw new \InvalidArgumentException(sprintf('Cannot read file %s', $css_file));
        }
        while(($line = fgets($handle)) !== false) {
            $return .= $this->embedLine($line, $base);
        }
        fclose($handle);

        return $return;
    }

    protected function embedLine($line, $base) {
        $matches = null;
        if( preg_match_all(self::SEARCH_PATTERN, $line, $matches) == true ) {
            return $matches[1][0]
                .$this->embedFile( $base . DIRECTORY_SEPARATOR . $matches[2][0] )
                .$matches[3][0]
                ."\n";
        }
        return $line;
    }

    protected function embedFile( $file ) {
        return sprintf( self::URI_PATTERN, mime_content_type($file), $this->base64($file) );
    }

    protected function base64( $file ) {
        if( is_file($file) == false || is_readable($file) == false ) {
            throw new \InvalidArgumentException(sprintf('Cannot read file %s', $file));
        }
        return base64_encode( file_get_contents($file) );
    }
}
