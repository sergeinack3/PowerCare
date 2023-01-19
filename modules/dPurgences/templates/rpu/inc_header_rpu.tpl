{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=switch_view value="synthese"}}
{{mb_default var=tab_mode value=1}}

<tr>
  {{if $rpu->_id}}
    <th class="title modify me-padding-top-0 me-padding-bottom-0" colspan="4">

      {{mb_include module=system template=inc_object_notes      object=$rpu}}
      {{mb_include module=system template=inc_object_idsante400 object=$rpu}}
      {{mb_include module=system template=inc_object_history    object=$rpu}}

      <a class="action" style="float: right;" title="Modifier uniquement le sejour" href="?m=planningOp&tab=vw_edit_sejour&sejour_id={{$sejour->_id}}">
        {{me_img src="edit.png" alt="modifier" icon="edit" class="me-primary"}}
      </a>

      {{if !$tab_mode}}
        <a class="button hslip" style="float: right;"
           href="{{if $switch_view === "synthese"}}
                   {{if $consult->_id}}
                     ?m=urgences&dialog=edit_consultation&selConsult={{$consult->_id}}&synthese_rpu=1
                   {{else}}
                     ?m=urgences&dialog=vw_synthese_rpu&rpu_id={{$rpu->_id}}
                   {{/if}}
                 {{elseif $app->_ref_user->isInfirmiere() || !$consult->_id}}
                   ?m=urgences&dialog=vw_aed_rpu&rpu_id={{$rpu->_id}}&sejour_id={{$sejour->_id}}
                 {{else}}
                   ?m=urgences&dialog=edit_consultation&selConsult={{$consult->_id}}
                 {{/if}}"
             >
          {{tr}}CRPU.{{$switch_view}}{{/tr}}
        </a>
      {{/if}}

      {{tr}}CRPU-title-modify{{/tr}}
      '{{$rpu}}'
      {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
    </th>
  {{else}}
    <th class="title me-th-new me-no-title me-color-white-high-emphasis me-no-convert-dark" colspan="4">
      {{mb_include module=urgences template=inc_button_choose_protocole}}

      {{tr}}CRPU-title-create{{/tr}}
      {{if $sejour->_NDA}}
        pour le dossier
        {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
      {{/if}}

      <span class="me-white-context">
        {{mb_include module=files template=inc_button_docitems context=$rpu form=editRPU}}
      </span>
    </th>
  {{/if}}
</tr>
