{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry('tabs-configure', true, { afterChange: function(container) {
    if (container.id == "CConfigEtab") {
      Configuration.edit('dPbloc', ['CGroups'], $('CConfigEtab'));
    }
  }
  }));
</script>

{{assign var="class" value="CPlageOp"}}

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
  <li><a href="#actions">{{tr}}Maintenance{{/tr}}</a></li>
  <li><a href="#offline">{{tr}}Offline{{/tr}}</a></li>
</ul>

<div id="CConfigEtab" style="display: none"></div>

<div id="actions" style="display: none;">
  {{mb_include template=inc_config_actions}}
</div>

<div id="offline" style="display: none;">
  <table class="tbl">
    <tr>
      <td>
        <a class="button search" target="_blank" href="?m=bloc&a=view_planning&g={{$g}}&offline=1">
          {{tr}}Bloc-Offline program{{/tr}}
        </a>
      </td>
    </tr>
    <tr>
      <td>
        <a class="button search" target="_blank"
           href="?m=bloc&a=view_planning&g={{$g}}&offline=1&_by_bloc=1&_page_break=1">
          {{tr}}Bloc-Offline program by room{{/tr}}
        </a>
      </td>
    </tr>
    <tr>
      <td>
        <a class="button search" target="_blank" href="?m=bloc&a=offline_preparation_salle&g={{$g}}">
          {{tr}}Bloc-Offline preparation equipment{{/tr}}
        </a>
      </td>
    </tr>
  </table>
</div>
