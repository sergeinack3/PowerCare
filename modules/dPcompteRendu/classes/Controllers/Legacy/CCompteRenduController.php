<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu\Controllers\Legacy;

use Exception;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbDT;
use Ox\Core\CMbPDFMerger;
use Ox\Core\CModelObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CModeleToPack;
use Ox\Mediboard\CompteRendu\CompteRenduFieldReplacer;
use Ox\Mediboard\CompteRendu\CPack;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Files\MailReceiverService;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSecondaryFunction;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CElementPrescription;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Printing\CPrinter;
use Ox\Mediboard\System\CExchangeSource;
use Psr\SimpleCache\InvalidArgumentException;
use ZipArchive;
use Ox\Core\CMbPath;

/**
 * Description
 */
class CCompteRenduController extends CLegacyController
{
    /**
     * Génération du PDF d'un compte-rendu et stream au client ou envoi vers une imprimante réseau
     */
    public function streamPDF(): void
    {
        $file_id = CView::get("file_id", "ref class|CFile");

        CView::checkin();

        $file = CFile::findorFail($file_id);

        // Mise à jour de la date d'impression
        $cr = CCompteRendu::findOrFail($file->object_id);
        $cr->loadContent();
        $cr->date_print = "now";

        if ($msg = $cr->store()) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);
        }
        $file->streamFile();
    }

    /**
     * Création / Modification d'un document (généré à partir d'un modèle)
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     */
    public function edit(array $params = []): ?int
    {
        $compte_rendu_id = CView::get("compte_rendu_id", 'ref class|CCompteRendu');
        $modele_id       = $params["modele_id"] ?? CView::get("modele_id", 'ref class|CCompteRendu');
        $pack_id         = CView::get("pack_id", 'ref class|CPack');
        $object_id       = CView::get("object_id", 'str');
        $switch_mode     = CView::get("switch_mode", 'bool default|0');
        $target_id       = $params["target_id"] ?? CView::get("target_id", 'str');
        $target_class    = $params["target_class"] ?? CView::get("target_class", 'str');
        $force_fast_edit = CView::get("force_fast_edit", 'bool default|0');
        $store_headless  = $params["store_headless"] ?? CView::get("store_headless", "bool default|0");
        $ext_cabinet_id  = CView::get("ext_cabinet_id", "num");

        /* Optionnal fields */
        $object_class = CView::get("object_class", 'str');
        $object_guid  = CView::get("object_guid", 'str');
        $unique_id    = CView::get("unique_id", 'str');
        $reload_zones = CView::get("reloadzones", 'bool default|0');

        CView::checkin();

        // Faire ici le test des différentes variables dont on a besoin
        $compte_rendu = new CCompteRendu();

        $curr_user   = CMediusers::get();
        $user_opener = new CMediusers();

        // Modification d'un document
        if ($compte_rendu_id) {
            $compte_rendu->load($compte_rendu_id);
            if (!$compte_rendu->_id) {
                CAppUI::stepAjax(CAppUI::tr("CCompteRendu-alert_doc_deleted"));
                CApp::rip();
            }
            $compte_rendu->loadContent();
            $compte_rendu->loadComponents();
            $compte_rendu->loadFile();

            $cache          = Cache::getCache(Cache::DISTR);
            $user_opened_id = $cache->get(
                CCompteRendu::CACHE_KEY_OPENER . '-' . $compte_rendu->_id,
                null,
                CCompteRendu::CACHE_TTL_OPENER
            );

            // Déjà ouvert par un autre utilisateur
            if ($user_opened_id && $curr_user->_id != $user_opened_id) {
                $user_opener->load($user_opened_id)->loadRefFunction();
            } elseif (!$cache->has(CCompteRendu::CACHE_KEY_OPENER . $compte_rendu->_id) || !$user_opened_id) {
                // Premier accesseur
                $cache->set(
                    CCompteRendu::CACHE_KEY_OPENER . '-' . $compte_rendu->_id,
                    $curr_user->_id,
                    CCompteRendu::CACHE_TTL_OPENER
                );
            }
        } elseif ($modele_id == 0 && !$pack_id) {
            // Création à partir d'un modèle vide
            $compte_rendu->valueDefaults();
            $compte_rendu->object_id    = $object_id;
            $compte_rendu->object_class = $target_class;
            $compte_rendu->_ref_object  = new $target_class();
            $compte_rendu->_ref_object->load($object_id);
            $compte_rendu->updateFormFields();
        } else {
            // Création à partir d'un modèle
            $compte_rendu->load($modele_id);
            $compte_rendu->loadFile();
            $compte_rendu->loadContent();
            $compte_rendu->_id         = null;
            $compte_rendu->function_id = null;
            $compte_rendu->group_id    = null;
            $compte_rendu->object_id   = $object_id;
            $compte_rendu->_ref_object = null;
            $compte_rendu->modele_id   = $modele_id;
            $header_id                 = null;
            $footer_id                 = null;

            // Utilisation des headers/footers
            if ($compte_rendu->header_id || $compte_rendu->footer_id) {
                $header_id = $compte_rendu->header_id;
                $footer_id = $compte_rendu->footer_id;
            }

            // On fournit la cible
            if ($target_id && $target_class) {
                $compte_rendu->object_id    = $target_id;
                $compte_rendu->object_class = $target_class;
            }

            // A partir d'un pack
            if ($pack_id) {
                $pack = new CPack();
                $pack->load($pack_id);

                $pack->loadContent();
                $compte_rendu->nom              = $pack->nom;
                $compte_rendu->object_class     = $pack->object_class;
                $compte_rendu->file_category_id = $pack->category_id;
                $compte_rendu->fast_edit        = $pack->fast_edit;
                $compte_rendu->fast_edit_pdf    = $pack->fast_edit_pdf;
                $compte_rendu->_source          = $pack->_source;
                $compte_rendu->modele_id        = null;

                $pack->loadHeaderFooter();

                $header_id = $pack->_header_found->_id;
                $footer_id = $pack->_footer_found->_id;

                // Marges et format
                /** @var $links CModeleToPack[] */
                $links                             = $pack->_back['modele_links'];
                $first_modele                      = reset($links);
                $first_modele                      = $first_modele->_ref_modele;
                $compte_rendu->factory             = $first_modele->factory;
                $compte_rendu->margin_top          = $first_modele->margin_top;
                $compte_rendu->margin_left         = $first_modele->margin_left;
                $compte_rendu->margin_right        = $first_modele->margin_right;
                $compte_rendu->margin_bottom       = $first_modele->margin_bottom;
                $compte_rendu->page_height         = $first_modele->page_height;
                $compte_rendu->page_width          = $first_modele->page_width;
                $compte_rendu->font                = $first_modele->font;
                $compte_rendu->size                = $first_modele->size;
                $compte_rendu->send                = $first_modele->send;
                $compte_rendu->signature_mandatory = $first_modele->signature_mandatory;
            }
            $compte_rendu->_source = $compte_rendu->generateDocFromModel(null, $header_id, $footer_id);
            $compte_rendu->updateFormFields();
        }

        $compte_rendu->loadRefsFwd();

        $compte_rendu->loadRefPrinter();

        $compte_rendu->_ref_object->loadRefsFwd();
        $object =& $compte_rendu->_ref_object;

        if (!$compte_rendu->_id) {
            if (!$compte_rendu->font) {
                $compte_rendu->font = array_search(
                    CAppUI::conf("dPcompteRendu CCompteRendu default_font"),
                    CCompteRendu::$fonts
                );
            }

            if (!$compte_rendu->size) {
                $compte_rendu->size = CAppUI::gconf("dPcompteRendu CCompteRendu default_size");
            }

            $compte_rendu->guessSignataire();
        } else {
            $compte_rendu->getDeliveryStatus();
        }

        // Calcul du user concerné
        $user = $curr_user;

        // Chargement dans l'ordre suivant pour les listes de choix si null :
        // - user courant
        // - anesthésiste
        // - praticien de la consultation
        if (!$user->isPraticien()) {
            $user    = new CMediusers();
            $user_id = null;

            switch ($object->_class) {
                case "CConsultAnesth":
                    /** @var $object CConsultAnesth */
                    $operation = $object->loadRefOperation();
                    $anesth    = $operation->_ref_anesth;
                    if ($operation->_id && $anesth->_id) {
                        $user_id = $anesth->_id;
                    }

                    if ($user_id == null) {
                        $user_id = $object->_ref_consultation->_praticien_id;
                    }
                    break;

                case "CConsultation":
                    /** @var $object CConsultation */
                    $user_id = $object->loadRefPraticien()->_id;
                    break;

                case "CSejour":
                    /** @var $object CSejour */
                    $user_id = $object->praticien_id;
                    break;

                case "COperation":
                    /** @var $object COperation */
                    $user_id = $object->chir_id;
                    break;

                default:
                    $user_id = $curr_user->_id;
            }

            $user->load($user_id);
        }

        $function = $user->loadRefFunction();

        // Chargement des catégories
        $listCategory = CFilesCategory::listCatClass($compte_rendu->object_class);
        if ($compte_rendu->object_class === "CEvenementPatient" && CModule::getActive("oxCabinet") && !$compte_rendu->file_category_id) {
            $compte_rendu->loadTargetObject();
            $categorie                      = CAppUI::gconf(
                "oxCabinet CEvenementPatient categorie_{$compte_rendu->_ref_object->type}_default"
            );
            $compte_rendu->file_category_id = $categorie;
        }

        // Décompte des imprimantes disponibles pour l'impression serveur
        $nb_printers = $curr_user->loadRefFunction()->countBackRefs("printers");

        // Gestion du template
        $templateManager           = new CTemplateManager();
        $templateManager->isModele = false;
        $templateManager->document = $compte_rendu->_source;
        $object->fillTemplate($templateManager);
        $templateManager->loadHelpers($user->_id, $compte_rendu->object_class, $curr_user->function_id);
        $templateManager->loadLists($user->_id, $modele_id ? $modele_id : $compte_rendu->modele_id);

        // Cas spécial des documents appliqués sur un protocole de prescription ou un élément de prescription :
        // se comporte comme un modèle.
        if (($object instanceof CPrescription && !$object->object_id) || $object instanceof CElementPrescription) {
            $templateManager->isModele  = true;
            $templateManager->valueMode = false;
        } else {
            $templateManager->applyTemplate($compte_rendu);
        }

        $creation = false;

        // Enregistrement du document si création
        if (!$compte_rendu->_id && ($store_headless || (!$compte_rendu->fast_edit && !$compte_rendu->fast_edit_pdf))) {
            $compte_rendu->content_id = "";
            $compte_rendu->_source    = $templateManager->document;
            $compte_rendu->user_id    = $compte_rendu->function_id = $compte_rendu->group_id = '';

            $creation = true;
        }

        if ($store_headless) {
            $compte_rendu->store();

            return $compte_rendu->_id;
        }

        $lists = $templateManager->getUsedLists($templateManager->allLists);

        // Afficher le bouton correpondant si on détecte un élément de publipostage
        $isCourrier = $templateManager->isCourrier();

        $destinataires = [];
        if ($isCourrier) {
            $destinataires = (new MailReceiverService($object))->getReceivers();
        }

        $can_lock      = $compte_rendu->canLock();
        $can_unclock   = $compte_rendu->canUnlock();
        $can_duplicate = $compte_rendu->canDuplicate();
        $compte_rendu->isLocked();
        $lock_bloked = $compte_rendu->_is_locked ? !$can_unclock : !$can_lock;
        if ($compte_rendu->valide && !CAppUI::gconf("dPcompteRendu CCompteRendu unlock_doc")) {
            $lock_bloked = 1;
        }
        $compte_rendu->canDo();
        $read_only = $compte_rendu->_is_locked || !$compte_rendu->_can->edit;

        if ($compte_rendu->_is_locked) {
            $templateManager->printMode = true;
        }
        if ($compte_rendu->_id && !$compte_rendu->canEdit()) {
            $templateManager->printMode = true;
        }

        /* Set the object_class if not passed in the get parameters */
        if (!$object_class) {
            $object_class = $compte_rendu->object_class;
        }

        $compte_rendu->_ext_cabinet_id = $ext_cabinet_id;
        if ($compte_rendu_id) {
            $compte_rendu->loadLastRefStatutCompteRendu();
        }

        $tpl_vars = [
            'listCategory'  => $listCategory,
            'compte_rendu'  => $compte_rendu,
            'modele_id'     => $modele_id,
            'curr_user'     => $curr_user,
            'user_opener'   => $user_opener,
            'lists'         => $lists,
            'isCourrier'    => $isCourrier,
            'user_id'       => $user->_id,
            'user_view'     => $user->_view,
            'object_id'     => $object_id,
            'object_class'  => $object_class,
            'nb_printers'   => $nb_printers,
            'pack_id'       => $pack_id,
            'destinataires' => $destinataires,
            'lock_bloked'   => $lock_bloked,
            'can_duplicate' => $can_duplicate,
            'read_only'     => $read_only,
            'unique_id'     => $unique_id,
            'creation'      => $creation,
        ];

        preg_match_all("/(:?\[\[Texte libre - ([^\]]*)\]\])/i", $compte_rendu->_source, $matches);

        $templateManager->textes_libres = $matches[2];

        // Suppression des doublons
        $templateManager->textes_libres = array_unique($templateManager->textes_libres);

        if (isset($compte_rendu->_ref_file->_id)) {
            $tpl_vars['file'] = $compte_rendu->_ref_file;
        }

        $tpl_vars['textes_libres'] = $templateManager->textes_libres;

        $exchange_source             = CExchangeSource::get("mediuser-" . $curr_user->_id);
        $tpl_vars['exchange_source'] = $exchange_source;

        // Ajout d'entête / pied de page à la volée
        $headers = [];
        $footers = [];

        if (CAppUI::gconf("dPcompteRendu CCompteRendu header_footer_fly")) {
            $headers = CCompteRendu::loadAllModelesFor($user->_id, "prat", $compte_rendu->object_class, "header");
            $footers = CCompteRendu::loadAllModelesFor($user->_id, "prat", $compte_rendu->object_class, "footer");
        }

        $tpl_vars['headers'] = $headers;
        $tpl_vars['footers'] = $footers;

        // Nettoyage des balises meta et link.
        // Pose problème lors de la présence d'un entête et ou/pied de page
        $source = &$templateManager->document;

        $source = preg_replace("/<meta\s*[^>]*\s*[^\/]>/", '', $source);
        $source = preg_replace("/(<\/meta>)+/i", '', $source);
        $source = preg_replace("/<link\s*[^>]*\s*>/", '', $source);

        $pdf_and_thumbs = CAppUI::pref("pdf_and_thumbs");

        // Chargement du modèle
        if ($compte_rendu->_id) {
            $compte_rendu->loadModele();
        }

        if ($reload_zones == 1) {
            $this->renderSmarty('inc_zones_fields', $tpl_vars);
        } elseif (
            !$compte_rendu_id && !$switch_mode
            && ($compte_rendu->fast_edit || $force_fast_edit || ($compte_rendu->fast_edit_pdf && $pdf_and_thumbs))
        ) {
            $printers = $function->loadBackRefs("printers") ?? [];

            /** @var $_printer CPrinter */
            foreach ($printers as $_printer) {
                $_printer->loadTargetObject();
            }

            $tpl_vars['_source']     = $templateManager->document;
            $tpl_vars['printers']    = $printers;
            $tpl_vars['object_guid'] = $object_guid;

            $this->renderSmarty('fast_mode', $tpl_vars);
        } else {
            // Charger le document précédent et suivant
            $prevnext = [];
            if ($compte_rendu->_id) {
                $object->loadRefsDocs();
                $prevnext = CMbArray::getPrevNextKeys($object->_ref_documents, $compte_rendu->_id);
            }

            $templateManager->initHTMLArea();
            $tpl_vars['switch_mode']     = $switch_mode;
            $tpl_vars['templateManager'] = $templateManager;
            $tpl_vars['prevnext']        = $prevnext;
            $this->renderSmarty('edit_compte_rendu', $tpl_vars);
        }

        return null;
    }

    /**
     * Call pour les éditions rapides
     * Cela permet de les retrouver dans les access logs
     */
    public function editFast(): void
    {
        (new CCompteRenduController())->edit();
    }

    public function autocomplete(): void
    {
        $this->checkPermRead();

        $user_id       = CView::get("user_id", "ref class|CMediusers");
        $function_id   = CView::get("function_id", "ref class|CFunctions");
        $object_class  = CView::get("object_class", "str");
        $object_id     = CView::get("object_id", "num");
        $keywords      = CView::get("keywords_modele", "str");
        $fast_edit     = CView::get("fast_edit", "bool default|1");
        $mode_store    = CView::get("mode_store", "bool default|1");
        $modele_vierge = CView::get("modele_vierge", "bool default|1");
        $type          = CView::get("type", "str");
        $appFine       = CView::get("appFine", "bool default|0");

        CView::checkin();

        CView::enableSlave();

        $compte_rendu = new CCompteRendu();

        $modeles = [];
        $favoris = [];

        $where = [
            "actif"     => "= '1'",
            'object_id' => 'IS NULL',
        ];

        if (!$fast_edit) {
            $where["fast_edit"]     = " = '0'";
            $where["fast_edit_pdf"] = " = '0'";
        }

        if ($mode_store) {
            $where["type"] = "= 'body'";
        } elseif ($type) {
            $where["type"] = "= '$type'";
        }

        if ($object_class) {
            $where["object_class"] = "= '$object_class'";
        }

        $module     = CModule::getActive("dPcompteRendu");
        $is_admin   = $module && $module->canAdmin();
        $is_cabinet = CAppUI::isCabinet();

        // Niveau utilisateur
        if ($user_id) {
            $user = CMediusers::get($user_id);
            $user->loadRefFunction();

            $users_ids = [$user->_id];

            $curr_user = CMediusers::get();

            if ($mode_store) {
                $users_ids[] = $curr_user->_id;
            }

            if ($object_class) {
                switch ($object_class) {
                    default:
                        break;
                    case "COperation":
                        $object = new $object_class();
                        $object->load($object_id);
                        $fields_chir = ["chir_id", "chir_2_id", "chir_3_id", "chir_4_id"];

                        foreach ($fields_chir as $_field_chir) {
                            if ($object->$_field_chir) {
                                $users_ids[] = $object->$_field_chir;
                            }
                        }
                }
            }

            $users_ids = array_unique($users_ids);

            foreach ($users_ids as $key => $_user_id) {
                if ($_user_id == $curr_user->_id) {
                    continue;
                }
                $_user = CMediusers::get($_user_id);
                if (!$_user->getPerm(PERM_EDIT)) {
                    unset($users_ids[$key]);
                }
            }

            $where["user_id"] = CSQLDataSource::prepareIn($users_ids);

            $modeles = $compte_rendu->seek($keywords, $where, 100, false, null, "nom");

            if (CAppUI::pref("show_favorites")) {
                $ds            = CSQLDataSource::get('std');
                $where_favoris = $where;
                unset($where_favoris["user_id"]);
                unset($where_favoris["object_id"]);
                $where_favoris["author_id"] = "= '" . CMediusers::get()->_id . "'";
                $request                    = new CRequest(false);
                $request->addSelect(["modele_id", "COUNT(*)"]);
                $request->addTable("compte_rendu");
                $request->addWhere($where_favoris);
                $request->addGroup("modele_id");
                $request->addOrder("COUNT(*) DESC");
                $request->setLimit("10");
                $favoris = $ds->loadList($request->makeSelect());
            }
        }

        // Niveau fonction
        // Inclusion des fonctions secondaires de l'utilisateur connecté
        // et de l'utilisateur concerné
        unset($where["user_id"]);

        $function_ids = [];

        if ($is_admin || !$is_cabinet || CAppUI::gconf("dPcompteRendu CCompteRenduAcces access_function")) {
            if (CModule::getActive('appFineClient') && $appFine && $is_admin) {
                $function_appFine          = new CFunctions();
                $ds                        = $function_appFine->getDS();
                $where_appFine             = [];
                $where_appFine['group_id'] = $ds->prepare('= ?', CGroups::loadCurrent()->_id);
                $function_ids              = $function_appFine->loadIds($where_appFine);
            } elseif ($user_id) {
                $sec_function            = new CSecondaryFunction();
                $whereSecFunc            = [];
                $whereSecFunc["user_id"] = " = '$curr_user->_id'";
                if ($user->getPerm(PERM_EDIT)) {
                    $whereSecFunc["user_id"] = "IN ('$user->_id', '$curr_user->_id')";
                }

                $function_sec = $sec_function->loadList($whereSecFunc);
                $function_ids = array_merge(
                    CMbArray::pluck($function_sec, "function_id"),
                    [$user->function_id, $curr_user->function_id]
                );
            } else {
                $function_ids = [$function_id];
            }

            $where["function_id"] = CSQLDataSource::prepareIn($function_ids);
            $modeles              = array_merge(
                $modeles,
                $compte_rendu->seek($keywords, $where, 100, false, null, "nom")
            );
        }

        // Niveau établissement
        // Inclusion de l'établissement courant si différent de l'établissement de l'utilisateur
        if ($is_admin || !$is_cabinet || CAppUI::gconf("dPcompteRendu CCompteRenduAcces access_group")) {
            unset($where["function_id"]);

            if ($function_id && !$user_id) {
                $function = new CFunctions();
                $function->load($function_id);
                $where["group_id"] = "= '$function->group_id'";
            } else {
                $where["group_id"] = CSQLDataSource::prepareIn($user->_group_id, CGroups::loadCurrent()->_id);
            }

            $modeles = array_merge($modeles, $compte_rendu->seek($keywords, $where, 100, false, null, "nom"));

            $modeles = CModelObject::naturalSort($modeles, ["nom"], true);
        }

        // Niveau instance
        unset($where["group_id"]);

        $where[] = "user_id IS NULL AND function_id IS NULL AND group_id IS NULL AND object_id IS NULL";

        $modeles = array_merge($modeles, $compte_rendu->seek($keywords, $where, 100, false, null, "nom"));

        if (CModule::getActive('appFineClient') && $appFine && $is_admin) {
            foreach ($modeles as $_modele) {
                if (!$_modele->function_id) {
                    continue;
                }
                $_modele->loadRefFunction();
            }
        }

        $modeles = CModelObject::naturalSort($modeles, ["nom"], true);

        if ($favoris) {
            $modeles_with_favorites = [];
            foreach ($favoris as $_modele) {
                if (array_key_exists($_modele["modele_id"], $modeles)) {
                    $modeles_with_favorites[$_modele["modele_id"]]                = $modeles[$_modele["modele_id"]];
                    $modeles_with_favorites[$_modele["modele_id"]]->_utilisations = $_modele["COUNT(*)"];
                }
            }
            foreach ($modeles as $_id => $_modele) {
                if (!isset($modeles_with_favorites[$_id])) {
                    $modeles_with_favorites[$_id] = $_modele;
                }
            }

            $modeles = $modeles_with_favorites;
        }

        $this->renderSmarty(
            'inc_modele_autocomplete',
            [
                'modeles'       => $modeles,
                'nodebug'       => true,
                'keywords'      => $keywords,
                'modele_vierge' => $modele_vierge,
                'appFine'       => $appFine,
            ]
        );
    }

    /**
     * Printing a selection of documents
     */
    public function print_docs(): void
    {
        $this->checkPermRead();

        $nbDoc   = CView::get("nbDoc", "str");
        $nbFile  = CView::get("nbFile", "str");
        $nbPresc = CView::get("nbPresc", "str");

        CView::checkin();

        $documents = [];
        $tmp_files = [];
        $pdf       = new CMbPDFMerger();

        if (is_array($nbDoc)) {
            CMbArray::removeValue("0", $nbDoc);
        }

        if (is_array($nbFile)) {
            CMbArray::removeValue("0", $nbFile);
        }

        if (is_array($nbPresc)) {
            CMbArray::removeValue("0", $nbPresc);
        }

        if ((!$nbDoc || !count($nbDoc)) && (!$nbFile || !count($nbFile)) && (!$nbPresc || !count($nbPresc))) {
            CAppUI::stepAjax("Aucun document à imprimer !");
            CApp::rip();
        }

        if (is_countable($nbDoc) && count($nbDoc)) {
            $compte_rendu = new CCompteRendu();
            $where        = ["compte_rendu_id" => CSQLDataSource::prepareIn(array_keys($nbDoc))];

            /** @var $_compte_rendu CCompteRendu */
            foreach ($compte_rendu->loadList($where) as $_compte_rendu) {
                $_compte_rendu->date_print = CMbDT::dateTime();
                $_compte_rendu->store();
                $_compte_rendu->makePDFpreview(1);

                $nb_print = $nbDoc[$_compte_rendu->_id];
                for ($i = 1; $i <= $nb_print; $i++) {
                    $pdf->addPDF($_compte_rendu->_ref_file->_file_path);
                }
            }
        }

        if (is_countable($nbFile) && count($nbFile)) {
            $file  = new CFile();
            $where = ["file_id" => CSQLDataSource::prepareIn(array_keys($nbFile))];

            /** @var $_file CFile */
            foreach ($file->loadList($where) as $_file) {
                $nb_print = $nbFile[$_file->_id];
                for ($i = 1; $i <= $nb_print; $i++) {
                    $tmp_file     = tempnam("./tmp", "file");
                    $tmp_file_pdf = $tmp_file . ".pdf";
                    $tmp_files[]  = $tmp_file_pdf;
                    $pdf->addPDF($pdf->convertPDToVersion14($_file->file_type, $tmp_file_pdf, $_file->_file_path));
                }
            }
        }

        if (is_countable($nbPresc) && count($nbPresc)) {
            $prescription = new CPrescription();
            $where        = ["prescription_id" => CSQLDataSource::prepareIn(array_keys($nbPresc))];
            foreach ($prescription->loadList($where) as $_prescription) {
                $prats = [$_prescription->_ref_object->praticien_id];

                // Une ordonnance par praticien prescripteur
                $_prescription->loadRefsLinesMedComments("0", "0", "1", "", "", "1");
                $_prescription->loadRefsLinesElementsComments("0", "0", "", "", "", "1");
                $_prescription->loadRefsPrescriptionLineMixes("", "0", "0", "1", "", "1");

                $prats = array_merge($prats, CMbArray::pluck($_prescription->_ref_prescription_lines, "praticien_id"));
                $prats = array_merge($prats, CMbArray::pluck($_prescription->_ref_prescription_line_mixes, "praticien_id"));
                $prats = array_merge($prats, CMbArray::pluck($_prescription->_ref_prescription_lines_element, "praticien_id"));

                $prats = array_unique($prats);

                foreach ($prats as $_prat_id) {
                    $params = [
                        "prescription_id"     => $_prescription->_id,
                        "praticien_sortie_id" => $_prat_id,
                        "stream"              => 0,
                    ];

                    CApp::fetch("dPprescription", "print_prescription_fr", $params);

                    $file = new CFile();

                    $where = [
                        "object_class" => "= '" . $_prescription->_ref_object->_class . "'",
                        "object_id"    => "= '" . $_prescription->_ref_object->_id . "'",
                        "file_name"    => "LIKE 'Ordonnance %'",
                    ];

                    if (!$file->loadObject($where, "file_id DESC")) {
                        continue;
                    }

                    $nb_print = $nbPresc[$_prescription->_id];
                    for ($i = 1; $i <= $nb_print; $i++) {
                        $pdf->addPDF($file->_file_path);
                    }
                }
            }
        }

        // Stream du PDF au client avec ouverture automatique
        // Si aucun pdf, alors PDFMerger génère une exception que l'on catche
        try {
            $pdf->merge("browser", "documents.pdf");
        } catch (Exception $e) {
            $this->rip();
        }

        foreach ($tmp_files as $_tmp_file) {
            unlink($_tmp_file);
        }
    }

    public function viewFields(): void
    {
        $this->checkPerm();

        $sections     = CView::get('sections', 'str');
        $max_sections = CView::get('max_sections', 'num default|3');

        CView::checkin();

        $this->renderSmarty('view_fields', ['sections' => $sections, 'max_sections' => $max_sections]);
    }

    public function viewImport(): void
    {
        $this->checkPermRead();

        $owner_guid = CView::request("owner_guid", "str");

        CView::checkin();

        $this->renderSmarty(
            'inc_vw_import_modele',
            [
                'owner'   => $owner_guid === "Instance" ?
                    CCompteRendu::getInstanceObject() : CMbObject::loadFromGuid($owner_guid),
                'classes' => CCompteRendu::getTemplatedClasses(),
            ]
        );
    }

    public function importModeles(): void
    {
        $this->checkPermRead();

        $owner_guid   = CView::post('owner_guid', 'str');
        $object_class = CView::post('object_class', 'str');

        CView::checkin();

        $owner = $owner_guid === 'Instance' ? CCompteRendu::getInstanceObject() : CMbObject::loadFromGuid($owner_guid);

        if (!$owner || !$owner->_id) {
            CAppUI::stepMessage(UI_MSG_WARNING, 'CCompteRendu-import-Owner wish not found');
        }

        $user_id     = "";
        $function_id = "";
        $group_id    = "";

        switch ($owner->_class) {
            case "CMediusers":
                $user_id = $owner->_id;
                break;
            case "CFunctions":
                $function_id = $owner->_id;
                break;
            case "CGroups":
                $group_id = $owner->_id;
                break;
            default:
                // No owner
                break;
        }
        $file = $_FILES["datafile"];

        if (strtolower(pathinfo($file["name"], PATHINFO_EXTENSION) !== "xml")) {
            CAppUI::stepAjax('CCompteRendu-import-File unknown', UI_MSG_ERROR);
            CApp::rip();
        }

        $doc = file_get_contents($file['tmp_name']);

        $xml = new CMbXMLDocument(null);
        $xml->loadXML($doc);

        $root = $xml->firstChild;

        if ($root->nodeName == 'modeles') {
            $root = $root->childNodes;
        } else {
            $root = [$xml->firstChild];
        }

        $modeles_ids = [];

        CCompteRendu::$import = true;

        foreach ($root as $_modele) {
            $modele = CCompteRendu::importModele($_modele, $user_id, $function_id, $group_id, $object_class, $modeles_ids);

            CAppUI::stepAjax($modele->nom . " - " . CAppUI::tr('CCompteRendu-msg-create'), UI_MSG_OK);
        }

        CCompteRendu::$import = false;

        CAppUI::js("window.opener.getForm('filterModeles').onsubmit()");
    }

    /**
     * Affiche la liste des référentiels pour le plugin CKEditor mbbenchmark
     *
     * @throws Exception
     */
    public function viewBenchmark(): void
    {
        $this->checkPermRead();

        CView::checkIn();

        $benchmarks = [];
        if (CModule::getActive("dPcim10")) {
            $benchmarks[] = "CIM10";
            $benchmarks[] = "DRC";
            $benchmarks[] = "CISP";
        }

        if (CModule::getActive("loinc")) {
            $benchmarks[] = "LOINC";
        }

        if (CModule::getActive("dPccam")) {
            $benchmarks[] = "CCAM";
        }

        if (CModule::getActive("dPmedicament")) {
            $benchmarks[] = "ATC";
        }

        $this->renderSmarty(
            'inc_vw_benchmark',
            [
                "benchmarks" => $benchmarks,
            ]
        );
    }

    /**
     * Crée un document zip avec les documents séléctionné en format PDF
     * @throws Exception
     */
    public function downloadZipFile(): void
    {
        $this->checkPermRead();
        $nbDoc = CView::get("nbDoc", "str");
        CView::checkin();

        //Creation de l'archive dans les fichiers tmp
        $user          = CMediusers::get();
        $function_name = $user->loadRefFunction()->text;
        $tempdir       = CAppUI::getTmpPath("recherche_documentaire_$user->_id");
        CMbPath::forceDir($tempdir);

        $zipname = "$tempdir/archive.zip";
        $archive = new ZipArchive();
        $archive->open($zipname, ZipArchive::CREATE);

        //Ajout des documents dans l'archive
        $compte_rendu = new CCompteRendu();
        $where        = ["compte_rendu_id" => CSQLDataSource::prepareIn(array_keys($nbDoc))];
        $name_files   = [];

        foreach ($compte_rendu->loadList($where) as $_compte_rendu) {
            $_compte_rendu->makePDFpreview(true, false);
            $tmp_file_name = basename($_compte_rendu->_ref_file->file_name, ".pdf");

            //Si un fichier a le même nom qu'un fichier existant on le renomme (ex: si tmp2.pdf
            // existe le prochain sera nommé tmp2(2).pdf

            if (array_key_exists($tmp_file_name, $name_files)) {
                $name_files[$tmp_file_name] += 1;
                $tmp_file_name              = $tmp_file_name . "(" . $name_files[$tmp_file_name] . ").pdf";
            } else {
                $name_files[$tmp_file_name] = 1;
                $tmp_file_name              = $tmp_file_name . ".pdf";
            }
            $archive->addFile($_compte_rendu->_ref_file->_file_path, $tmp_file_name);
        }

        $archive->close();
        $output_name = sprintf(
            "Recherche documentaire du %s - %s.zip",
            CMbDT::date(),
            strtr($function_name, './\\"\'', '')
        );

        ob_end_clean();
        header('Content-Type: application/zip');
        header(sprintf('Content-Disposition: attachment; filename="%s"', $output_name));
        readfile($zipname);

        //Suppresion de l'archive
        unlink($zipname);
        rmdir($tempdir);

        CApp::rip();
    }

    /**
     * Ouvre une modale qui liste tous les statuts du compte-rendu
     * @throws Exception
     */
    public function showAllStatut(): void
    {
        $this->checkPermRead();
        $compte_rendu_id = CView::get("compte_rendu_id", 'ref class|CCompteRendu');
        CView::checkin();

        $compte_rendu = CCompteRendu::find($compte_rendu_id);
        $compte_rendu->loadRefStatutCompteRendu();

        $this->renderSmarty(
            'inc_statuts_compte_rendu',
            [
                'statuts' => $compte_rendu->_ref_statut_compte_rendu,
            ]
        );
    }

    /**
     * Delete in cache the user who opened the document (triggered when closing the popup)
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function clearOpener(): void
    {
        $this->checkPerm();

        $compte_rendu_id = CView::get('compte_rendu_id', 'ref class|CCompteRendu');

        CView::checkin();

        $cache = Cache::getCache(Cache::DISTR);

        $cache->delete(CCompteRendu::CACHE_KEY_OPENER . '-' . $compte_rendu_id);
    }

    /**
     * Tool which renames old fields
     *
     * @return void
     * @throws Exception
     */
    public function correctFields(): void
    {
        $this->checkPermAdmin();

        CAppUI::stepAjax(
            'CCompteRendu-Number of docs with fields replaced',
            UI_MSG_OK,
            (new CompteRenduFieldReplacer())->bulkReplace()
        );
    }
}
