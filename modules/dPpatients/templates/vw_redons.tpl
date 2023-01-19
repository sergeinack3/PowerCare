{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=redon ajax=$ajax}}

{{assign var=object value=$sejour}}
{{assign var=patient value=$sejour->_ref_patient}}

<script>
  Main.add(function() {
    Redon.sejour_id = '{{$sejour->_id}}';

    Redon.refreshRedons();
  });
</script>

{{mb_include module=soins template=inc_patient_banner}}

<div class="me-margin-top-4">
  {{foreach from="Ox\Mediboard\Patients\CRedon"|static:"list" key=_redon_class item=_redons}}
    <button type="button" class="search" onclick="Redon.selectRedons('{{$_redon_class}}');">{{tr}}CRedon.constante_medicale.{{$_redon_class}}{{/tr}}</button>
  {{/foreach}}
</div>

<div id="redons_area"></div>
