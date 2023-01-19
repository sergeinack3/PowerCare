{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editcomment-actengap" action="" method="post" onsubmit="return onSubmitFormAjax(this)">
    {{mb_key object=$acte_ngap}}
    {{mb_class object=$acte_ngap}}
    <table class="form">
        {{mb_include  module=system template=inc_form_table_header object=$acte_ngap}}
        <tr>
            <th>{{mb_label object=$acte_ngap field=comment_acte}}</th>
            <td>
                {{mb_field object=$acte_ngap field=comment_acte value=$comment_acte prop="text helped" form="editcomment-actengap"
                    aidesaisie="validateOnBlur: 0"}}
            </td>
        </tr>
        <tr>
            <td class="button" colspan="2">
                {{if !$acte_ngap->_id}}
                    <button class="submit" type="button" onclick="ActesNGAP.submitComment('{{$name_form}}')">{{tr}}Save{{/tr}}</button>
                {{else}}
                    <button class="submit" type="button" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
                {{/if}}
            </td>
        </tr>
    </table>
</form>
