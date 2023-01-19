{{*
 * @package Mediboard\openData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-HDEtablissement" method="post" onsubmit="return onSubmitFormAjax(this,{onComplete: Control.Modal.close});">
    {{mb_key object=$HDEtablissement}}
    {{mb_class object=$HDEtablissement}}
    <input type="hidden" name="del" value="" />

    <table class="main form">
        <tr>
            <th>
                {{mb_label object=$HDEtablissement field=finess}}
            </th>
            <td>
                {{mb_value object=$HDEtablissement field=finess}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$HDEtablissement field=raison_sociale}}
            </th>
            <td>
                {{mb_value object=$HDEtablissement field=raison_sociale}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$HDEtablissement field=adresse}}
            </th>
            <td>
                {{mb_field object=$HDEtablissement field=adresse form="edit-HDEtablissement"}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$HDEtablissement field=cp}}
            </th>
            <td>
                {{mb_field object=$HDEtablissement field=cp form="edit-HDEtablissement"}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$HDEtablissement field=ville}}
            </th>
            <td>
                {{mb_field object=$HDEtablissement field=ville form="edit-HDEtablissement"}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$HDEtablissement field=commune_insee}}
            </th>
            <td>
                {{mb_field object=$HDEtablissement field=commune_insee form="edit-HDEtablissement"}}
            </td>
        </tr>
        <tr>
            <td colspan="2" class="button">
                <button type="submit" class="save">{{tr}}common-action-Save{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>