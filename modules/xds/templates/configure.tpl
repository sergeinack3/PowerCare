{{*
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        Control.Tabs.create('tabs-configure', true, {
            afterChange: function (container) {
                if (container.id == "config-etab") {
                    Configuration.edit('xds', ['CGroups'], $('config-etab'));
                }
            }
        });
    });
</script>

<ul id="tabs-configure" class="control_tabs">
    <li><a href="#config-etab">{{tr}}XDS-msg-config etab{{/tr}}</a></li>
    <li><a href="#config">{{tr}}XDS-msg-config static{{/tr}}</a></li>
</ul>

<div id="config-etab" style="display: none;">
    {{mb_include template=inc_configure_source}}
</div>

<div id="config">
    <form name="editConfig" method="post" onsubmit="return onSubmitFormAjax(this);">
        {{mb_configure module=$m}}

        <table class="form">
            <tr>
                <th class="title" colspan="2">Configuration</th>
            </tr>

            <tr>
                <td class="button" colspan="2">
                    <button class="submit">{{tr}}Save{{/tr}}</button>
                </td>
            </tr>
        </table>
    </form>
</div>
