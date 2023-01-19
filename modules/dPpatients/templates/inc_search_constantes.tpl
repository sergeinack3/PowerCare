{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=dPpatients script=dashboard}}

<table class="tbl">
  <tbody>
  {{if $releves|@count > 0}}
    <div id="div_constant">
          {{mb_include module=dPpatients template=inc_search_constants}}
    </div>
  {{/if}}
  </tbody>
</table>
