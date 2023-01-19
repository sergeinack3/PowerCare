{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="3">
      <span class="texticon texticon-grossesse" style="float:right"
            onmouseover="ObjectTooltip.createEx(this, '{{$grossesse->_guid}}'">
        <img src="./style/mediboard_ext/images/icons/grossesse.png" alt="{{tr}}CGrossesse{{/tr}}">
        {{tr}}CGrossesse.linked{{/tr}}
      </span>
      {{tr}}CGrossesse-RDV-liste{{/tr}} ({{mb_value object=$grossesse->_ref_parturiente}})

    </th>
  </tr>
  <tr>
    <th>{{tr}}CGrossesse-RDV-name{{/tr}}</th>
    <th class="narrow">{{tr}}CGrossesse-RDV-date{{/tr}}</th>
    <th>{{tr}}CGrossesse-RDV-existants{{/tr}}</th>
  </tr>
  {{foreach from=$list_rdv item=_item key=_rdv}}
  <tr>
    <td>
      {{tr}}CGrossesse-RDV-{{$_rdv}}{{/tr}}
    </td>
    <td>
      {{tr}}date.From{{/tr}} {{$_item.dates[0]|date_format:$conf.date}}
      {{tr}}date.to{{/tr}} {{$_item.dates[1]|date_format:$conf.date}}
    </td>
    <td>
      {{foreach from=$_item.consultations item=_consult name=consultations}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}');"
              class="type_item circled {{ if $_consult->annule}}fa fa-exclamation-triangle{{/if}}">
              {{$_consult->_view}} - {{mb_value object=$_consult field=_date}}
        </span>
        {{if !$smarty.foreach.consultations.last}}
          <br />
        {{/if}}
        {{foreachelse}}
        <span class="empty">{{tr}}CConsultation.none{{/tr}}</span>
      {{/foreach}}
      <button type="button" class="new notext"
              onclick="Consultation.editRDVModal(null, null, null, {{$grossesse->parturiente_id}}, null,
              {{$grossesse->_id}}, null, Control.Modal.refresh, '{{$_item.dates[0]}}')" style="float:right">
        {{tr}}CConsultation-title-create{{/tr}}
      </button>
    </td>
  </tr>
  {{/foreach}}

  {{if $consultations_restantes}}
  <tr>
    <td colspan="2" rowspan="3">
      {{tr}}CGrossesse-consultations-restantes{{/tr}}
    </td>
    {{foreach from=$consultations_restantes item=_consult}}
    <td style="display:block">
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}');"
                  class="type_item circled {{ if $_consult->annule}}fa fa-exclamation-triangle{{/if}}">
                  {{$_consult->_view}} - {{mb_value object=$_consult field=_date}}
            </span>
    </td>
    {{/foreach}}
  </tr>
  {{/if}}
</table>
