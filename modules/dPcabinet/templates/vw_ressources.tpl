{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=ressource register=true}}
{{mb_script module=files   script=file register=true}}

<script>
  Main.add(function() {
    $("function_id_cab").onchange();
  });
</script>

<select id="function_id_cab" onchange="Ressource.refreshList(this.value)">
  {{mb_include module=mediusers template=inc_options_function list=$functions selected=$function_id}}
</select>

<div id="ressources_area"></div>