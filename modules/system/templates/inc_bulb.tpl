{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=img_ampoule     value="ampoule"}}
{{mb_default var=show_empty      value=false}}
{{mb_default var=level           value=false}}
{{mb_default var=tag             value=false}}
{{mb_default var=object_guid     value=false}}
{{mb_default var=event_trigger   value=false}}
{{mb_default var=callback        value=false}}
{{mb_default var=alert_top       value=false}}
{{mb_default var=alert_left      value=false}}
{{mb_default var=alert_nb        value=false}}
{{mb_default var=alert_show_span value=false}}
{{mb_default var=title           value=false}}
{{mb_default var=title_tr        value=false}}
{{mb_default var=style_css       value=""}}
{{mb_default var=event_function  value="Alert.showAlerts('`$object_guid`', '`$tag`', '`$level`', `$callback`, this)"}}
{{assign var=container_style value=""}}
{{if $show_empty && !$alert_nb}}
  {{assign var=container_style value="display:none;"}}
{{/if}}
<div class="me-bulb-info me-bulb-{{$img_ampoule}}" style="{{$style_css}} {{$container_style}}"
     {{$event_trigger}}="{{$event_function|JSAttribute|html_entity_decode}}" title="{{if $title_tr}}{{tr}}{{$title_tr}}{{/tr}}{{/if}} {{$title}}">
  {{mb_include module=system template=inc_vw_counter_tip top=$alert_top right=$alert_left
               count=$alert_nb show_span=$alert_show_span}}
</div>
