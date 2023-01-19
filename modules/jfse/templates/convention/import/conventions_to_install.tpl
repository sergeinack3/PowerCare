{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
    <tr>
        <th>
            {{tr}}CConvention-elements to install{{/tr}}
        </th>
    </tr>
    {{if $conventions_to_install|@count}}
        <tr>
            {{mb_include module=jfse template=convention/convention/list conventions=$conventions_to_install}}
        </tr>
    {{else}}
        <tr>
            <td>{{tr}}CConvention-no convention to install{{/tr}}</td>
        </tr>
    {{/if}}
    <tr>
        <th>
            {{tr}}CGrouping-elements to install{{/tr}}
        </th>
    </tr>
    {{if $groupings_to_install|@count}}
        <tr>
            {{mb_include module=jfse template=convention/grouping/list groupings=$groupings_to_install}}
        </tr>
    {{else}}
        <tr>
            <td>{{tr}}CGrouping-no grouping to install{{/tr}}</td>
        </tr>
    {{/if}}
</table>

