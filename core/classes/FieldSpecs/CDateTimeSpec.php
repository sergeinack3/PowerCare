<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;

/**
 * DateTime value (YYYY-MM-DD HH:MM:SS)
 */
class CDateTimeSpec extends CMbFieldSpec
{
    public $refDate;
    public $hideDate;

    public $keywords = ['current', 'now'];

    /**
     * @inheritdoc
     */
    function getSpecType()
    {
        return "dateTime";
    }

    /**
     * @inheritdoc
     */
    function getDBSpec()
    {
        return "DATETIME";
    }

    /**
     * @inheritdoc
     */
    function getOptions()
    {
        return [
                'refDate'  => 'field',
                'hideDate' => 'bool',
            ] + parent::getOptions();
    }

    /**
     * @inheritdoc
     */
    function getValue($object, $params = [])
    {
        $propValue = $object->{$this->fieldName};

        $format = CMbArray::extract($params, "format", CAppUI::conf("datetime"));

        if ($format === "relative") {
            $relative = CMbDT::relativeDuration($propValue, CMbDT::dateTime());

            return $relative["locale"] ?? null;
        }

        $date = CMbArray::extract($params, "date", $this->refDate ? $object->{$this->refDate} : null);
        if ($date && CMbDT::date($propValue) === $date) {
            $format = CAppUI::conf("time");
        }

        if (!$propValue || $propValue === "0000-00-00 00:00:00") {
            return "";
        }

        return CMbDT::format($propValue, $format);
    }

    /**
     * @inheritdoc
     */
    function checkParams($object)
    {
        $propValue = &$object->{$this->fieldName};
        if (in_array($propValue, $this->keywords)) {
            $propValue = CMbDT::dateTime();
        }

        return parent::checkParams($object);
    }

    /**
     * @inheritdoc
     */
    function checkProperty($object)
    {
        $propValue = &$object->{$this->fieldName};

        if (!preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}[ \+][0-9]{1,2}:[0-9]{1,2}(:[0-9]{1,2})?$/", $propValue)) {
            return "format de dateTime invalide : '$propValue'";
        }

        $propValue = strtr($propValue, "+", " ");

        [$date, $time] = explode(' ', $propValue);
        if (!CMbDT::isLunarDate($date) && !CMbDT::isDatetimeValid($propValue)) {
            return "La date '{$propValue}' est invalide";
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function sample($object, $consistent = true)
    {
        parent::sample($object, $consistent);

        $object->{$this->fieldName} = CMbDT::getRandomDate('1970-01-01 00:00:00', CMbDT::dateTime());
    }

    /**
     * @inheritdoc
     */
    function getFormHtmlElement($object, $params, $value, $className)
    {
        $use_slider = CMbArray::extract($params, "slider", false);
        if ($use_slider) {
            return $this->getFormElementDateTimeSlider("datetime", $params, $value);
        }

        return $this->getFormElementDateTime($object, $params, $value, $className, CAppUI::conf("datetime"));
    }

    /**
     * @inheritdoc
     */
    public function getLitteralDescription(): string
    {
        return "Date et heure au format : 'YYYY-MM-DD HH:mm:ss'. " . parent::getLitteralDescription();
    }

    /**
     * @return array
     */
    public function transform(): array
    {
        $datas              = parent::transform();
        $datas['hide_date'] = $this->hideDate;

        return $datas;
    }
}
