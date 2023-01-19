{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  chooseModalGraph = function () {
    var form = getForm("the_choose_graph");
    var url = new Url('patients', 'ajax_courbe_reference_graph');
    url.addParam('patient_id', '{{$patient_id}}');
    url.addParam('graph_name', $V(form.choose_graph));
    url.requestUpdate("courbe_reference");
  };

  Main.add(function () {
    var form = getForm("the_choose_graph");
    chooseModalGraph($V(form.choose_graph));
  });
</script>

<div style="text-align: center;">
  <span>{{tr}}common-Choose a graphic{{/tr}} : </span>
  <form action="" name="the_choose_graph">
    <select name="choose_graph" onchange="chooseModalGraph();">
      <option value="poids"{{if $graph_name == 'poids'}} selected="selected"{{/if}}>{{tr}}CConstantesMedicales-poids{{/tr}}</option>
      <option value="taille"{{if $graph_name == 'taille'}} selected="selected"{{/if}}>{{tr}}CConstantesMedicales-taille{{/tr}}</option>
      <option value="_imc"{{if $graph_name == "_imc"}} selected="selected"{{/if}}>{{tr}}CConstantesMedicales-_imc{{/tr}}</option>
      <option value="perimetre_cranien"{{if $graph_name == "perimetre_cranien"}} selected="selected"{{/if}}>{{tr}}CConstantesMedicales-perimetre_cranien{{/tr}}</option>
      <option value="bilirubine_transcutanee"{{if $graph_name == "bilirubine_transcutanee"}} selected="selected"{{/if}}>{{tr}}CConstantesMedicales-bilirubine_transcutanee{{/tr}}</option>
      <option value="bilirubine_totale_sanguine"{{if $graph_name == "bilirubine_totale_sanguine"}} selected="selected"{{/if}}>{{tr}}CConstantesMedicales-bilirubine_totale_sanguine{{/tr}}</option>
    </select>
  </form>
</div>

<div id="courbe_reference"></div>
