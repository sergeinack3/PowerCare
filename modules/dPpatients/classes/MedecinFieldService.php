<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CMbString;

/**
 * Description
 */
class MedecinFieldService
{
    /** @var string[] */
    protected static $mapping_fields = [
        'ville'    => 'commune',
        'portable' => 'tel2',
    ];

    /**
     * @var string[]
     */
    protected static $fields_replaceable = [
        'email'
    ];

    /** @var CMedecin */
    protected $medecin;

    /** @var CMedecinExercicePlace */
    protected $medecin_exercice_place;

    public function __construct(CMedecin $medecin, CMedecinExercicePlace $medecin_exercice_place)
    {
        $this->medecin                = $medecin;
        $this->medecin_exercice_place = $medecin_exercice_place;
        $this->medecin_exercice_place->loadRefExercicePlace();
    }

    public function getAdresse(): ?string
    {
        $adresse = $this->getField('adresse');

        $cp_ville = $this->getCP() . ' ' . strtoupper($this->getVille());

        if (strpos($adresse, $cp_ville) !== false) {
            $adresse = str_replace($cp_ville, '', $adresse);
        }

        return $adresse;
    }

    public function getCP(): ?string
    {
        return $this->getField('cp');
    }

    public function getVille(): ?string
    {
        return CMbString::upper($this->getField('ville'));
    }

    public function getTel(): ?string
    {
        return $this->getField('tel');
    }

    public function getPortable(): ?string
    {
        return $this->getField('portable');
    }

    public function getFax(): ?string
    {
        return $this->getField('fax');
    }

    public function getEmail(): ?string
    {
        return $this->getField('email');
    }

    public function getMssanteAddress(): ?string
    {
        return $this->getField('mssante_address', false);
    }

    public function getDisciplines(): ?string
    {
        return $this->getField('disciplines', false);
    }

    protected function getField(string $field, bool $from_exercice_place = true): ?string
    {
        // Field name from exercice place
        $field_place = $this->getFieldName($field);

        $context = $from_exercice_place ?
            $this->medecin_exercice_place->_ref_exercice_place : $this->medecin_exercice_place;

        return ($context->_id && $this->isFieldIrreplaceable($context, $field_place)) ?
            $context->getFormattedValue($field_place) : $this->medecin->getFormattedValue($field);
    }

    protected function getFieldName(string $field): string
    {
        if (isset(static::$mapping_fields[$field])) {
            return static::$mapping_fields[$field];
        }

        return $field;
    }

    /**
     * Check if field can be used on medecin
     *
     * @param CExercicePlace|CMedecinExercicePlace $context
     * @param string $field
     *
     * @return bool
     */
    protected function isFieldIrreplaceable($context, string $field): bool
    {
        // Not a replaceable field, we keep it
        if (!in_array($field, static::$fields_replaceable)) {
            return true;
        }

        // Field valued, we keep it
        if ($context->{$field}) {
            return true;
        }

        // We can replace it
        return false;
    }
}
