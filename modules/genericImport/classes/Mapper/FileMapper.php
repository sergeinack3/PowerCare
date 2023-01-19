<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\File;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Files\Import\OxPivotFile;

/**
 * Description
 */
class FileMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'     => $this->getValue($row, OxPivotFile::FIELD_ID),
            'file_name'       => $this->getValue($row, OxPivotFile::FIELD_NOM),
            'file_date'       => $this->getValue($row, OxPivotFile::FIELD_DATE)
                ? $this->convertToDateTime($row[OxPivotFile::FIELD_DATE]) : null,
            'file_type'       => $this->getValue($row, OxPivotFile::FIELD_TYPE),
            'file_content'    => $this->getValue($row, OxPivotFile::FIELD_CONTENU),
            'file_path'       => ($path = $this->getValue($row, OxPivotFile::FIELD_CHEMIN))
                ? $this->getFilepath($path) : null,
            'author_id'       => $this->getValue($row, OxPivotFile::FIELD_AUTEUR),
            'consultation_id' => $this->getValue($row, OxPivotFile::FIELD_CONSULTATION),
            'sejour_id'       => $this->getValue($row, OxPivotFile::FIELD_SEJOUR),
            'patient_id'      => $this->getValue($row, OxPivotFile::FIELD_PATIENT),
            'evenement_id'    => $this->getValue($row, OxPivotFile::FIELD_EVENEMENT),
            'file_cat_name'   => $this->getValue($row, OxPivotFile::FIELD_CATEGORIE),
        ];

        return File::fromState($map);
    }

    private function getFilePath(string $file_path): ?string
    {
        $base_path = $this->configuration['external_files_path'];
        [$search, $replace] = $this->getReplacementsPaths($this->configuration['external_files_replacement_path']);

        return $base_path . DIRECTORY_SEPARATOR . str_replace($search, $replace, $file_path);
    }

    private function getReplacementsPaths(string $replacements_config): array
    {
        $search = $replace = [];

        foreach (explode(',', $replacements_config) as $replacement) {
            $replacement_parts = explode('|', $replacement);
            $search[]          = $replacement_parts[0];
            $replace[]         = $replacement_parts[1];
        }

        return [$search, $replace];
    }
}
