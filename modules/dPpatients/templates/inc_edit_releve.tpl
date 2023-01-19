{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editReleve" method="post"
      onsubmit="return Redon.saveReleve(this, null, function() {
        Control.Modal.close();
        Control.Modal.refresh();
      })">
  {{mb_class object=$releve}}
  {{mb_key   object=$releve}}

  {{mb_field object=$releve field=_qte_diff hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$releve}}

    <tr>
      {{me_form_field mb_object=$releve mb_field=qte_observee nb_cells=2}}
        {{mb_field object=$releve field=qte_observee form=editReleve increment=true
                   onchange="Redon.updateDiff(this, `$releve->redon_id`, parseFloat(`$qte_for_diff`))"}} ml
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field mb_object=$releve mb_field=date nb_cells=2}}
        {{mb_field object=$releve field=date form=editReleve register=true}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_bool mb_object=$releve mb_field=vidange_apres_observation nb_cells=2}}
        {{mb_field object=$releve field=vidange_apres_observation typeEnum=checkbox}}
      {{/me_form_bool}}
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button class="save">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
