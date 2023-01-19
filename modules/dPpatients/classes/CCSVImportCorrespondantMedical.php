<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CAppUI;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Description
 */
class CCSVImportCorrespondantMedical extends CMbCSVObjectImport
{
    /** @var int */
    protected $line_number;

    /** @var bool */
    protected $force_update;

    /** @var array */
    protected $results;

    /** @var string[]  */
    public static $FIELDS = [
        'nom',
        'prenom',
        'jeunefille',
        'sexe',
        'actif',
        'titre',
        'adresse',
        'ville',
        'cp',
        'tel',
        'fax',
        'portable',
        'email',
        'disciplines',
        'orientations',
        'complementaires',
        'type',
        'adeli',
        'rpps',
    ];

    /**
     * @inheritdoc
     */
    function import()
    {
        $this->openFile();
        $this->setColumnNames();
        $this->line_number = 1;
        $this->results     = [];

        while ($this->current_line = $this->readAndSanitizeLine()) {
            $this->results[$this->line_number]          = $this->current_line;
            $this->results[$this->line_number]['error'] = '0';

            if (!isset($this->current_line['nom'])) {
                CAppUI::setMsg(
                    'CCSVImportCorrespondantMedical-Nom is mandatory at line',
                    UI_MSG_WARNING,
                    $this->line_number
                );
            }

            $medecin         = new CMedecin();
            $medecin->nom    = $this->current_line['nom'];
            $medecin->prenom = isset($this->current_line['prenom']) ? $this->current_line['prenom'] : null;
            $medecin->email  = isset($this->current_line['email']) ? $this->current_line['email'] : null;
            $medecin->type   = isset($this->current_line['type']) ? $this->current_line['type'] : 'medecin';

            if (CAppUI::isCabinet()) {
                $medecin->function_id = CMediusers::get()->loadRefFunction()->_id;
            } elseif (CAppUI::isGroup()) {
                $medecin->group_id = CGroups::loadCurrent()->_id;
            }

            $medecin->loadMatchingObjectEsc();

            if (!$medecin->_id || $this->force_update) {
                $medecin->jeunefille
                    = isset($this->current_line['jeunefille']) ? $this->current_line['jeunefille'] : null;
                $medecin->sexe            = isset($this->current_line['sexe']) ? $this->current_line['sexe'] : null;
                $medecin->actif           = isset($this->current_line['actif']) ? $this->current_line['actif'] : null;
                $medecin->titre           = isset($this->current_line['titre']) ? $this->current_line['titre'] : null;
                $medecin->adresse
                    = isset($this->current_line['adresse']) ? $this->current_line['adresse'] : null;
                $medecin->ville           = isset($this->current_line['ville']) ? $this->current_line['ville'] : null;
                $medecin->cp              = isset($this->current_line['cp']) ? $this->current_line['cp'] : null;
                $medecin->tel             = isset($this->current_line['tel']) ? $this->current_line['tel'] : null;
                $medecin->fax             = isset($this->current_line['fax']) ? $this->current_line['fax'] : null;
                $medecin->portable
                    = isset($this->current_line['portable']) ? $this->current_line['portable'] : null;
                $medecin->disciplines
                    = isset($this->current_line['disciplines']) ? $this->current_line['disciplines'] : null;
                $medecin->orientations
                    = isset($this->current_line['orientations']) ? $this->current_line['orientations'] : null;
                $medecin->complementaires
                    = isset($this->current_line['complementaires']) ? $this->current_line['complementaires'] : null;
                $medecin->adeli           = isset($this->current_line['adeli']) ? $this->current_line['adeli'] : null;
                $medecin->rpps            = isset($this->current_line['rpps']) ? $this->current_line['rpps'] : null;

                $new = $medecin->_id ? 'modify' : 'new';
                if ($msg = $medecin->store()) {
                    CAppUI::setMsg($msg, UI_MSG_WARNING);
                    $this->results[$this->line_number]['error'] = $msg;
                } else {
                    CAppUI::setMsg("CMedecin-msg-$new", UI_MSG_OK);
                }
            } else {
                $this->results[$this->line_number]['error'] = '1';
            }

            foreach ($this->csv->column_names as $_name) {
                $this->results[$this->line_number][$_name] = $medecin->$_name;
            }

            $this->line_number++;
        }

        $this->csv->close();

        return $this->results;
    }

    /**
     * Set the options for the import
     *
     * @param bool $force_update Force the update of objects when found
     *
     * @return void
     */
    public function setOptions($force_update = false): void
    {
        $this->force_update = $force_update;
    }

    /**
     * @inheritdoc
     */
    function sanitizeLine($line): ?array
    {
        if (!is_array($line)) {
            return [];
        }

        return array_map('addslashes', array_map('trim', $line));
    }
}
