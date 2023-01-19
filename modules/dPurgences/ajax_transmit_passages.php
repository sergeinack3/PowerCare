<?php

/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Eai\CEAITools;
use Ox\Interop\Ror\CRORException;
use Ox\Interop\Ror\CRORFactory;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Urgences\CExtractPassages;

CCanDo::checkAdmin();

$extract_passages_id = CView::get('extract_passages_id', 'ref class|CExtractPassages');
$limit               = CView::get('limit', 'num default|10');
CView::checkin();

if (isset($extractPassages) && $extractPassages->_id) {
    $extract_passages_id = $extractPassages->_id;
}

$extractPassages = new CExtractPassages();

// Appel de la fonction d'extraction du RPUSender
try {
    $rpuSender = CRORFactory::getSender();

    if ($extract_passages_id) {
        $extractPassages->load($extract_passages_id);
        if (!$extractPassages->_id) {
            CAppUI::stepAjax("Impossible de charger le document.", UI_MSG_ERROR);
        }

        $file = new CFile();
        $file->setObject($extractPassages);
        $file->loadMatchingObject();

        if (!$file->_id) {
            CAppUI::stepAjax("Impossible de récupérer le document.", UI_MSG_ERROR);
        }
        $tentative = 5;
        if ($extractPassages->type == "activite") {
            $rpuSender->transmitActivite($extractPassages);
        } elseif ($extractPassages->type == "tension") {
            $rpuSender->transmitTension($extractPassages);
        } elseif ($extractPassages->type == "deces") {
            $rpuSender->transmitDeces($extractPassages);
        } elseif ($extractPassages->type == "litsChauds") {
            $rpuSender->transmitLitsChauds($extractPassages);
        } elseif ($extractPassages->type == "urg") {
            $rpuSender->transmitUrg($extractPassages);
        } else {
            $rpuSender->transmitRPU($extractPassages);
        }

        if (!$extractPassages->date_echange) {
            $extractPassages->notifyError();
        }
    } else {
        $leftjoin                    = [];
        $leftjoin["files_mediboard"] = "files_mediboard.object_id = extract_passages.extract_passages_id
    AND files_mediboard.object_class = 'CExtractPassages'";

        $group_id     = CGroups::loadCurrent()->_id;
        $max_attempts = CAppUI::gconf('ror General max_attempts_skipped', $extractPassages->group_id);
        $date_max     = CMbDT::dateTime("-15 DAYS");

        $where                                    = [];
        $where["files_mediboard.file_id"]         = "IS NOT NULL";
        $where["extract_passages.date_echange"]   = "IS NULL";
        $where["extract_passages.date_extract"]   = " >= '$date_max'";
        $where['extract_passages.message_valide'] = " = '1'";
        $where['extract_passages.group_id']       = " = '" . $group_id . "'";
        $where[] = "`extract_passages`.`nb_tentatives` < " . $max_attempts
            . ' OR `extract_passages`.`nb_tentatives` IS NULL';

        $order = "extract_passages.date_extract DESC";

        /** @var CExtractPassages[] $passages */
        $passages = $extractPassages->loadList($where, $order, $limit, null, $leftjoin);
        foreach ($passages as $_passage) {
            // if error $extractPassages is treated in catch
            $extractPassages = $_passage;

            if ($_passage->type == "activite") {
                $rpuSender->transmitActivite($_passage);
            } elseif ($_passage->type == "deces") {
                $rpuSender->transmitDeces($_passage);
            } elseif ($_passage->type == "tension") {
                $rpuSender->transmitTension($_passage);
            } elseif ($_passage->type == "litsChauds") {
                $rpuSender->transmitLitsChauds($_passage);
            } elseif ($_passage->type == "urg") {
                $rpuSender->transmitUrg($_passage);
            } else {
                $rpuSender->transmitRPU($_passage);
            }

            if (!$_passage->date_echange) {
                $_passage->notifyError();
            }
        }
    }
} catch (CRORException $exception) {
    $extractPassages->notifyError();

    CAppUI::stepAjax($exception->getMessage(), UI_MSG_WARNING);
    CApp::log($exception->getMessage(), UI_MSG_ERROR);
}
