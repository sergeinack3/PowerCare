{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=title value=0}}
{{mb_default var=float value=""}}
{{mb_default var=font_size value="2em"}}
{{mb_default var=with_tooltip value=1}}

{{if !$operation->_status_panier}}
  {{mb_return}}
{{/if}}

<script>
  ObjectTooltip.modes.panier = {
    module: "planningOp",
    action: "ajax_tooltip_panier",
    sClass: "tooltip"
  };
</script>

<i class="fas fa-shopping-basket"
   style="font-size: {{$font_size}}; color: {{$operation->_color_panier}}; {{if $float}}float: {{$float}};{{/if}}"
   {{if $with_tooltip}}
     onmouseover="ObjectTooltip.createEx(
       this, null, 'panier',
       {
         operation_id: '{{$operation->_id}}',
         javascript: 0
       },
       {
         duration: 0.4
       });"
   {{/if}}
></i>