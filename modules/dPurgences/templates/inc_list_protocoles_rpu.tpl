{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title">
      {{tr}}CProtocoleRPU.list{{/tr}}
    </th>
  </tr>
  {{foreach from=$protocoles_rpu item=_protocole_rpu}}
  <tr>
    <td {{if !$_protocole_rpu->actif}}class="opacity-40"{{/if}} onclick="ProtocoleRPU.edit('{{$_protocole_rpu->_id}}')" style="cursor: pointer;">
      <div>
        <strong>
          {{if $_protocole_rpu->default}}
            <i class="fa fa-star" title="{{tr}}CProtocoleRPU-default-desc{{/tr}}"></i>
          {{/if}}
          {{mb_value object=$_protocole_rpu field=libelle}}
        </strong>
      </div>

      <div class="compact">
        {{if $_protocole_rpu->responsable_id}}
          {{mb_label object=$_protocole_rpu field=responsable_id}} : {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_protocole_rpu->_ref_responsable}}
        {{/if}}

        {{if $_protocole_rpu->mode_entree_id}}
          {{if $_protocole_rpu->responsable_id}}&mdash;{{/if}}
          {{mb_label object=$_protocole_rpu field=mode_entree_id}} : {{$_protocole_rpu->_ref_mode_entree}}
        {{elseif $_protocole_rpu->mode_entree}}
          {{if $_protocole_rpu->responsable_id}}&mdash;{{/if}}
          {{mb_label object=$_protocole_rpu field=mode_entree}} : {{mb_value object=$_protocole_rpu field=mode_entree}}
        {{/if}}
      </div>

      <div class="compact">
        {{if $_protocole_rpu->uf_soins_id}}
          {{mb_label object=$_protocole_rpu field=uf_soins_id}} : {{$_protocole_rpu->_ref_uf_soins}}
        {{/if}}

        {{if $_protocole_rpu->transport}}
          {{if $_protocole_rpu->uf_soins_id}}&mdash;{{/if}}
          {{mb_label object=$_protocole_rpu field=transport}} : {{mb_value object=$_protocole_rpu field=transport}}
        {{/if}}
        {{if $_protocole_rpu->provenance}}
          {{if $_protocole_rpu->uf_soins_id || $_protocole_rpu->transport}}&mdash;{{/if}}
          {{mb_label object=$_protocole_rpu field=provenance}} : {{mb_value object=$_protocole_rpu field=provenance}}
        {{/if}}
        {{if $_protocole_rpu->pec_transport}}
          {{if $_protocole_rpu->uf_soins_id || $_protocole_rpu->pec_transport}}&mdash;{{/if}}
          {{mb_label object=$_protocole_rpu field=pec_transport}} : {{mb_value object=$_protocole_rpu field=pec_transport}}
        {{/if}}
      </div>

      {{if $_protocole_rpu->charge_id}}
        <div class="compact">
          {{mb_label object=$_protocole_rpu field=charge_id}} : {{$_protocole_rpu->_ref_charge}}
        </div>
      {{/if}}

      {{if $_protocole_rpu->box_id}}
        <div class="compact">
          {{mb_label object=$_protocole_rpu field=box_id}} : {{$_protocole_rpu->_ref_box}}
        </div>
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty">
      {{tr}}CProtocoleRPU.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>
