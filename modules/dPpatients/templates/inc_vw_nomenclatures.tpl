{{*
* @package Mediboard\Patients
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=antecedent register=true}}
{{mb_script module=loinc    script=loinc  register=true}}
{{mb_script module=snomed   script=snomed register=true}}

<script>
  Main.add(function () {
    Control.Tabs.create('nomenclature_tabs', true);

    Loinc.showLoincFilters('{{$object_guid}}');
    Snomed.showSnomedFilters('{{$object_guid}}');
  });
</script>

<ul id="nomenclature_tabs" class="control_tabs">
  {{if "loinc"|module_active}}
    <li><a href="#loinc">{{tr}}CLoinc-LOINC nomenclature{{/tr}}</a></li>
  {{/if}}

  {{if "snomed"|module_active}}
    <li><a href="#snomed">{{tr}}CSnomed-SNOMED nomenclature{{/tr}}</a></li>
  {{/if}}
</ul>

<div id="loinc" style="display: none;"></div>
<div id="snomed" style="display: none;"></div>


