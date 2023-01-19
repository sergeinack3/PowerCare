{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigEtiquettes" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  {{assign var="class" value="CSejour"}}
  <table class="form">
    <tr>
      <th class="title" colspan="2">Tag pour les numéros de dossier</th>
    </tr>

    {{mb_include module=system template=inc_config_str var=tag_dossier            }}
    {{mb_include module=system template=inc_config_str var=tag_dossier_group_idex }}
    {{mb_include module=system template=inc_config_str var=tag_dossier_pa         }}
    {{mb_include module=system template=inc_config_str var=tag_dossier_cancel     }}
    {{mb_include module=system template=inc_config_str var=tag_dossier_trash      }}
    {{mb_include module=system template=inc_config_str var=tag_dossier_rang       }}
    {{mb_include module=system template=inc_config_bool var=use_dossier_rang      }}
    {{mb_include module=system template=inc_config_bool var=show_modal_identifiant}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>