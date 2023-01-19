{{*
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        Control.Tabs.create('tabs-configure', true);
        Configuration.edit(
            'lpp',
            'CGroups',
            $('CConfigEtab')
        );
    });
</script>

<ul id="tabs-configure" class="control_tabs">
    <li><a href="#database">{{tr}}config-db-dbname{{/tr}}</a></li>
    <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
</ul>

<div id="database" style="display: none;">
    {{mb_include template=database_config}}
</div>

<div id="CConfigEtab" style="display: none">

</div>
