{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $type == "appointments"}}
  <table class="main layout">
    <tr>
      <td>
        <span class="type_item circled">
          {{tr}}CConsultation{{/tr}}
        </span>
      </td>
    </tr>

    {{foreach from=$list item=item name=appointments}}
      <tr>
        <td style="width: 50%;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            {{$item}}
          </span>

          {{mb_value object=$item field=_datetime}}
          <br>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_chir}}
        </td>
        <td>
          {{if $item->categorie_id}}
            <span class="timeline_description">
            {{mb_include module=cabinet template=inc_icone_categorie_consult consultation=$item categorie=$item->_ref_categorie display_name=true}}
          </span>
          {{/if}}
          {{if $item->rques}}
            <span class="timeline_description">{{mb_value object=$item field=rques}}</span>
          {{/if}}
          {{if $item->motif}}
            <span class="timeline_description">{{mb_value object=$item field=motif}}</span>
          {{/if}}
          {{if $item->histoire_maladie}}
            <span class="timeline_description">{{mb_value object=$item field=histoire_maladie}}</span>
          {{/if}}
          {{if $item->examen}}
            <span class="timeline_description">{{mb_value object=$item field=examen}}</span>
          {{/if}}
          {{if $item->conclusion}}
            <span class="timeline_description">{{mb_value object=$item field=conclusion}}</span>
          {{/if}}
          {{if $item->_nb_files_docs}}
            <span class="timeline_description">{{$item->_nb_files_docs}} document(s)</span>
          {{/if}}
        </td>
      </tr>
      {{if !$smarty.foreach.appointments.last}}
        <tr>
          <td colspan="2"><hr class="item_separator"/></td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{/if}}

{{if $type == "anesth_appointments"}}
  <table class="main layout">
    <tr>
      <td>
        <span class="type_item circled anesth">
          {{tr}}CConsultAnesth{{/tr}}
        </span>
      </td>
    </tr>

    {{foreach from=$list item=item name=anesth}}
      <tr>
        <td style="width: 50%;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            {{$item}}
          </span>
          <br>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_chir}}
        </td>
        <td style="width: 50%;">
          {{if $item->_ref_consultation->categorie_id}}
            <span class="timeline_description">
            {{mb_include module=cabinet template=inc_icone_categorie_consult consultation=$item->_ref_consultation categorie=$item->_ref_consultation->_ref_categorie display_name=true}}
          </span>
          {{/if}}
          {{if $item->_ref_consultation->rques}}
            <span class="timeline_description">{{$item->_ref_consultation->rques}}</span>
          {{/if}}
          {{if $item->_ref_consultation->motif}}
            <span class="timeline_description">{{$item->_ref_consultation->motif}}</span>
          {{/if}}
          {{if $item->_ref_consultation->histoire_maladie}}
            <span class="timeline_description">{{$item->_ref_consultation->histoire_maladie}}</span>
          {{/if}}
          {{if $item->_ref_consultation->examen}}
            <span class="timeline_description">{{$item->_ref_consultation->examen}}</span>
          {{/if}}
          {{if $item->_ref_consultation->conclusion}}
            <span class="timeline_description">{{$item->_ref_consultation->conclusion}}</span>
          {{/if}}
          {{if $item->_ref_consultation->_nb_files_docs}}
            <span class="timeline_description">{{$item->_ref_consultation->_nb_files_docs}} document(s)</span>
          {{/if}}
          <span class="timeline_description">
            <button type="button" class="print" onclick="TimelineImplement.printFichePregnancy('{{$item->_id}}')">
              {{tr}}CConsultAnesth-Anesth-file{{/tr}}
            </button>
          </span>
        </td>
      </tr>
      {{if !$smarty.foreach.anesth.last}}
        <tr>
          <td colspan="2"><hr class="item_separator"/></td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{/if}}
