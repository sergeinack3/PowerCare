<?php
/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Class CHtml
 */
class CHtml
{

    private $content;

    /**
     * CHtml constructor.
     *
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getContentWithoutTags(): string
    {
        return strip_tags($this->content);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->content;
    }

    /**
     * Output content
     */
    public function display(){
        echo $this->content;
    }
}
