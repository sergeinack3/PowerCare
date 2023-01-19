{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        $("tab_indexes").fixedTableHeaders();
    });
</script>

<div class="small-info">
    <ul>
        <li><strong>{{tr}}CIndexChecker-msg-Not in DB{{/tr}}</strong> : {{$count_missing_db}}</li>
        <li><strong>{{tr}}CIndexChecker-msg-Not in properties{{/tr}}</strong> : {{$count_not_expected_db}}</li>
    </ul>
</div>
<div id="tab_indexes" >
    <table class="main tbl" class="x-scroll">
        <tbody>


        {{foreach from=$errors key=_class_name item=_problems}}
            {{foreach from=$_problems key=_fields item=_type name=index_errors}}
                <tr>
                    {{if $smarty.foreach.index_errors.first}}
                        <td class="title me-text-align-center" rowspan="{{$_problems|@count}}">{{$_class_name}}</td>
                    {{/if}}

                    <td class="me-text-align-center">{{$_fields}}</td>
                    <td class="me-text-align-center{{if $_type === 'missing_db'}} warning {{elseif $_type === 'ok'}} ok{{/if}}">
                        {{if $_type == 'missing_db'}}
                            {{tr}}CIndexChecker-msg-Not in DB{{/tr}}
                        {{elseif $_type === 'ok'}}
                            {{tr}}common-Enabled{{/tr}}
                        {{/if}}
                    </td>
                    <td class="me-text-align-center{{if $_type === 'not_expected_db'}} warning {{elseif $_type === 'ok'}} ok{{/if}}">
                        {{if $_type == 'not_expected_db'}}
                            {{tr}}CIndexChecker-msg-Not in properties{{/tr}}
                        {{elseif $_type === 'ok'}}
                            {{tr}}common-Enabled{{/tr}}
                        {{/if}}
                    </td>
                </tr>
            {{/foreach}}

            {{foreachelse}}
            <tr>
                <td class="empty">
                    {{tr}}CIndexChecker-msg-No error{{/tr}}
                </td>
            </tr>
        {{/foreach}}
        </tbody>

        <thead>
        <tr>
            <th class="me-text-align-center">{{tr}}common-Class name{{/tr}}</th>
            <th class="me-text-align-center">{{tr}}common-Field|pl{{/tr}}</th>
            <th class="me-text-align-center">{{tr}}CIndexChecker-msg-Db info{{/tr}}</th>
            <th class="me-text-align-center">{{tr}}CIndexChecker-msg-Property info{{/tr}}</th>
        </tr>
        </thead>


    </table>
</div>

