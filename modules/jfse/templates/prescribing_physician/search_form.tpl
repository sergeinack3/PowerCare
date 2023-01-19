{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=PrescribingPhysician ajax=$ajax}}

<form name="prescribing_physician_search_form" action="" method="post" onsubmit="return false;">
    <input type="hidden" name="jfse_user_id" value="{{$jfse_user_id}}">
    <table class="form">
        <tr>
            {{me_form_field nb_cells=1 mb_object=$physician mb_field=last_name class='me-padding-5'}}
                <input type="text" name="last_name">
            {{/me_form_field}}

            {{me_form_field nb_cells=1 mb_object=$physician mb_field=first_name class='me-padding-5'}}
                <input type="text" name="first_name">
            {{/me_form_field}}

            {{me_form_field nb_cells=1 mb_object=$physician mb_field=national_id class='me-padding-5'}}
                <input type="text" name="national_id">
            {{/me_form_field}}
        </tr>
        <tr>
            <td colspan="3" class="button">
                <button class="search" type="button"
                        onclick="PrescribingPhysician.searchList(this.form)">{{tr}}Search{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>

<div>
    <table class="tbl">
        <thead>
            <tr>
                <th>{{tr}}CPrescribingPhysician-last_name{{/tr}}</th>
                <th>{{tr}}CPrescribingPhysician-speciality{{/tr}}</th>
                <th>{{tr}}CPrescribingPhysician-type{{/tr}}</th>
                <th class="narrow"></th>
            </tr>
        </thead>
        <tbody id="search_list">
            <tr>
                <td colspan="4" class="empty">
                    {{tr}}No result{{/tr}}
                </td>
            </tr>
        </tbody>
    </table>
</div>
