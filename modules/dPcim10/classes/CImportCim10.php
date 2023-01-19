<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Cim10\Oms\CCIM10OmsImport;

use const PHP_EOL;

/**
 * Import the data of the ATIH file
 * The xls file must be converted to csv, encoded in UTF-8, with | as delimitor and " as string delimitor.
 */
class CImportCim10 implements IShortNameAutoloadable
{
    /**
     * @var CSQLDataSource The cim10 data source
     */
    private $ds;
    private $input;
    private $chapters;
    private $added_codes;
    private $added_inclusions;
    private $added_exclusions;
    private $added_notes;
    private $deleted_codes;
    private $deleted_inclusions;
    private $deleted_exclusions;
    private $deleted_notes;

    /** @var CCIM10OmsImport|null */
    private $import;

    /**
     * The constructor
     *
     * @param string          $input  The path of the input file
     * @param CSQLDataSource  $ds     The data source of the Cim10 database
     * @param CCIM10OmsImport $import The optional instance for external import (allows messages handling for cli)
     */
    function __construct(string $input, CSQLDataSource $ds, ?CCIM10OmsImport $import = null)
    {
        $this->input              = $input;
        $this->ds                 = $ds;
        $this->added_codes        = 0;
        $this->added_inclusions   = 0;
        $this->added_exclusion    = 0;
        $this->added_notes        = 0;
        $this->deleted_codes      = 0;
        $this->deleted_inclusions = 0;
        $this->deleted_exclusions = 0;
        $this->deleted_notes      = 0;
        $this->import             = $import;
    }

    /**
     * Main function
     *
     * @return void
     */
    function run()
    {
        $content = trim(file_get_contents($this->input));

        $lines = preg_replace_callback(
            '/[^"|]' . PHP_EOL . '/',
            ['self', 'removeLineFeeds'],
            $content
        );
        $lines = explode(PHP_EOL, $lines);

        $headers    = ['code', 'type', 'origine', 'date', 'label'];
        $insertions = [
            'chapter'     => [],
            'group'       => [],
            'category'    => [],
            'subcategory' => [],
            'subdivision' => [],
            'inclusion'   => [],
            'exclusion'   => [],
            'definition'  => [],
            'memo'        => [],
            'glossaire'   => [],
        ];
        $deletions  = [
            'inclusion'   => [],
            'exclusion'   => [],
            'definition'  => [],
            'memo'        => [],
            'glossaire'   => [],
            'subdivision' => [],
            'subcategory' => [],
            'category'    => [],
            'group'       => [],
        ];

        $entry_chapter = null;
        foreach ($lines as $_row => $_line) {
            if ($_row == 0) {
                continue;
            }
            $entry = str_getcsv($_line, '|', '"');
            if (count($entry) != 5) {
                continue;
            }
            $entry = array_combine($headers, str_getcsv($_line, '|', '"'));

            $entry['type'] = strtolower(CMbString::removeAccents($entry['type']));

            switch ($entry['type']) {
                case 'creation chapitre':
                    if (strpos($entry['code'], 'Chapitre') !== false) {
                        $entry_chapter = $entry;
                    } else {
                        $insertions['chapter'][] = [$entry, $entry_chapter];
                    }
                    break;
                case 'creation groupe':
                    $insertions['group'][] = $entry;
                    break;
                case 'creation categorie':
                    $insertions['category'][] = $entry;
                    break;
                case 'creation sous-categorie':
                    $insertions['subcategory'][] = $entry;
                    break;
                case 'subdivision de libelle':
                    $insertions['subdivision'][] = $entry;
                    break;
                case 'ajout note d\'inclusion':
                    $insertions['inclusion'][] = $entry;
                    break;
                case 'ajout note d\'exclusion':
                    $insertions['exclusion'][] = $entry;
                    break;
                case 'ajout note de definition':
                    $insertions['definition'][] = $entry;
                    break;
                case 'ajout note d\'utilisation':
                    $insertions['memo'][] = $entry;
                    break;
                case 'suppression subdivision':
                    $deletions['subdivision'][] = $entry;
                    break;
                case 'suppression sous-categorie':
                    $deletions['subcategory'][] = $entry;
                    break;
                case 'suppression categorie':
                    $deletions['category'][] = $entry;
                    break;
                case 'suppression groupe':
                    $deletions['group'][] = $entry;
                    break;
                case 'suppression note d\'exclusion':
                    $deletions['exclusion'][] = $entry;
                    break;
                case 'suppression note d\'inclusion':
                    $deletions['inclusion'][] = $entry;
                    break;
                case 'suppression note d\'utilisation':
                    $deletions['memo'][] = $entry;
                    break;
                case 'suppression note de definition':
                    $deletions['definition'][] = $entry;
                    break;
                case 'suppression note d\'utilisation et d\'inclusion':
                    $labels = explode('#EOL', $entry['label']);
                    foreach ($labels as $_label) {
                        $_entry = $entry;
                        /* Utilisation note */
                        if (strpos($_label, 'Note') === 0) {
                            $_entry['label']     = str_replace('Note : ', '', $_label);
                            $deletions['memo'][] = $_entry;
                        } else {
                            $_entry['label']          = str_replace('Comprend : ', '', $_label);
                            $deletions['inclusion'][] = $_entry;
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        foreach ($insertions as $_type => $_insertions) {
            foreach ($_insertions as $_insert) {
                $this->add($_type, $_insert);
            }
        }

        if ($this->import instanceof CCIM10OmsImport) {
            $this->import->addMessage(["$this->added_codes codes ont été ajoutés à la CIM10", UI_MSG_OK]);
            $this->import->addMessage(
                ["$this->added_inclusions notes d'inclusions ont été ajoutées à la CIM10", UI_MSG_OK]
            );
            $this->import->addMessage(
                ["$this->added_exclusions notes d'exclusions ont été ajoutées à la CIM10", UI_MSG_OK]
            );
            $this->import->addMessage(
                ["$this->added_notes notes (descriptions, memos) ont été ajoutées à la CIM10", UI_MSG_OK]
            );
        } else {
            CAppUI::stepAjax("$this->added_codes codes ont été ajoutés à la CIM10", UI_MSG_OK);
            CAppUI::stepAjax("$this->added_inclusions notes d'inclusions ont été ajoutées à la CIM10", UI_MSG_OK);
            CAppUI::stepAjax("$this->added_exclusions notes d'exclusions ont été ajoutées à la CIM10", UI_MSG_OK);
            CAppUI::stepAjax("$this->added_notes notes (descriptions, memos) ont été ajoutées à la CIM10", UI_MSG_OK);
        }

        foreach ($deletions as $_type => $_deletions) {
            foreach ($_deletions as $_delete) {
                $this->delete($_type, $_delete);
            }
        }
        if ($this->import instanceof CCIM10OmsImport) {
            $this->import->addMessage(["$this->deleted_codes codes ont été supprimés de la CIM10", UI_MSG_OK]);
            $this->import->addMessage(
                ["$this->deleted_inclusions notes d'inclusions ont été supprimées de la CIM10", UI_MSG_OK]
            );
            $this->import->addMessage(
                ["$this->deleted_exclusions notes d'exclusions ont été supprimées de la CIM10", UI_MSG_OK]
            );
            $this->import->addMessage(
                ["$this->deleted_notes notes (descriptions, memos) ont été supprimées de la CIM10", UI_MSG_OK]
            );
        } else {
            CAppUI::stepAjax("$this->deleted_codes codes ont été supprimés de la CIM10", UI_MSG_OK);
            CAppUI::stepAjax("$this->deleted_inclusions notes d'inclusions ont été supprimées de la CIM10", UI_MSG_OK);
            CAppUI::stepAjax("$this->deleted_exclusions notes d'exclusions ont été supprimées de la CIM10", UI_MSG_OK);
            CAppUI::stepAjax(
                "$this->deleted_notes notes (descriptions, memos) ont été supprimées de la CIM10",
                UI_MSG_OK
            );
        }
    }

    /**
     * Add the entry in accordance with it's type
     *
     * @param string $type  The type of the modification
     * @param array  $entry The entry to add
     *
     * @return void
     */
    function add($type, $entry)
    {
        switch ($type) {
            case 'chapter':
                [$master_entry, $chapter_entry] = $entry;
                $this->addChapter($master_entry, $chapter_entry);
                break;
            case 'group':
                $this->addGroup($entry);
                break;
            case 'category':
                $this->addCategory($entry);
                break;
            case 'subcategory':
                $this->addSubCategory($entry);
                break;
            case 'subdivision':
                $this->addSubCategory($entry);
                break;
            case 'inclusion':
                $this->addInclusionNote($entry);
                break;
            case 'exclusion':
                $this->addExclusionNote($entry);
                break;
            case 'definition':
                $this->addDefinition($entry);
                break;
            case 'memo':
                $this->addMemo($entry);
                break;
        }
    }

    /**
     * Delete the entry in accordance with it's type
     *
     * @param string $type  The type of the modification
     * @param array  $entry The entry to delete
     *
     * @return void
     */
    function delete($type, $entry)
    {
        $code = self::cleanCode($entry['code']);
        switch ($type) {
            case 'exclusion':
                $this->deleteExclusion($entry);
                break;
            case 'inclusion':
                $this->deleteInclusion($entry);
                break;
            case 'memo':
                $this->deleteMemo($entry);
                break;
            case 'definition':
                $this->deleteDefinition($entry);
                break;
            case 'group':
                $code = "($code)";
                $this->deleteCode($code);
                break;
            default:
                $this->deleteCode($code);
                break;
        }
    }

    /**
     * Add a  chapter to the Cim10 database
     *
     * @param array $master_entry  The master entry
     * @param array $chapter_entry The chapter entry
     *
     * @return void
     */
    function addChapter($master_entry, $chapter_entry)
    {
        /* We remove the useless characters in the code */
        $master_entry['code'] = self::cleanCode($master_entry['code']);
        $code                 = '(' . $master_entry['code'] . ')';
        [$sort] = explode('-', $master_entry['code']);
        $date = CMbDT::format($master_entry['date'], '%Y-%m-%d 00:00:00');

        /* Check if the chapter already exists */
        $query = "SELECT `SID` FROM `master` WHERE `code` LIKE '$code' AND `type` = 'C';";
        if (!is_null($this->ds->loadResult($query))) {
            return;
        }

        /* Insert the chapter in the master table*/
        $query = "INSERT INTO `master` (`code`, `sort`, `abbrev`, `level`, `type`, `valid`, `date`, `author`)
      VALUES ('$code', '$sort\\'', '$code', '1', 'C', 1, '$date', '" . $master_entry['origine'] . "');";
        if ($this->ds->exec($query)) {
            $this->added_codes++;

            /* Get the SID of the chapter */
            $sid = $this->ds->insertId();

            /* Update the master entry for setting the id1 */
            $query = "UPDATE `master` SET `id1` = $sid WHERE `SID` = $sid;";
            $this->ds->exec($query);

            /* Insert the label */
            $this->insertLabel($sid, 'S', self::cleanLabel($master_entry['label']), $date, $master_entry['origine']);

            $rom = str_replace('Chapitre ', '', $chapter_entry['code']);
            $dec = CMbString::roman2dec($rom);

            /* Create the chapter in the chapter table */
            $query = "INSERT INTO `chapter` (`chap`, `SID`, `rom`) VALUES ($dec, $sid, '$rom');";
            $this->ds->exec($query);
        }
    }

    /**
     * Add a group to the Cim10 database
     *
     * @param array $entry The entry to add
     *
     * @return void
     */
    function addGroup($entry)
    {
        /* We remove the useless characters in the code */
        $entry['code'] = self::cleanCode($entry['code']);
        $code          = '(' . $entry['code'] . ')';
        [$sort] = explode('-', $entry['code']);
        $date = CMbDT::format($entry['date'], '%Y-%m-%d 00:00:00');

        /* Check if the group already exists */
        $query = "SELECT `SID` FROM `master` WHERE `code` LIKE '$code' AND `type` = 'G';";
        if (!is_null($this->ds->loadResult($query))) {
            return;
        }

        /* Insert the group in the master table*/
        $query = "INSERT INTO `master` (`code`, `sort`, `abbrev`, `level`, `type`, `valid`, `date`, `author`)
      VALUES ('$code', '$sort-', '$code', '2', 'G', 1, '$date', '" . $entry['origine'] . "');";
        if ($this->ds->exec($query)) {
            $this->added_codes++;

            /* Get the SID of the group */
            $sid = $this->ds->insertId();

            /* Insert the label */
            $this->insertLabel($sid, 'S', self::cleanLabel($entry['label']), $date, $entry['origine']);

            /* Get the SID of the ancestor */
            $ancestor = $this->getChapterAncestor($code);

            /* Update the master entry for setting the id1 */
            $query = "UPDATE `master` SET `id1` = " . $ancestor['sid'] . ", `id2` = $sid WHERE `SID` = $sid;";
            $this->ds->exec($query);
        }
    }

    /**
     * Add a category to the Cim10 database
     *
     * @param array $entry The entry to add
     *
     * @return void
     */
    function addCategory($entry)
    {
        $code = self::cleanCode($entry['code']);
        $date = CMbDT::format($entry['date'], '%Y-%m-%d 00:00:00');

        /* Check if the category already exists */
        $query = "SELECT `SID` FROM `master` WHERE `code` LIKE '$code' AND `type` = 'K';";
        if (!is_null($this->ds->loadResult($query))) {
            return;
        }

        /* Insert the group in the master table*/
        $query = "INSERT INTO `master` (`code`, `sort`, `abbrev`, `level`, `type`, `valid`, `date`, `author`)
      VALUES ('$code', '$code,', '$code', '3', 'K', 1, '$date', '" . $entry['origine'] . "');";
        if ($this->ds->exec($query)) {
            $this->added_codes++;

            /* Get the SID of the category */
            $sid = $this->ds->insertId();

            /* Insert the label */
            $this->insertLabel($sid, 'S', self::cleanLabel($entry['label']), $date, $entry['origine']);

            /* Get the SID of the ancestor */
            $ancestor = $this->getGroupAncestor($code);

            /* Update the master entry for setting the id1 */
            $query = "UPDATE `master` SET `id1` = " . $ancestor['id1'] . ", `id2` = " . $ancestor['id2'] . ", `id3` = $sid WHERE `SID` = $sid;";
            $this->ds->exec($query);
        }
    }

    /**
     * Add a category to the Cim10 database
     *
     * @param array $entry The entry to add
     *
     * @return void
     */
    function addSubCategory($entry)
    {
        $code = self::cleanCode($entry['code']);
        $date = CMbDT::format($entry['date'], '%Y-%m-%d 00:00:00');

        /* Check if the subcategory already exists */
        $query = "SELECT `SID` FROM `master` WHERE `code` LIKE '$code' AND `type` = 'S';";
        if (!is_null($this->ds->loadResult($query))) {
            return;
        }

        $abbrev = str_replace('.', '', $code);

        $clean_code = $code;
        if (strpos($clean_code, 'X') === 0) {
            $clean_code[0] = 'x';
        }
        /* We remove the X in the code */
        $clean_code = str_replace('X', '', $code);

        /* Check if the direct ancestor is a Category or a Subcategory, and get it's data */
        if (
            strlen(substr($clean_code, strpos($clean_code, '.') + 1)) == 1 ||
            strpos(substr($code, strpos($code, '.') + 1), 'X') === 0
        ) {
            $ancestor = $this->getCategoryAncestor($code);
        } else {
            $ancestor = $this->getSubCategoryAncestor($code);
            $ancestor = $ancestor[0];
        }

        $level = intval($ancestor['level']) + 1;

        /* Insert the group in the master table*/
        $query = "INSERT INTO `master` (`code`, `sort`, `abbrev`, `level`, `type`, `valid`, `date`, `author`)
      VALUES ('$code', '$code', '$abbrev', '$level', 'S', 1, '$date', '" . $entry['origine'] . "');";
        if ($this->ds->exec($query)) {
            $this->added_codes++;

            $sid = $sid = $this->ds->insertId();

            /* Insert the label */
            $this->insertLabel($sid, 'S', self::cleanLabel($entry['label']), $date, $entry['origine']);

            /* Forge the query updating of the id */
            $set_ids = [];
            for ($i = 1; $i <= $level; $i++) {
                if ($i < $level) {
                    $set_ids[$i] = "`id$i` = " . $ancestor["id$i"];
                } elseif ($i == $level) {
                    $set_ids[$i] = "`id$i` = $sid";
                }
            }
            /* Update the master entry for setting the ids */
            $query = "UPDATE `master` SET " . implode(', ', $set_ids) . " WHERE `SID` = $sid;";
            $this->ds->exec($query);
        }
    }

    /**
     * Add an inclusion note to the Cim10 database
     *
     * @param array $entry The entry to add
     *
     * @return void
     */
    function addInclusionNote($entry)
    {
        $code = self::cleanCode($entry['code']);
        /* Check if the code is a group */
        if (strpos($code, '-') !== false) {
            $code = "($code)";
        }
        $date = CMbDT::format($entry['date'], '%Y-%m-%d 00:00:00');
        $sid  = $this->getSID($code);
        if (!is_null($sid)) {
            $note = self::cleanLabel($entry['label']);

            /* We add one inclusion note for each line in the label */
            foreach (explode('#EOL', $note) as $inclusion) {
                /* Check if the inclusion already exists */
                $query = "SELECT `LID` FROM `libelle` WHERE `FR_CHRONOS` LIKE '$inclusion' AND `SID` = $sid AND `source` = 'I';";
                if (!is_null($this->ds->loadResult($query))) {
                    return;
                }

                /* Add the inclusion note */
                $lid = $this->insertLabel($sid, 'I', $inclusion, $date, $entry['origine']);
                if ($lid !== 0) {
                    $this->added_inclusions++;

                    /* Create the link between the inclusion note and the code */
                    $query = "INSERT INTO `include` (`SID`, `LID`) VALUES ($sid, $lid);";
                    $this->ds->exec($query);
                }
            }
        }
    }

    /**
     * Add exclusion notes to the Cim10 database
     *
     * @param array $entry The entry to add
     *
     * @return void
     */
    function addExclusionNote($entry)
    {
        $code = self::cleanCode($entry['code']);
        $date = CMbDT::format($entry['date'], '%Y-%m-%d 00:00:00');

        /* Check if the code is a group or a chapter */
        if (strpos($code, '-') !== false && strpos($code, '-') != strlen($code) - 1) {
            $code = "($code)";
        }
        $sid = $this->getSID($code);
        if (is_null($sid)) {
            return;
        }

        $note = self::cleanLabel($entry['label']);

        /* For each line in the label : */
        foreach (explode('#EOL', $note) as $_note) {
            /* Search the codes in the exclusion note */
            $regex = '#\(?([A-Z][0-9]{2}[\.0-9X]*[-]*[A-Z]*[0-9]{0,2}[\.0-9X]*)[,\-\*\s]*[)]?#';
            preg_match_all($regex, $_note, $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[1] as $_match) {
                $code_excl = $_match[0];

                if (strpos($code_excl, '-') !== false) {
                    /* Check if the exclusion is on a group or an interval */
                    if (strpos($code_excl, '-') != strlen($code_excl) - 1) {
                        /* Check if the code is a group */
                        if (strpos($code_excl, '.') === false) {
                            $code_excl = "($code_excl)";
                            $sid_excl  = $this->getSID($code_excl);
                            if (!is_null($sid_excl)) {
                                $this->insertExclusionNote(
                                    $sid,
                                    $code_excl,
                                    $_note,
                                    $date,
                                    $entry['origine'],
                                    $sid_excl
                                );
                            } else {
                                /* The exclusion is not on a group, but on an interval */
                                $code_excl = str_replace(['(', ')'], '', $code_excl);
                                $letter    = $code_excl[0];
                                $begin     = intval(substr($code_excl, 1, 2));
                                $end       = intval(substr($code_excl, 5, 2));

                                /* Add an exclusion note for each code in the interval */
                                for ($i = $begin; $i <= $end; $i++) {
                                    $this->insertExclusionNote($sid, "$letter$i", $_note, $date, $entry['origine']);
                                }
                            }
                        } else {
                            /* Interval of subdivisions */
                            $begin_letter = $code_excl[0];
                            $end_letter   = $code_excl[6];
                            $begin        = intval($code_excl[4]);
                            $end          = intval($code_excl[10]);
                            /* Some intervals are too big, like A00.0-Q99.9 */
                            if ($begin_letter == $end_letter) {
                                /* Add an exclusion note for each code in the interval */
                                for ($i = $begin; $i <= $end; $i++) {
                                    $this->insertExclusionNote(
                                        $sid,
                                        substr($code_excl, 0, 4) . $i,
                                        $_note,
                                        $date,
                                        $entry['origine']
                                    );
                                }
                            }
                        }
                    } else {
                        $query  = "SELECT `SID`, `code` FROM `master` WHERE `code` LIKE '" . substr(
                            $code_excl,
                            0,
                            strlen(
                                $code_excl
                            ) - 1
                        ) . "%';";
                        $result = $this->ds->exec($query);
                        while ($row = $this->ds->fetchAssoc($result)) {
                            if (strpos($row['code'], 'X') == false) {
                                $this->insertExclusionNote(
                                    $sid,
                                    $row['code'],
                                    $_note,
                                    $date,
                                    $entry['origine'],
                                    $row['SID']
                                );
                            }
                        }
                    }
                } else {
                    $this->insertExclusionNote($sid, $code_excl, $_note, $date, $entry['origine']);
                }
            }
        }
    }

    /**
     * Insert an exclusion note to the cim10 database
     *
     * @param integer      $sid       The sid of the code
     * @param string       $code_excl The code targeted by the exclusion
     * @param string       $note      The text of the exclusion
     * @param string       $date      The date of the modification
     * @param string       $origine   The origin of the modification
     * @param null|integer $sid_excl  The sid of the target of the exclusion
     *
     * @return void
     */
    function insertExclusionNote($sid, $code_excl, $note, $date, $origine, $sid_excl = null)
    {
        if (is_null($sid_excl)) {
            $sid_excl = $this->getSID($code_excl);
        }

        if (!is_null($sid_excl)) {
            /* Check if the exclusion already exists */
            $query = "SELECT `LID` FROM `libelle` WHERE `FR_CHRONOS` LIKE '$note' AND `SID` = $sid AND `source` = 'E';";
            if (!is_null($this->ds->loadResult($query))) {
                return;
            }

            $lid = $this->insertLabel($sid, 'E', $note, $date, $origine);
            if ($lid !== 0) {
                $this->added_exclusions++;

                /* Create the link between the exclusion note ans the code */
                $query = "INSERT INTO `exclude` (`SID`, `excl`, `plus`, `LID`) VALUES ($sid, $sid_excl, 1, $lid);";
                $this->ds->exec($query);
            }
        }
    }

    /**
     * Add a definition of a code in the cim10 database
     *
     * @param array $entry The entry to add
     *
     * @return void
     */
    function addDefinition($entry)
    {
        $code = self::cleanCode($entry['code']);
        if (strpos($code, '-') !== false) {
            $code = "($code)";
        }
        $date = CMbDT::format($entry['date'], '%Y-%m-%d 00:00:00');
        $sid  = $this->getSID($code);

        $note = self::cleanLabel($entry['label']);

        /* Check if the definition already exists */
        $query = "SELECT `LID` FROM `libelle` WHERE `FR_CHRONOS` LIKE '$note' AND `SID` = $sid AND `source` = 'D';";
        if (!is_null($this->ds->loadResult($query))) {
            return;
        }

        $lid = $this->insertLabel($sid, 'D', $note, $date, $entry['origine']);
        if ($lid !== 0) {
            $this->added_notes++;

            /* Create the link between the description and the code */
            $query = "INSERT INTO `descr` (`SID`, `LID`) VALUES ($sid, $lid);";
            $this->ds->exec($query);
        }
    }

    /**
     * Add an utilisation memo to the cim10 database
     *
     * @param array $entry The entry to add
     *
     * @return void
     */
    function addMemo($entry)
    {
        $code = self::cleanCode($entry['code']);
        $sid  = [];
        if (strpos($code, '-') !== false && strpos($code, '.') === false) {
            $code = "($code)";
        }


        /* Get the SID if the code is Chapter */
        if (strpos($code, 'Chapitre') !== false) {
            $parts = explode(' ', $code);
            $query = "SELECT `SID` FROM `chapter` WHERE `rom` LIKE '" . $parts[1] . "';";
            $_sid  = $this->ds->loadResult($query);
            if ($_sid) {
                $sid[] = $_sid;
            }
        } elseif (strpos($code, '-') !== false && strpos($code, '.') !== false) {
            /* If the memo has to be add for several code : */
            [$group, $subdivision] = explode('.', $code);
            $letter = $group[0];
            [$begin, $end] = explode('-', $group);
            $begin = intval(substr($begin, 1, 2));
            $end   = intval(substr($end, 1, 2));
            for ($i = $begin; $i <= $end; $i++) {
                $_sid = $this->getSID("$letter$i.$subdivision");
                if ($_sid) {
                    $sid[] = $_sid;
                }
            }
        } else {
            $_sid = $this->getSID($code);
            if ($_sid) {
                $sid[] = $_sid;
            }
        }

        $date = CMbDT::format($entry['date'], '%Y-%m-%d 00:00:00');
        $note = self::cleanLabel($entry['label']);
        $note = str_replace('#EOL', "\n", $note);
        foreach ($sid as $_sid) {
            /* Check if the exclusion already exists */
            $query = "SELECT `MID` FROM `memo` WHERE `FR_OMS` LIKE '$note' AND `SID` = $_sid;";
            if (!is_null($this->ds->loadResult($query))) {
                return;
            }

            /* Add the memo */
            $query = "INSERT INTO `memo` (`SID`, `source`, `valid`, `memo`, `FR_OMS`, `date`, `author`)
      VALUES ($_sid, 'N', 'Yes', '$note', '$note', '$date', '" . $entry['origine'] . "');";
            $this->ds->exec($query);

            /* Get the id of the memo */
            $mid = $this->ds->insertId();
            if ($mid !== 0) {
                $this->added_notes++;

                /* Create the link between the memo and the code */
                $query = "INSERT INTO `glossaire` (`SID`, `MID`) VALUES ($_sid, $mid);";
                $this->ds->exec($query);
            }
        }
    }

    /**
     * Delete a code and the associated labels, inclusion notes, exclusion notes in the Cim10 database
     *
     * @param string $code The code to delete
     *
     * @return void
     */
    function deleteCode($code)
    {
        $sid = $this->getSID($code);
        if (is_null($sid)) {
            return;
        }

        $query = "DELETE FROM `common` WHERE `SID` = $sid;";
        $this->ds->exec($query);

        $query = "DELETE FROM `libelle` WHERE `SID` = $sid;";
        $this->ds->exec($query);

        $query = "DELETE FROM `memo` WHERE `SID` = $sid;";
        $this->ds->exec($query);
        $query = "DELETE FROM `note` WHERE `SID` = $sid;";
        $this->ds->exec($query);
        $query = "DELETE FROM `glossaire` WHERE `SID` = $sid;";
        $this->ds->exec($query);

        $query  = "SELECT `LID` FROM `exclude` WHERE `excl` = $sid;";
        $result = $this->ds->exec($query);
        while ($lid = $this->ds->fetchRow($result)) {
            $query = "DELETE FROM `libelle` WHERE `LID` = $lid[0];";
            $this->ds->exec($query);
        }
        $query = "DELETE FROM `exclude` WHERE `SID` = $sid OR `excl` = $sid;";
        $this->ds->exec($query);

        $query  = "SELECT `LID` FROM `dagstar` WHERE `assoc` = $sid;";
        $result = $this->ds->exec($query);
        while ($lid = $this->ds->fetchRow($result)) {
            $query = "DELETE FROM `libelle` WHERE `LID` = $lid[0];";
            $this->ds->exec($query);
        }

        $query = "DELETE FROM `dagstar` WHERE `SID` = $sid OR `assoc` = $sid;";
        $this->ds->exec($query);

        $query = "DELETE FROM `system` WHERE `SID` = $sid;";
        $this->ds->exec($query);
        $query = "DELETE FROM `refer` WHERE `SID` = $sid;";
        $this->ds->exec($query);
        $query = "DELETE FROM `indir` WHERE `SID` = $sid;";
        $this->ds->exec($query);
        $query = "DELETE FROM `include` WHERE `SID` = $sid;";
        $this->ds->exec($query);
        $query = "DELETE FROM `descr` WHERE `SID` = $sid;";
        $this->ds->exec($query);

        $query = "DELETE FROM `master` WHERE `SID` = $sid;";
        if ($this->ds->exec($query)) {
            $this->deleted_codes++;
        }
    }

    /**
     * Delete an inclusion note
     *
     * @param array $entry The entry to delete
     *
     * @return void
     */
    function deleteInclusion($entry)
    {
        $code = self::cleanCode($entry['code']);

        /* Check if the code is a group */
        if (strpos($code, '-') !== false) {
            $code = "($code)";
        }
        $sid = $this->getSID($code);
        if (!is_null($sid)) {
            $note = self::cleanLabel($entry['label']);

            /* We add one inclusion note for each line in the label */
            foreach (explode('#EOL', $note) as $inclusion) {
                $query = "SELECT `LID` FROM `libelle` WHERE `FR_OMS` LIKE '$inclusion' AND `SID` = $sid;";
                $lid   = $this->ds->loadResult($query);

                if (!is_null($lid)) {
                    /* Delete the label */
                    $query = "DELETE FROM `libelle` WHERE`SID` = $sid AND `LID` = $lid;";
                    if ($this->ds->exec($query)) {
                        $this->deleted_inclusions++;

                        /* Delete the inclusion */
                        $query = "DELETE FROM `include` WHERE`SID` = $sid AND `LID` = $lid;";
                        $this->ds->exec($query);
                    }
                }
            }
        }
    }

    /**
     * Delete an exclusion note
     *
     * @param array $entry The entry to delete
     *
     * @return void
     */
    function deleteExclusion($entry)
    {
        $code = self::cleanCode($entry['code']);
        if (strpos($code, '-') !== false) {
            $code = "($code)";
        }
        $label = self::cleanLabel($entry['label']);
        $sid   = $this->getSID($code);

        if (!is_null($sid)) {
            /* Search the codes in the exclusion note */
            $regex = '#\(?([A-Z][0-9]{2}[\.0-9X]*[-]*[A-Z]*[0-9]{0,2}[\.0-9X]*)[,\-\*\s]*[)]?#';
            preg_match_all($regex, $label, $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[1] as $_match) {
                if (strpos($_match[0], '-') !== false) {
                    $query  = "SELECT `SID`, `code` FROM `master` WHERE `code` LIKE '" . substr(
                        $_match[0],
                        0,
                        strlen($_match[0]) - 1
                    ) . "%';";
                    $result = $this->ds->exec($query);
                    while ($row = $this->ds->fetchAssoc($result)) {
                        $query = "SELECT `LID` FROM `exclude` WHERE `SID` = $sid AND `excl` = " . $row['SID'] . ";";
                        $lid   = $this->ds->loadResult($query);

                        if (!is_null($lid)) {
                            $query = "DELETE FROM `libelle` WHERE `LID` = $lid;";
                            $this->ds->exec($query);
                        }

                        $query = "DELETE FROM `exclude` WHERE `SID` = $sid AND `excl` = " . $row['SID'] . ";";
                        if ($this->ds->exec($query)) {
                            $this->deleted_exclusions++;
                        }
                    }
                } else {
                    $code_excl = self::cleanCode($_match[0]);
                    $sid_excl  = $this->getSID($code_excl);
                    if (!is_null($sid_excl)) {
                        $query = "SELECT `LID` FROM `exclude` WHERE `SID` = $sid AND `excl` = $sid_excl;";
                        $lid   = $this->ds->loadResult($query);


                        if (!is_null($lid)) {
                            $query = "DELETE FROM `libelle` WHERE `LID` = $lid;";
                            $this->ds->exec($query);
                        }

                        $query = "DELETE FROM `exclude` WHERE `SID` = $sid AND `excl` = $sid_excl;";
                        if ($this->ds->exec($query)) {
                            $this->deleted_exclusions++;
                        }
                    }
                }
            }
        }
    }

    /**
     * Delete a memo (utilisation note)
     *
     * @param array $entry The entry to delete
     *
     * @return void
     */
    function deleteMemo($entry)
    {
        $code = self::cleanCode($entry['code']);

        /* Check if the code is a group */
        if (strpos($code, '-') !== false) {
            $code = "($code)";
        }

        $sid = $this->getSID($code);
        if (!is_null($sid)) {
            $note = self::cleanLabel($entry['label']);

            $query = "SELECT `MID` FROM `memo` WHERE `FR_OMS` LIKE '$note' AND `SID` = $sid;";
            $mid   = $this->ds->loadResult($query);

            if (!is_null($mid)) {
                /* Delete the label */
                $query = "DELETE FROM `memo` WHERE`SID` = $sid AND `MID` = $mid;";
                if ($this->ds->exec($query)) {
                    $this->deleted_notes++;

                    /* Delete the note */
                    $query = "DELETE FROM `note` WHERE`SID` = $sid AND `MID` = $mid;";
                    $this->ds->exec($query);
                }
            }
        }
    }

    /**
     * Delete a definition
     *
     * @param array $entry The entry to delete
     *
     * @return void
     */
    function deleteDefinition($entry)
    {
        $code = self::cleanCode($entry['code']);

        /* Check if the code is a group */
        if (strpos($code, '-') !== false) {
            $code = "($code)";
        }
        $sid = $this->getSID($code);
        if (!is_null($sid)) {
            $note = self::cleanLabel($entry['label']);

            $query = "SELECT `LID` FROM `libelle` WHERE `FR_CHRONOS` LIKE '$note' AND `SID` = $sid AND `source` = 'D';";
            $lid   = $this->ds->loadResult($query);

            if (!is_null($lid)) {
                /* Delete the label */
                $query = "DELETE FROM `libelle` WHERE`SID` = $sid AND `LID` = $lid;";
                if ($this->ds->exec($query)) {
                    $this->deleted_notes++;

                    /* Delete the inclusion */
                    $query = "DELETE FROM `descr` WHERE`SID` = $sid AND `LID` = $lid;";
                    $this->ds->exec($query);
                }
            }
        }
    }

    /**
     * Insert a label and return it's id
     *
     * @param integer $sid     The SID of the code
     * @param string  $source  The type of the label
     * @param string  $label   The label
     * @param string  $date    The date of addition of the label to the Cim10
     * @param string  $origine The author of the modification
     *
     * @return int
     */
    function insertLabel($sid, $source, $label, $date, $origine)
    {
        $query = "INSERT INTO `libelle` (`SID`, `source`, `valid`, `libelle`, `FR_OMS`, `FR_CHRONOS`, `date`, `author`)
      VALUES ($sid, '$source', 1, '$label', '$label', '$label', '$date', '$origine');";
        if ($this->ds->exec($query)) {
            return $this->ds->insertId();
        } else {
            return 0;
        }
    }

    /**
     * Get the SID of the given code
     *
     * @param string      $code The code to find
     * @param string|null $type The type of the code (C for chapter, G for group, K for category and S for subdivision
     *
     * @return string|null
     */
    function getSID($code, $type = null)
    {
        if (is_null($type)) {
            $query = "SELECT `SID` FROM `master` WHERE `code` LIKE '$code';";
        } else {
            $query = "SELECT `SID` FROM `master` WHERE `code` LIKE '$code' AND `type` = '$type';";
        }

        return $this->ds->loadResult($query);
    }

    /**
     * Search the first chapter ancestor of the given group
     *
     * @param string $code The code of the group
     *
     * @return array|null
     */
    function getChapterAncestor($code)
    {
        if (!isset($this->chapters)) {
            $this->chapters = [];
            $query          = "SELECT `sid`, `code` FROM `master` WHERE `type` = 'C';";
            $result         = $this->ds->exec($query);
            while ($row = $this->ds->fetchAssoc($result)) {
                $this->chapters[] = $row;
            }
        }

        $ancestor    = null;
        $group_codes = explode('-', str_replace(['(', ')'], '', $code));

        foreach ($this->chapters as $_chapter) {
            $_chapter_codes = explode('-', str_replace(['(', ')'], '', $_chapter['code']));
            /* Check if one of the boundary of the group is equal to one of the chapter */
            if ($group_codes[0] == $_chapter_codes[0] || $group_codes[1] == $_chapter_codes[1]) {
                $ancestor = $_chapter;
                break;
            } else {
                $begin = [$group_codes[0], $_chapter_codes[0]];
                $end   = [$group_codes[1], $_chapter_codes[1]];
                sort($begin);
                sort($end);
                /* Check if the boundaries of the group are between the ones of the chapter */
                if (array_search($group_codes[0], $begin) === 1 && array_search($group_codes[1], $end) === 0) {
                    $ancestor = $_chapter;
                    break;
                }
            }
        }

        return $ancestor;
    }

    /**
     * Search the first group ancestor of the given category
     *
     * @param string $code The code of the category
     *
     * @return array|null
     */
    function getGroupAncestor($code)
    {
        $groups = [];
        $query  = "SELECT `SID`, `code`, `id1`, `id2` FROM `master` WHERE `type` = 'G' AND `sort` LIKE '" . $code[0] . "%';";
        $result = $this->ds->exec($query);
        while ($row = $this->ds->fetchAssoc($result)) {
            $groups[] = $row;
        }

        $ancestor = null;
        foreach ($groups as $_group) {
            $_group_codes = explode('-', str_replace(['(', ')'], '', $code));
            /* Check if the code is equal to a boundary of the the group */
            if ($code == $_group_codes[0] || $code == $_group_codes[1]) {
                $ancestor = $_group;
                break;
            } else {
                $begin = [$code, $_group_codes[0]];
                $end   = [$code, $_group_codes[1]];
                sort($begin);
                sort($end);
                /* Check if the code is between the two boundaries of the the group */
                if (array_search($code, $begin) === 1 && array_search($code, $end) === 0) {
                    $ancestor = $_group;
                    break;
                }
            }
        }

        return $ancestor;
    }

    /**
     * Search the first category of the given subcategory
     *
     * @param string $code The code of the subcategory
     *
     * @return array|null
     */
    function getCategoryAncestor($code)
    {
        $query = "SELECT `SID`, `code`, `level`, `id1`, `id2`, `id3`, `id4`, `id5`, `id6`, `id7` FROM `master`
      WHERE `code` LIKE '" . substr($code, 0, 3) . "%' AND `type` = 'K';";

        $result = $this->ds->exec($query);

        return $this->ds->fetchAssoc($result);
    }

    /**
     * Search the first subcategory or subdivision of the given code
     *
     * @param string $code The code
     *
     * @return array|null
     */
    function getSubCategoryAncestor($code)
    {
        $clean_code = $code;
        if (strpos($clean_code, 'X') === 0) {
            $clean_code[0] = 'x';
        }
        $sub = substr($clean_code, strpos($clean_code, '.'));
        if (strpos($sub, 'X') === false) {
            $ancestor_code = substr($clean_code, 0, strlen($clean_code) - 1);
        } else {
            $ancestor_code = substr($clean_code, 0, strpos($clean_code, 'X'));
        }
        $ancestor_code = str_replace('x', 'X', $ancestor_code);

        $query = "SELECT `SID`, `code`, `level`, `id1`, `id2`, `id3`, `id4`, `id5`, `id6`, `id7` FROM `master`
      WHERE `code` LIKE '$ancestor_code';";

        $result   = $this->ds->exec($query);
        $ancestor = $this->ds->fetchAssoc($result);

        if (empty($ancestor)) {
            return $this->getSubCategoryAncestor($ancestor_code);
        } else {
            return [$ancestor, $ancestor_code];
        }
    }

    /**
     * Remove all the useless characters in the code
     *
     * @param string $code The code to clean
     *
     * @return string
     */
    static function cleanCode($code)
    {
        $search  = [
            '-',
            html_entity_decode('&ndash;', null, 'UTF-8'),
            html_entity_decode('&mdash;', null, 'UTF-8'),
            "\xe2\x88\x92",
            '?',
            '+',
            html_entity_decode('&dagger;', null, 'UTF-8'),
            html_entity_decode('&Dagger;', null, 'UTF-8'),
            '(groupe)',
            '*',
            '#EOL',
            '(',
            ')',
        ];
        $replace = [
            '',
            '-',
            '-',
            '-',
            '-',
            'X',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];

        return trim(str_replace($search, $replace, $code));
    }

    /**
     * Remove all the special characters in the Cim10 file
     *
     * @param string $label The label to clean
     *
     * @return string
     */
    static function cleanLabel($label)
    {
        $search  = [
            "'",
            '?',
            html_entity_decode('&dagger;', ENT_COMPAT),
            html_entity_decode('&Dagger;', ENT_COMPAT),
            html_entity_decode('&ndash;', ENT_COMPAT),
            html_entity_decode('&#8722;', ENT_COMPAT),
            html_entity_decode('&mdash;', ENT_COMPAT),
            html_entity_decode('&tilde;', ENT_COMPAT),
            html_entity_decode('&lsquo;', ENT_COMPAT),
            html_entity_decode('&rsquo;', ENT_COMPAT),
            html_entity_decode('&ldquo;', ENT_COMPAT),
            html_entity_decode('&rdquo;', ENT_COMPAT),
            html_entity_decode('&bull;', ENT_COMPAT),
        ];
        $replace = [
            "\\'",
            '',
            '',
            '',
            '-',
            '-',
            '-',
            '-',
            "\\'",
            "\\'",
            "\\'",
            "\\'",
            '',
        ];

        return utf8_decode(trim(str_replace($search, $replace, $label)));
    }

    /**
     * Remove the line feeds and replace it by #EOL
     *
     * @param array $matches The matches
     *
     * @return string
     */
    static function removeLineFeeds($matches)
    {
        return str_replace(PHP_EOL, '#EOL', $matches[0]);
    }
}
