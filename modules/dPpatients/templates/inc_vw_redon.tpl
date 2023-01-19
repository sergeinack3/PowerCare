{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=last_releve value=$redon->_ref_last_releve}}

{{assign var=form_name value="editReleve`$redon->constante_medicale`"}}
{{assign var=redon_id value=$redon->_id}}

{{if !$redon->sous_vide}}
  <div class="small-info">
    {{tr}}CRedon-Redon pas sous vide{{/tr}}
  </div>
{{/if}}

<table class="main form">
  <tr>
    <th class="title" colspan="2">
        {{tr}}CRedon.constante_medicale.{{$redon->constante_medicale}}{{/tr}}
    </th>
  </tr>
  <tr>
    <td class="halfPane me-valign-top">
      <form class="forms_redon" name="{{$form_name}}" method="post" onsubmit="return Redon.saveReleve(this, '{{$redon->_id}}');">
        {{mb_class object=$releve}}
        {{mb_key object=$releve}}
        {{mb_field object=$releve field=redon_id hidden=true}}

        <table class="form">
          <tr>
            <th class="category" colspan="4">
              {{tr}}CReleveRedon-New releve{{/tr}}
            </th>
          </tr>

          <tr>
            {{me_form_field mb_object=$releve mb_field=qte_observee nb_cells=2}}
              {{mb_field object=$releve field=qte_observee form=$form_name increment=true onchange="Redon.updateDiff(this, `$redon->_id`, parseFloat(`$qtes_for_diff.$redon_id`));"}} ml
            {{/me_form_field}}

            {{me_form_field mb_object=$releve mb_field=_qte_diff nb_cells=2}}
              {{mb_field object=$releve field=_qte_diff readonly=true value=0}} ml
            {{/me_form_field}}
          </tr>

          <tr>
            {{me_form_field mb_object=$releve mb_field=date nb_cells=4}}
              {{mb_field object=$releve field=date form=$form_name register=true}}
            {{/me_form_field}}
          </tr>

          <tr>
            {{me_form_bool mb_object=$releve mb_field=vidange_apres_observation nb_cells=4 typeEnum=checkbox}}
              {{mb_field object=$releve field=vidange_apres_observation typeEnum=checkbox}}
            {{/me_form_bool}}
          </tr>
        </table>
      </form>
    </td>

    <td>
      <table class="form">
        <tr>
          <th class="category" colspan="2">
            <button type="button" class="search" style="float: right;" onclick="Redon.listReleves('{{$redon->constante_medicale}}', '{{$redon->_id}}');">
              {{tr}}CRedon-List releves{{/tr}}
            </button>

            {{tr}}CReleveRedon-Last releve{{/tr}}
          </th>
        </tr>
        <tr>
          {{me_form_field mb_object=$last_releve mb_field=qte_observee nb_cells=2 class="halfPane"}}
            <span id="qte_observee_{{$redon->_id}}" data-vidange_apres_observation="{{$last_releve->vidange_apres_observation}}">
                {{mb_value object=$last_releve field=qte_observee}}
            </span>
            <span>
              &nbsp;ml
            </span>
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field mb_object=$redon mb_field=_qte_cumul nb_cells=2}}
            {{mb_value object=$releve field=_qte_cumul}} ml
            {{if $last_releve->vidange_apres_observation}}({{tr}}CReleveRedon-vidange_apres_observation{{/tr}}){{/if}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field mb_object=$last_releve mb_field=date nb_cells=2}}
            {{mb_value object=$last_releve field=date}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field mb_object=$last_releve mb_field=user_id nb_cells=2}}
            {{mb_value object=$last_releve field=user_id}}
          {{/me_form_field}}
        </tr>

        {{if $last_releve->_id && $last_releve->user_id === $app->user_id}}
        <tr>
          <td class="button" colspan="2">
            <form name="delRedon" method="post">
              {{mb_class object=$last_releve}}
              {{mb_key   object=$last_releve}}
              <input type="hidden" name="redon_id" value="{{$redon->_id}}" />
              <input type="hidden" name="qte_observee" value="{{$last_releve->qte_observee}}" />
              <input type="hidden" name="del" value="1" />
              <button type="button" class="trash" onclick="Redon.delReleve(this.form, '{{$redon->_id}}');">{{tr}}Delete{{/tr}}</button>
            </form>
          </td>
        </tr>
        {{/if}}
      </table>
    </td>
  </tr>
  <tr>

  </tr>
</table>
