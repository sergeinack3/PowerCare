<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Controllers\Legacy;

use Exception;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Description
 */
class FileStatisticsLegacyController extends CLegacyController
{
    public function vwStats(): void
    {
        CCanDo::checkAdmin();

        $doc_class = CValue::get("doc_class", "CFile");
        if (!is_subclass_of($doc_class, CDocumentItem::class)) {
            trigger_error("Wrong '$doc_class' won't inerit from CDocumentItem", E_USER_ERROR);
        }

        // Création du template
        $this->renderSmarty("vw_stats.tpl", [
            "doc_class" => $doc_class,
        ]);
    }

    /**
     * @throws Exception
     */
    public function incVwStats(): void
    {
        CCanDo::checkAdmin();

        $doc_class = CView::get("doc_class", "str default|CFile");
        $factory = CView::get("factory", "str");

        CView::checkin();
        CView::enableSlave();

        $func = new CFunctions();

        /** @var CDocumentItem $doc */
        $doc          = new $doc_class();
        if ($doc instanceof CCompteRendu) {
            $users_stats = $doc->getUsersStats($factory);
        } else {
            $users_stats = $doc->getUsersStats();
        }

        $funcs_stats  = [];
        $groups_stats = [];

        $total = [
            "docs_weight" => 0,
            "docs_count"  => 0,
        ];

        $is_doc = get_class($doc) === CCompteRendu::class;

        if ($is_doc) {
            $total['docs_read_time']  = 0;
            $total['docs_write_time'] = 0;
            // get doc with read/write time to calculate the average
            $total['doc_with_duree_ecriture'] = 0;
            $total['doc_with_duree_lecture'] = 0;
            $total['docs_average_read_time'] = 0;
            $total['docs_average_write_time'] = 0;
        }

        if (CModule::getActive("mediusers")) {
            $users_ids = [];
            foreach ($users_stats as $_stat_user) {
                $users_ids[] = $_stat_user["owner_id"];
            }

            $user  = new CMediusers();
            $users = $user->loadList(["user_id" => CSQLDataSource::prepareIn($users_ids)]);
            CStoredObject::massLoadFwdRef($users, "function_id");
        }

        // Stat per user
        foreach ($users_stats as &$_stat_user) {
            $total["docs_weight"]             += $_stat_user["docs_weight"];
            $total["docs_count"]              += $_stat_user["docs_count"];
            $total['doc_with_duree_ecriture'] += $_stat_user["doc_with_duree_ecriture"];
            $total['doc_with_duree_lecture']  += $_stat_user["doc_with_duree_lecture"];
            if ($is_doc) {
                $total['docs_read_time']  += $_stat_user['docs_read_time'];
                $total['docs_write_time'] += $_stat_user['docs_write_time'];
            }

            $_stat_user["_docs_average_weight"] = $_stat_user["docs_count"] ?
                ($_stat_user["docs_weight"] / $_stat_user["docs_count"]) : 0;


            // Make it mediusers uninstalled compliant
            if (CModule::getActive("mediusers")) {
                // Get the owner
                $user                     = CMediusers::get($_stat_user["owner_id"]);
                $_stat_user["_ref_owner"] = $user;

                if (!$user->_id) {
                    continue;
                }

                // Initialize function data
                $function = $user->loadRefFunction();
                if (!isset($funcs_stats[$function->_id])) {
                    $funcs_stats[$function->_id] = [
                        "docs_weight" => 0,
                        "docs_count"  => 0,
                        "_ref_owner"  => $function,
                    ];

                    if ($is_doc) {
                        $funcs_stats[$function->_id]['docs_read_time']  = 0;
                        $funcs_stats[$function->_id]['docs_write_time'] = 0;
                        $funcs_stats[$function->_id]['doc_with_duree_ecriture'] = 0;
                        $funcs_stats[$function->_id]['doc_with_duree_lecture'] = 0;
                    }
                }

                // Cummulate data per function
                $stat_func                =& $funcs_stats[$function->_id];
                $stat_func["docs_weight"] += $_stat_user["docs_weight"];
                $stat_func["docs_count"]  += $_stat_user["docs_count"];

                if ($is_doc) {
                    $stat_func['docs_read_time']  += $_stat_user['docs_read_time'];
                    $stat_func['docs_write_time'] += $_stat_user['docs_write_time'];
                    $stat_func['doc_with_duree_ecriture'] += $_stat_user['doc_with_duree_ecriture'];
                    $stat_func['doc_with_duree_lecture']  += $_stat_user['doc_with_duree_lecture'];
                }

                // Initialize group data
                $group = $function->loadRefGroup();
                if (!isset($groups_stats[$group->_id])) {
                    $groups_stats[$group->_id] = [
                        "docs_weight" => 0,
                        "docs_count"  => 0,
                        "_ref_owner"  => $group,
                    ];

                    if ($is_doc) {
                        $groups_stats[$group->_id]['docs_read_time']  = 0;
                        $groups_stats[$group->_id]['docs_write_time'] = 0;
                        $groups_stats[$group->_id]['doc_with_duree_ecriture'] = 0;
                        $groups_stats[$group->_id]['doc_with_duree_lecture'] = 0;
                    }
                }

                // Cummulate data per group
                $stat_group                =& $groups_stats[$group->_id];
                $stat_group["docs_weight"] += $_stat_user["docs_weight"];
                $stat_group["docs_count"]  += $_stat_user["docs_count"];

                if ($is_doc) {
                    $stat_group['docs_read_time']          += $_stat_user['docs_read_time'];
                    $stat_group['docs_write_time']         += $_stat_user['docs_write_time'];
                    $stat_group['doc_with_duree_ecriture'] += $_stat_user['doc_with_duree_ecriture'];
                    $stat_group['doc_with_duree_lecture']  += $_stat_user['doc_with_duree_lecture'];
                }
            }
        }

        // Get user data percentages
        foreach ($users_stats as &$_stat_user) {
            $_stat_user["_docs_weight_percent"] = $total["docs_weight"] ?
                ($_stat_user["docs_weight"] / $total["docs_weight"]) : 0;
            $_stat_user["_docs_count_percent"]  = $total["docs_count"] ? ($_stat_user["docs_count"] / $total["docs_count"]) : 0;
            if ($is_doc) {
                $_stat_user['docs_average_read_time']  = $_stat_user['doc_with_duree_lecture'] ?
                    $_stat_user["docs_read_time"] / $_stat_user["doc_with_duree_lecture"] : 0;
                $_stat_user['docs_average_write_time'] = $_stat_user['doc_with_duree_ecriture'] ?
                    $_stat_user["docs_write_time"] / $_stat_user["doc_with_duree_ecriture"] : 0;
            }
        }

        // Get function data percentages
        foreach ($funcs_stats as $function_id => &$_stat_func) {
            $_stat_func["_docs_weight_percent"] = $total["docs_weight"] ?
                ($_stat_func["docs_weight"] / $total["docs_weight"]) : 0;
            $_stat_func["_docs_count_percent"]  = $total["docs_count"] ? ($_stat_func["docs_count"] / $total["docs_count"]) : 0;
            $_stat_func["_docs_average_weight"] = $_stat_func["docs_count"] ?
                ($_stat_func["docs_weight"] / $_stat_func["docs_count"]) : 0;
            if ($is_doc) {
                $_stat_func['docs_average_read_time']  = $_stat_func['doc_with_duree_ecriture'] ?
                    $_stat_func["docs_read_time"] / $_stat_func["doc_with_duree_ecriture"] : 0;
                $_stat_func['docs_average_write_time'] = $_stat_func['doc_with_duree_lecture'] ?
                    $_stat_func["docs_write_time"] / $_stat_func["doc_with_duree_lecture"] : 0;
            }
        }

        // Get function data percentages
        foreach ($groups_stats as $group_id => &$_stat_group) {
            $_stat_group["_docs_weight_percent"] = $total["docs_weight"] ?
                ($_stat_group["docs_weight"] / $total["docs_weight"]) : 0;
            $_stat_group["_docs_count_percent"]  = $total["docs_count"] ? ($_stat_group["docs_count"] / $total["docs_count"]) : 0;
            $_stat_group["_docs_average_weight"] = $_stat_group["docs_count"] ?
                ($_stat_group["docs_weight"] / $_stat_group["docs_count"]) : 0;
            if ($is_doc) {
                $_stat_group['docs_average_read_time']  = $_stat_group['doc_with_duree_ecriture'] ?
                    $_stat_group["docs_read_time"] / $_stat_group["doc_with_duree_ecriture"] : 0;
                $_stat_group['docs_average_write_time'] = $_stat_group['doc_with_duree_lecture'] ?
                    $_stat_group["docs_write_time"] / $_stat_group["doc_with_duree_lecture"] : 0;
            }
        }

        $total["_docs_average_weight"] = $total["docs_count"] ? ($total["docs_weight"] / $total["docs_count"]) : 0;

        if ($is_doc) {
            $total['docs_average_read_time']  = $total['doc_with_duree_ecriture'] ?
                $total["docs_read_time"] / $total["doc_with_duree_ecriture"] : 0;
            $total['docs_average_write_time'] = $total['doc_with_duree_lecture'] ?
                $total["docs_write_time"] / $total["doc_with_duree_lecture"] : 0;
            $total['docs_average_read_time']  = CMbDT::friendlyDuration($total['docs_average_read_time'])['locale'];
            $total['docs_average_write_time'] = CMbDT::friendlyDuration($total['docs_average_write_time'])['locale'];

            foreach ($users_stats as $_key => $_user_stats) {
                $users_stats[$_key]['docs_average_read_time']  = CMbDT::friendlyDuration(
                    $_user_stats['docs_average_read_time']
                )['locale'];
                $users_stats[$_key]['docs_average_write_time'] = CMbDT::friendlyDuration(
                    $_user_stats['docs_average_write_time']
                )['locale'];
            }

            foreach ($funcs_stats as $_key => $_func_stats) {
                $funcs_stats[$_key]['docs_average_read_time']  = CMbDT::friendlyDuration(
                    $_func_stats['docs_average_read_time']
                )['locale'];
                $funcs_stats[$_key]['docs_average_write_time'] = CMbDT::friendlyDuration(
                    $_func_stats['docs_average_write_time']
                )['locale'];
            }

            foreach ($groups_stats as $_key => $_group_stats) {
                $groups_stats[$_key]['docs_average_read_time']  = CMbDT::friendlyDuration(
                    $_group_stats['docs_average_read_time']
                )['locale'];
                $groups_stats[$_key]['docs_average_write_time'] = CMbDT::friendlyDuration(
                    $_group_stats['docs_average_write_time']
                )['locale'];
            }
        }
        // Création du template
        $this->renderSmarty(
            "inc_stats_documents",
            [
                "doc_class"    => $doc_class,
                "users_stats"  => $users_stats,
                "funcs_stats"  => $funcs_stats,
                "groups_stats" => $groups_stats,
                "total"        => $total,
                'is_doc'       => $is_doc,
                'factory'      => $factory,
            ],
        );
    }
}
