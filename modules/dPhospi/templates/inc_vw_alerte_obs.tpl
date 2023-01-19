{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=alerte value=$obs->_ref_alerte}}

{{if !$alerte || !$alerte->_id}}
  {{mb_return}}
{{/if}}

{{assign var=img value=ampoule_grey}}
{{if $alerte->handled == 0}}
  {{assign var=img value=ampoule_rose}}
{{/if}}

<div id="alert_obs_{{$obs->_id}}">
  {{if $img == "ampoule_rose"}}
    {{assign var=bulb_function
    value="Alert.showAlerts('`$obs->_guid`', 'observation', 'medium', function() { refreshAlertObs('`$obs->_id`'); Soins.compteurAlertesObs(`$obs->sejour_id`); }, this);"}}
  {{else}}
    {{assign var=bulb_function value="ObjectTooltip.createDOM(this, 'tracabilite_obs_`$obs->_id`');"}}
  {{/if}}
  {{mb_include module=system template=inc_bulb img_ampoule=$img event_trigger="onmouseover"
  event_function=$bulb_function}}

  <div id="tooltip-alerts--medium-{{$obs->_guid}}" style="display: none; height: 400px; width: 400px; overflow-x:auto;"></div>

  <div id="tracabilite_obs_{{$obs->_id}}" style="display: none;">
    <table class="tbl">
      <tr>
        <th class="title" colspan="3">Traçabilité des alertes</th>
      </tr>
      <tr>
        <th>Traité par</th>
        <th class="narrow">Date de création</th>
        <th class="narrow">Date de traitement</th>
      </tr>
      <tr>
        <td>{{$alerte->_ref_handled_user}}</td>
        <td>{{mb_value object=$alerte field=creation_date}}</td>
        <td>{{mb_include module=system template=inc_object_history object=$alerte}} {{mb_value object=$alerte field=handled_date}}</td>
      </tr>
    </table>
  </div>
</div>