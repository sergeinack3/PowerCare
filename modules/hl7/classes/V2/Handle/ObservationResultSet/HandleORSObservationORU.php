<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle\ObservationResultSet;

use DOMNode;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\Exceptions\V2\CHL7v2ExceptionWarning;
use Ox\Mediboard\Sante400\CIdSante400;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class HandleORSObservation
 *
 * @package Ox\Interop\Hl7\V2\Handle\ObservationResultSet
 */
class HandleORSObservationORU extends HandleORSObservation
{
    public function handle(ParameterBag $bag): void
    {
        parent::handle($bag);

        $this->observation = $observation = $bag;

        // ORC
        $this->ORC = $this->handleORC($observation->get('ORC'));

        // OBR
        $this->OBR = $this->handleOBR($observation->get("OBR"));

        if (!$observation->has('OBX')) {
            return;
        }

        // OBX.*
        foreach ($observation->get('OBX') as $key => $OBX) {
            try {
                // handle segment OBX
                if ($this->handleOBX($OBX, $key)) {
                    // Notify parent that we integrated an element
                    $this->message->addElementTreated();
                }
            } catch (CHL7v2ExceptionWarning $warning) {
                $this->addCode($warning->getWarning());
            }
        }
    }

    /**
     * @param DOMNode|null $ORC
     *
     * @return ParameterBag
     */
    protected function handleORC(?DOMNode $ORC): ParameterBag
    {
        // not used in Perop
        return new ParameterBag();
    }

    /**
     * @param DOMNode $OBR
     *
     * @return ParameterBag
     * @throws Exception
     */
    protected function handleOBR(DOMNode $node_OBR): ParameterBag
    {
        $OBR         = new ParameterBag();
        $observation = $this->observation;

        // OBR.4 : Code de l'examen demandé
        $OBR->set(self::OBR_UNIVERSAL_SERVICE_ID, $this->message->queryNode('OBR.4', $node_OBR));

        // OBR.7 : Récupération de la date du relevé
        $OBR->set(self::OBR_DATETIME, $this->getOBRObservationDateTime($node_OBR));

        // OBR.18 :
        // Ajout d'une config pour savoir si on doit contrôler OBR.18 à cause de certains labos
        // qui nous envoient toujours la même valeur dans OBR.18
        $id_partner = CAppUI::gconf("hl7 ORU verify_OBR_18")
            ? $this->getIdDocumentPartner($observation->get("OBR"))
            : null;
        $OBR->set(self::OBR_ID_PARTNER, $id_partner);

        // OBR identity identifier
        $OBR_identity_identifier = null;
        if ($handle_OBR_identity_identifier = $this->sender->_configs["handle_OBR_identity_identifier"]) {
            $OBR_identity_identifier = $this->message->queryTextNode(
                $handle_OBR_identity_identifier,
                $observation->get("OBR")
            );
        }
        $OBR->set(self::OBR_IDENTITY_ID, $OBR_identity_identifier);

        return $OBR;
    }

    /**
     * @param DOMNode $OBX
     * @param string  $key
     *
     * @return bool
     * @throws CHL7v2Exception
     * @throws CHL7v2ExceptionWarning
     */
    protected function handleOBX(DOMNode $OBX, string $key): bool
    {
        // Determine target
        $this->target_object = $target_object = $this->determineTarget($OBX);

        // On n'a pas retrouvé la cible, et je ne suis pas en mode SAS
        if (!$target_object || !$target_object->_id) {
            if (!$this->isModeSAS()) {
                throw new CHL7v2ExceptionWarning("E301");
            }
        }

        // OBX.14 : Date de l'observation
        $OBX_dateTime = $this->getOBXObservationDateTime($OBX);

        // Determine date
        if (CMbArray::get($this->sender->_configs, 'creation_date_file_like_treatment')) {
            $date_result = CMbDT::dateTime();
        } else {
            $date_result = $OBX_dateTime ?: $this->OBR->get(self::OBR_DATETIME);
            $date_result = CMbDT::dateTime($date_result);
        }

        $bag = $this->getParameters(
            new ParameterBag(
                [
                    'OBR'               => $this->OBR,
                    'ORC'               => $this->ORC,
                    'OBSERVATION.index' => $this->observation_index,
                    'OBX.index'         => (int)$key,
                    'target_object'     => $target_object,
                    'result_date'       => $date_result,
                ]
            )
        );

        // handle OBX
        $this->getObjectOBXHandle($OBX, $key)->handle($bag);

        // On store l'idex de l'identifiant du système tiers
        if ($OBR_identity_identifier = $this->OBR->get(self::OBR_IDENTITY_ID)) {
            $idex = CIdSante400::getMatch(
                $this->target_object->_class,
                "OBR_" . $this->sender->_tag_hl7,
                $OBR_identity_identifier,
                $this->target_object->_id
            );
            if (!$idex->_id) {
                $idex->store();
            }
        }

        return true;
    }

    /**
     * @param DOMNode $OBX
     * @param int     $OBX_index
     *
     * @return HandleORSOBX
     * @throws CHL7v2ExceptionWarning
     */
    protected function getObjectOBXHandle(DOMNode $OBX, int $OBX_index): HandleORSOBX
    {
        // OBX.2 : Type de l'OBX
        $value_type = $this->getOBXValueType($OBX);

        // Treatment OBX
        switch ($value_type) {
            // Reference Pointer
            case "RP":
                return new HandleORSFilesRP($this->message, $this->observation, $OBX);
            // Encapsulated Data
            case "ED":
                return new HandleORSFilesED($this->message, $this->observation, $OBX);

            default:
                throw (new CHL7v2ExceptionWarning('E309'))
                    ->setPosition("OBSERVATION[$this->observation_index]/OBX[$OBX_index]");
        }
    }
}
