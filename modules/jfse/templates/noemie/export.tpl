{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
    Main.add(function() {
        let form = getForm('exportNoemiePayments');
        Calendar.regField(form.elements['date_min']);
        Calendar.regField(form.elements['date_max']);
    });
</script>

<form name="exportNoemiePayments" method="post" action="?m=jfse&raw=jfseIndex" target="_blank">
    <input type="hidden" name="m" value="jfse">
    <input type="hidden" name="a" value="index"/>
    <input type="hidden" name="route" value="noemie/exportPayments"/>
    <input type="hidden" name="jfse_user_id" value="{{$jfse_user_id}}">

    <table class="form">
        <tr>
            {{me_form_field nb_cells=1 label="date-deb"}}
                <input type="hidden" name="date_min" class="date" value="">
            {{/me_form_field}}
            {{me_form_field nb_cells=1 label="date-fin"}}
                <input type="hidden" name="date_max" class="date" value="">
            {{/me_form_field}}
        </tr>
        <tr>
            <td class="button" colspan="2">
                <button type="submit" class="download">{{tr}}Export{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>
