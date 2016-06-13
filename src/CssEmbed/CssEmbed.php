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
 * @author Pierre Tachoire <pierre.tachoire@gmail.com>
 */
class CssEmbed
{

    const SEARCH_PATTERN = "%url\\(['\" ]*((?!data:|//)[^'\"#\?: ]+)['\" ]*\\)%U";
    const URI_PATTERN = "url(data:%s;base64,%s)";
    const HTTP_ENABLED = 0x1;
    const HTTP_DEFAULT_HTTPS = 0x2;
    const HTTP_SWALLOW_EXCEPTIONS = 0x3;

    protected $root_dir;

    /** @var integer the http flags */
    protected $http_flags = 0;

    /**
     * @param $root_dir
     */
    public function setRootDir($root_dir)
    {
        $this->root_dir = $root_dir;
    }

    /**
     * Allow assets referenced over HTTP to be embedded, or assets in a css
     * file loaded over HTTP.
     *
     * @param integer $flags CssEmbed::HTTP_ENABLED: enable embedding over http;
     * CssEmbed::HTTP_DEFAULT_HTTPS: for URLs with no scheme, use https instead
     * of http; CssEmbed::HTTP_SWALLOW_EXCEPTIONS: if there is an error fetching
     * a remote asset, do not throw exception and do not replace.
     *
     * @return void
     */
    public function setAllowHttp(
        $flags = CssEmbed::HTTP_ENABLED|CssEmbed::HTTP_SWALLOW_EXCEPTIONS
    ) {
        $this->http_flags = (int) $flags;
    }

    /**
     * @param $css_file
     * @return null|string
     * @throws \InvalidArgumentException
     */
    public function embedCss($css_file)
    {
        if ($this->http_flags & self::HTTP_ENABLED) {
            return $this->httpEnabledEmbedCss($css_file);
        }
        $this->setRootDir(dirname($css_file));
        $return = null;
        $handle = fopen($css_file, "r");
        if ($handle === false) {
            throw new \InvalidArgumentException(sprintf('Cannot read file %s', $css_file));
        }
        while (($line = fgets($handle)) !== false) {
            $return .= $this->embedString($line);
        }
        fclose($handle);

        return $return;
    }

    /**
     * @param $content
     * @return mixed
     */
    public function embedString($content)
    {
        return preg_replace_callback(self::SEARCH_PATTERN, array($this, 'replace'), $content);
    }


    /**
     * @param $matches
     * @return string
     */
    protected function replace($matches)
    {
        return $this->embedFile($this->root_dir . DIRECTORY_SEPARATOR . $matches[1]);
    }

    /**
     * @param $file
     * @return string
     */
    protected function embedFile($file)
    {
        return sprintf(self::URI_PATTERN, $this->mimeType($file), $this->base64($file));
    }

    /**
     * @param $file
     * @return string
     */
    protected function mimeType($file)
    {
        if (function_exists('mime_content_type')) {
            return mime_content_type($file);
        }

        if ($info = @getimagesize($file)) {
            return($info['mime']);
        }

        return 'application/octet-stream';
    }

    /**
     * @param $file
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function base64($file)
    {
        if (is_file($file) === false || is_readable($file) === false) {
            throw new \InvalidArgumentException(sprintf('Cannot read file %s', $file));
        }

        return base64_encode(file_get_contents($file));
    }

    /**
     * @param $css_file
     * @return null|string
     * @throws \InvalidArgumentException
     */
    public function httpEnabledEmbedCss($css_file)
    {
        if (empty($this->http_flags)) {
            $this->setAllowHttp();
        }
        $this->setRootDir(dirname($css_file));
        $return = null;
        $handle = fopen($css_file, "r");
        if ($handle === false) {
            throw new \InvalidArgumentException(sprintf('Cannot read file %s', $css_file));
        }
        while (($line = fgets($handle)) !== false) {
            $return .= $this->httpEnabledEmbedString($line);
        }
        fclose($handle);

        return $return;
    }

    /**
     * @param $content
     * @return mixed
     */
    public function httpEnabledEmbedString($content)
    {
        return preg_replace_callback(
            self::SEARCH_PATTERN,
            array($this, 'httpEnabledReplace'),
            $content
        );
    }

    /**
     * @param $matches
     * @return string
     */
    protected function httpEnabledReplace($matches)
    {
        if ($this->isHttpAsset($matches[1])) {
            if ($asset_path = $this->resolveHttpAssetUrl($this->root_dir, $matches[1])) {
                if ($replacement = $this->httpEmbedAsset($asset_path)) {
                    return $replacement;
                }
            }
            return $matches[0];
        }
        // drop back to default functionality for non-remote assets
        return $this->replace($matches);
    }

    /**
     * @param string $url the URL to the file to embed
     * @return string|bool the string for the CSS url property, or FALSE if the
     * url could not be opened.
     */
    protected function httpEmbedAsset($url)
    {
        if (false === ($content = file_get_contents($url))) {
            $this->httpError('Cannot read url %s', $url);
            return false;
        }
        if (!empty($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (stripos($header, 'Content-Type:') === 0) {
                    $mime = trim(substr($header, strlen('Content-Type:')));
                }
            }
        }
        if (empty($mime)) {
            $this->httpError('No mime type sent with "%s"', $url);
            $mime = 'application/octet-stream';
        }
        return sprintf(self::URI_PATTERN, $mime, base64_encode($content));
    }

    /**
     * Check if an asset is remote or local
     *
     * @param string $path the path specified in the CSS file
     *
     * @return bool
     */
    protected function isHttpAsset($path)
    {
        // if the root directory is remote, all assets are remote
        $schemes = array('http://', 'https://', '//');
        foreach ($schemes as $scheme) {
            if (strpos($this->root_dir, $scheme) === 0) {
                return true;
            }
        }
        // check for remote embedded assets
        foreach ($schemes as $scheme) {
            if (strpos($path, $scheme) === 0) {
                return true;
            }
        }
        // otherwise, it's a local asset
        return false;
    }

    /**
     * Resolve the URL to an http asset
     *
     * @param string $root_url the root URL
     * @param string
     */
    protected function resolveHttpAssetUrl($root_url, $path)
    {
        $default_scheme = ($this->http_flags & self::HTTP_DEFAULT_HTTPS)
                        ? 'https:'
                        : 'http:'
                        ;

        // case 1: path is already fully qualified url
        if (strpos($path, '//') === 0) {
            $path = $default_scheme . $path;
        }
        if (preg_match('/^https?:\/\//', $path)) {
            if (!filter_var($path, FILTER_VALIDATE_URL)) {
                $this->httpError('Invalid asset url "%s"', $path);
                return false;
            }
            return $path;
        }

        // case 2: the root directory is remote
        if (strpos($root_url, '//') === 0) {
            $root_url = $default_scheme . $root_url;
        }
        // asset is absolute path
        if (strpos($path, '/') === 0) {
            $root_url = preg_replace('#^(https?://[^/]+).*#', '$1', $root_dir);
        // asset is relative path
        } elseif (substr($root_url, -1) !== '/') {
            $root_url .= '/';
        }
        $url = $root_url . $path;
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->httpError('Could not resolve "%s" with root "%s"', $path, $this->root_dir);
            return false;
        }
        return $url;
    }

    /**
     * Throw an exception if HTTP_SWALLOW_EXCEPTIONS is not set
     *
     * @param string $msg the message
     * @param string $interpolations... strings to interpolate in the error message
     * @throws \InvalidArgmumentException
     * @return void
     */
    protected function httpError($msg, $interpolations)
    {
        if ($this->http_flags & self::HTTP_SWALLOW_EXCEPTIONS) {
            return;
        }
        $msg = call_user_func_array('sprintf', func_get_args());
        throw new \InvalidArgmentException($msg);
    }
}
