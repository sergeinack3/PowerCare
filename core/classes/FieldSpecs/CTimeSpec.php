<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;
use Ox\Core\CValue;

/**
 * Time value (HH:MM:SS)
 */
class CTimeSpec extends CMbFieldSpec
{
    /** @var string */
    public $min;
    
    /** @var string */
    public $max;
    
    /** @var bool */
    public $duration;

    public $keywords = ['current', 'now'];

    /**
     * @inheritdoc
     */
    function getSpecType()
    {
        return "time";
    }

    /**
     * @inheritdoc
     */
    function getDBSpec()
    {
        return "TIME";
    }

    /**
     * @inheritdoc
     */
    function checkParams($object)
    {
        $propValue = &$object->{$this->fieldName};
        if (in_array($propValue, $this->keywords)) {
            $propValue = CMbDT::time();
        }

        return parent::checkParams($object);
    }

    /**
     * @inheritdoc
     */
    function checkProperty($object)
    {
        $propValue = &$object->{$this->fieldName};

        $time_format = "/^\d{1,2}:\d{1,2}(:\d{1,2})?$/";

        // Format
        if (!preg_match($time_format, $propValue)) {
            return "Format d'heure invalide";
        }

        // min
        if ($this->min) {
            if (!preg_match($time_format, $this->min)) {
                trigger_error("Spécification de minimum time invalide (min = $this->min)", E_USER_WARNING);

                return "Erreur système";
            }
            if ($propValue < $this->min) {
                return "Doit avoir une valeur minimale de $this->min";
            }
        }

        // max
        if ($this->max) {
            if (!preg_match($time_format, $this->max)) {
                trigger_error("Spécification de maximum time invalide (max = $this->max)", E_USER_WARNING);

                return "Erreur système";
            }
            if ($propValue > $this->max) {
                return "Doit avoir une valeur maximale de $this->max";
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function getOptions()
    {
        return [
                'min' => 'time',
                'max' => 'time',
            ] + parent::getOptions();
    }

    /**
     * @inheritdoc
     */
    function getValue($object, $params = [])
    {
        if ($this->duration) {
            return CMbDT::formatDuration($object->{$this->fieldName});
        }

        $propValue = $object->{$this->fieldName};
        $format    = CValue::first(@$params["format"], CAppUI::conf("time"));

        return $propValue ? CMbDT::format($propValue, $format) : "";
    }

    /**
     * @inheritdoc
     */
    function sample($object, $consistent = true)
    {
        parent::sample($object, $consistent);

        $min                        = $this->min ?? '00:00:00';
        $max                        = $this->max ?? '23:59:59';
        $object->{$this->fieldName} = CMbDT::getRandomDate($min, $max, 'H:i:s');
    }

    /**
     * @inheritdoc
     */
    function getFormHtmlElement($object, $params, $value, $className)
    {
        if ($this->duration && !$value) {
            $value = "00:00:00";
        }

        $use_slider = CMbArray::extract($params, "slider", false);

        if ($use_slider) {
            return $this->getFormElementDateTimeSlider("time", $params, $value);
        }

        return $this->getFormElementDateTime($object, $params, $value, $className, CAppUI::conf("time"));
    }

    /**
     * @inheritdoc
     */
    public function getLitteralDescription(): string
    {
        $litteral = "Heure";
        if ($this->duration) {
            $litteral = "Durée";
        }
        $litteral .= " au format 'HH:mm:ss'";

        if ($this->min || $this->max) {
            if ($this->min) {
                $litteral .= ", minimum : '$this->min'";
            }
            if ($this->max) {
                $litteral .= ", maximum : '$this->max'";
            }
        }

        return "$litteral. " . parent::getLitteralDescription();
    }
}
