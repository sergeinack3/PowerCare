{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  {{assign var=equipes value='Ox\Erp\COXEquipe::getMOE'|static_call:null}}

  <select name="c[{{$_feature}}]" onchange="" {{if $is_inherited}}disabled{{/if}}>
    <option value="">&mdash; Choisissez une équipe</option>

    {{foreach from=$equipes item=_equipe}}
      <option value="{{$_equipe->_id}}" {{if $_equipe->_id == $value}}selected{{/if}}>{{$_equipe}}</option>
    {{/foreach}}
  </select>
{{else}}
  {{if $value}}
    <span onmouseover="ObjectTooltip.createEx(this, 'COXEquipe-{{$value}}');">
      COXEquipe-{{$value}}
    </span>
  {{/if}}
{{/if}}
