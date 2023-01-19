{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=Version ajax=$ajax}}

<script type="text/javascript">
    Main.add(() => {
        Version.searchAutocomplete();
    });
</script>

<table class="me-w100 tbl">
    <tr>
        <th class="me-w50">{{tr}}CardConfigReader-Software{{/tr}}</th>
        <th>{{tr}}config-jfse-API{{/tr}}</th>
    </tr>
    <tr>
        <td>
            <form action="">
                <input type="text" id="jfse_users_autocomplete" name="name">
                <input type="hidden" id="jfse_user_id" name="jfse_user_id">

                <button type="button" class="search" onclick="Version.software(this.form)">
                    {{tr}}CardConfigReader-Software{{/tr}}
                </button>
            </form>
        </td>

        <td>
            <button type="button" class="search" onclick="Version.api()">{{tr}}config-jfse-API{{/tr}}</button>
        </td>
    </tr>

    <tr>
        <td id="software" class="me-valign-top"></td>
        <td id="api" class="me-valign-top"></td>
    </tr>
</table>





