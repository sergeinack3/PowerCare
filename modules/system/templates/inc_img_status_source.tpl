{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=exchange_source ajax=true}}

{{mb_default var=actor_guid        value=""}}
{{mb_default var=actor_actif        value=""}}
{{mb_default var=accessibility      value=0}}
{{mb_default var=actor_parent_class value=""}}
{{unique_id var=uid}}

<script>
  {{if $accessibility}}
    Main.add(ExchangeSource.resfreshImageStatus.curry($('{{$uid}}'), '{{$actor_actif}}', '{{$actor_parent_class}}'));
  {{/if}}
</script>

<!--<img class="status" id="{{$uid}}" data-id="{{$exchange_source->_id}}"
  data-guid="{{$exchange_source->_guid}}" src="images/icons/status_grey.png"
  title="{{$exchange_source->name}}" />-->

<i class="fa fa-circle" style="color:grey" id="{{$uid}}" name="{{$actor_guid}}" data-id="{{$exchange_source->_id}}"
   data-guid="{{$exchange_source->_guid}}" title="{{$exchange_source->name}}"></i>
