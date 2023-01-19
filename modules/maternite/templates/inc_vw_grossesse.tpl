{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite          script=naissance          ajax=true}}
{{mb_script module=monitoringPatient  script=surveillance_perop ajax=true}}

{{assign var=patient   value=$operation->_ref_patient}}
{{assign var=sejour    value=$operation->_ref_sejour}}
{{assign var=grossesse value=$sejour->_ref_grossesse}}

<script>
  Main.add(function () {
    Control.Tabs.create("tab-grossesse", true, {
      afterChange: function (container) {
        switch (container.id) {
        {{if "monitoringMaternite"|module_active && "monitoringMaternite general active_graph_supervision"|gconf}}
          case "surveillance_partogramme":
            SurveillancePerop.showPartogramme('{{$operation->_id}}', 0, 0);
            break;
          case "surveillance_post_partum":
            SurveillancePerop.showPostPartum('{{$operation->_id}}', '{{$grossesse->_id}}', 0, 0);
            break;
        {{/if}}

          case "naissance_area":
            Naissance.reloadNaissances("{{$operation->_id}}");
            break;

          case "grossesse-data":
            new Url("maternite", "ajax_vw_tdb_grossesse", "action")
              .addParam("grossesse_id", '{{$grossesse->_id}}')
              .addParam("operation_id", "{{$operation->_id}}")
              .addParam("with_buttons", "{{$with_buttons}}")
              .addParam("is_tdb_maternite", 0)
              .addParam("standalone", 1)
              .addParam("creation_mode", 0)
              .requestUpdate("grossesse-data");
        }
      }
    });
  });
</script>

{{if $patient->nom|is_numeric}}
  <div class="big-info">
    {{tr}}CGrossesse-born_under_x{{/tr}}
  </div>
{{/if}}

<ul class="control_tabs small" id="tab-grossesse">
  <li><a href="#grossesse-data">{{tr}}CGrossesse{{/tr}}</a></li>
  {{if "monitoringMaternite"|module_active && "monitoringMaternite general active_graph_supervision"|gconf}}
    <li><a href="#surveillance_partogramme">{{tr}}CSupervisionGraphPack.use_contexts.parto{{/tr}}</a></li>
    <li><a href="#surveillance_post_partum">{{tr}}CSupervisionGraphPack.use_contexts.post_partum{{/tr}}</a></li>
  {{/if}}
  <li><a href="#naissance_area">{{tr}}CNaissance{{/tr}}(s)</a></li>
</ul>

<div id="grossesse-data" style="display: none;"></div>
<div id="surveillance_partogramme" class="me-padding-1" style="display: none;"></div>
<div id="surveillance_post_partum" class="me-padding-1" style="display: none;"></div>
<div id="naissance_area" style="display: none;" class="me-padding-0"></div>

