{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
    Main.add(function() {
        CardlessPatient.setFieldNotNull(CardlessPatient.situation_code_form.elements['situation_code']);
    });
</script>

{{me_form_field nb_cells=$nb_cells}}
    <select id="selectCodeSituation_situation_code" name="situation_code" class="notNull">
        <option value="">&mdash; {{tr}}Select{{/tr}}</option>
        {{foreach from=$situations item=situation}}
            <option value="{{$situation.code}}">{{$situation.code}} &mdash; {{$situation.label}}</option>
        {{/foreach}}
    </select>
    <label for="selectCodeSituation_situation_code" title="{{tr}}CBeneficiary-situation_code-title{{/tr}}">
        {{tr}}CBeneficiary-situation_code{{/tr}}
    </label>
{{/me_form_field}}
