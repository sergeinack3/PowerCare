{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  {{foreach from=$list item=item name=appointments}}
    {{if $smarty.foreach.appointments.first}}
      <tr>
        <td>
          <span class="type_item circled">
            {{if $item->teleconsultation}}
                {{tr}}CConsultation-teleconsultation{{/tr}}
            {{else}}
                {{tr}}CConsultation{{/tr}}
            {{/if}}
          </span>
        </td>
      </tr>
    {{/if}}
    <tr>
      <td style="width: 50%;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
          {{$item}}
        </span>
        <br>
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
          <span class="timeline_description">{{$item->_nb_files_docs}} {{tr}}CCompteRendu-document(|pl){{/tr}}</span>
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
