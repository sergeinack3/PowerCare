<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Updators;

use Exception;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Patients\CIdentityProofType;

class IdentityProofTypeUpdator
{
    /** @var CSetup $setup */
    private $setup;
    /** @var CSQLDataSource */
    private $ds;
    /** @var array */
    private $types_id = [];

    /** @var array */
    public static $types = [
        'PASSEPORT'          => ['label' => 'Passeport', 'id' => 1],
        'ID_CARD'            => ['label' => 'Carte d\'identité', 'id' => 2],
        'BIRTH_ACT'          => ['label' => 'Acte de naissance', 'id' => 3],
        'FAMILY_RECORD_BOOK' => ['label' => 'Livret de famille', 'id' => 4],
        'RESIDENT_PERMIT'    => ['label' => 'Titre / Carte de séjour', 'id' => 5],
    ];

    /** @var array */
    public static $old_types_mapper = [
        'passeport'                   => 'PASSEPORT',
        'carte_identite'              => 'ID_CARD',
        'carte_identite_electronique' => 'ID_CARD',
        'acte_naissance'              => 'BIRTH_ACT',
        'livret_famille'              => 'FAMILY_RECORD_BOOK',
        'carte_sejour'                => 'RESIDENT_PERMIT',
    ];

    public function __construct(CSetup $setup)
    {
        $this->setup = $setup;
        $this->ds    = $setup->ds;
    }

    public function makeUpdate(): void
    {
        $this->insertTypes();
        $this->updateIdentitySources();
        $this->updateStatusPatient();
    }

    private function insertTypes(): void
    {
        $entries = [];
        foreach (self::$types as $code => $data) {
            $entries[] = "({$data['id']}, \"{$data['label']}\", '{$code}', '"
                . CIdentityProofType::TRUST_LEVEL_HIGH . "', '0')";
        }

        $query = "INSERT INTO `identity_proof_types`
            (`identity_proof_type_id`, `label`, `code`, `trust_level`, `editable`) VALUES \n"
            . implode(",\n", $entries) . ';';

        $this->setup->addQuery($query);
    }

    private function updateIdentitySources(): void
    {
        foreach (self::$old_types_mapper as $old_type => $code) {
            if (array_key_exists($code, self::$types)) {
                $query = "UPDATE `source_identite` SET `identity_proof_type_id` = " . self::$types[$code]['id']
                    . " WHERE `type_justificatif` = '{$old_type}';";
                $this->setup->addQuery($query);
            }
        }
    }

    private function updateStatusPatient(): void
    {
        $this->setup->addQuery(
            "UPDATE `patients` LEFT JOIN `source_identite`
                ON `source_identite`.`source_identite_id` = `patients`.`source_identite_id` SET `status` = 'PROV'
                WHERE `source_identite`.`mode_obtention` = 'interop'
                AND `source_identite`.`type_justificatif` IN ('absence_justificatif', 'doc_asile');"
        );
    }
}
