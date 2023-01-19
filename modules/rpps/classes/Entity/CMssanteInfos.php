<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Entity;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbString;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;

/**
 * Description
 */
class CMssanteInfos extends CAbstractExternalRppsObject
{
    /** @var int */
    public $mssante_info_id;

    /** @var string */
    public $type_bal;

    /** @var string */
    public $email;

    /** @var int */
    public $type_id_structure;

    /** @var string */
    public $id_structure;

    /** @var string */
    public $service_rattachement;

    /** @var string */
    public $civilite;

    /** @var string */
    public $categorie_profession;

    /** @var string */
    public $libelle_categorie_profession;

    /** @var string */
    public $code_profession;

    /** @var string */
    public $libelle_profession;

    /** @var string */
    public $code_savoir_faire;

    /** @var string */
    public $libelle_savoir_faire;

    /** @var string */
    public $dematerialisation;

    /** @var string */
    public $raison_sociale_bal;

    /** @var string */
    public $enseigne_commerciale_structure;

    /** @var string */
    public $complement_localisation_structure;

    /** @var string */
    public $complement_distribution_structure;

    /** @var string */
    public $numero_voie_structure;

    /** @var string */
    public $complement_numero_voie_structure;

    /** @var string */
    public $type_voie_structure;

    /** @var string */
    public $libelle_voie_structure;

    /** @var string */
    public $lieu_dit_mention_structure;

    /** @var string */
    public $ligne_acheminement_structure;

    /** @var string */
    public $code_postal_structure;

    /** @var string */
    public $departement_structure;

    /** @var string */
    public $pays_structure;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "mssante_info";
        $spec->key   = "mssante_info_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['type_bal']                          = 'enum list|ORG|PER notNull';
        $props['email']                             = 'email notNull';
        $props['type_id_structure']                 = 'str';
        $props['id_structure']                      = 'str';
        $props['service_rattachement']              = 'str';
        $props['civilite']                          = 'str';
        $props['categorie_profession']              = 'str';
        $props['libelle_categorie_profession']      = 'str';
        $props['code_profession']                   = 'str';
        $props['libelle_profession']                = 'str';
        $props['code_savoir_faire']                 = 'str';
        $props['libelle_savoir_faire']              = 'str';
        $props['dematerialisation']                 = 'enum list|O|N';
        $props['raison_sociale_bal']                = 'str';
        $props['enseigne_commerciale_structure']    = 'str';
        $props['complement_localisation_structure'] = 'str';
        $props['complement_distribution_structure'] = 'str';
        $props['numero_voie_structure']             = 'str';
        $props['complement_numero_voie_structure']  = 'str';
        $props['type_voie_structure']               = 'str';
        $props['libelle_voie_structure']            = 'str';
        $props['lieu_dit_mention_structure']        = 'str';
        $props['ligne_acheminement_structure']      = 'str';
        $props['code_postal_structure']             = 'str';
        $props['departement_structure']             = 'str';
        $props['pays_structure']                    = 'str';


        return $props;
    }

    /**
     * @param CMedecinExercicePlace $medecin_exercice_place
     *
     * @return string|CMedecinExercicePlace
     * @throws Exception
     */
    public function synchronizeMssante(?CMedecinExercicePlace $medecin_exercice_place = null)
    {
        if (!$medecin_exercice_place) {
            return null;
        }

        $addresses = explode("\n", $medecin_exercice_place->mssante_address ?? '');


        if (!in_array($this->email, $addresses)) {
            $addresses[] = CMbString::lower($this->email);

            $medecin_exercice_place->mssante_address = implode("\n", array_unique($addresses));
        }

        return $medecin_exercice_place;
    }

    public function synchronize(?CMedecin $medecin = null): CMedecin
    {
        return $medecin ?? new CMedecin();
    }

    /**
     * Comparison of objects (for PHPUnit assertions).
     * This is for the manual import of correspondant purpose
     *
     * @param CMssanteInfos $other
     *
     * @return bool
     */
    public function equals(self $other): bool
    {
        return (
            $this->email === $other->email
        );
    }

    /**
     * Sanitizes the mssante addresses in order to remove bad datas
     *
     * @param CMedecinExercicePlace $medecin_exercice_place
     *
     * @return CMedecinExercicePlace
     * @throws Exception
     */
    public function sanitizeMedecinExercicePlaceMSSante(
        CMedecinExercicePlace $medecin_exercice_place
    ): CMedecinExercicePlace {
        $mep_addresses = array_unique(explode("\n", $medecin_exercice_place->mssante_address));

        $this->email = strtolower($this->email);

        if (!in_array($this->email, $mep_addresses)) {
            return $medecin_exercice_place;
        }

        $exercice_place = $medecin_exercice_place->loadRefExercicePlace();

        // On retire les adresses qui ont été mal affectées
        if (
            $this->id_structure !== $exercice_place->finess_juridique
            || ($this->id_structure && !$exercice_place->_id)
        ) {
            CMbArray::removeValue($this->email, $mep_addresses);
        }

        $medecin_exercice_place->mssante_address = implode("\n", $mep_addresses);

        return $medecin_exercice_place;
    }

    /**
     * Check if a mssante email address is already attributed to a medecin exercice place
     *
     * @param CMedecin $medecin
     *
     * @return bool
     */
    public function addressExistsInMedecinExercicePlace(CMedecin $medecin): bool
    {
        $medecin_exercice_places = $medecin->getMedecinExercicePlaces();

        $medecin_exercice_places = array_filter($medecin_exercice_places, function ($_medecin_exercice_place) {
            return $_medecin_exercice_place->exercice_place_id != null;
        });

        $adresses        = CMbArray::pluck($medecin_exercice_places, 'mssante_address');
        $final_addresses = [];
        foreach ($adresses as $_address) {
            $final_addresses[] = explode("\n", $_address);
        }

        return in_array_recursive(strtolower($this->email), $final_addresses);
    }
}
