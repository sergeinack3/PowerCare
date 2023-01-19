{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td colspan="2">
      <a class="button new me-margin-top-4" href="?m={{$m}}&tab=vw_cellSaver&cell_saver_id=0">{{tr}}CCellSaver.create{{/tr}}</a>
    </td>
  </tr>
  <tr>
    <td class="halfPane">
      <table class="tbl">
        <tr>
          <th class="title" colspan="4">{{tr}}CCellSaver{{/tr}}</th>
        </tr>
        <tr>
          <th>{{tr}}CCellSaver.marque{{/tr}}</th>
          <th>{{tr}}CCellSaver.modele{{/tr}}</th>
        </tr>
        {{foreach from=$cell_saver_list key=id item=cs}}
          <tr>
            <td><a href="?m={{$m}}&tab=vw_cellSaver&cell_saver_id={{$cs->_id}}" title="Voir ou modifier le cell saver">
                {{mb_value object=$cs field=marque}}
              </a>
            </td>
            <td><a href="?m={{$m}}&tab=vw_cellSaver&cell_saver_id={{$cs->_id}}" title="Voir ou modifier le cell saver">
                {{mb_value object=$cs field=modele}}
              </a>
            </td>
          </tr>
          {{foreachelse}}
          <tr>
            <td colspan="3" class="empty">{{tr}}CCellSaver.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
    <td class="halfPane">
      <form name="edit_cellSaver" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        {{mb_class object=$cell_saver}}
        {{mb_key   object=$cell_saver}}
        <input type="hidden" name="del" value="0" />
        <table class="form">
          <tr>
            {{if $cell_saver->_id}}
              <th class="title modify" colspan="2">{{tr}}CCellSaver.modify{{/tr}} {{$cell_saver}}</th>
            {{else}}
              <th class="title me-th-new" colspan="2">{{tr}}CCellSaver.create{{/tr}}</th>
            {{/if}}
          </tr>
          <tr>
            <th>{{mb_label object=$cell_saver field="marque"}}</th>
            <td>{{mb_field object=$cell_saver size=32 field="marque"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$cell_saver field="modele"}}</th>
            <td>{{mb_field object=$cell_saver size=32 field="modele"}}</td>
          </tr>
          <tr>
            <td class="button" colspan="4">
              {{if $cell_saver->_id}}
                <button class="modify me-primary">{{tr}}Save{{/tr}}</button>
                <button type="button" class="trash"
                        onclick="confirmDeletion(this.form, {typeName: '', objName: '{{$cell_saver->_view|smarty:nodefaults|JSAttribute}}'})">
                  {{tr}}Delete{{/tr}}
                </button>
              {{else}}
                <button class="submit me-primary">{{tr}}Create{{/tr}}</button>
              {{/if}}
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>