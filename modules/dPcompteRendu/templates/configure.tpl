{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  correctAligns = function() {
    urlCorrectAlign = new Url("compteRendu", "vw_correct_aligns")
      .requestModal("80%", "80%");
  };

  Main.add(function() {
    Control.Tabs.create("tabs_modeles", true);
    Configuration.edit(
      'dPcompteRendu',
      ['CGroups'],
      $('CConfigEtab')
    );
  });
</script>

<form name="editConfig" action="?m={{$m}}&{{$actionType}}=configure" method="post" onsubmit="return checkForm(this)">
  {{mb_configure module=$m}}

  <ul class="control_tabs" id="tabs_modeles">
    <li>
      <a href="#modeles">{{tr}}module-dPcompteRendu-court{{/tr}}</a>
    </li>
    <li>
      <a href="#tools">{{tr}}Tools{{/tr}}</a>
    </li>
    <li>
      <a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a>
    </li>
  </ul>

  <div id="modeles" style="display: none;">
    {{mb_include template=CCompteRendu_config}}
  </div>
  <div id="tools" style="display: none;">
    {{mb_include template=CCompteRendu_tools_config}}
  </div>
  <div id="CConfigEtab" style="display: none"></div>
</form>
