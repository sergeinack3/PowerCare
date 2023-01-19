<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CLegacyController;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Ccam\CCodeCCAM;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;
use Ox\Mediboard\Ccam\CFavoriCCAM;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CActeCCAM;
use Ox\Mediboard\System\CTag;
use Ox\Mediboard\System\CTagItem;

class FavorisLegacyController extends CLegacyController
{
    public function viewFavoris(): void
    {
        $this->checkPermRead();

        $_filter_class = CView::get("_filter_class", 'str');
        $tag_id       = CView::get("tag_id", 'num');

        CView::checkin();

        $tag_id = '' !== $tag_id ? (int)$tag_id : null;

        $list = [];
        $user = CUser::get();

        if (!$tag_id) {
            $actes = new CActeCCAM();
            $codes = $actes->getFavoris($user->_id, $_filter_class);

            foreach ($codes as $value) {
                $code = CDatedCodeCCAM::get($value["code_acte"]);
                $code->getChaps();

                $code->favoris_id = 0;
                $code->occ = $value["nb_acte"];
                $code->class = $value["object_class"];

                $chapitre =& $code->chapitres[0];

                if (!array_key_exists($chapitre['code'], $list)) {
                    $list[$chapitre['code']] = [
                        'nom' => $chapitre['code'],
                        'codes' => []
                    ];
                }
                $list[$chapitre["code"]]["codes"][$value["code_acte"]] = $code;
            }
        }

        $fusion = $list;

        $codesByChap = CFavoriCCAM::getOrdered($user->_id, $_filter_class, true, $tag_id);

        //Fusion des deux tableaux
        foreach ($codesByChap as $keychapter => $chapter) {
            if (!array_key_exists($keychapter, $fusion)) {
                $fusion[$keychapter] = $chapter;
                continue;
            }

            foreach ($chapter["codes"] as $keycode => $code) {
                if (!array_key_exists($keycode, $fusion[$keychapter]["codes"])) {
                    $fusion[$keychapter]["codes"][$keycode] = $code;
                    continue;
                }
                // Référence vers le favoris pour l'ajout de tags
                $fusion[$keychapter]["codes"][$keycode]->favoris_id = $code->favoris_id;
                $fusion[$keychapter]["codes"][$keycode]->_ref_favori = $code->_ref_favori;
            }
        }

        $tag_tree = CFavoriCCAM::getTree($user->_id);

        $favoris = new CFavoriCCAM();
        $favoris->_filter_class = $_filter_class;

        $this->renderSmarty('vw_idx_favoris.tpl', [
            'favoris' => $favoris,
            'list' => $list,
            'fusion' => $fusion,
            'codesByChap' => $codesByChap,
            'tag_tree' => $tag_tree,
            'tag_id' => $tag_id,
        ]);
    }

    public function countFavoris(): void
    {
        $this->checkPermAdmin();
        $user_id     = CView::get('user_id', 'ref class|CMediusers');
        $function_id = CView::get('function_id', 'ref class|CFunctions');

        CView::checkin();

        $favori = new CFavoriCCAM();

        if ($user_id) {
            $favori->favoris_user = $user_id;
        } elseif ($function_id) {
            $favori->favoris_function = $function_id;
        }

        $count = $favori->countMatchingList();

        if ($user_id || $function_id) {
            $data = ['count' => $count];
        } else {
            $data = ['count' => 0];
        }

        $this->renderJson($data);
    }

    public function storeFavoris(): void
    {
        $do = new CDoObjectAddEdit("CFavoriCCAM", "favoris_id");

        CView::checkin();

        // Amélioration des textes
        if ($favori_user = CView::post("favoris_user", 'ref class|CUser')) {
            $user = new CMediusers();
            $user->load($favori_user);
            $for = " pour $user->_view";
            $do->createMsg .= $for;
            $do->modifyMsg .= $for;
            $do->deleteMsg .= $for;
        } elseif ($favori_function = CView::post('favoris_function', 'ref class|CFunctions')) {
            $function = new CFunctions();
            $function->load($favori_function);
            $for = " pour $function->_view";
            $do->createMsg .= $for;
            $do->modifyMsg .= $for;
            $do->deleteMsg .= $for;
        }

        $do->redirect = null;

        $do->doIt();

        if (CAppUI::pref("new_search_ccam") == 1) {
            echo CAppUI::getMsg();
            CApp::rip();
        }
    }

    public function exportFavoris(): void
    {
        $this->checkPermAdmin();

        $user_id = CView::post('user_id', 'ref class|CMediusers');
        $function_id = CView::post('function_id', 'ref class|CFunctions');

        CView::checkin();

        $favori = new CFavoriCCAM();

        $type = '';
        $class = '';
        $id = '';
        $name = '';
        if ($user_id) {
            $favori->favoris_user = $user_id;
            $type = 'Utilisateur';
            $class = 'CMediusers';
            $id = $user_id;
            $user = CMediusers::get($user_id);
            $name = "$user->_user_last_name $user->_user_first_name";
        } elseif ($function_id) {
            $favori->favoris_function = $function_id;
            $type = 'Fonction';
            $class = 'CFunctions';
            $id = $function_id;
            /** @var CFunctions $function */
            $function = CMbObject::loadFromGuid("CFunctions-$function_id");
            $name = $function->text;
        }

        /** @var CFavoriCCAM[] $favoris */
        $favoris = $favori->loadMatchingList();
        $tag_items = CMbObject::massLoadBackRefs($favoris, 'tag_items');
        CMbObject::massLoadFwdRef($tag_items, 'tag_id');

        $file = new CCSVFile();

        $file->writeLine(
            array(
                'Type',
                'Propriétaire',
                'Tag',
                'Rang',
                'Code',
                'Type objet'
            )
        );

        foreach ($favoris as $favori) {
            $favori->loadRefsTagItems();

            $tags = array();
            foreach ($favori->_ref_tag_items as $tag) {
                $tags[] = $tag->_view;
            }

            $file->writeLine(
                array(
                    $class,
                    $id,
                    implode('|', $tags),
                    $favori->rang,
                    $favori->favoris_code,
                    $favori->object_class
                )
            );
        }

        $file->stream('favoris_ccam_' . str_replace(' ', '_', $name));
        CApp::rip();
    }

    public function importFavoris(): void
    {
        $this->checkPermAdmin();

        $user_id = CView::post('user_id', 'ref class|CMediusers');
        $function_id = CView::post('function_id', 'ref class|CFunctions');
        $file = CValue::files('formfile');

        CView::checkin();

        if (!array_key_exists('tmp_name', $file) || $file['tmp_name'][0] == '') {
            CAppUI::setMsg('Aucun fichier sélectionné', UI_MSG_ERROR);
            CAppUI::getMsg();
            CApp::rip();
        }

        $file = new CCSVFile($file['tmp_name'][0]);

        $imported = 0;
        $errors = 0;
        if ($file->countLines()) {
            $file->setColumnNames(['owner_type', 'owner_id', 'tags', 'rank', 'code', 'object_class']);

            $file->jumpLine(1);

            while ($line = $file->readLine(true, true)) {
                $favori = new CFavoriCCAM();

                if ($user_id) {
                    $favori->favoris_user = $user_id;
                } elseif ($function_id) {
                    $favori->favoris_user = $function_id;
                } elseif (in_array(strtolower($line['owner_type']), ['user', 'utilisateur', 'cuser'])) {
                    $favori->favoris_user = $line['owner_id'];
                } elseif (in_array(strtolower($line['owner_type']), ['fonction', 'function', 'cfunctions'])) {
                    $favori->favoris_user = $line['owner_id'];
                }

                if ($line['rank']) {
                    $favori->rang = $line['rank'];
                }

                $code = CCodeCCAM::get(trim($line['code']));
                if ($code->code == '-') {
                    $errors++;
                    continue;
                }

                $favori->favoris_code = $code->code;

                if ($line['object_class']) {
                    if (in_array(strtolower($line['object_class']), ['consultation', 'consult', 'cconsultation'])) {
                        $favori->object_class = 'CConsultation';
                    }
                    if (in_array(strtolower($line['object_class']), ['sejour', 'csejour'])) {
                        $favori->object_class = 'CSejour';
                    } else {
                        $favori->object_class = 'COperation';
                    }
                }

                $favori->loadMatchingObject();

                if ($msg = $favori->store()) {
                    $errors++;
                    continue;
                }

                if ($line['tags']) {
                    $tags = explode('|', $line['tags']);

                    foreach ($tags as $tag_name) {
                        $parent = false;
                        /* Gestion des tags parents */
                        if (strpos($tag_name, '&raquo;')) {
                            [$parent_name, $tag_name] = explode(' &raquo; ', $tag_name);
                            $parent = new CTag();
                            $parent->name = $parent_name;
                            $parent->object_class = $favori->_class;
                            $parent->loadMatchingObject();
                            if ($msg = $parent->store()) {
                                continue;
                            }
                        }

                        $tag = new CTag();
                        $tag->name = $tag_name;
                        $tag->object_class = $favori->_class;

                        if ($parent) {
                            $tag->parent_id = $parent->_id;
                        }

                        $tag->loadMatchingObject();
                        if ($msg = $tag->store()) {
                            continue;
                        }

                        $tag_item = new CTagItem();
                        $tag_item->tag_id = $tag->_id;
                        $tag_item->object_id = $favori->_id;
                        $tag_item->object_class = $favori->_class;

                        $tag_item->loadMatchingObject();
                        $tag_item->store();
                    }
                }

                $imported++;
            }
        }

        $file->close();

        if ($errors) {
            CAppUI::setMsg("$errors favoris en erreur", UI_MSG_ERROR);
        }

        if ($imported) {
            CAppUI::setMsg("$imported favoris importés", UI_MSG_OK);
        }

        echo CAppUI::getMsg();
    }
}
