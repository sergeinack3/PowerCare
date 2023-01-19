{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination total=$total_disciplines current=$page change_page='CDiscipline.changePage'}}

<script>
  Main.add(function() {
    $("listDisciplinesDiv").fixedTableHeaders();
  });
</script>

<div id="listDisciplinesDiv">
  <table class="tbl">
    <tbody>
      {{foreach from=$disciplines item=_discipline}}
        <tr>
          <td>
            <button class="edit notext" onclick="CDiscipline.edit('{{$_discipline->_id}}', this)">{{tr}}Edit{{/tr}}</button>
          </td>
          <td class="text">
            {{$_discipline->_view}}
          </td>
          <td>
            {{$_discipline->categorie}}
          </td>
          <td style="text-align: center;">
            {{$_discipline->_ref_users|@count}}
          </td>
        </tr>
      {{/foreach}}
    </tbody>
    <thead>
      <tr>
        <th class="button narrow"></th>
        <th>{{mb_label object=$discipline field="text"}}</th>
        <th>{{mb_label object=$discipline field="categorie"}}</th>
        <th>{{tr}}CDiscipline-back-users{{/tr}}</th>
      </tr>
    </thead>
  </table>
</div>