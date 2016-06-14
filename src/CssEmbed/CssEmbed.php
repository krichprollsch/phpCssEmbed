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

    const HTTP_SEARCH_PATTERN = "%url\\(['\" ]*((?!data:)[^'\" ]+)['\" ]*\\)%U";
    const HTTP_ENABLED = 1;
    const HTTP_DEFAULT_HTTPS = 2;
    const HTTP_URL_ON_ERROR = 4;
    const HTTP_EMBED_FONTS = 8;
    const HTTP_EMBED_SVG = 16;
    const HTTP_EMBED_SCHEME = 32;
    const HTTP_EMBED_URL_ONLY = 64;

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
     * file loaded over HTTP. Flags:
     *
     *     - CssEmbed::HTTP_ENABLED: enable embedding over http;
     *     - CssEmbed::HTTP_DEFAULT_HTTPS: for URLs with no scheme, use https to
     *       for urls instead of http
     *     - CssEmbed::HTTP_URL_ON_ERROR: if there is an error fetching a remote
     *       asset, embed the URL instead of throwing an exception
     *     - CssEmbed::HTTP_EMBED_FONTS: embedding fonts will usually break them
     *       in most browsers.  Enable this flag to force the embed.
     *     - CssEmbed::HTTP_EMBED_SVG: SVG is often used as a font face; however
     *       including these in a stylesheet will cause it to bloat for browsers
     *       that don't use it.  By default SVGs will be replaced with the URL
     *       to the asset, set this flag to embed it.
     *     - CssEmbed::HTTP_EMBED_SCHEME: By default, assets that are converted
     *       to URLs instead of data urls have no scheme (eg, "//example.com").
     *       This is better for stylesheets that are maybe served over http or
     *       https, but it will break stylesheets served from a local HTML file.
     *       Set this option to force the schema (eg, "http://example.com").
     *     - CssEmbed::HTTP_EMBED_URL_ONLY: do not convert assets to data URLs,
     *       only the fully qualified URL.
     *
     *
     * @param integer $flags
     *
     * @return void
     */
    public function setAllowHttp(
        $flags = CssEmbed::HTTP_ENABLED|CssEmbed::HTTP_URL_ON_ERROR
    ) {
        $this->http_flags = (int) $flags;
    }

    /**
     * Set a single http option flag. See setAllowHttp for a description of
     * available flags.
     *
     * @param integer $flag
     *
     * @return void
     */
    public function setHttpFlag($flag)
    {
        $this->http_flags |= $flag;
    }

    /**
     * unset a single http option flag. See setAllowHttp for a description of
     * available flags.
     *
     * @param integer $flag
     *
     * @return void
     */
    public function unsetHttpFlag($flag)
    {
        $this->http_flags = $this->http_flags & (~ $flag);
    }

    /**
     * @param $css_file
     * @return null|string
     * @throws \InvalidArgumentException
     */
    public function embedCss($css_file, $force_root_dir = false)
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
     * @param $force_root_dir force the root directory
     * @return null|string
     * @throws \InvalidArgumentException
     */
    public function httpEnabledEmbedCss($css_file, $force_root_dir = false)
    {
        if (empty($this->http_flags)) {
            $this->setAllowHttp();
        }
        $root_dir = $force_root_dir ? $force_root_dir : dirname($css_file);
        $this->setRootDir($root_dir);
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
            self::HTTP_SEARCH_PATTERN,
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
        // fall back to default functionality for non-remote assets
        if (!$this->isHttpAsset($matches[1])) {
            return $this->replace($matches);
        }
        if ($asset_url = $this->resolveHttpAssetUrl($this->root_dir, $matches[1])) {
            if ($replacement = $this->httpEmbedAsset($asset_url)) {
                return $replacement;
            }
            return $this->httpEmbedAssetUrl($asset_url);
        }
        return $matches[0];
    }

    /**
     * @param string $url the URL to the file to embed
     * @return string|bool the string for the CSS url property, or FALSE if the
     * url could not/should not be embedded.
     */
    protected function httpEmbedAsset($url)
    {
        if ($this->http_flags & self::HTTP_EMBED_URL_ONLY) {
            return;
        }
        if (false === ($content = @file_get_contents($url))) {
            $this->httpError('Cannot read url %s', $url);
            return false;
        }
        if (!empty($http_response_header)) {
            foreach ($http_response_header as $header) {
                $header = strtolower($header);
                if (strpos($header, 'content-type:') === 0) {
                    $mime = trim(substr($header, strlen('content-type:')));
                }
            }
        }
        if (empty($mime)) {
            $this->httpError('No mime type sent with "%s"', $url);
            return false;
        }

        // handle a special case: fonts will usually break if embedded.
        $embed_fonts = ($this->http_flags & self::HTTP_EMBED_FONTS);
        $is_font = strpos($mime, 'font') !== false;
        if ($is_font && !$embed_fonts) {
            return false;
        }
        
        // another special case:  SVG is often a font and will cause the
        // stylesheet to bloat if it's embeded for browsers that don't use it.
        $embed_svg = ($this->http_flags & self::HTTP_EMBED_SVG);
        $is_svg = strpos($mime, 'svg') !== false;
        if ($is_svg && !($embed_svg || $embed_fonts)) {
            return false;
        }
        
        return sprintf(self::URI_PATTERN, $mime, base64_encode($content));
    }

    protected function httpEmbedAssetUrl($url)
    {
        if (!($this->http_flags & self::HTTP_EMBED_SCHEME)) {
            $url = preg_replace('/^https?:/', '', $url);
        }
        return sprintf("url('%s')", $url);
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
        // absolutes should be remote
        if (strpos($path, '/') === 0) {
            return true;
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

        if (strpos($root_url, '//') === 0) {
            $root_url = $default_scheme . $root_url;
        }
        $root_domain = preg_replace('#^(https?://[^/]+).*#', '$1', $root_url);
        $root_path = substr($root_url, strlen($root_domain));

        // case 2: asset is absolute path
        if (strpos($path, '/') === 0) {
            return $root_domain . $path;
        }

        // case 3: asset is relative path        
        // remove directory transversal (file_get_contents seems to choke on it)
        $path = explode('/', $path);
        $root_path = array_filter(explode('/', $root_path));
        $asset_path = array();
        while (NULL !== ($part = array_shift($path))) {
            if (!$part || $part === '.') {
                // drop the empty part
            } elseif ($part == '..') {
                array_pop($root_path);
            } else {
                $_path[] = $part;
            }
        }
        $asset_path = implode('/', $_path);
        $root_path = empty($root_path) ? '/' : '/' . implode('/', $root_path) . '/';
        $url = $root_domain . $root_path . $asset_path;
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->httpError('Could not resolve "%s" with root "%s"', $path, $this->root_dir);
            return false;
        }
        return $url;
    }

    /**
     * Throw an exception if HTTP_URL_ON_ERROR is not set
     *
     * @param string $msg the message
     * @param string $interpolations... strings to interpolate in the error message
     * @throws \InvalidArgmumentException
     * @return void
     */
    protected function httpError($msg, $interpolations)
    {
        if ($this->http_flags & self::HTTP_URL_ON_ERROR) {
            return;
        }
        $msg = call_user_func_array('sprintf', func_get_args());
        throw new \InvalidArgumentException($msg);
    }
}
