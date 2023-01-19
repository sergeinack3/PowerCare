{{*
 * @package Mediboard\jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=health_insurance}}

<script type="text/javascript">
  Main.add(function () {
    HealthInsurance.searchAutocomplete();
  });
</script>

<form method="post" id="searchHealthInsuranceForm">
  {{mb_label class=CHealthInsurance field=name}}
  <input type="text" id="search_health_insurance" class="autocomplete" name="search_health_insurance" size="50">
</form>

