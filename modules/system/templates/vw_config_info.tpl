{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=page value=0}}

<script>
  Main.add(function() {
    var form = getForm('config-search');
    form.on('change',function () {$V(form.elements.start, 0)});
    form.on('ui:change',function () {$V(form.elements.start, 0)});
  });

  changePageConfig = function(page) {
    var form = getForm('config-search');
    $V(form.elements.start, page, false);
    form.onsubmit();
  };

  vwCompareConfigs = function () {
    var url = new Url('system', 'vw_config_compare');
    url.requestModal('90%', '90%');
  }
</script>

<div align="right">
  <a class="button fas fa-external-link-alt" href="?m=system&raw=ajax_export_configs" target="_blank">{{tr}}CConfiguration-action-export{{/tr}}</a>
  <button type="button" class="hslip" onclick="vwCompareConfigs();">{{tr}}CConfiguration-action-compare{{/tr}}</button>
</div>

<form name="config-search" method="get" onsubmit="return onSubmitFormAjax(this, null, 'info')">
  <input type="hidden" name="m" value="system"/>
  <input type="hidden" name="a" value="ajax_search_config"/>
  <input type="hidden" name="start" value="{{$page}}"/>

  <table class="main form">
    <tr>
      <th><label for="keywords">{{tr}}system-config-search keywords{{/tr}}</label></th>
      <td><input type="text" name="keywords" size="80" style="font-size: 1.4em;" /></td>
    </tr>

    <tr>
      <th>{{tr}}Type|pl{{/tr}}</th>
      <td>
        <input type="checkbox" name="configs" value="1" checked/>
        <label for="configs">{{tr}}Config|pl{{/tr}}</label>

        <input type="checkbox" name="prefs" value="1" checked/>
        <label for="prefs">{{tr}}Preferences{{/tr}}</label>

        <input type="checkbox" name="func_perms" value="1" checked/>
        <label for="func_perms">{{tr}}FunctionalPerms{{/tr}}</label>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button class="button search me-primary">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="info" class="me-padding-0"></div>