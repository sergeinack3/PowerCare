{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=all_constantes value='Ox\Mediboard\Patients\CConstantesMedicales'|static:"list_constantes"}}

<strong>{{mb_title object=$constantes field=$constante}}:</strong>
<span {{if $add_class}} class="constante-value-container" data-constante="{{$constante}}" {{/if}}>
  {{if $constantes->$constante}}
    {{if isset($all_constantes.$constante.formfields|smarty:nodefaults)}}
      {{assign var=field value=$all_constantes.$constante.formfields[0]}}
      {{$constantes->$field}}
    {{else}}
      {{mb_value object=$constantes field=$constante}}
    {{/if}}

    {{$all_constantes.$constante.unit}}
  {{else}}
    &mdash;
  {{/if}}
</span>