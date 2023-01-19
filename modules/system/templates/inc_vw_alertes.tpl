{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=alerts value=$object->_refs_alerts_not_handled}}
{{assign var=object_guid value=$object->_guid}}

{{assign var=ampoule_see_action value=0}}

{{if "dPprescription"|module_active}}
  {{assign var="ampoule_see_action" value=$app->user_prefs.ampoule_see_action}}
{{/if}}

<table class="tbl">
  <tr>
    <th class="title" colspan="2">
      {{$alerts|@count}} alertes
      {{if $alert_ids|@count && (!$object|instanceof:'Ox\Mediboard\Prescription\CPrescription'
          || (!"dPprescription general hide_bttn_closeAllAlertes"|gconf && !in_array($ampoule_see_action, array("2", "3"))))}}
        <form name="closeAlertes-{{$level}}-{{$object_guid}}" method="post"
              onsubmit="return onSubmitFormAjax(this, (function() {
                this.up('div.tooltip').remove();
                Alert.callback();}).bind(this));">
          <input type="hidden" name="m"         value="system" />
          <input type="hidden" name="dosql"     value="do_alert_aed" />
          <input type="hidden" name="alert_ids" value="{{"-"|implode:$alert_ids}}" />
          <input type="hidden" name="handled"   value="1" />
          <button type="submit" class="singleclick tick">
            Traiter toutes les alertes
          </button>
        </form>
      {{/if}}

      {{if $object|instanceof:'Ox\Mediboard\Prescription\CPrescription'}}
        <div class="text">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_ref_object->_guid}}')">
            {{$object->_ref_object->_view}}
          </span>
        </div>
      {{/if}}
    </th>
  </tr>
  {{foreach from=$alerts item=_alert}}
  <tr>
    <td class="narrow">
      {{assign var=can_treat value=1}}

      {{if $object|instanceof:'Ox\Mediboard\Prescription\CPrescription'}}
        {{assign var=can_treat value=0}}

        {{if in_array($ampoule_see_action, array("0", "1"))
          || ($ampoule_see_action === "4" && $app->_ref_user->function_id === $_alert->_ref_user->function_id)
          || ($ampoule_see_action === "5" && $_alert->_edit_access)}}

          {{assign var=can_treat value=1}}
        {{/if}}
      {{/if}}

      {{if $can_treat}}
        <form name="editAlert-{{$_alert->_id}}" method="post"
              onsubmit="return onSubmitFormAjax(this, (function() {
                this.up('div.tooltip').remove();
                Alert.callback();}).bind(this));">
          <input type="hidden" name="m" value="system" />
          <input type="hidden" name="dosql" value="do_alert_aed" />
          {{mb_key object=$_alert}}
          <input type="hidden" name="handled" value="1" />
          <button type="submit" class="tick notext">Traiter</button>
        </form>
      {{/if}}
    </td>
    <td class="text compact">
      {{mb_value object=$_alert field=comments}}
    </td>
  </tr>
  {{/foreach}}
</table>
