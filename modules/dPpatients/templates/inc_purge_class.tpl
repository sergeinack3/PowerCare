{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <td width="20%">
      <div class="small-warning">{{tr}}system-Infos purge {{$class_name}}{{/tr}}</div>

      <form name="purge-by-class-{{$class_name}}" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-purge-{{$class_name}}')">
        <input type="hidden" name="m" value="dPpatients"/>
        <input type="hidden" name="dosql" value="do_purge_objects"/>
        <input type="hidden" name="class_name" value="{{$class_name}}"/>

        <table class="main form">
          <tr>
            <th>{{tr}}system-purge-count{{/tr}}</th>
            <td>
              <input readonly type="number" value="{{$count}}" name="total_count"/>
            </td>
          </tr>

          <tr>
            <th>{{tr}}Start at{{/tr}}</th>
            <td><input type="number" name="start" value="0" step="10"/></td>
          </tr>

          <tr>
            <th>{{tr}}system-purge-step{{/tr}}</th>
            <td><input type="number" name="step" value="10" step="10"/></td>
          </tr>

          <tr>
            <th>{{tr}}system-purge-max_id{{/tr}}</th>
            <td><input type="number" name="max_id" value=""/></td>
          </tr>

          <tr>
            <th>{{tr}}Auto{{/tr}}</th>
            <td><input type="checkbox" name="continue" value="1"/></td>
          </tr>

          <tr>
            <td class="button" colspan="2">
              <button type="button" class="trash" onclick="confirmPurgeObjects(this.form, '{{$class_name}}')">{{tr}}system-action-purge{{/tr}}</button></td>
          </tr>
        </table>
      </form>
    </td>

    <td>
      <div id="result-purge-{{$class_name}}"></div>
    </td>
  </tr>
</table>
