{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">{{tr}}CInteropActor-msg-List config to migrate|pl{{/tr}}</div>

<table class="main tbl">
    <tr>
        <th>{{tr}}CInteropActor-msg-Connector|pl{{/tr}}</th>
        <th>{{tr}}CInteropActor-msg-Configuration|pl{{/tr}}</th>
    </tr>

    {{foreach from=$report item=_report}}
        <tr>
            <td>{{$_report.actor->_view}}</td>
            <td>
                {{if !$_report.norm}}
                    {{tr}}CInteropActor-msg-None configuration to migrate{{/tr}}
                {{else}}
                    <ul>
                        {{foreach from=$_report.norm item=_norm}}
                            <li>{{$_norm}}</li>
                        {{/foreach}}
                    </ul>
                {{/if}}
            </td>
        </tr>
    {{/foreach}}
</table>


