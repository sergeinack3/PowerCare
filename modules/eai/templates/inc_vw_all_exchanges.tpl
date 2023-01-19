{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-contrast me-table-col-separated">
  <tr>
    <th colspan="2"></th>
    <th class="me-text-align-center">{{mb_title class=CExchangeDataFormat field="object_id"}}</th>
    <th class="me-text-align-center">{{mb_title class=CExchangeDataFormat field="id_permanent"}}</th>
    <th class="me-text-align-center">{{mb_title class=CExchangeDataFormat field="date_production"}}</th>
    <th class="me-text-align-center">{{mb_title class=CExchangeDataFormat field="sender_id"}}</th>
    <th class="me-text-align-center">{{mb_title class=CExchangeDataFormat field="receiver_id"}}</th>
    <th class="me-text-align-center">{{mb_title class=CExchangeDataFormat field="type"}}</th>
    <th class="me-text-align-center">{{mb_title class=CExchangeDataFormat field="send_datetime"}}</th>
    <th class="me-text-align-center">{{mb_title class=CExchangeDataFormat field="statut_acquittement"}}</th>
  </tr>
  {{foreach from=$exchanges key=_exchange_classname item=_exchanges}}
    <tr>
      <th class="section" colspan="13">
        {{tr}}{{$_exchange_classname}}{{/tr}}
      </th>
    </tr>
    {{foreach from=$_exchanges item=_exchange}}
      <tr>
        <td class="narrow">
          {{if $_exchange->_self_sender}}
            <i class="fa fa-arrow-right" style="color: green;" title="{{tr}}CExchangeDataFormat-message sent{{/tr}}"></i>
          {{else}}
            <i class="fa fa-arrow-left" style="color: blue;" title="{{tr}}CExchangeDataFormat-message received{{/tr}}"></i>
          {{/if}}
        </td>
        <td class="narrow">
          <button type="button" onclick="ExchangeDataFormat.viewExchange('{{$_exchange->_guid}}')" class="search">
            {{$_exchange->_id}}
          </button>
        </td>
        <td class="narrow">
          {{if $_exchange->object_id}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_exchange->object_class}}-{{$_exchange->object_id}}');">
              {{$_exchange->object_id}}
            </span>
          {{else}}
            <em>{{$_exchange->object_class}}</em>
          {{/if}}
        </td>
        <td class="narrow">
          {{if $_exchange->id_permanent}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_exchange->object_class}}-{{$_exchange->object_id}}', 'identifiers');">
            {{$_exchange->id_permanent}}
          </span>
          {{/if}}
        </td>
        <td class="narrow">
          <label title='{{mb_value object=$_exchange field="date_production"}}'>
            {{mb_value object=$_exchange field="date_production" format=relative}}
          </label>
        </td>
        {{assign var=emetteur value=$_exchange->_ref_sender}}
        <td class="{{if $_exchange->sender_id == '0'}}error{{/if}} narrow">
          {{if $_exchange->_self_sender}}
              <i class="fa fa-laptop" style="font-size: large;" title="[SELF]"></i>
          {{else}}
            <a href="?m=eai&tab=vw_idx_interop_actors#interop_actor_guid={{$emetteur->_guid}}">
              {{$emetteur->_view}}
            </a>
          {{/if}}
        </td>
        {{assign var=destinataire value=$_exchange->_ref_receiver}}
        <td class="narrow">
          {{if $_exchange->_self_receiver}}
              <i class="fa fa-laptop" style="font-size: large;" title="[SELF]"></i>
          {{else}}
            <a href="?m=eai&tab=vw_idx_interop_actors#interop_actor_guid={{$destinataire->_guid}}">
             <span onmouseover="ObjectTooltip.createEx(this, '{{$destinataire->_guid}}');">
               {{$destinataire->_view}}
             </span>
            </a>
          {{/if}}
        </td>
        <td>
          <span title="{{tr}}hl7-msg-{{$_exchange->type}}{{/tr}}">{{mb_value object=$_exchange field="type"}} </span>
          <br />
          <span class="compact text">
            {{if $_exchange|instanceof:'Ox\Interop\Hl7\CExchangeHL7v2'}}
              <span title="{{tr}}hl7-evt_{{$_exchange->type}}-{{$_exchange->code}}{{/tr}}">
                  {{mb_value object=$_exchange field="code"}} -
                </span>
            {{/if}}
            {{if $_exchange|instanceof:'Ox\Interop\Hl7\CExchangeHL7v2' || $_exchange|instanceof:'Ox\Interop\Hprim21\CEchangeHprim21' || $_exchange|instanceof:'Ox\Interop\Hprimsante\CExchangeHprimSante'}}
              {{mb_value object=$_exchange field="version"}}
            {{/if}}
          </span>
        </td>
        <td class="narrow">
          <label title='{{mb_value object=$_exchange field="send_datetime"}}'>
            {{mb_value object=$_exchange field="send_datetime" format=relative}}
          </label>
        </td>
        {{assign var=statut_acq value=$_exchange->statut_acquittement}}
        <td class="{{if !$statut_acq && $_exchange->_self_sender}}
               hatching
             {{elseif !$statut_acq ||
        ($statut_acq == 'erreur') ||
        ($statut_acq == 'AR') ||
        ($statut_acq == 'err') ||
        ($statut_acq == 'T')}}
               error
             {{elseif ($statut_acq == 'avertissement') ||
        ($statut_acq == 'avt') ||
        ($statut_acq == 'AE') ||
        ($statut_acq == 'P')}}
               warning
             {{/if}}
             narrow">
          {{mb_value object=$_exchange field="statut_acquittement"}}

          <br />
          <span class="text compact">
            {{foreach from=$_exchange->_observations item=_observation}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_exchange->_guid}}');">
               {{$_observation.code}}
             </span>
            {{/foreach}}
          </span>
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="13" class="empty">
          {{tr}}{{$_exchange_classname}}.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>