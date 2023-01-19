<?php

/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Handle\ORU;

use DOMNode;
use DOMNodeList;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\Repository\ConsultationRepository;
use Ox\Interop\Eai\Repository\OperationRepository;
use Ox\Interop\Eai\Repository\SejourRepository;
use Ox\Interop\Eai\Resolver\FileTargetResolver;
use Ox\Interop\Hprimsante\CHPrimSanteRecordORU;
use Ox\Interop\Hprimsante\Exceptions\CHPrimSanteExceptionWarning;

class HandleObservation extends Handle
{
    /** @var string */
    public const KEY_OBSERVATION_RESULT_SET = 'observation_result_set';

    /** @var array */
    protected $OBR;

    /**
     * @throws CHPrimSanteExceptionWarning
     */
    public function handle(array $params): void
    {
        $this->params = $params;

        /** @var DOMNode|null $observation_node */
        if (!$observation_node = CMbArray::get($params, self::KEY_OBR_NODE)) {
            throw $this->makeError('OBR', '18');
        }

        $this->OBR = $this->handleOBR($observation_node);

        /** @var DOMNodeList $OBX_nodes */
        $OBX_nodes = CMbArray::get($params, CHPrimSanteRecordORU::KEY_OBX_LIST);
        foreach ($OBX_nodes as $index => $OBX_node) {
            try {
                $params = array_merge(
                    $params,
                    [
                        self::KEY_OBX_NODE => $OBX_node,
                        self::KEY_OBX_INDEX => $index,
                    ]
                );

                // retrieve or determine target
                $this->message->target = $this->determineTarget($params);

                // complete params
                $params = $this->getParameters($params);

                $this->getObjectResultHandle($OBX_node)->handle($params);
            } catch (CHPrimSanteExceptionWarning $exception) {
                $this->message->addError($exception->getHprimError($this->message->_ref_exchange_hpr));
            }
        }
    }

    /**
     * @param DOMNode $OBR_node
     *
     * @return array
     */
    protected function handleOBR(DOMNode $OBR_node): array
    {
        return [
            self::KEY_OBR_NODE => $OBR_node,
        ];
    }

    /**
     * Get object for handle observation
     *
     * @param DOMNode $OBX_node
     *
     * @return HandleObservationResult
     */
    protected function getObjectResultHandle(DOMNode $OBX_node): HandleObservationResult
    {
        return new HandleObservationResultFiles($this->message);
    }

    /**
     * Allow add parameters for handle results
     *
     * @param array $params
     *
     * @return array
     */
    protected function getParameters(array $params): array
    {
        return $params;
    }

    /**
     * Determine target for file or integration of content
     *
     * @param array $params
     *
     * @return CStoredObject|null
     * @throws CHPrimSanteExceptionWarning
     */
    private function determineTarget(array $params): ?CStoredObject
    {
        static $search = true;

        if ($this->message->target || !$search) {
            return $this->message->target;
        }

        if ($this->isModeSAS()) {
            $search = false;

            return $this->message->target = null;
        }

        $OBR_node = CMbArray::get($params, self::KEY_OBR_NODE);
        $OBX_node = CMbArray::get($params, self::KEY_OBX_NODE);
        $OBR_node = $OBR_node->parentNode;
        $OBX_node = $OBX_node->parentNode;
        $sender   = $this->getSender();
        $patient  = $this->getPatient();
        $target_class = $sender->_configs['object_attach_OBX'];
        $sejour = $this->message->sejour;

        // Retrieve Praticien author
        $praticien_id = $this->message->getObservationAuthor($OBR_node)->_id;

        // Resolve datetime observation
        $observation_dt = $this->message->getOBRObservationDateTime($OBR_node);
        $datetime       = CMbDT::dateTime($observation_dt ?: $this->message->getOBXObservationDateTime($OBX_node));

        // Consultation search
        $consultation_repository = (new ConsultationRepository())
            ->setPatient($patient)
            ->setPraticienId($praticien_id)
            ->setDateConsultation($datetime)
            ->setSejour($sejour);

        // operation search
        $operation_repository = (new OperationRepository())
            ->setPatient($patient)
            ->setSejour($sejour)
            ->setDateOperation($datetime)
            ->setPraticienId($praticien_id);

        // sejour search
        $sejour_repository = (new SejourRepository())
            ->setSejourFound($sejour);

        $object = (new FileTargetResolver())
            ->setPatient($patient)
            ->setModeSas($this->isModeSAS())
            // ->setId400Category($id400_category) // todo où trouver l'identifiant de la categorie dans le message ?
            ->setConsultationRepository($consultation_repository)
            ->setOperationRepository($operation_repository)
            ->setSejourRepository($sejour_repository)
            ->resolve($sender, $target_class ?? '');

        // mode sas = off && no target
        if (!$object) {
            throw $this->makeError('OBR', '03');
        }

        return $object;
    }
}
