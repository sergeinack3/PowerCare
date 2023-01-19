{{*
 * @package Mediboard\Maternité
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier_perinat value=$grossesse->_ref_dossier_perinat}}

<form name="printDossierPerinat" method="post" target="_blank" action="?m=maternite&dialog=print_dossier_mater">
  {{mb_key object=$grossesse}}
  <input type="hidden" name="sejour_id" value="{{$sejour_id}}" />
  <input type="hidden" name="operation_id" value="{{$operation_id}}" />

  <input type="hidden" name="dossier_mater[identification][]" value="identification" />

  <table class="main">
    <tr>
      {{assign var=i value=0}}
      {{foreach from=$dossier_perinat->_listChapitres item=_parts key=title}}
      {{if $i % 2 === 0}}
    </tr>
    <tr>
      {{/if}}
      <td class="halfPane">
        <fieldset>
          <legend>
            <label>
              <input type="checkbox" checked
                     onclick="this.up('fieldset').select('input.part').invoke('writeAttribute', 'checked', this.checked);" /> {{tr}}CDossierPerinat-{{$title}}{{/tr}}
            </label>
          </legend>

          {{foreach from=$_parts item=_status key=_part}}
            <div>
              <label>
                <input type="checkbox" name="dossier_mater[{{$title}}][]" value="{{$_part}}" class="part"
                       checked /> {{tr}}CDossierPerinat-{{$title}}-{{$_part}}{{/tr}}
              </label>
            </div>
          {{/foreach}}
        </fieldset>
      </td>

      {{math equation=x+1 x=$i assign=i}}
      {{/foreach}}
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button class="print">{{tr}}Print{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
