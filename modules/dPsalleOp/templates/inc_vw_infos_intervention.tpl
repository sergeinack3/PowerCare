{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main form">
  <tr>
    <td>
      {{mb_include module=salleOp template=inc_vw_infos_patient}}
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>
          <i class="fas fa-briefcase-medical"></i> {{tr}}COperation-action-Intervention information-court|pl{{/tr}}
          {{if "monitoringPatient"|module_active && "monitoringBloc"|module_active && "monitoringBloc general active_graph_supervision"|gconf}}
            <button type="button" class="search compact not-printable me-primary" onclick="refreshFicheAnesth(true);">
              {{tr}}CAnesthPerop-action-Anesthesia sheet{{/tr}}
            </button>
          {{/if}}
        </legend>
        <form name="perop-editInfosASAFrm" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_key   object=$selOp}}
          {{mb_class object=$selOp}}

          <table class="main form">
            <tr>
              <th class="category">
                {{mb_label object=$selOp field="anesth_id"}}
              </th>
              {{if !$selOp->_ref_sejour->grossesse_id}}
                <th class="category">
                  {{mb_label object=$selOp field="type_anesth"}}
                </th>
                <th class="category">
                  {{mb_label object=$selOp field="position_id"}}
                </th>
              {{/if}}
              <th class="category">
                {{mb_label object=$selOp field="ASA"}}
              </th>
            </tr>
            <tr>
              <td style="text-align: center">
                {{if $modif_operation}}
                  <select name="anesth_id" onchange="this.form.onsubmit()">
                    <option value="">&mdash; {{tr}}CNaissanceRea.rea_par.anesth{{/tr}}</option>
                    {{foreach from=$listAnesths item=curr_anesth}}
                      <option
                        value="{{$curr_anesth->user_id}}" {{if $selOp->_ref_anesth->user_id == $curr_anesth->user_id}} selected="selected" {{/if}}>
                        {{$curr_anesth->_view}}
                      </option>
                    {{/foreach}}
                  </select>
                {{elseif $selOp->_ref_anesth->user_id}}
                  {{assign var="keyChir" value=$selOp->_ref_anesth->user_id}}
                  {{assign var="typeChir" value=$listAnesths.$keyChir}}
                  {{$typeChir->_view}}
                {{else}}
                  -
                {{/if}}
              </td>
              {{if !$selOp->_ref_sejour->grossesse_id}}
                <td style="text-align: center">
                  {{if $modif_operation}}
                    <select name="type_anesth" onchange="this.form.onsubmit()">
                      <option value="">&mdash; {{tr}}CAnesthPerop-Type of anesthesia{{/tr}}</option>
                      {{foreach from=$listAnesthType item=curr_anesth}}
                        {{if $curr_anesth->actif || $selOp->type_anesth == $curr_anesth->type_anesth_id}}
                          <option
                            value="{{$curr_anesth->type_anesth_id}}" {{if $selOp->type_anesth == $curr_anesth->type_anesth_id}} selected="selected" {{/if}}>
                            {{$curr_anesth->name}}{{if !$curr_anesth->actif && $selOp->type_anesth == $curr_anesth->type_anesth_id}}({{tr}}Obsolete{{/tr}}){{/if}}
                          </option>
                        {{/if}}
                      {{/foreach}}
                    </select>
                  {{elseif $selOp->type_anesth}}
                    {{assign var="keyAnesth" value=$selOp->type_anesth}}
                    {{assign var="typeAnesth" value=$listAnesthType.$keyAnesth}}
                    {{$typeAnesth->name}}
                  {{else}}
                    -
                  {{/if}}
                </td>
                <td style="text-align: center">

                  <script>
                    Main.add(function () {
                      var formPosition = getForm("perop-editInfosASAFrm");
                      new Url("planningOp", "position_autocomplete")
                        .addParam('group_id', {{$g}})
                        .autoComplete(
                          formPosition.position_id_view,
                          null,
                          {
                            minChars:           0,
                            method:             "get",
                            select:             "view",
                            dropdown:           true,
                            afterUpdateElement: function (field, selected) {
                              $V(field.form["position_id"], selected.getAttribute("id").split("-")[2]);
                            }
                          }
                        );
                    });
                  </script>
                  {{mb_field object=$selOp field=position_id hidden=1 onchange="this.form.onsubmit()"}}
                  {{assign var=position_object value=$selOp->loadRefPosition()}}
                  <input type="text" name="position_id_view" value="{{$position_object->_view}}" style="width: 12em;"/>
                  <button type="button" class="cancel notext me-tertiary me-dark"
                          onclick="$V(this.form.position_id, ''); $V(this.form.position_id_view, '')"></button>
                </td>
              {{/if}}
              <td style="text-align: center">
                {{mb_field object=$selOp field="ASA" emptyLabel="Choose" onchange="this.form.onsubmit()"}}
              </td>
            </tr>
          </table>
        </form>
      </fieldset>
    </td>
  </tr>
  {{if $show_cormack}}
    <tr>
      <td>
        <fieldset>
          <legend><i class="fas fa-eye"></i> {{tr}}CConsultAnesth-cormack{{/tr}}</legend>
          {{mb_include module=salleOp template=inc_vw_score_cormack}}
        </fieldset>
      </td>
    </tr>
  {{/if}}
</table>


