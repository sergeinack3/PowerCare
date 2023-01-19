{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigModeEasy" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    <tr>
      <th class="title" colspan="2">Affichage de la DHE simplifiée</th>
    </tr>

    {{assign var=class value=COperation}}
    <tr>
      <th class="category" colspan="2">{{tr}}Display-mode{{/tr}}</th>
    </tr>

    {{mb_include module=system template=inc_config_enum var=mode_easy values="1col|2col"}}

    {{assign var=class value=CSejour}}

    <tr>
      <th class="category" colspan="2">{{tr}}CSejour{{/tr}}</th>
    </tr>

    {{mb_include module=system template=inc_config_bool var=easy_cim10}}
    {{mb_include module=system template=inc_config_bool var=easy_service}}
    {{mb_include module=system template=inc_config_bool var=easy_chambre_simple}}
    {{mb_include module=system template=inc_config_bool var=easy_ald_c2s}}
    {{mb_include module=system template=inc_config_bool var=easy_isolement}}
    {{mb_include module=system template=inc_config_bool var=easy_atnc}}
    {{mb_include module=system template=inc_config_bool var=easy_mode_sortie}}
    {{mb_include module=system template=inc_config_bool var=easy_entree_sortie}}

    {{assign var=class value=COperation}}

    <tr>
      <th class="category" colspan="2">{{tr}}COperation{{/tr}}</th>
    </tr>

    {{mb_include module=system template=inc_config_bool var=easy_materiel}}
    {{mb_include module=system template=inc_config_bool var=easy_remarques}}
    {{mb_include module=system template=inc_config_bool var=easy_regime}}
    {{mb_include module=system template=inc_config_bool var=easy_accident}}
    {{mb_include module=system template=inc_config_bool var=easy_assurances}}
    {{mb_include module=system template=inc_config_bool var=easy_type_anesth}}
    {{mb_include module=system template=inc_config_str var=easy_length_input_label size=5 numeric=true spinner_min=40 spinner_max=88}}

    <tr>
      <th class="category" colspan="2">{{tr}}CPatient{{/tr}}</th>
    </tr>

    {{assign var=class value=CPatient}}

    {{mb_include module=system template=inc_config_bool var=easy_correspondant}}
    {{mb_include module=system template=inc_config_bool var=easy_tutelle}}
    {{mb_include module=system template=inc_config_bool var=easy_handicap}}
    {{mb_include module=system template=inc_config_bool var=easy_aide_organisee}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
