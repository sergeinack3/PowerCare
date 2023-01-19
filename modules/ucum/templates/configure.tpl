{{*
 * @package Mediboard\Ucum
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<script>
    Main.add(function () {
        Control.Tabs.create('tabs-configure', true, {
            afterChange: function (container) {
                if (container.id == "CConfigEtab") {
                    Configuration.edit('ucum', ['CGroups'], $('CConfigEtab'));
                }
            }
        });
    });
</script>

<ul id="tabs-configure" class="control_tabs">
    <li><a href="#CSourceHttp">{{tr}}CSourceHTTP{{/tr}} </a></li>
    <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}} </a></li>
</ul>

<div id="CSourceHttp" style="display: none">
    {{mb_include module=system template=inc_config_exchange_source source=$ucum_source}}
    {{mb_include module=system template=inc_config_exchange_source source=$ucum_source_search}}
</div>
<div id="CConfigEtab" style="display: none"></div>

