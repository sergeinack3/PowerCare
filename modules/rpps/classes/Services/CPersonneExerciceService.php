<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Services;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;
use Ox\Core\CPDODataSource;
use Ox\Core\CSQLDataSource;
use Ox\Import\Rpps\Entity\CAbstractExternalRppsObject;
use Ox\Import\Rpps\Entity\CPersonneExercice;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CExercicePlace;

class CPersonneExerciceService implements IShortNameAutoloadable
{
    public const STEP  = 20;
    public const LIMIT = 100;

    /** @var CPDODataSource|CSQLDataSource */
    private $ds;
    /** @var CPersonneExercice */
    private $personne_exercice;

    /** @var array */
    public $where = [];
    /** @var array */
    public $ljoin = [];
    /** @var string */
    private $group;
    /** @var int */
    public $start;

    public function __construct()
    {
        $this->personne_exercice = new CPersonneExercice();
        $this->ds                = $this->personne_exercice->getDS();
    }

    /**
     * Search the list of praticioner (CPersonneExercice) from RPPS module
     */
    public function searchPraticionerFromRPPS(): array
    {
        $praticiens = [];

        $this->where["type_identifiant"] = $this->ds->prepare(
            "= ?",
            CAbstractExternalRppsObject::TYPE_IDENTIFIANT_RPPS
        );

        $order    = "nom, prenom";
        $counter_personne_exercice = $this->personne_exercice->countList(
            $this->where
        ) > self::LIMIT ? self::LIMIT : $this->personne_exercice->countList($this->where);
        $personnes                 = $this->personne_exercice ->loadList($this->where, $order, "{$this->start}, " . self::STEP);

        foreach ($personnes as $_personne_exercice) {
            $exercicePlace                 = new CExercicePlace();
            $exercicePlace->adresse        = $_personne_exercice->buildAdresse();
            $exercicePlace->cp             = $_personne_exercice->cp;
            $exercicePlace->commune        = $_personne_exercice->libelle_commune;
            $exercicePlace->raison_sociale = $_personne_exercice->raison_sociale_site;

            $praticiens[$_personne_exercice->identifiant]['praticien']        = $_personne_exercice;
            $praticiens[$_personne_exercice->identifiant]['exercicePlaces'][] = $exercicePlace;
        }

        return [$counter_personne_exercice, $praticiens];
    }

    /**
     * Compare a praticioner (CPersonneExercice) and mediuser
     */
    public function comparePraticionerMediuser(CMediusers $mediuser, array $practicioner): array
    {
        if (!$mediuser->_id || (count($practicioner) != 1)) {
            return [];
        }

        $praticien = [];

        foreach ($practicioner as $rpps => $_praticioner) {
            $praticien = $_praticioner["praticien"];
        }


        $compare_fields = [
            "_user_last_name"  => "nom",
            "_user_first_name" => "prenom",
            "_user_email"      => "email",
            "_user_phone"      => "tel",
            "rpps"             => "identifiant",
        ];

        $counter_error = 0;

        foreach ($compare_fields as $mediuser_field => $practicioner_field) {
            $_mediuser_field     = CMbString::lower($mediuser->$mediuser_field);
            $_practicioner_field = CMbString::lower($praticien->$practicioner_field);

            if ($_mediuser_field != $_practicioner_field) {
                $counter_error++;
            }

            $practicioner_mediuser["fields"][$practicioner_field] = [$_mediuser_field => $_practicioner_field];
        }

        $practicioner_mediuser['error'] = $counter_error;

        return $practicioner_mediuser;
    }
}
