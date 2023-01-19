{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=correspondant}}
{{mb_script module=patients script=autocomplete}}

<button type="button" class="new" onclick="Correspondant.edit(0, '{{$object->_id}}', Correspondant.refreshList.curry('{{$object->_id}}'))">
  {{tr}}CCorrespondantPatient-title-create{{/tr}}
</button>
<div id="list-correspondants"></div>

<script type="text/javascript">
Main.add(function(){
  try {
    Correspondant.refreshList({{$object->_id}});
  } catch(e) {}
});
</script>