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
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;

/**
 * Password
 */
class CPasswordSpec extends CMbFieldSpec
{
    public $minLength;
    public $revealable;

    /** @var boolean Can we generate random password? */
    public $randomizable;

    /**
     * @inheritdoc
     */
    function getSpecType()
    {
        return "password";
    }

    /**
     * @inheritdoc
     */
    function getDBSpec()
    {
        return "VARCHAR(50)";
    }

    /**
     * @inheritdoc
     */
    function getOptions()
    {
        return [
                'minLength'    => 'num',
                'revealable'   => 'bool',
                'randomizable' => 'bool',
            ] + parent::getOptions();
    }

    // TODO: Factoriser les check
    function checkProperty($object)
    {
        $propValue = $object->{$this->fieldName};

        // minLength
        if ($this->minLength) {
            if (!$length = $this->checkLengthValue($this->minLength)) {
                trigger_error(
                    "Spécification de longueur minimale invalide (longueur = $this->minLength)",
                    E_USER_WARNING
                );

                return "Erreur système";
            }

            if (strlen($propValue) < $length) {
                return "Le mot de passe n'a pas la bonne longueur '$propValue' (longueur minimale souhaitée : $length)'";
            }
        }

        // notContaining
        if ($field = $this->notContaining) {
            if ($msg = $this->checkTargetPropValue($object, $field)) {
                return $msg;
            }

            $targetPropValue = $object->$field;
            if (($targetPropValue !== null) && stripos($propValue, $targetPropValue) !== false) {
                return "Le mot de passe ne doit pas contenir '$field->fieldName'";
            }
        }

        // notNear
        if ($field = $this->notNear) {
            if ($msg = $this->checkTargetPropValue($object, $field)) {
                return $msg;
            }
            $targetPropValue = $object->$field;
            if (levenshtein($propValue, $targetPropValue ?? '') < 3) {
                return "Le mot de passe ressemble trop à '$field->fieldName'";
            }
        }

        // alphaAndNum
        if ($this->alphaAndNum) {
            if (!preg_match("/[A-z]/", $propValue) || !preg_match("/\d+/", $propValue)) {
                return 'Le mot de passe doit contenir au moins un chiffre ET une lettre';
            }
        }

        // alphaLowChars
        if ($this->alphaLowChars && (!preg_match('/[a-z]/', $propValue))) {
            return 'Le mot de passe doit contenir au moins une lettre bas-de-casse (sans disacritique)';
        }

        // alphaUpChars
        if ($this->alphaUpChars && (!preg_match('/[A-Z]/', $propValue))) {
            return 'Le mot de passe doit contenir au moins une lettre en capitale d\'imprimerie (sans accent)';
        }

        // alphaChars
        if ($this->alphaChars && (!preg_match('/[A-z]/', $propValue))) {
            return 'Le mot de passe doit contenir au moins une lettre (sans accent)';
        }

        // numChars
        if ($this->numChars && (!preg_match('/\d/', $propValue))) {
            return 'Le mot de passe doit contenir au moins un chiffre';
        }

        // specialChars
        if ($this->specialChars && (!preg_match('/[!-\/:-@\[-`\{-~]/', $propValue))) {
            return 'Le mot de passe doit contenir au moins un caractère spécial';
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function getFormHtmlElement($object, $params, $value, $className)
    {
        $form  = CMbArray::extract($params, "form"); // needs to be extracted
        $field = CMbString::htmlSpecialChars($this->fieldName);
        $name  = CMbArray::extract($params, 'name');
        $extra = CMbArray::makeXmlAttributes($params);

        $name = $name ?: $field;

        $placeholder = $value ? CAppUI::tr('common-Password saved') : CAppUI::tr('common-No password');

        $sHtml = '<input type="password" placeholder="' . $placeholder . '" name="' . $name . '" class="' . CMbString::htmlSpecialChars(
                trim($className . ' ' . $this->prop)
            ) . ' styled-element" ';

        if ($this->revealable) {
            $sHtml .= ' value="' . CMbString::htmlSpecialChars($value) . '" ';
        }

        $sHtml .= $extra . ' />';

        if ($this->revealable) {
            $sHtml .= '<button class="lookup notext" type="button" onclick="var i=$(this).previous(\'input\');i.type=(i.type==\'password\')?\'text\':\'password\'"></button>';
        }

        if ($this->randomizable) {
            $random_call = "getRandomPassword(this, '$object->_class', '$name');";
            $title       = CAppUI::tr("common-action-Get random password");
            $sHtml       .= '<button class="dice notext" type="button" onclick="' . $random_call . '" title="' . $title . '"></button>';
        }

        $sHtml .= '<span id="' . $name . '_message"></span>';

        return $sHtml;
    }

    /**
     * @inheritdoc
     */
    function sample($object, $consistent = true)
    {
        parent::sample($object, $consistent);
        $object->{$this->fieldName} = self::randomString(
            array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z')),
            8
        );
    }

    /**
     * @inheritdoc
     */
    public function getLitteralDescription(): string
    {
        $litteral = "Mot de passe. " . parent::getLitteralDescription();

        if ($this->minLength) {
            $litteral .= CAppUI::tr("CPasswordSpec-msg-password at least character", [$this->minLength]);
        }

        return $litteral;
    }
}
