<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTimeImmutable;
use Exception;
use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Domain\Version\ApiVersion;
use Ox\Mediboard\Jfse\Domain\Version\CardReaderConfig;
use Ox\Mediboard\Jfse\Domain\Version\Computer;
use Ox\Mediboard\Jfse\Domain\Version\Detail;
use Ox\Mediboard\Jfse\Domain\Version\PCSCReader;
use Ox\Mediboard\Jfse\Domain\Version\SesamVitaleComponent;
use Ox\Mediboard\Jfse\Domain\Version\Software;
use Ox\Mediboard\Jfse\Domain\Version\SRTVersion;
use Ox\Mediboard\Jfse\Domain\Version\SSVVersion;
use Ox\Mediboard\Jfse\Domain\Version\STSVersion;
use Ox\Mediboard\Jfse\Domain\Version\Version;

class VersionMapper extends AbstractMapper
{
    public function arrayToApiVersion(array $data): ApiVersion
    {
        return ApiVersion::hydrate(
            [
                "ssv" => $this->arrayToSSV($data["ssv"]),
                "srt" => $this->arrayToSRT($data["srt"]),
                "sts" => $this->arrayToSTS($data["sts"]),
            ]
        );
    }

    private function arrayToSSV(array $data): SSVVersion
    {
        return SSVVersion::hydrate(
            [
                "debug"                   => CMbArray::get($data, "debug", null),
                "computer"                => $this->arrayToComputer($data["posteTravail"]),
                "card_reader_configs"     => $this->arrayToCardReaderConfigs($data["lstConfigurationLecteurCartes"]),
                "pcsc_readers"            => $this->arrayToPCSCReaders($data["lstLecteurPCSC"]),
                "sesam_vitale_components" => $this->arrayToSVComponents($data["lstComposantSesamVitale"]),
            ]
        );
    }

    private function arrayToComputer(array $row): Computer
    {
        return Computer::hydrate(
            [
                "group"         => $row["groupe"],
                "ssv_version"   => $row["noVersionSSV"],
                "galss_version" => $row["noVersionGALSS"],
                "pss_version"   => $row["noVersionPSS"],
            ]
        );
    }

    private function arrayToCardReaderConfigs(array $data): array
    {
        return array_map(
            function (array $row): CardReaderConfig {
                return $this->arrayToCardReaderConfig($row);
            },
            $data
        );
    }

    private function arrayToCardReaderConfig(array $row): CardReaderConfig
    {
        return CardReaderConfig::hydrate(
            [
                "group"                   => $row["groupe"],
                "reader_constructor_name" => $row["nomConstructeurLecteur"],
                "reader_type"             => $row["typeLecteur"],
                "serial_number"           => $row["noSerieLecteur"],
                "os_reader"               => $row["oSLecteur"],
                "reader_amount_softwares" => (int)$row["nombreLogicielsLecteur"],
                "softwares"               => $this->arrayToSoftwares($row["lstLogicielLecteur"]),
            ]
        );
    }

    private function arrayToSoftwares(array $data): array
    {
        return array_map(
            function (array $row): Software {
                return $this->arrayToSoftware($row);
            },
            $data
        );
    }

    private function arrayToSoftware(array $row): Software
    {
        return Software::hydrate(
            [
                "name"           => $row["nom"],
                "version_number" => $row["noVersion"],
                "date_time"      => new DateTimeImmutable($row["dateHeure"]),
                "checksum"       => $row["checksum"],
            ]
        );
    }

    private function arrayToPCSCReaders(array $data): array
    {
        return array_map(
            function (array $row): PCSCReader {
                return $this->arrayToPCSCReader($row);
            },
            $data
        );
    }

    private function arrayToPCSCReader(array $row): PCSCReader
    {
        return PCSCReader::hydrate(
            [
                "group"     => $row["groupe"],
                "name"      => $row["nom"],
                "card_type" => $row["typeCarte"],
            ]
        );
    }

    private function arrayToSVComponents(array $data): array
    {
        return array_map(
            function (array $row): SesamVitaleComponent {
                return $this->arrayToSVComponent($row);
            },
            $data
        );
    }

    private function arrayToSVComponent(array $row): SesamVitaleComponent
    {
        return SesamVitaleComponent::hydrate(
            [
                "group"          => $row["groupe"],
                "id"             => $row["identifiant"],
                "label"          => $row["libelle"],
                "version_number" => $row["noVersion"],
            ]
        );
    }

    /**
     * @throws Exception
     */
    private function arrayToSRT(array $data): SRTVersion
    {
        $modification_date = null;
        if (isset($data["dateModification"]) && $data["dateModification"] !== "") {
            $modification_date = new DateTimeImmutable($data["dateModification"]);
        }

        return SRTVersion::hydrate(
            [
                "debug"                => CMbArray::get($data, "debug", null),
                "group"                => $data["groupe"],
                "referential"          => $data["versionReferentiel"],
                "referential_server"   => $data["versionReferentielServeur"],
                "ccam_db"              => $data["versionBaseCCAM"],
                "ccm_db_server"        => $data["versionBaseCCAMServeur"],
                "modification_date"    => $modification_date,
                "referential_variant"  => $data["varianteReferentiel"],
                "comment"              => $data["commentaire"],
                "referential_revision" => $data["revisionReferentiel"],
                "software_version"     => $data["versionPartieLogicielle"],
            ]
        );
    }

    private function arrayToSTS(array $data): STSVersion
    {
        return STSVersion::hydrate(
            [
                "debug"   => CMbArray::get($data, "debug", null),
                "details" => $this->arrayToDetails($data["lstDetails"]),
            ]
        );
    }

    private function arrayToDetails(array $data): array
    {
        return array_map(
            function (array $row): Detail {
                return $this->arrayToDetail($row);
            },
            $data
        );
    }

    private function arrayToDetail(array $row): Detail
    {
        return Detail::hydrate(
            [
                "group"                       => $row["groupe"],
                "module_identification"       => $row["identificationModule"],
                "module_identification_label" => $row["identificationModuleLibelle"],
                "module_version"              => $row["versionModule"],
                "external_tables_version"     => $row["versionTablesExternes"],
                "variant"                     => $row["variante"],
                "comment"                     => $row["commentaire"],
            ]
        );
    }

    public function arrayToVersion(array $data): Version
    {
        $data = $data["version"];

        return Version::hydrate(
            [
                "organisations"    => $data["organismes"],
                "cdc"              => $data["cdc"],
                "cdc_date"         => new DateTimeImmutable($data["dateCdc"]),
                "prices_date"      => new DateTimeImmutable($data["dateTarifs"]),
                "mail"             => ($data["mail"]) ?: null,
                "server_version"   => $data["serveurVersion"],
                "daemon_version"   => $data["daemonVersion"],
                "ccam_version"     => $data["ccamVersion"],
                "base_api_version" => $data["socleApiVersion"],
            ]
        );
    }
}
