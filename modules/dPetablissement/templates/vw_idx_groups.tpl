{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=autocomplete}}
{{mb_script module=dPetablissement script=Group}}
<table class="main layout">
  <tr>
    <td class="halfPane">

      <!-- Liste des entités juridiques -->
      <table class="main tbl">
        <tr>
          <th class="title" colspan="3">
            <button type="button" class="new me-primary" onclick="Group.addeditLegalEntity()"  style="float: left">{{tr}}CLegalEntity new{{/tr}}</button>
            {{tr}}CLegalEntity all{{/tr}}
          </th>
        </tr>
        <tr>
          <th class="category"></th>
          <th class="category">{{mb_label class=CLegalEntity field=name}}</th>
          <th class="category">{{mb_label class=CLegalEntity field=code}}</th>
        </tr>
        {{foreach from=$legal_entities item=_legal_entity}}
          <tr>
            <td class="narrow">
              <button class="edit notext"  onclick="Group.addeditLegalEntity('{{$_legal_entity->_id}}')">></button>
            </td>
            <td>
              {{mb_value object=$_legal_entity field=name}}
            </td>
            <td>
              {{mb_value object=$_legal_entity field=code}}
            </td>
          </tr>
          {{foreachelse}}
          <tr>
            <td class="empty" colspan="3">{{tr}}CLegalEntity.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
    <td class="halfPane">

      <!-- Liste des établissements -->
      <table class="main tbl">
        <tr>
          <th class="title" colspan="5">
            {{if $can->edit}}
              <button onclick="Group.addedit()" class="new me-primary" style="float: left">
                {{tr}}CGroups-button new{{/tr}}
              </button>
            {{/if}}
            {{tr}}CGroups all{{/tr}}
          </th>
        </tr>
        <tr>
        <tr>
          <th class="category"></th>
          <th class="category">{{tr}}CGroups-title name{{/tr}}</th>
          <th class="category">{{tr}}CGroups-title associated functions{{/tr}}</th>
          <th class="category">{{tr}}CGroups-title associated legal entity{{/tr}}</th>
          <th class="category">{{tr}}CGroups-title structure{{/tr}}</th>
        </tr>
        {{foreach from=$groups item=_group}}
          <tr id="row-{{$_group->_guid}}">
            {{if $can->edit}}
              <td class="narrow">
                <button class="edit notext" onclick="Group.addedit('{{$_group->_id}}')"></button>
              </td>
            {{/if}}
            <td>
              {{mb_value object=$_group field=text}}
            </td>
            <td class="text narrow">
              {{if $_group->_ref_functions}}
                {{$_group->_ref_functions|@count}}
              {{/if}}
            </td>
            <td class="narrow text">
              {{if $_group->legal_entity_id}}
                {{$_group->_ref_legal_entity->name}}
              {{else}}
                <span class="empty">{{tr}}CLegalEntity.none{{/tr}}</span>
              {{/if}}
            </td>
            <td class="narrow">
              <button type="button" class="lookup" onclick="Group.viewStructure('{{$_group->_id}}');">
                {{tr}}CGroups-button see structure{{/tr}}
              </button>
            </td>
          </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>


