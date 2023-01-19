{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=details ajax=$ajax}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs_stats', true);

    {{if $compte_rendu->type === "body"}}
      Details.statPeriodicalOwner('{{$compte_rendu->_class}}', '{{$compte_rendu->_id}}', null, null, null, 'stat_modele');
    {{/if}}
  });
</script>

<ul id="tabs_stats" class="control_tabs">
  <li>
    <a href="#usage_modele">
      {{tr}}CCompteRendu-Usage{{/tr}}
    </a>
  </li>
  {{if $compte_rendu->type === "body"}}
    <li>
      <a href="#stat_modele">
        {{tr}}common-Statistic|pl{{/tr}}
      </a>
    </li>
  {{/if}}
</ul>

<div id="usage_modele" style="display: none;">
  <table class="main">
    <tr>
      <th class="title" colspan="2">
        '{{$compte_rendu->nom}}'
      </th>
    </tr>

    <tr>
      <td style="width: 50%;">
        <div style="max-height: 550px; overflow-y: auto;">
          <table class="tbl">
            <tr>
              <th>
                {{if $compte_rendu->type == "body"}}{{tr}}CCompteRendu-List of pack|pl{{/tr}}{{else}}{{tr}}CCompteRendu-List of model|pl{{/tr}}{{/if}}
              </th>
            </tr>

            {{foreach from=$modeles item=_modele}}
              <tr>
                <td class="text">
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$_modele->_guid}}')">
                    {{if $compte_rendu->type != "body"}}
                      <a href="#1" onclick="Modele.edit('{{$_modele->_id}}')">
                        {{mb_value object=$_modele field=nom}}
                      </a>
                    {{else}}
                      {{mb_value object=$_modele field=nom}}
                    {{/if}}
                  </span>
                </td>
              </tr>
            {{foreachelse}}
              <tr>
                <td class="empty">
                  {{tr}}CCompteRendu.none{{/tr}}
                </td>
              </tr>
            {{/foreach}}
          </table>
        <div>
      </td>
      <td>
        <div style="max-height: 550px; overflow-y: auto;">
          <table class="tbl">
            <tr>
              <th colspan="2">
                {{tr}}CCompteRendu-User of the model|pl{{/tr}}
              </th>
            </tr>

            {{foreach from=$counts key=_user_id item=_count}}
              {{assign var=user value=$users.$_user_id}}
              <tr>
                <td class="text">
                  {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$user}}
                </td>
                <td class="narrow" style="text-align: center;">
                  {{$_count}}
                </td>
              </tr>
              {{foreachelse}}
              <tr>
                <td colspan="2" class="empty">
                  {{tr}}CMediusers.none{{/tr}}
                </td>
              </tr>
            {{/foreach}}
          </table>
        </div>
      </td>
    </tr>
  </table>
</div>

{{if $compte_rendu->type === "body"}}
  <div id="stat_modele" style="display: none;"></div>
{{/if}}
