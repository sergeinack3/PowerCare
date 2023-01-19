{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=level value="medium"}}
{{mb_default var=template_icon value="inc_bulb"}}
{{mb_default var=tag   value=""}}
{{mb_default var=callback value="Prototype.emptyFunction"}}
{{mb_default var=show_empty value=""}}
{{mb_default var=show_span value=""}}
{{mb_default var=event value="onclick"}}
{{mb_default var=img_ampoule value="ampoule"}}
{{mb_default var=keep_img value=false}}

{{if $object}}
  {{mb_default var=nb_alerts value=$object->_count_alerts_not_handled}}
  {{assign var=object_guid value=$object->_guid}}
{{else}}
    {{mb_default var=nb_alerts value=0}}
    {{assign var=object_guid value=false}}
{{/if}}

{{if !$keep_img && $level === "high"}}
  {{assign var=img_ampoule value="ampoule_urgence"}}
{{/if}}

{{if !$keep_img && $object|instanceof:'Ox\Mediboard\Prescription\CPrescription' && $object->_id && in_array($object->_ref_object->type, array("psy", "ssr"))}}
  {{assign var=img_ampoule value="ampoule_green"}}
{{/if}}

{{unique_id var=unique_alerte}}

{{if $nb_alerts || $show_empty}}
  {{mb_include module=system template=$template_icon img_ampoule=$img_ampoule object_guid=$object_guid
               unique_alerte=$unique_alerte level=$level tag=$tag callback=$callback
               alert_nb=$nb_alerts show_empty=$show_empty event_trigger=$event alert_show_span=$show_span}}
  <div id="tooltip-alerts-{{$unique_alerte}}-{{$level}}-{{$object_guid}}" style="display: none; height: 400px; width: 400px; overflow-x:auto;"></div>
{{/if}}
