{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=Stats ajax=$ajax}}

<script>
    Main.add(() => {
        Jfse.displayAutocomplete('user_management/autocomplete', 'user_view', null, {
            updateElement: (selected) => {
                $('jfse_id').value = selected.dataset.jfseUserId;
                $('user_view').value = selected.querySelector('.view').innerHTML;
            }
        });

        var form = getForm('stats-filter');
        Calendar.regField(form.begin);
        Calendar.regField(form.end);
    })
</script>

<form name="stats-filter" method="post" onsubmit="return false;">
    <table class="form" id="form-stats-jfse">
        <tr>
            <th class="title" colspan="2">{{tr}}CStatsController{{/tr}}</th>
        </tr>
        <tr>
            <th class="me-w50">{{tr}}CJfseUserView{{/tr}}</th>
            <td>
                <input type="text" id="user_view" name="name">
                <input type="hidden" id="jfse_id" name="jfse_id">
            </td>
        </tr>
        <tr>
            <th>{{tr}}StatRequest-Amount days last transmission{{/tr}}</th>
            <td><input type="checkbox" name="choice" value="1"></td>
        </tr>
        <tr>
            <th>{{tr}}StatRequest-Amount pending invoices{{/tr}}</th>
            <td><input type="checkbox" name="choice" value="2"></td>
        </tr>
        <tr>
            <th>{{tr}}StatRequest-Total rejected invoices{{/tr}}</th>
            <td><input type="checkbox" name="choice" value="3" id="choice-3" onchange="Stats.toggleDatesForm()"></td>
        </tr>
        <tr>
            <th>{{tr}}StatRequest-Amount invoices between dates{{/tr}}</th>
            <td><input type="checkbox" name="choice" value="4" id="choice-4" onchange="Stats.toggleDatesForm()"></td>
        </tr>
        <tr class="dates" style="display: none;">
            <th>{{tr}}common-Date|pl{{/tr}}</th>
            <td>
                {{tr}}StatRequest-Date begin{{/tr}} <input type="hidden" name="begin"><br>
                {{tr}}StatRequest-Date end{{/tr}} <input type="hidden" name="end">
            </td>
        </tr>
        <tr>
            <td colspan="2" class="me-text-align-center">
                <button type="button" class="search" onclick="Stats.getResults(this.form)">{{tr}}Search{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>

<div id="stats"></div>
