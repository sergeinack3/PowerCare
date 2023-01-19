{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form method="post" id="editFormula">
    <table class="main form">
        <tr>
            <th>{{mb_label object=$formule field=formula_id}}</th>
            <td>{{mb_field object=$formule field=formula_id size=50}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$formule field=label}}</th>
            <td>{{mb_field object=$formule field=label}}</td>
        </tr>
        <tr>
            <th>{{tr}}CFormula-multiplicateur{{/tr}}</th>
            <td>
                <input type="number" name="multiplicateur" step="0.5" min="0" max="20">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CFormula-plafond{{/tr}}</th>
            <td>
                <input type="number" name="plafond" step="0.5" min="0">
            </td>
        </tr>
        <tr>
            {{mb_include module=Jfse template=formula/operand_select field="operande1"}}
        </tr>
        <tr>
            {{mb_include module=Jfse template=formula/operateur_select}}
        </tr>
        <tr>
            {{mb_include module=Jfse template=formula/operand_select field="operande2"}}
        </tr>
        <tr>
            <td colspan="2" class="me-text-align-center">
                <button type="button" onclick="Formula.save(this.form)">
                    {{tr}}Save{{/tr}}
                </button>
                {{if $formule->formula_id}}
                    <button onclick="Formula.delete({{$formule->formula_id}})">
                        {{tr}}Delete{{/tr}}
                    </button>
                {{/if}}
            </td>
        </tr>
    </table>
</form>
