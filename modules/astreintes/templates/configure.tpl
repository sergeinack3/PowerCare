{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    Configuration.edit(
      'astreintes',
      ['CGroups'],
      'context-config-groups'
    );
  });
</script>

<div id="context-config-groups"></div>

<script>
  offlineListAstreinte = function() {
    var url = new Url("astreintes", "offlineListAstreintes");
    url.addParam("dialog", 1);
    url.addParam('period', 'month');
    url.pop(700, 600, $T("CPlageAstreinte-list"));
  };
</script>

<form name="editConfigAstreintes" action="?m={{$m}}&{{$actionType}}=configure" method="post" onsubmit="return checkForm(this)">
  {{mb_configure module=$m}}
  <table class="form">
    {{mb_include module=system template=configure_shortcut shortcut=ButtonAstreintesShortcut}}

    <tr><th class="category" colspan="10">{{tr}}Offline{{/tr}}</th></tr>

    <tr>
      <td class="button" colspan="2">
        <a class="button search" href="#" onclick="offlineListAstreinte();">{{tr}}CPlageAstreinte-list{{/tr}} </a>
      </td>
    </tr>

    <tr><td class="button" colspan="2"><button class="modify" type="submit">{{tr}}Save{{/tr}}</button></td></tr>
  </table>
</form>
