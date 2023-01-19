{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr {{if !$_actor->actif}} class="opacity-30" {{/if}} id="line_{{$_actor->_guid}}">
  <td class="narrow compact">
    <button type="button" title="{{tr}}Edit{{/tr}} {{$_actor->_view}}" class="edit compact notext me-tertiary"
            onclick="InteropActor.editActor('{{$_actor->_guid}}');">
      {{tr}}Edit{{/tr}} {{$_actor->_view}}
    </button>
    {{if $_actor|instanceof:'Ox\Interop\Eai\CInteropReceiver'}}
      <button type="button" class="duplicate notext compact me-tertiary" title="{{tr}}Duplicate{{/tr}}"
              onclick="InteropActor.duplicateReceiver('{{$_actor->_guid}}')">
      </button>
      {{if $_actor->_ref_last_exchange}}
        <button type="button" class="list notext compact me-tertiary" title="{{tr}}Exchanges{{/tr}}"
                onclick="InteropActor.openExchangesReceiver('{{$_actor->_guid}}', '{{$_actor->_ref_last_exchange->_class}}')">
        </button>
      {{/if}}
    {{/if}}

    {{if $_actor|instanceof:'Ox\Interop\Eai\CInteropSender'}}
      <button type="button" class="duplicate notext compact me-tertiary" title="{{tr}}Duplicate{{/tr}}"
              onclick="InteropActor.duplicateSender('{{$_actor->_guid}}')">
      </button>
      {{if $_actor->_ref_last_exchange}}
        <button type="button" class="list notext compact me-tertiary" title="{{tr}}Exchanges{{/tr}}"
                onclick="InteropActor.openExchangesSender('{{$_actor->_guid}}', '{{$_actor->_ref_last_exchange->_class}}')">
        </button>
      {{/if}}
    {{/if}}

    {{if $_actor|instanceof:'Ox\Interop\Ftp\CSenderFTP'}}
      {{mb_script module=ftp script=sender_ftp ajax=true}}
      <button type="button" class="notext fas fa-sync me-tertiary" onclick="SenderFTP.dispatch('{{$_actor->_guid}}');"
              title="{{tr}}CSenderFTP-utilities_dispatch{{/tr}}">
        {{tr}}CSenderFTP-utilities_dispatch{{/tr}}
      </button>
    {{/if}}

    {{if $_actor|instanceof:'Ox\Interop\Ftp\CSenderSFTP'}}
      {{mb_script module=ftp script=sender_sftp ajax=true}}
      <button type="button" class="notext fas fa-sync me-tertiary" onclick="SenderSFTP.dispatch('{{$_actor->_guid}}');"
              title="{{tr}}CSenderSFTP-utilities_dispatch{{/tr}}">
        {{tr}}CSenderSFTP-utilities_dispatch{{/tr}}
      </button>
    {{/if}}

    {{if $_actor|instanceof:'Ox\Mediboard\System\CSenderFileSystem'}}
      {{mb_script module=system script=sender_fs ajax=true}}
      <button type="button" class="notext fas fa-sync me-tertiary" onclick="SenderFS.dispatch('{{$_actor->_guid}}');"
              title="{{tr}}CSenderFileSystem-utilities_dispatch{{/tr}}">
        {{tr}}CSenderFileSystem-utilities_dispatch{{/tr}}
      </button>
    {{/if}}

      {{if $_actor->actif}}
        <button type="button" class="fas fa-network-wired notext me-tertiary"
                onclick="InteropActor.testAccessibilitySources('{{$_actor->_guid}}')"
                title="{{tr}}CInteropActor-msg-Test accessibility sources{{/tr}}">
        </button>
      {{/if}}
  </td>

  <td class="text compact">
    <!-- Destinataires éligibles à la transmission/réception de l'INS -->
      {{if $_actor->_is_ins_compatible}}
        <img src="images/icons/logo-ins.png" style="width: 20px;"
             title="{{tr}}CInteropActor-msg-INS compatible actor {{/tr}}"/>
      {{/if}}

    <a href="#" onclick="InteropActor.viewActor('{{$_actor->_guid}}', null, this.up('tr'));"
       title="Afficher l'acteur d'intégration" class="me-inline-block">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_actor->_guid}}');">
        {{$_actor->_view}}
        </span>
    </a>
  </td>

  <td class="narrow">
      {{if $_actor->role != $conf.instance_role}}
        <i class="fas fa-exclamation-triangle" style="color: goldenrod;"
           title="{{tr var1=$_actor->role var2=$conf.instance_role}}CInteropActor-msg-Actor incompatible %s with the instance role %s{{/tr}}"></i>
    {{/if}}

    {{if $_actor->role == "prod"}}
      <strong style="color: red" title="{{tr}}CInteropActor-role.prod{{/tr}}">{{tr}}CInteropActor-role.prod-court{{/tr}}</strong>
    {{else}}
      <span style="color: green" title="{{tr}}CInteropActor-role.qualif{{/tr}}">{{tr}}CInteropActor-role.qualif-court{{/tr}}</span>
    {{/if}}
  </td>

  <td class="narrow text compact">
    {{assign var=group value=$_actor->_ref_group}}

    <span onmouseover="ObjectTooltip.createEx(this, '{{$_actor->_guid}}');">
        {{if $group->_id == $g}}
          <strong>{{$group->code}}</strong>
        {{else}}
          {{$group->code}}
        {{/if}}
    </span>
  </td>

  <td class="narrow" style="text-align: center">
      {{assign var=actor_guid value=$_actor->_guid}}

    {{mb_include module="system" template="inc_form_button_active" field_name="actif" object=$_actor
    onComplete="InteropActor.refreshActor('$actor_guid')"}}
  </td>

  {{if $_actor|instanceof:'Ox\Interop\Eai\CInteropReceiver'}}
    <td class="narrow" style="text-align: center">
      <i class="{{if $_actor->synchronous}}fas fa-exchange-alt{{else}}fas fa-long-arrow-alt-right{{/if}}"
         title="{{tr}}CInteropReceiver-msg-{{if $_actor->synchronous}}bidirectional synchronization{{else}}unidirectional synchronization{{/if}}{{/tr}}"></i>
    </td>
  {{/if}}

  <td id="sources_line_{{$_actor->_guid}}">
    {{mb_include module=eai template=inc_refresh_status_source}}
  </td>

  <td class="narrow" style="text-align: center">
    {{if !$_actor->_ref_last_exchange || !$_actor->_ref_last_exchange->_id}}
      <i class="fa fa-ban" style="color: black;" title="{{tr}}CInteropActor-msg-No message sent{{/tr}}"></i>
    {{elseif $_actor->_ref_last_exchange && ($_actor->_last_exchange_time > $_actor->exchange_format_delayed)}}
      <i class="fa fa-hourglass" style="color: red;"
         title="{{tr var1=$_actor->_last_exchange_time var2=$_actor->_ref_last_exchange->send_datetime}}
             {{$_actor->_parent_class}}-msg-Delayed messages %s at %s{{/tr}}">
      </i>
    {{/if}}
  </td>
</tr>
