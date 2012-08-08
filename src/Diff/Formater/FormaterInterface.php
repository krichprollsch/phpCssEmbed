<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  11/05/12 16:04
 */

namespace Diff\Formater;

/**
 * FormatterInterface
 *
 * @author pierre
 */
interface FormaterInterface
{
    /**
     * @abstract
     * @param $value
     * @return mixed
     */
    public function addition($value);

    /**
     * @abstract
     * @param $value
     * @return mixed
     */
    public function deletion($value);

    /**
     * @abstract
     * @param $value
     * @return mixed
     */
    public function equality($value);
}
