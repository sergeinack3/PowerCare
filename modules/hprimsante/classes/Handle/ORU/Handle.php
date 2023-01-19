<?php

/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Handle\ORU;

use Ox\Core\CMbArray;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hprimsante\CHPrimSanteRecordORU;
use Ox\Interop\Hprimsante\Events\CHPrimSanteEvent;
use Ox\Interop\Hprimsante\Exceptions\CHPrimSanteExceptionError;
use Ox\Interop\Hprimsante\Exceptions\CHPrimSanteExceptionWarning;
use Ox\Mediboard\Patients\CPatient;

abstract class Handle
{
    /** @var string */
    public const KEY_OBR_NODE = 'OBR_node';

    /** @var string */
    public const KEY_OBR_INDEX = 'OBR_index';

    /** @var string */
    public const KEY_OBX_NODE = 'OBX_node';

    /** @var string */
    public const KEY_OBX_INDEX = 'OBX_index';

    /** @var CHPrimSanteRecordORU */
    protected $message;

    /** @var array */
    protected $params;

    /** @var string  */
    protected $component_separator = '~';

    /** @var string */
    protected $separator_repetition = '^';

    /**
     * Construct of Handle
     *
     * @param CHPrimSanteRecordORU $message
     */
    public function __construct(CHPrimSanteRecordORU $message)
    {
        $this->message = $message;

        // determine separator component
        $hprim_message = $this->message->_ref_exchange_hpr->_event_message;
        if ($hprim_message instanceof CHPrimSanteEvent) {
            if ($hprim_message->message && ($separator_component = $hprim_message->message->componentSeparator)) {
                $this->component_separator = $separator_component;
            }
            if ($hprim_message->message && ($repetition_separator = $hprim_message->message->repetitionSeparator)) {
                $this->separator_repetition = $repetition_separator;
            }
        }
    }

    /**
     * Handle message information
     *
     * @param array $params
     *
     * @return void
     */
    abstract public function handle(array $params): void;

    /**
     * Return patient from message
     *
     * @return CPatient|null
     */
    public function getPatient(): ?CPatient
    {
        return $this->message->patient;
    }

    /**
     * Return sender interop from message
     *
     * @return CInteropSender
     */
    public function getSender(): CInteropSender
    {
        return $this->message->_ref_sender;
    }

    /**
     * @return CStoredObject|null
     */
    public function getTarget(): ?CStoredObject
    {
        return $this->message->target;
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        $index_obr = CMbArray::get($this->params, self::KEY_OBR_INDEX, 0) + 1;
        $index_obx = CMbArray::get($this->params, self::KEY_OBX_INDEX, 0) + 1;

        return array_key_exists(self::KEY_OBX_INDEX, $this->params) ? "OBR[$index_obr].OBX[$index_obx]" : $index_obr;
    }

    /**
     * Make error which skip current integration of patient data
     *
     * @param string      $segment_error
     * @param string      $code_error
     * @param string|null $field
     * @param string|null $comment
     * @param array       $address
     *
     * @return CHPrimSanteExceptionError
     */
    public function makeImportantError(
        string $segment_error,
        string $code_error,
        ?string $field = null,
        ?string $comment = null,
        array $address = []
    ): CHPrimSanteExceptionError {
        if (!$address) {
            $address = $this->makeAddressError($segment_error);
        }

        return new CHPrimSanteExceptionError('P', $code_error, $address, $field, $comment);
    }

    /**
     * Make error which skip current integration (OBR OR OBX)
     *
     * @param string      $segment_error
     * @param string      $code_error
     * @param string|null $field
     * @param string|null $comment
     * @param array       $address
     *
     * @return CHPrimSanteExceptionWarning
     */
    public function makeError(
        string $segment_error,
        string $code_error,
        ?string $field = null,
        ?string $comment = null,
        array $address = []
    ): CHPrimSanteExceptionWarning {
        if (!$address) {
            $address = $this->makeAddressError($segment_error);
        }

        return new CHPrimSanteExceptionWarning('P', $code_error, $address, $field, $comment);
    }

    private function makeAddressError(string $segment_error): array
    {
        $schema = ['P', 'OBR', 'OBX'];
        $address = [];

        foreach ($schema as $segment) {
            $address[] = [
                $segment,
                $this->getRang($segment),
                $this->getIdentifier($segment)
            ];

            if ($segment === $segment_error) {
                break;
            }
        }

        return $address;
    }

    /**
     * @return bool
     */
    protected function isModeSAS(): bool
    {
        return (bool) $this->getSender()->_configs['mode_sas'];
    }

    /**
     * Get rang of segment
     *
     * @param string $segment
     *
     * @return int
     */
    private function getRang(string $segment): int
    {
        switch ($segment) {
            case 'P':
                return $this->message->loop + 1;
            case 'OBR':
                return ($idx_obr = CMbArray::get($this->params, self::KEY_OBR_INDEX)) !== null ? ($idx_obr + 1) : 0;
            case 'OBX':
                return ($idx_obx = CMbArray::get($this->params, self::KEY_OBX_INDEX)) !== null ? ($idx_obx + 1) : 0;
            default:
                return 0;
        }
    }

    /**
     * @param string $segment
     *
     * @return array
     */
    private function getIdentifier(string $segment): array
    {

        switch ($segment) {
            case 'P':
                if (!$P_node = CMbArray::get($this->params, CHPrimSanteRecordORU::KEY_P_NODE)) {
                    return [];
                }

                $p_2 = $this->message->queryTextNode('P.2', $P_node);
                $p_3 = $this->message->queryTextNode('P.3', $P_node);
                $p_4 = $this->message->queryTextNode('P.4', $P_node);

                return [$p_2 ?: null, $p_3 ?: null,  $p_4 ?: null];
            case 'OBR':
                if (!$OBR_node = CMbArray::get($this->params, self::KEY_OBR_NODE)) {
                    return [];
                }

                $obr_2 = $this->message->queryTextNode('OBR.2', $OBR_node);
                $obr_3 = $this->message->queryTextNode('OBR.3', $OBR_node);

                return [$obr_2 ?: null, $obr_3 ?: null];
            case 'OBX':
                if (!$OBX_node = CMbArray::get($this->params, self::KEY_OBX_NODE)) {
                    return [];
                }

                $obx_3 = $this->message->queryTextNode('OBX.3', $OBX_node);

                return $obx_3 ? [$obx_3] : [];
            default:
                return [];
        }
    }
}
