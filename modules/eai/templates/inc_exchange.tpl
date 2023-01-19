{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object}}
<tr>
  <td colspan="21" class="empty">
    {{tr}}CExchangeDataFormat.none{{/tr}}
  </td>
</tr> 
{{else}}
<tr>
  <td>
    <input id="exchange_checkbox_{{$object->_id}}" type="checkbox" name="exchange_checkbox" style="display: none"/>
    <span class="far fa-square fa-2x me-color-grey"
          style="cursor: pointer; vertical-align: middle"
          onclick="togglePrint(document.getElementById('exchange_checkbox_{{$object->_id}}'));">
    </span>
  </td>
  <td class="narrow">
    {{if $object->_self_sender}}
      <i class="fa fa-arrow-right" style="color: green;" title="{{tr}}CExchangeDataFormat-message sent{{/tr}}"></i>
    {{else}}
      <i class="fa fa-arrow-left" style="color: blue;" title="{{tr}}CExchangeDataFormat-message received{{/tr}}"></i>
    {{/if}}

      {{if $object->_delayed}}
        <i class="fa fa-hourglass" style="color: orange;" title='{{$object->_delayed}} minutes'></i>
      {{/if}}

      {{if $object->master_idex_missing}}
        <button class="warning notext" type="button"
                onclick="ExchangeDataFormat.defineMasterIdexMissing('{{$object->_guid}}')"
                title="{{tr}}CExchangeDataFormat-master_idex_missing-desc{{/tr}}">
            {{tr}}CExchangeDataFormat-master_idex_missing-desc{{/tr}}
        </button>
      {{/if}}

      {{if $object|instanceof:'Ox\Interop\Cda\CExchangeCDA' && $object->report}}
        <button type="button" class="button notext" title="{{tr}}CReport-msg-Show report{{/tr}}"
                onclick="ExchangeDataFormat.showReportCDA('{{$object->_id}}')">
          <i class="far fa-file-alt"></i>
        </button>
      {{/if}}
  </td>
  <td class="narrow">
      {{mb_include module=system template=inc_object_notes object=$object float="none"}}
  </td>
  <td class="narrow">
    <form name="del{{$object->_guid}}" action="" method="post">
        {{mb_class object=$object}}
        {{mb_key object=$object}}
      <input type="hidden" name="del" value="1"/>

      <button class="cancel notext" type="button" onclick="confirmDeletion(this.form, {
          ajax:1, 
          typeName:&quot;{{tr}}{{$object->_class}}.one{{/tr}}&quot;,
          objName:&quot;{{$object->_view|smarty:nodefaults|JSAttribute}}&quot;},
          { onComplete: ExchangeDataFormat.refreshExchangesList.curry(getForm('filterExchange'))
        })">
      </button>
      <button class="edit notext" type="button" onclick="ExchangeDataFormat.editExchange('{{$object->_guid}}')">
        {{tr}}Edit{{/tr}}
      </button>
    </form>
    {{if $object->_self_receiver}}
      <button class="fas fa-sync notext" style="color: blue !important;" type="button" {{if $object->reprocess >= $conf.eai.max_reprocess_retries}}disabled{{/if}}
        onclick="ExchangeDataFormat.reprocessing('{{$object->_guid}}')" 
        title="{{tr}}Reprocess{{/tr}} ({{$object->reprocess}}/{{$conf.eai.max_reprocess_retries}} fois)">
          {{if $object->reprocess}}{{$object->reprocess}}{{/if}}
      </button>
    {{/if}}
    {{if $object->_self_sender}}
      <button class="fa fa-share notext" style="color: green !important;" onclick="ExchangeDataFormat.sendMessage('{{$object->_guid}}')"
        type="button" title="{{tr}}Send{{/tr}}">
      </button>
    {{/if}}
  </td>
  <td class="narrow">
    <button type="button" onclick="ExchangeDataFormat.viewExchange('{{$object->_guid}}')" class="search">
     {{$object->_id}}
    </button>
  </td>
  <td class="narrow">
    {{if $object->object_id}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$object->object_class}}-{{$object->object_id}}');">
        {{$object->object_id}}
      </span>
    {{else}}
      <em>{{$object->object_class}}</em>
    {{/if}}
  </td>
  <td class="narrow">
    {{if $object->id_permanent}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$object->object_class}}-{{$object->object_id}}', 'identifiers');">
        {{$object->id_permanent}}
      </span>
    {{/if}}
  </td>
  <td class="narrow">
    <label title='{{mb_value object=$object field="date_production"}}'>
      {{mb_value object=$object field="date_production" format=relative}}
    </label>
  </td>
  {{assign var=emetteur value=$object->_ref_sender}}
  <td class="{{if $object->sender_id == '0'}}error{{/if}} text exchange-sender">
     {{if $object->_self_sender}}
       <i class="fa fa-laptop" style="font-size: large;" title="[SELF]"></i>
     {{else}}
       <a href="?m=eai&tab=vw_idx_interop_actors#interop_actor_guid={{$emetteur->_guid}}">
         {{$emetteur->_view}}
       </a>
     {{/if}}
  </td>
  {{assign var=destinataire value=$object->_ref_receiver}}
  <td class="text exchange-receiver">
    {{if $object->_self_receiver}}
      <i class="fa fa-laptop" style="font-size: large;" title="[SELF]"></i>
     {{else}}
       <a href="?m=eai&tab=vw_idx_interop_actors#interop_actor_guid={{$destinataire->_guid}}">
         <span onmouseover="ObjectTooltip.createEx(this, '{{$destinataire->_guid}}');">
           {{$destinataire->_view}}
         </span>
       </a>
     {{/if}}
  </td>
  <td class="{{if $object->type == 'inconnu'}}error{{/if}} narrow">
    <span title="{{tr}}hl7-msg-{{$object->type}}{{/tr}}">{{mb_value object=$object field="type"}} </span>
  </td>
  <td class="{{if $object->sous_type == 'inconnu'}}error{{/if}} narrow">
    {{if $object->sous_type}}
      {{mb_value object=$object field="sous_type"}}
      <br />
    {{/if}}
    <span class="compact text">
      {{if $object|instanceof:'Ox\Interop\Hl7\CExchangeHL7v2'}}
        <span title="{{tr}}hl7-evt_{{$object->type}}-{{$object->code}}{{/tr}}">
          {{mb_value object=$object field="code"}} -
        </span>
      {{/if}}
      {{if $object|instanceof:'Ox\Interop\Hl7\CExchangeHL7v2' || $object|instanceof:'Ox\Interop\Hprim21\CEchangeHprim21' || $object|instanceof:'Ox\Interop\Hprimsante\CExchangeHprimSante'}}
          {{mb_value object=$object field="version"}}
      {{/if}}
    </span>
  </td>
  <td class="{{if $object->send_datetime}}ok{{else}}warning{{/if}} narrow">
    <label title='{{mb_value object=$object field="send_datetime"}}'>
        {{mb_value object=$object field="send_datetime" format=relative}}
    </label>
  </td>
    {{assign var=statut_acq value=$object->statut_acquittement}}
  <td class="{{if (!$statut_acq && $object->_self_sender) || $object|instanceof:'Ox\Interop\Cda\CExchangeCDA'}}
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
    {{mb_value object=$object field="statut_acquittement"}}

    <br />
    <span class="text compact">
      {{foreach from=$object->_observations item=_observation}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}');">
         {{$_observation.code}}
       </span>
      {{/foreach}}
    </span>
  </td>
  <td class="{{if !$object->message_valide}}error{{/if}} narrow" style="text-align: center">
    <a target="_blank"
       href="?m=eai&a=download_exchange&exchange_guid={{$object->_guid}}&dialog=1&suppressHeaders=1&message=1"
       class="far fa-save notext" style="font-size: large;"></a>
  </td>
  <td
    class="{{if (!$statut_acq && $object->_self_sender) || $object|instanceof:'Ox\Interop\Cda\CExchangeCDA'}}hatching{{elseif !$object->acquittement_valide}}warning{{/if}} narrow"
    style="text-align: center">
      {{if $object->_acquittement}}
        <a target="_blank"
           href="?m=eai&a=download_exchange&exchange_guid={{$object->_guid}}&dialog=1&suppressHeaders=1&ack=1"
           class="far fa-save notext" style="font-size: large;"></a>
      {{/if}}
  </td>
</tr>
{{/if}}
