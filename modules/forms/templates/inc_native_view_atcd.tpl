{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cim10 script=CIM}}

{{if "dPmedicament"|module_active}}
  {{mb_script module="dPmedicament" script="medicament_selector"}}
{{/if}}

<script type="text/javascript">
Main.add(function(){
  var url = new Url("dPcabinet", "listAntecedents");

  {{if $object|instanceof:'Ox\Mediboard\Maternite\CGrossesse'}}
    url.addParam("grossesse_id", '{{$object->_id}}');
  {{else}}
    url.addParam("sejour_id", '{{$object->_id}}');
  {{/if}}

  url.addParam("show_header", 0);
  url.requestUpdate('tab-native_views-atcd');
});
</script>
