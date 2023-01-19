<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\CMbDT;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CSourceIdentite;

/**
 * Class CHL7v2SegmentZFD
 * ZFD - Represents an HL7 ZFD message segment (Complément démographique)
 */
class CHL7v2SegmentZFD extends CHL7v2Segment
{

    /** @var string */
    public $name = "ZFD";


    /** @var CPatient */
    public $patient;

    /**
     * Build ZFD segement
     *
     * @param CHEvent $event Event
     * @param string  $name  Segment name
     *
     * @return null
     */
    function build(CHEvent $event, $name = null)
    {
        parent::build($event);

        $patient = $this->patient;

        // ZFD-1: Date lunaire
        if (CMbDT::isLunarDate($patient->naissance)) {
            $date   = explode("-", $patient->naissance);
            $data[] = [
                [
                    // ZFD-1.1 : Jour
                    $date[2],
                    // ZFD-1.2 : Mois
                    $date[1],
                    // ZFD-1.1 : Année
                    $date[0],
                ],
            ];
        } else {
            $data[] = null;
        }

        // ZFD-2: Nombre de semaines de gestation
        $data[] = null;

        // ZFD-3 : Consentement SMS
        $data[] = $patient->allow_sms_notification == 1 ? 'Y' : ($patient->allow_sms_notification == 0 ? 'N' : null);

        /** @var CSourceIdentite $source_identite_active */
        $source_identite_active = $patient->loadRefSourceIdentite();

        // ZFD-4 : Indicateur de date de naissance corrigée
        $data[] = $source_identite_active->date_naissance_corrigee == 1 ? 'Y' : ($source_identite_active->date_naissance_corrigee == 0 ? 'N' : null);

        // ZFD-5 : Mode d'obtention de l'identité
        // Table : IHE-ZFD-5 - 9003
        // SM - Saisie manuelle
        // CV - Carte vitale
        // INSI - Téléservice INSi
        // CB - Code à barre
        // RFID - Puce RFID
        $data[] = $source_identite_active->getModeObtention() ? CHL7v2TableEntry::mapTo(
            "9003",
            $source_identite_active->getModeObtention()
        ) : null;

        // ZFD-6 : Date d'interrogation du téléservice INSi
        $data[] = $source_identite_active->getModeObtention() === CSourceIdentite::MODE_OBTENTION_INSI ? $source_identite_active->debut : null;

        // ZFD-7 : Type de justificatif d'identité
        // Table : IHE-ZFD-7 - 9004
        // AN - Extrait d'acte de naissance
        // CC - Carnet de circulation
        // CE - Carte européenne
        // CM - Carte militaire
        // CN - Carte nationale d'identité
        // CS - Carte de séjour
        // LE - Livret de famille
        // PA - Passeport
        // PC - Permis de conduire
        $identityProofType = $source_identite_active->loadRefIdentityProofType();
        $data[]            = $identityProofType->_id ? CHL7v2TableEntry::mapTo(
            "9004",
            $identityProofType->code,
            $identityProofType->code
        ) : null;

        // ZFD-8 : Date de fin de validité du document
        $data[] = $source_identite_active->date_fin_validite;

        $this->fill($data);
    }
}
