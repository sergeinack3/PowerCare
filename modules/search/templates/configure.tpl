{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=search script=Search}}

<script>
  Main.add(function () {
    var tabs = Control.Tabs.create('tabs-configure', true, {
      afterChange: function (container) {
        switch (container.id) {
          case "CConfigEtab"    :
            Configuration.edit('search', 'CGroups', container.id);
            break;
          case "CConfigServeur" :
            Search.configServeur();
            break;
          case "CConfigES"      :
            Search.configES();
            break;
          case "CConfigStat" :
            Search.configStat();
            break;
          case "CConfigQuery" :
            Search.configQuery();
            break;
          default :
            Configuration.edit('search', 'CGroups', container.id);
            break;
        }
      }
    });
  });
</script>

<table class="main">
  <tr>
    <td>
      <ul id="tabs-configure" class="control_tabs">
        <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
        <li><a href="#CConfigServeur">{{tr}}CConfigServeur{{/tr}}</a></li>
        <li><a href="#CConfigES">{{tr}}CConfigElastic{{/tr}}</a></li>
        <li><a href="#CConfigStat">{{tr}}CConfigStat{{/tr}}</a></li>
        <li><a href="#CConfigQuery">{{tr}}CConfigQuery{{/tr}}</a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td>
      <div id="CConfigEtab" style="display: none"></div>
      <div id="CConfigServeur" style="display: none"></div>
      <div id="CConfigES" style="display: none"></div>
      <div id="CConfigStat" style="display: none"></div>
      <div id="CConfigQuery" style="display: none"></div>
    </td>
  </tr>
</table>