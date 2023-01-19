<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Convention;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\ApiClients\ConventionClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Mappers\ConventionMapper;
use ZipArchive;

/**
 * Class ConventionService
 *
 * @package Ox\Mediboard\Jfse\Domain\Convention
 */
final class ConventionService extends AbstractService
{
    /** @var ConventionClient */
    protected $client;

    /** @var array */
    public static $convert_map = [];

    /**
     * ConventionService constructor.
     *
     * @param ConventionClient $client
     */
    public function __construct(ConventionClient $client = null)
    {
        $this->client = $client ?? new ConventionClient();
    }

    public function updateConvention(Convention $convention): bool
    {
        $this->client->updateConvention($convention);

        return true;
    }

    public function listConventions(): array
    {
        $conventions = [];
        $response    = $this->client->listConventions();
        $data        = ConventionMapper::getConventionsFromResponse($response);
        foreach ($data as $convention) {
            $conventions[] = Convention::hydrate($convention);
        }

        return $conventions;
    }

    public function listRegroupements(): array
    {
        $groupings = [];
        $response  = $this->client->listRegroupements();
        $data      = ConventionMapper::getGroupingsFromResponse($response);
        foreach ($data as $grouping) {
            $groupings[] = Grouping::hydrate($grouping);
        }

        return $groupings;
    }

    public function listCorrespondences(): array
    {
        $correspondences = [];
        $response        = $this->client->listCorrespondances();
        $data            = ConventionMapper::getCorrespondencesFromResponse($response);
        foreach ($data as $correspondence) {
            $correspondences[] = Correspondence::hydrate($correspondence);
        }

        return $correspondences;
    }

    public function updateRegroupement(Grouping $grouping): bool
    {
        $this->client->updateRegroupement($grouping);

        return true;
    }

    public function updateCorrespondance(Correspondence $correspondence): bool
    {
        $this->client->updateCorrespondance($correspondence);

        return true;
    }

    public function deleteConvention(int $id): bool
    {
        $this->client->deleteConvention($id);

        return true;
    }

    public function deleteRegroupement(int $id): bool
    {
        $this->client->deleteRegroupement($id);

        return true;
    }

    public function deleteCorrespondance(int $id): bool
    {
        $this->client->deleteCorrespondance($id);

        return true;
    }

    public function listTypesConvention(): array
    {
        $types_convention = [];
        $response         = $this->client->listTypesConvention();
        $data             = ConventionMapper::getTypeConventionFromResponse($response);
        foreach ($data as $type_convention) {
            $types_convention[] = ConventionType::hydrate($type_convention);
        }

        return $types_convention;
    }

    public function importConventionsRegroupementsByPS(int $mode, ?int $dest_jfse_id, ?int $dest_group_id): bool
    {
        $this->client->importConventionsRegroupementsByPS($mode, $dest_jfse_id, $dest_group_id);

        return true;
    }

    public function importFichierBin(string $file_binary): bool
    {
        $this->client->importFichierBin($file_binary);

        return true;
    }

    public function importFichiersZip(string $file_name, ?int $jfse_id): bool
    {
        $temp_dir      = "tmp/tmp_zip_convention/";
        $file_binaries = [];

        $zip_file = new ZipArchive();
        $zip_file->open($file_name);
        $zip_file->extractTo($temp_dir);

        $files = scandir($temp_dir);

        foreach ($files as $key => $file) {
            if ($file !== "." && $file !== "..") {
                $file_binaries[] = self::convertFileContentToBinary(
                    file_get_contents($temp_dir . $file)
                );
            }
        }
        $this->client->importFichiersZip($file_binaries, $jfse_id);

        return true;
    }

    public function uploadFichiersCsv(string $file_name, ?int $jfse_id): ?string
    {
        $file_binary = self::convertFileContentToBinary(file_get_contents($file_name));

        $response = $this->client->uploadFichiersCsv($file_binary, $jfse_id);

        return CMbArray::get($response->getContent(), 'nomFichier');
    }

    public function listConventionsToInstall(string $file_name, int $jfse_id): array
    {
        $conventions_to_install = $groupings_to_install = [];

        $response = $this->client->listConventionsToInstall($file_name, $jfse_id);

        $data_convention = ConventionMapper::getConventionsToInstallFromResponse($response);
        foreach ($data_convention as $convention) {
            $conventions_to_install[] = Convention::hydrate($convention);
        }

        $data_grouping = ConventionMapper::getGroupingsToInstallFromResponse($response);
        foreach ($data_grouping as $grouping) {
            $groupings_to_install[] = Grouping::hydrate($grouping);
        }

        return [
            "conventions_to_install" => $conventions_to_install,
            "groupings_to_install"   => $groupings_to_install,
        ];
    }

    public function updateConventionsViaCsv(string $file_name, int $jfse_id): bool
    {
        $this->client->updateConventionsViaCsv($file_name, $jfse_id);

        return true;
    }

    public function deleteFichierConventions(string $file_name, int $jfse_id): bool
    {
        $this->client->deleteFichierConventions($file_name, $jfse_id);

        return true;
    }

    private function convertCharToBinary(string $char): string
    {
        $data = unpack('H*', $char);

        return base_convert($data[1], 16, 2);
    }

    private function convertFileContentToBinary(string $content): string
    {
        $string_to_convert = str_split($content);
        $result            = [];
        foreach ($string_to_convert as $_char) {
            if (!isset(self::$convert_map[$_char])) {
                self::$convert_map[$_char] = self::convertCharToBinary($_char);
            }
            $result[] = self::$convert_map[$_char];
        }

        return implode('', $result);
    }
}
