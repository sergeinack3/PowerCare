<?php

/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbString;
use Ox\Core\CModelObject;
use Ox\Core\CStoredObject;

/**
 * Classe permettant la manipualtion de la table buffer
 */
class CSearchIndexing extends CStoredObject
{
    public static $current_ids           = [];
    public static $current_ids_to_delete = [];
    public static $current_ids_to_update = [];
    /**
     * @var integer Primary key
     */
    public $search_indexing_id;

    // DB Fields
    public $type; //[create|update|delete]
    public $object_class; //[CmbObject class]
    public $object_id; //[CmbObject id]
    public $date; //[date de l'insertion dans la table buffer]

    /** @var boolean Has the item been indexed? */
    public $processed;

    public function getSpec(): CMbObjectSpec
    {
        $spec                 = parent::getSpec();
        $spec->table          = "search_indexing";
        $spec->key            = "search_indexing_id";
        $spec->loggable       = false;
        $spec->insert_delayed = true;

        return $spec;
    }

    public function getProps(): array
    {
        $props                 = parent::getProps();
        $props["object_class"] = "str notNull maxLength|50";
        $props["object_id"]    = "ref class|CMbObject meta|object_class notNull unlink back|search_indexing";
        $props["type"]         = "enum list|create|store|delete|merge default|create";
        $props["date"]         = "dateTime notNull";
        $props['processed']    = 'bool notNull default|0';

        return $props;
    }

    /**
     * @param int    $limit Number to objects to index
     * @param string $class Class of objects to index
     *
     * @throws Exception
     */
    public static function getDataToIndex(int $limit, string $class = null): array
    {
        $search_indexing            = new self();
        $search_indexing->processed = 0;
        if ($class) {
            $search_indexing->object_class = $class;
        }

        $search_indexings = $search_indexing->loadMatchingList("search_indexing_id", $limit);

        $retour = [];

        /** @var CSearchIndexing $search_indexing */
        foreach ($search_indexings as $key => $search_indexing) {
            if ($data = $search_indexing->formatToElastic()) {
                switch ($search_indexing->type) {
                    case 'create':
                    case 'store':
                    case 'merge':
                        $retour['index'][] = $data;
                        break;
                    case 'delete':
                        $retour['delete'][] = $data;
                        break;
                    default:
                        // Do nothing
                }
            } else {
                // On le supprime de la table tampon
                $search_indexing->delete();
            }
        }


        return $retour;
    }

    /**
     * @return array|bool
     * @throws Exception
     */
    public function formatToElastic()
    {
        $guid = $this->object_class . '-' . $this->object_id;

        // Nécessaire pour le nettoyage de la table tampon
        static::$current_ids[$guid] = $this->_id;

        $retour = [
            "guid" => $guid,
            "id"   => $this->object_id,
            "type" => $this->object_class,
        ];

        if ($this->type !== 'delete') {
            /** @var IIndexableObject|CStoredObject $object */
            $object = CModelObject::getInstance($this->object_class);

            // objet supprimé avant son indexation
            if (!$object->load($this->object_id)) {
                return false;
            }

            // On récupère les champs à indexer
            $retour = array_merge($retour, $object->getIndexableData());

            if (!$retour["date"]) {
                $retour["date"] = CMbDT::format(CMbDT::dateTime(), "%Y/%m/%d");
            }

            /* @toto cast & encodage */
            $retour['body']          = $this->object_class !== 'CFile' ? $this->normalizeEncoding(
                $retour['body']
            ) : $retour['body'];
            $retour['title']         = $this->object_class !== 'CFile' ? $this->normalizeEncoding(
                $retour['title']
            ) : $retour['title'];
            $retour['patient_id']    = (int)$retour['patient_id'];
            $retour['prat_id']       = (int)$retour['prat_id'];
            $retour['author_id']     = (int)$retour['author_id'];
            $retour['function_id']   = (int)$retour['function_id'];
            $retour['group_id']      = (int)$retour['group_id'];
            $retour['object_ref_id'] = (int)$retour['object_ref_id'];
        } else {
            $retour["date"] = CMbDT::format(CMbDT::dateTime(), "%Y/%m/%d");
        }

        return $retour;
    }

    /**
     * Method to normalize text
     *
     * @param String $text The text to normalize
     *
     * @return String
     */
    public function normalizeEncoding(string $text): string
    {
        $text = mb_convert_encoding($text, "UTF-8", "Windows-1252");
        CMbString::normalizeUtf8($text);

        return $text;
    }

    /**
     * Cleaning text before indexing method
     *
     * @param string $content the content which have to be cleaned
     *
     * @return string The content cleaned
     */
    public static function getRawText(string $content): string
    {
        $content = strtr(
            $content,
            [
                "<"      => " <",
                "&nbsp;" => " ",
            ]
        );
        $content = CMbString::removeHtml($content);
        $content = preg_replace("/\s+/", ' ', $content);
        $content = html_entity_decode($content, ENT_COMPAT);

        return trim($content);
    }

    /**
     * Recale la table tampon apres indexation des docs dans elastic
     *
     * @return void
     */
    public static function majData(): void
    {
        $searchIndexing      = new self();
        $search_indexing_ids = [];

        // Delete
        if (!empty(static::$current_ids_to_delete)) {
            foreach (static::$current_ids_to_delete as $guid) {
                // Correspondance object.guid & search_indexing_id
                $search_indexing_ids[] = static::$current_ids[$guid];
            }
            $searchIndexing->deleteAll($search_indexing_ids);
            static::$current_ids_to_delete = [];
        }

        // Update
        if (!empty(static::$current_ids_to_update)) {
            foreach (static::$current_ids_to_update as $guid) {
                // Correspondance object.guid & search_indexing_id
                $search_indexing_ids[] = static::$current_ids[$guid];
            }
            $ds    = $searchIndexing->getDS();
            $query = "UPDATE {$searchIndexing->_spec->table} SET processed = '1' WHERE {$searchIndexing->_spec->key} "
                . $ds::prepareIn($search_indexing_ids);
            $ds->exec($query);
            static::$current_ids_to_update = [];
        }
    }

    /**
     * Store into temporary table all the data from CMbObject
     * Méthode effectuant tous les remplissages de la table buffer selon les CMbObject
     * Attention table vidée en debut de traitement
     *
     * @param array $object_classes The name of the CMbObject required
     *
     * @return void
     * @throws Exception
     */
    public function firstIndexingStore(array $object_classes): void
    {
        $date = CMbDT::dateTime();
        $ds   = $this->getDS();

        $ds->exec("TRUNCATE TABLE {$this->_spec->table}");

        foreach ($object_classes as $object_class) {
            switch ($object_class) {
                case 'CCompteRendu':
                    $queries = [
                        "INSERT INTO `search_indexing` (`object_class`, `object_id`, `type`, `date`)
          SELECT 'CCompteRendu', `compte_rendu`.`compte_rendu_id`, 'create', '$date' FROM `compte_rendu`
          WHERE `compte_rendu`.`object_id` IS NOT NULL AND `compte_rendu`.`object_class` != 'CPatient'",
                    ];
                    break;
                case 'CTransmissionMedicale':
                    $queries = [
                        "INSERT INTO `search_indexing` (`object_class`, `object_id`, `type`, `date`)
          SELECT 'CTransmissionMedicale', `transmission_medicale`.`transmission_medicale_id`, 'create', '$date'
          FROM `transmission_medicale`",
                    ];
                    break;
                case 'CObservationMedicale':
                    $queries = [
                        "INSERT INTO `search_indexing` (`object_class`, `object_id`, `type`, `date`)
          SELECT 'CObservationMedicale', `observation_medicale`.`observation_medicale_id`, 'create', '$date'
          FROM `observation_medicale`",
                    ];
                    break;
                case 'CConsultation':
                    $queries = [
                        "INSERT INTO `search_indexing` (`object_class`, `object_id`, `type`, `date`)
          SELECT 'CConsultation', `consultation`.`consultation_id`, 'create', '$date' FROM `consultation`"
                    ];
                    break;
                case 'CConsultAnesth':
                    $queries = [
                        "INSERT INTO `search_indexing` (`object_class`, `object_id`, `type`, `date`)
          SELECT 'CConsultAnesth', `consultation_anesth`.`consultation_anesth_id`, 'create', '$date'
          FROM `consultation_anesth`",
                    ];
                    break;
                case 'CFile':
                    $queries = [
                        "INSERT INTO `search_indexing` (`object_class`, `object_id`, `type`, `date`)
          SELECT 'CFile', `files_mediboard`.`file_id`, 'create', '$date'
          FROM `files_mediboard`
          WHERE `files_mediboard`.`object_class` IN  ('CSejour', 'CConsultation', 'CConsultAnesth', 'COperation')
          AND `files_mediboard`.`file_type` NOT LIKE 'video/%'
          AND `files_mediboard`.`file_type` NOT LIKE 'audio/%'",
                    ];
                    break;
                case 'CPrescriptionLineMedicament':
                    $queries = [
                        "INSERT INTO `search_indexing` (`object_class`, `object_id`, `type`, `date`)
          SELECT 'CPrescriptionLineMedicament', `prescription_line_medicament`.`prescription_line_medicament_id`, 'create', '$date'
          FROM `prescription_line_medicament`, `prescription`
          WHERE  `prescription_line_medicament`.`prescription_id` = `prescription`.`prescription_id`
           AND `prescription`.`object_class` != 'CDossierMedical'
          AND `prescription`.`object_id` IS NOT NULL",
                    ];
                    break;
                case 'CPrescriptionLineMix':
                    $queries = [
                        "INSERT INTO `search_indexing` (`object_class`, `object_id`, `type`, `date`)
          SELECT 'CPrescriptionLineMix', `prescription_line_mix`.`prescription_line_mix_id`, 'create', '$date'
          FROM `prescription_line_mix`, `prescription`
          WHERE  `prescription_line_mix`.`prescription_id` = `prescription`.`prescription_id`
          AND `prescription`.`object_class` != 'CDossierMedical'
          AND `prescription`.`object_id` IS NOT NULL",
                    ];
                    break;
                case 'CPrescriptionLineElement':
                    $queries = [
                        "INSERT INTO `search_indexing` (`object_class`, `object_id`, `type`, `date`)
          SELECT 'CPrescriptionLineElement', `prescription_line_element`.`prescription_line_element_id`, 'create', '$date'
          FROM `prescription_line_element`, `prescription`
          WHERE  `prescription_line_element`.`prescription_id` = `prescription`.`prescription_id`
          AND `prescription`.`object_class` != 'CDossierMedical'
          AND `prescription`.`object_id` IS NOT NULL",
                    ];
                    break;
                case 'CExObject':
                    $ex_classes_ids = $ds->loadColumn("SELECT ex_class_id FROM ex_class");
                    $queries        = [];
                    foreach ($ex_classes_ids as $_ex_class_id) {
                        $queries[] = "INSERT INTO `search_indexing` (`object_class`, `object_id`, `type`, `date`)
          SELECT 'CExObject_$_ex_class_id', `ex_object_id`, 'create', '$date'
          FROM `ex_object_$_ex_class_id`";
                    }
                    break;
                case 'COperation':
                    $queries = [
                        "INSERT INTO `search_indexing` (`object_class`, `object_id`, `type`, `date`)
          SELECT 'COperation', `operations`.`operation_id`, 'create', '$date' FROM `operations`",
                    ];
                    break;
                case 'CDossierMedical':
                    $queries = [
                        "INSERT INTO `search_indexing` (object_class, object_id, type, date)
          SELECT 'CDossierMedical', dossier_medical_id, 'create', '$date' FROM dossier_medical 
          WHERE codes_cim != '' AND codes_cim IS NOT NULL"
                    ];
                    break;
                default:
                    $queries = [];
            };

            // Exec
            foreach ($queries as $query) {
                $ds->exec($query);
            }
        };
    }
}
