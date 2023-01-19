{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=callback value="Relance.reloadButton.curry(`$sejour->_id`)"}}

{{assign var=relance value=$sejour->_ref_relance}}

<div id="relance_button_{{$sejour->_id}}">
  {{if !$relance->_id}}
    <button type="button" class="tick" onclick="Relance.edit(null, '{{$sejour->_id}}', {{$callback}})">Créer une relance</button>
  {{else}}
    {{if $relance->datetime_cloture}}
      Clôturée le {{mb_value object=$relance field=datetime_cloture}}
    {{elseif $relance->datetime_relance}}
      Relancé le {{mb_value object=$relance field=datetime_relance}}
    {{else}}
      Créée le {{mb_value object=$relance field=datetime_creation}}
    {{/if}}

    <button type="button" class="edit notext" onclick="Relance.edit('{{$relance->_id}}', null, {{$callback}})"></button>
  {{/if}}
</div>