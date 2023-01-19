<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbString;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class CFilesExplorer
{
    private const EXPORT_RESOURCES_LIMIT = 0.75;

    private const EXPORT_STEP = 500;

    private const ORDER_WAYS = [
        'ASC',
        'DESC',
    ];

    private const MODE_DEFAULT = 'default';
    private const MODE_FAST    = 'fast';
    private const MODE_MIN     = 'min';

    private const COLUMNS_DEFAULT_MODE = [
        "ID DB",
        "Taille DB (kB)",
        "Taille FS (kB)",
        "Présent FS",
        "Diff taille SQL/FS",
        "Hash",
        "Nom",
        "Mimetype",
        "Type d'objet",
        "Catégorie",
        "Contexte",
        "Patient",
        "Auteur",
        "Fonction",
        "Date/Heure",
        "Annulé",
        "Chemin Complet",
    ];

    private const COLUMNS_MINIMAL_MODE = [
        "Présent FS",
        "Hash",
        "Chemin Complet",
    ];

    private const COLUMNS_FAST_MODE = [
        "ID DB",
        "Taille DB (kB)",
        "Taille FS (kB)",
        "Présent FS",
        "Diff taille SQL/FS",
        "Hash",
        "Nom",
        "Mimetype",
        "Type d'objet",
        "Date/Heure",
        "Annulé",
        "Chemin Complet",
    ];

    /** @var CSQLDataSource */
    private $ds;

    /** @var array */
    private $where = [];

    /** @var string */
    private $order;

    /** @var array */
    private $group_by = [];

    /** @var array */
    private $ljoin = [];

    /** @var array */
    private $file_statuses = [];

    /** @var resource */
    private $fp;

    /** @var CCSVFile */
    private $csv_writer;

    /** @var int */
    private $count;

    /** @var Chronometer */
    private $chrono;

    /** @var array */
    private $files_stats = [];

    private $count_files = 0;

    public function getFileList(
        ?string $from_date,
        ?string $to_date,
        ?string $_order,
        ?string $_way,
        ?int $min_size,
        ?int $max_size,
        ?string $mimetype,
        ?string $object_class,
        ?string $file_hash,
        ?string $file_name,
        ?bool $annule,
        ?int $user_id,
        ?int $category_id,
        ?int $function_id,
        ?string $limit
    ): array {
        $file     = new CFile();
        $this->ds = $file->getDS();

        $this->order    = $this->buildOrder($_order, $_way);
        $this->group_by = ['file_id'];

        // Handle Date
        if ($from_date && $to_date) {
            $this->where['file_date'] = $this->ds->prepare('BETWEEN ?1 AND ?2', $from_date, $to_date);
        } elseif ($from_date) {
            $this->where['file_date'] = $this->ds->prepare('> ?', $from_date);
        } elseif ($to_date) {
            $this->where['file_date'] = $this->ds->prepare('< ?', $to_date);
        }

        /* Warning: file_size range is converted to kB */
        if ($min_size && $max_size) {
            $this->where['doc_size'] = $this->ds->prepare('BETWEEN ?1 AND ?2', $min_size * 1024, $max_size * 1024);
        } elseif ($min_size) {
            $this->where['doc_size'] = $this->ds->prepare('> ?', $min_size * 1024);
        } elseif ($max_size) {
            $this->where['doc_size'] = $this->ds->prepare('< ?', $max_size * 1024);
        }

        if ($mimetype) {
            $this->where['file_type'] = $this->ds->prepareLike("%$mimetype%");
        }

        if ($object_class) {
            $this->where['object_class'] = $this->ds->prepareLike("%$object_class%");
        }

        if ($file_hash) {
            $this->where['file_real_filename'] = $this->ds->prepareLike("%$file_hash%");
        }

        if ($file_name) {
            $this->where['file_name'] = $this->ds->prepareLike("%$file_name%");
        }

        if ($annule !== null) {
            $this->where['annule'] = $this->ds->prepareLike("$annule");
        }

        if ($user_id) {
            $this->where['author_id'] = $this->ds->prepareLike("$user_id");
        }

        if ($category_id) {
            $this->where['file_category_id'] = $this->ds->prepareLike("$category_id");
        }

        if ($function_id) {
            $this->ljoin["users_mediboard"] = "users_mediboard.user_id = author_id";
            $this->where["function_id"]     = $this->ds->prepareLike("$function_id");
        }


        if ($this->count === null) {
            $this->count = $file->countList($this->where, null, $this->ljoin);
        }


        $files = $file->loadList($this->where, $this->order, $limit, $this->group_by, $this->ljoin);

        return [$files, $this->count];
    }

    private function buildOrder(?string $field, ?string $way): ?string
    {
        if (!property_exists(CFile::class, $field) || !in_array($way, self::ORDER_WAYS)) {
            throw new CMbException('CFileExplorer-Error-Order is invalid');
        }

        if ($field && $way) {
            return "{$field} {$way}";
        }

        if ($field) {
            return $field;
        }

        return null;
    }

    public function buildFileInfos(
        array $files,
        bool $with_refs = true,
        bool $reset = false,
        bool $only_missing = false
    ): array {
        if ($reset) {
            $this->file_statuses = [];
        }

        if ($with_refs) {
            CStoredObject::massLoadFwdRef($files, 'author_id');
            CStoredObject::massLoadFwdRef($files, 'file_category_id');
        }

        /** @var CFile $_file */
        foreach ($files as $_file) {
            if ($with_refs) {
                $_file->loadRefAuthor();
            }

            $file_exists        = false;
            $fs_file_size       = 0;
            $file_size_mismatch = true;
            $_file->completeFilePath();
            $file_path = $_file->_file_path;

            $start_time                     = microtime(true);
            $exists                         = file_exists($file_path);
            $this->files_stats[$_file->_id] = (microtime(true) - $start_time);
            $this->count_files++;

            if ($exists) {
                if ($only_missing) {
                    continue;
                }

                $file_exists  = true;
                $fs_file_size = filesize($file_path);
                if ((int)$_file->doc_size === $fs_file_size) {
                    $file_size_mismatch = false;
                }
            }

            $patient = null;
            if ($with_refs) {
                $patient = $_file->getIndexablePatient();
            }

            $this->file_statuses[$_file->_id] = [
                'file'               => $_file,
                'file_path'          => $file_path,
                'file_exists'        => $file_exists,
                'fs_file_size'       => $fs_file_size,
                'file_size_mismatch' => $file_size_mismatch,
                'patient'            => ($patient instanceof CPatient) ? $patient : null,
            ];
        }

        return $this->file_statuses;
    }

    public function exportCsvWithTimer(array $filter): bool
    {
        // Init buffer
        $this->initOutput();

        $mode         = $filter['mode'];
        $only_missing = $filter['only_missing'];

        unset($filter['mode']);
        unset($filter['only_missing']);

        // Init CSV writer
        $this->initWriter($mode);

        $this->chrono = new Chronometer();
        $this->chrono->start();

        // Avoid using the whole memory
        $max_memory = (CMbString::fromDecaBinary(ini_get('memory_limit'))) * self::EXPORT_RESOURCES_LIMIT;
        $max_time   = ini_get('max_execution_time') * self::EXPORT_RESOURCES_LIMIT;

        $end_ok = true;

        do {
            $start           = (isset($start)) ? ($start + self::EXPORT_STEP) : 0;
            $filter['limit'] = "{$start}, " . self::EXPORT_STEP;

            // Chargement de toutes les infos et checks nécessaires pour les fichiers
            [$files,] = $this->getFileList(...array_values($filter));

            if (!$files) {
                break;
            }

            $files_infos = $this->buildFileInfos($files, (bool)($mode === self::MODE_DEFAULT), true, $only_missing);

            foreach ($files_infos as $_infos) {
                switch ($mode) {
                    case self::MODE_MIN:
                        $line = $this->getMinLine($_infos);
                        break;
                    case self::MODE_FAST:
                        $line = $this->getFastLine($_infos);
                        break;
                    case self::MODE_DEFAULT:
                    default:
                        $line = $this->getLine($_infos);
                }
                // Write line
                $this->csv_writer->writeLine($line);
            }

            // écriture des fichiers dans le tampon phpoutput
            $this->sendOutput();

            $this->chrono->step('');

            if (intval($this->chrono->total) > $max_time || memory_get_usage() > $max_memory) {
                $end_ok = false;
                break;
            }
        } while (true);

        $this->chrono->stop();

        $this->endWriter();

        return $end_ok;
    }

    private function initOutput(): void
    {
        $date = CMbDT::dateTime();

        // Vide et désactive le tampon de sortie actuel (en-têtes MB, page liste modules, etc.)
        ob_end_clean();

        // Génération des en-têtes CSV
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-Type: text/csv;charset=ISO-8859-1');
        header('Content-Disposition: attachment; filename="file_explorer_stats_' . $date . '.csv"');

        // Envoi des en-têtes
        ob_flush();
        flush();
    }

    private function sendOutput(): void
    {
        // Vidage manuel du cache statique (car copie par valeur)
        Cache::flushInner();

        // Envoi du tampon de sortie
        ob_flush();
        flush();

        // Forçage du GC pour libération mémoire
        gc_collect_cycles();
    }

    private function initWriter(string $mode): void
    {
        // Ouverture d'un fichier CSV sur le wrapper du tampon de sortie
        $this->fp         = fopen('php://output', 'w');
        $this->csv_writer = new CCSVFile($this->fp); // Use PROFILE_EXCEL as default

        switch ($mode) {
            case self::MODE_FAST:
                $column_names = self::COLUMNS_FAST_MODE;
                break;
            case self::MODE_MIN:
                $column_names = self::COLUMNS_MINIMAL_MODE;
                break;
            case self::MODE_DEFAULT:
            default:
                $column_names = self::COLUMNS_DEFAULT_MODE;
        }

        $this->csv_writer->writeLine($column_names);
    }

    private function endWriter(): void
    {
        $this->csv_writer->close();
    }

    private function getMinLine(array $infos): array
    {
        return [
            'Présent FS'     => $this->getPresentFs($infos),
            'Hash'           => ($infos['file'])->file_real_filename,
            'Chemin Complet' => $infos['file_path'],
        ];
    }

    private function getFastLine(array $infos): array
    {
        /** @var CFile $file */
        $file = $infos['file'];

        return [
            'ID DB'              => $file->_id,
            'Taille DB (kB)'     => $this->getDbSize($file),
            'Taille FS (kB)'     => $this->getFsSize(($infos['fs_file_size']) ?: 0),
            'Présent FS'         => $this->getPresentFs($infos),
            'Diff taille SQL/FS' => $this->getDiffDbFs((bool)$infos['file_size_mismatch']),
            'Hash'               => $file->file_real_filename,
            'Nom'                => $file->file_name,
            'Mimetype'           => $file->file_type,
            'Type d\'objet'      => CAppUI::tr($file->object_class),
            'Date/Heure'         => $this->getDateFormat($file->file_date),
            'Annulé'             => $file->annule,
            'Chemin Complet'     => $infos['file_path'],
        ];
    }

    private function getLine(array $infos): array
    {
        /** @var CFile $file */
        $file = $infos['file'];

        return [
            'ID DB'              => $file->_id,
            'Taille DB (kB)'     => $this->getDbSize($file),
            'Taille FS (kB)'     => $this->getFsSize(($infos['fs_file_size']) ?: 0),
            'Présent FS'         => $this->getPresentFs($infos),
            'Diff taille SQL/FS' => $this->getDiffDbFs((bool)$infos['file_size_mismatch']),
            'Hash'               => $file->file_real_filename,
            'Nom'                => $file->file_name,
            'Mimetype'           => $file->file_type,
            'Type d\'objet'      => CAppUI::tr($file->object_class),
            'Catégorie'          => $this->getCategoryName($file),
            'Contexte'           => $this->getContext($file),
            'Patient'            => $this->getPatient($file),
            'Auteur'             => $this->getAuthor($file),
            'Fonction'           => $this->getFunction($file),
            'Date/Heure'         => $this->getDateFormat($file->file_date),
            'Annulé'             => $file->annule,
            'Chemin Complet'     => $infos['file_path'],
        ];
    }

    private function getPresentFs(array $infos): string
    {
        return (isset($infos['file_exists']) && $infos['file_exists']) ? 'Oui' : 'Non';
    }

    private function getDbSize(CFile $file): string
    {
        return number_format($file->doc_size / 1024, 2);
    }

    private function getFsSize(int $size): string
    {
        return number_format((int)$size / 1024, 2);
    }

    private function getDiffDbFs(bool $mismatch): string
    {
        return ($mismatch) ? 'Oui' : 'Non';
    }

    private function getDateFormat(string $date): string
    {
        return CMbDT::format($date, CAppUI::conf('datetime'));
    }

    private function getCategoryName(CFile $file): ?string
    {
        if ($cat = $file->loadRefCategory()) {
            return $cat->nom;
        }

        return null;
    }

    private function getContext(CFile $file): ?string
    {
        if ($target = $file->loadTargetObject()) {
            return $target->_view;
        }

        return null;
    }

    private function getAuthor(CFile $file): ?string
    {
        if ($author = $file->loadRefAuthor()) {
            return $author->_view;
        }

        return null;
    }

    private function getFunction(CFile $file): ?string
    {
        if ($author = $file->loadRefAuthor()) {
            if ($function = $author->loadRefFunction()) {
                return $function->_view;
            }
        }

        return null;
    }

    private function getPatient(CFile $file): ?string
    {
        $patient_object = $file->getIndexablePatient();
        if ($patient_object instanceof CPatient) {
            return ucfirst($patient_object->civilite)
                . ' ' . $patient_object->nom . ' '
                . $patient_object->prenom
                . ' (' . $patient_object->naissance . ')';
        }

        return null;
    }

    public function getStats(): array
    {
        return [
            'file_count'                => $this->count_files,
            'min_access_time'           => ($this->files_stats) ? round(min($this->files_stats) * 1000, 5) : 0,
            'max_access_time'           => ($this->files_stats) ? round(max($this->files_stats) * 1000, 5) : 0,
            'mean_access_time'          => ($this->files_stats)
                ? round(CMbArray::average($this->files_stats) * 1000, 5)
                : 0,
            'std_deviation_access_time' => ($this->files_stats) ? round(CMbArray::variance($this->files_stats), 5) : 0,
        ];
    }
}
