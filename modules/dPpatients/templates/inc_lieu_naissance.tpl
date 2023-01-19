{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=full value=false}}

{{if $object->_code_insee == "99999" || is_null($object->_code_insee)}}
  {{tr}}Unknown{{/tr}}
{{else}}
    {{if $full}}
        {{mb_value object=$object field="lieu_naissance"}}
        {{if $object->pays_naissance_insee == 250}}
            ({{tr}}CPatient-commune_naissance_insee-court{{/tr}}: {{mb_value object=$object field="commune_naissance_insee"}} - {{mb_label object=$object field="cp"}}: {{mb_value object=$object field="cp_naissance"}})
        {{elseif $object->pays_naissance_insee != 250 && $object->cp_naissance}}
            ({{mb_label object=$object field="cp"}}: {{mb_value object=$object field="cp_naissance"}})
        {{/if}}
        {{mb_value object=$object field="_pays_naissance_insee"}}
        {{if $object->pays_naissance_insee != 250}}({{tr}}CPaysInsee-country{{/tr}}: {{mb_value object=$object field="_code_insee"}}){{/if}}
    {{else}}
        {{mb_value object=$object field="lieu_naissance"}}
        {{if $object->pays_naissance_insee == 250}}
          ({{tr}}CPatient-commune_naissance_insee-court{{/tr}}: {{mb_value object=$object field="commune_naissance_insee"}})
        {{/if}}
        <span {{if $object->pays_naissance_insee != 250 && $object->lieu_naissance}}class="me-margin-left-5"{{/if}}>{{mb_value object=$object field="_pays_naissance_insee"}}</span>
        {{if $object->pays_naissance_insee != 250}}
          ({{tr}}CPaysInsee-country{{/tr}}: {{mb_value object=$object field="_code_insee"}})
        {{/if}}
    {{/if}}
{{/if}}
