{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        Search.words_request = '{{$words}}';

        {{if $pagination}}
        Search.updateForm('{{$start}}', '{{$stop}}', '{{$nbresult}}');
        {{/if}}
    });
</script>

<style>

    .divInfoResultats {
        text-align: center;
        margin: 10px;
        color: #808080;
        font-size: 12px;
        margin-bottom: 20px;
    }

    .divResults {
        margin: 10px;
        margin-left: 5px;
    }

    .divDoc {
        border-left: 2px solid white;
        margin-bottom: 12px;
        padding: 2px;
    }

    .divDoc:nth-child(odd) {
        background: #efeff0;
    }

    .divDoc:hover {
        border-left: 2px solid grey;
    }

    .divType {
        border-radius: 5px;
        background: rgb(66, 133, 244);
        color: white;
        padding: 3px;
        display: inline-block;
    }

    .divTypeAggregate {
        background: #0DC143;
    }

    .divType:hover {
        text-decoration: underline dotted;
    }

    .divTitre {
        margin-left: 2px;
        color: rgb(17, 85, 204);
        font-size: 14px;
        display: inline-block;
        border: 0 !important;
    }

    .divPatient {
        display: inline-block;
        margin-left: 2px;
        border: 0 !important;
    }

    .divPatient:hover {
        text-decoration: underline dotted;
    }

    .divBody {
        margin-top: 2px;
        margin-bottom: -10px;
    }

    .divBodyAggregate {
        margin-left: 15px;
        margin-top: 5px;
        border-radius: 5px;
        background: rgb(66, 133, 244);
        color: white;
        padding: 3px;
        display: inline-block;
    }

    .divBodyAggregate:hover {
        cursor: pointer;

    }

    .divDate, .divAuthor {
        color: #808080;
        display: inline-block;
        border: 0 !important;
    }

    .divAuthor:hover {
        text-decoration: underline dotted;
    }

    .btnChangePage {
        margin: 10px;
    }

</style>

{{mb_default var=filters_state value=true}}

{{if $filters_state}}
    <table class="form">
        <tr>
            <th class="title" colspan="8">{{tr}}Filters{{/tr}}</th>
        </tr>
        <tr>
            <th>{{tr}}CSearchQueryFilter-Expression{{/tr}}</th>
            <td>{{$words}}</td>
            <th>{{tr}}AdvancedSearch-Date|pl{{/tr}}</th>
            <td>
                {{if $date_min}}{{$date_min}}{{else}}{{tr}}AdvancedSearch-Infinite{{/tr}}{{/if}}
                ->
                {{if $date_max}}{{$date_max}}{{else}}{{tr}}AdvancedSearch-Infinite{{/tr}}{{/if}}
            </td>
            <th>Patient</th>
            <td>{{if $patient}}{{mb_value object=$patient}}{{else}}N/A{{/if}}</td>
            <th>User</th>
            <td>{{if $user}}{{mb_value object=$user}}{{else}}N/A{{/if}}</td>
        </tr>
    </table>
{{/if}}

<!-- PAGINATION -->
{{if $pagination}}
    <div class="divInfoResultats">
        {{if $aggregate }}
            Top {{'Ox\Mediboard\Search\CSearch'|const:REQUEST_AGG_SIZE}} {{tr}}mod-search-aggregate{{/tr}} -
        {{else}}
            {{if $nbresult > 0}}
                {{assign var=start value=$start+1}}
                {{$start|integer}} - {{$stop|integer}} sur
            {{/if}}
        {{/if}}
        {{$nbresult|integer}} {{tr}}mod-search-results{{/tr}} ({{tr}}mod-search-results-info{{/tr}} {{$time}}ms)
    </div>
{{/if}}

<!-- DISPLAY AGGREGATE -->
{{if $aggregate}}
<div class="divResults">
    {{foreach from=$results item=_result}}
        <div class="divDoc">
            <div class="divTitre">
                <div class="divType divTypeAggregate"
                     onmouseover="ObjectTooltip.createEx(this, '{{$_result->getKey()}}')">
                    {{$_result->getTitle()}}
                </div>
                {{if $_result->getPatient()}}
                    {{mb_value object=$_result->getPatient()}}
                {{/if}}
            </div>
            <br>
            <div class="divBodyAggregate"
                 onclick="Search.startWithAggregate('{{$_result->getTitle()}}','{{$_result->getKey()}}')">
                {{$_result->getCount()}} {{tr}}mod-search-results-documents{{/tr}}
            </div>
            <br>
            {{if $_result->getAuthor()}}
                {{mb_value object=$_result->getAuthor()}}
            {{/if}}
        </div>
    {{/foreach}}
</div>
{{else}}
<!-- DISPLAY DOCS -->
<div class="divResults">
    {{foreach from=$results key=_key item=_result}}
        <!-- GUID -->
        {{assign var=guid value=$_result->getGuid()}}
        <!-- TYPE -->
        {{if $_result->getType()|strpos:"CExObject" !== false}}
            {{assign var=type value="CExObject"}}
        {{else}}
            {{assign var=type value=$_result->getType()}}
        {{/if}}
        <div class="divDoc" id="divDoc_{{$_key}}">
            <div class="divType" onmouseover="ObjectTooltip.createEx(this, '{{$guid}}')">
                {{tr}}{{$type}}{{/tr}}
            </div>
            <div class="divTitre">
                {{$_result->getTitle()}} -

                {{if $_result->getPatient()}}
                    {{mb_value object=$_result->getPatient()}}
                {{/if}}
            </div>
            <br>
            <div class="divBody">{{$_result->getBody($obfuscate)|utf8_decode|smarty:nodefaults}}</div>
            <br>
            {{if $_result->getAuthor()}}
                {{mb_value object=$_result->getAuthor()}}
                &nbsp;-&nbsp;
            {{/if}}
            <div class="divDate">{{$_result->getStringDate()}}</div>
        </div>
    {{/foreach}}
    {{if $pagination}}
        {{if $stop < $nbresult}}
            <button type="button" class="btnChangePage button search"
                    onclick="Search.changePage();this.style.display = 'none'">
                {{tr}}mod-search-more-results{{/tr}}
            </button>
        {{/if}}
    {{/if}}
{{/if}}
</div>
