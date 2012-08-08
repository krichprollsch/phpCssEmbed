<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  11/05/12 16:03
 */

namespace Diff;

use Diff\Formater\FormaterInterface;

/**
 * Diff
 *
 * @author pierre
 */
class Diff
{
    /**
     * @var FormaterInterface
     */
    protected $formater;

    public function __construct( FormaterInterface $formater ) {
        $this->formater = $formater;
    }

    public function compare( \SeekableIterator $old, \SeekableIterator $new ) {

        $old->rewind();
        $new->rewind();
        $new_position = -1;
        $old_position = -1;

        $new_more = array();
        $old_more = array();

        try {
            while( $old->valid() || $new->valid() ) {

                if( $old->valid() ) {
                    $old_position++;
                    if( ($ind=array_search($old->current(), $new_more)) !== false ) {
                        //trouvé
                        //tout ce qui est dans $old_more est delete
                        foreach( $old_more as $value ) { $this->formater->deletion($value); }
                        $old_more = array();
                        //tout ce qui est dans $new_more est add
                        foreach( $new_more as $key => $value ) {
                            if($key == $ind) {
                                $this->formater->equality($value);
                                break;
                            }
                            $this->formater->addition($value);
                        }
                        $new_position = $new_position - count($new_more) + $ind  + 1;
                        $new_more = array();
                        $new->seek( $new_position );

                        $old->next();
                        $new->next();
                        continue;
                    } else {
                        $old_more[] = $old->current();
                    }

                    $old->next();
                }

                if( $new->valid() ) {
                    $new_position++;
                    if( ($ind=array_search($new->current(), $old_more)) !== false ) {
                        //trouvé
                        //tout ce qui est dans $new_more est add
                        foreach( $new_more as $value ) { $this->formater->addition($value); }
                        $new_more = array();

                        //tout ce qui est dans $old_more est delete
                        foreach( $old_more as $key => $value ) {
                            if($key == $ind) {
                                $this->formater->equality($value);
                                break;
                            }
                            $this->formater->deletion($value);
                        }
                        $old_position = $old_position - count($old_more) + $ind + 1;
                        $old_more = array();
                        $old->seek( $old_position );

                        $old->next();
                        $new->next();
                        continue;
                    } else {
                        $new_more[] = $new->current();
                    }

                    $new->next();
                }
            }
        } catch( \OutOfBoundsException $ex ) {}
    }
}
