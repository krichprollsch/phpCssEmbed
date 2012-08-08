<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  11/05/12 16:04
 */

namespace Diff\Formater;

/**
 * DefaultFormater
 *
 * @author pierre
 */
class DefaultFormater implements FormaterInterface
{
    /**
     * @var resource|string
     */
    protected $stdout;

    /**
     * @var bool
     */
    protected $to_close=false;

    /**
     * @param string $stdout
     * @throws \InvalidArgumentException
     */
    public function __construct( $stdout="php://stdout" ) {
        if( is_string($stdout)) {
            $stdout = @fopen($stdout, "a");
            if( $stdout === false ) {
                throw new \InvalidArgumentException('stdout is not a valid string');
            }
            $this->to_close=true;
        }
        if( is_resource($stdout) == false ) {
            throw new \InvalidArgumentException('stdout is not a resource');
        }
        $this->stdout = $stdout;
    }

    /**
     * @param $value
     */
    public function addition($value) {
        fwrite($this->stdout, sprintf("+%s\n", $value));
    }

    /**
     * @param $value
     */
    public function deletion($value) {
        fwrite($this->stdout, sprintf("-%s\n", $value));
    }

    /**
     * @param $value
     */
    public function equality($value) {
        fwrite($this->stdout, sprintf("%s\n", $value));
    }

    /**
     *
     */
    public function __destruct() {
        if($this->to_close) {
            @fclose($this->stdout);
        }
    }
}
