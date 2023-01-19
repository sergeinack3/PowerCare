<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;

/**
 * Code of different types (CCAM, CIM, ADELI, etc)
 */
class CCodeSpec extends CMbFieldSpec
{
    public $ccam;
    public $cim10;
    public $cim10Pmsi;
    public $adeli;
    public $insee;
    public $rib;
    public $siret;
    public $order_number;

    /**
     * @inheritdoc
     */
    function getSpecType()
    {
        return "code";
    }

    /**
     * @inheritdoc
     */
    function getDBSpec()
    {
        $type_sql = null;

        if ($this->ccam) {
            $type_sql = "VARCHAR(7)";
        } elseif ($this->cim10) {
            $type_sql = "VARCHAR(6)";
        } elseif ($this->cim10Pmsi) {
            $type_sql = "VARCHAR(10)";
        } elseif ($this->adeli) {
            $type_sql = "VARCHAR(9)";
        } elseif ($this->insee) {
            $type_sql = "VARCHAR(15)";
        } elseif ($this->rib) {
            $type_sql = "VARCHAR(23)";
        } elseif ($this->siret) {
            $type_sql = "VARCHAR(14)";
        }

        return $type_sql;
    }

    /**
     * @inheritdoc
     */
    function getOptions()
    {
        return [
                'ccam'         => 'bool',
                'cim10'        => 'bool',
                'cim10Pmsi'    => 'bool',
                'adeli'        => 'bool',
                'insee'        => 'bool',
                'rib'          => 'bool',
                'siret'        => 'bool',
                'order_number' => 'bool',
            ] + parent::getOptions();
    }

    /**
     * Check validity of INSEE code
     *
     * @param string $insee INSEE code
     *
     * @return string|null String on error, null on OK
     */
    static function checkInsee($insee)
    {
        $matches = [];
        if (!preg_match("/^([12478][0-9]{2}[0-9]{2}[0-9][0-9ab][0-9]{3}[0-9]{3})([0-9]{2})$/i", $insee, $matches)) {
            return "Matricule incorrect";
        }

        $code = preg_replace(['/2A/i', '/2B/i'], [19, 18], $matches[1]);
        $code = intval($code);

        $cle = $matches[2];

        // Should be floatval when PHP 7.2
        if (97 - intval(bcmod($code, 97)) != $cle) {
            return "Matricule incorrect, la clé n'est pas valide";
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function checkProperty($object)
    {
        $propValue = $object->{$this->fieldName};

        // ccam
        if ($this->ccam) {
            //^[A-Z]{4}[0-9]{3}(-[0-9](-[0-9])?)?$
            // ancienne expression reguliere ([a-z0-9]){0,7}
            if (!preg_match("/^[A-Z]{4}[0-9]{3}(-[0-9](-[0-9])?)?$/i", $propValue)) {
                return "Code CCAM incorrect";
            }
        } // cim10
        elseif ($this->cim10) {
            if (!preg_match("/^[a-z][0-9x+.]{2,6}$/i", $propValue)) {
                // $codeCim = new CCodeCIM10($propValue);
                // if ($codeCim->loadLite()) {
                //   return "Code CIM inconnu";
                // }

                return "Code CIM incorrect, doit contenir une lettre, puis de 2 à 6 chiffres, et du symbole + ou de la lettre X";
            }
        } // cim10 PMSI
        elseif ($this->cim10Pmsi) {
            if (!preg_match("/^[a-z]([0-9]{1,5})((\+|x)[0-9])?$/i", $propValue)) {
                return "Code CIM incorrect, doit contenir une lettre, puis de 2 à 5 chiffres ou la lettre X";
            }
        } // adeli
        elseif ($this->adeli) {
            if (!preg_match("/^([a-zA-Z0-9]){9}$/i", $propValue)) {
                return "Code Adeli incorrect, doit contenir exactement 9 caractères";
            }

            if (!CMbString::luhnForAdeli($propValue)) {
                return "Code Adeli incorrect, le code n'est pas un nombre de luhn";
            }
        } // RIB
        elseif ($this->rib) {
            $compte_banque  = substr($propValue, 0, 5);
            $compte_guichet = substr($propValue, 5, 5);
            $compte_numero  = substr($propValue, 10, 11);
            $compte_cle     = substr($propValue, 21, 2);
            $tabcompte      = "";
            $len            = strlen($compte_numero);
            for ($i = 0; $i < $len; $i++) {
                $car = substr($compte_numero, $i, 1);
                if (!is_numeric($car)) {
                    $c         = ord($car) - 64;
                    $b         = ($c < 10) ? $c : (($c < 19) ? $c - 9 : $c - 17);
                    $tabcompte .= $b;
                } else {
                    $tabcompte .= $car;
                }
            }
            $int = $compte_banque . $compte_guichet . $tabcompte . $compte_cle;
            if (!((strlen($int) >= 21) && (bcmod($int, 97) == 0))) {
                return "Rib incorrect";
            }
        } // INSEE
        elseif ($this->insee) {
            if (preg_match("/^([0-9]{7,8}[A-Z])$/i", $propValue)) {
                return;
            }

            return self::checkInsee($propValue);
        } // siret
        elseif ($this->siret) {
            if (!CMbString::luhn($propValue)) {
                return "Code SIRET incorrect, doit contenir exactement 14 chiffres";
            }
        } // order_number
        elseif ($this->order_number) {
            if (!preg_match('#\%id#', $propValue)) {
                return "Format de numéro de serie incorrect, doit contenir au moins une fois %id";
            }
        } else {
            return "Spécification de code invalide";
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function getFormHtmlElement($object, $params, $value, $className)
    {
        return $this->getFormElementText($object, $params, $value, $className);
    }

    /**
     * @inheritdoc
     */
    function sample($object, $consistent = true)
    {
        parent::sample($object, $consistent);
        $propValue = &$object->{$this->fieldName};

        // ccam
        if ($this->ccam) {
            $propValue = "BFGA004";
        } // cim10
        elseif ($this->cim10) {
            $propValue = "H251";
        } // adeli
        elseif ($this->adeli) {
            $propValue = "123456789";
        } // rib
        elseif ($this->rib) {
            $propValue = "11111111111111111111111";
        } // siret
        elseif ($this->siret) {
            $propValue = "73282932000074";
        } // insee
        elseif ($this->insee) {
            $propValue = "100000000000047";
        }
    }

    /**
     * @inheritdoc
     */
    public function getLitteralDescription(): string
    {
        $litteral = "Code";

        if ($this->ccam) {
            $litteral = "Code CCAM de la forme : 'AAAANNN'";
        }
        if ($this->cim10) {
            $litteral = "Code CIM10 de la forme : 'ANNN' ou 'ANNNN'";
        }
        if ($this->adeli) {
            $litteral = "Code ADELI de 9 chiffres dont le dernier assure le contrôle de parité";
        }
        if ($this->insee) {
            $litteral = "Code INSEE de 15 chiffres";
        }
        if ($this->rib) {
            $litteral = "Code RIP de 23 chiffres dont les 2 derniers assurent le contrôle de parité";
        }
        if ($this->siret) {
            $litteral = "Code SIRET de 14 chiffres";
        }

        return "$litteral. " . parent::getLitteralDescription();
    }
}
