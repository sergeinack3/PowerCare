{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  nextAudit = function (start) {
    var form = getForm("purge-imported-objects");
    $V(form.elements.start, start);
  };
</script>

<h2>{{tr}}mod-importTools-tab-vw_purge_imported_objects{{/tr}}</h2>

<form name="purge-imported-objects" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-purge-objects')">
  <input type="hidden" name="m" value="importTools"/>
  <input type="hidden" name="dosql" value="do_purge_imported_objects"/>

  <table class="main form">
    <tr>
      <th><label for="purge_classes">{{tr}}importTools-purge classes{{/tr}}</label></th>
      <td>
        <select name="purge_classes">
          {{foreach from=$available_classes item=_class}}
            <option value="{{$_class}}">{{$_class}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th><label for="start">{{tr}}Start{{/tr}}</label></th>
      <td><input type="text" name="start" value="0"/></td>
    </tr>

    <tr>
      <th><label for="step">{{tr}}Step{{/tr}}</label></th>
      <td><input type="text" name="step" value="100"/></td>
    </tr>

    <tr>
      <th><label for="import_tag">{{tr}}importTools-purge import tag{{/tr}}</label></th>
      <td><input type="text" name="import_tag" value="{{"importTools import import_tag"|gconf}}"/></td>
    </tr>

    <tr>
      <th><label for="audit">{{tr}}importTools-purge-audit{{/tr}}</label></th>
      <td><input type="checkbox" name="audit" value="1" checked/></td>
    </tr>

    <tr>
      <th><label for="continue">{{tr}}Continue{{/tr}}</label></th>
      <td><input type="checkbox" name="continue" value="1"/></td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="submit" class="button trash">{{tr}}importTools-purge imported objects{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-purge-objects"></div>