{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th style="width: 25px;">
      {{tr}}CDevisCodage{{/tr}}
    </th>
    <td>
      {{if $devis->_id}}
        <span class="text compact" title="{{$devis->libelle}}">
          {{$devis->libelle|truncate:30}}
        </span>

        <div style="width: 100%; display: inline-block; text-align: right;">
          <button class="edit notext" type="button" onclick="DevisCodage.edit('{{$devis->_id}}', DevisCodage.viewDevis.curry('{{$devis->codable_class}}', '{{$devis->codable_id}}'));">
            {{tr}}CDevisCodage-title-modify{{/tr}}
          </button>
          <button class="print notext" type="button" onclick="DevisCodage.print({{$devis->_id}});">{{tr}}CDevisCodage-print{{/tr}}</button>
          <button class="trash notext" type="button" {{if $devis->_count_actes != 0}} disabled="disabled"{{/if}} onclick="DevisCodage.remove('{{$devis->_id}}', DevisCodage.viewDevis.curry('{{$devis->codable_class}}', '{{$devis->codable_id}}'));">
            {{tr}}Delete{{/tr}}
          </button>
        </div>
      {{else}}
        {{assign var=datetime value='Ox\Core\CMbDT::dateTime'|static_call:null}}

        <script type="text/javascript">
          callbackDevis = function(devis_id) {
            DevisCodage.edit(devis_id, DevisCodage.viewDevis.curry('{{$devis->codable_class}}', '{{$devis->codable_id}}'));
          };
        </script>

        <span class="empty">
          {{tr}}CDevisCodage.none{{/tr}}
        </span>

        <div style="with: 100%; display: inline-block; text-align: right;">
          <button type="button" class="new notext" title="{{tr}}CDevisCodage-title-create{{/tr}}" onclick="DevisCodage.create({
            codable_class: '{{$devis->codable_class}}',
            codable_id: '{{$devis->codable_id}}',
            event_type: '{{$devis->event_type}}',
            patient_id: '{{$devis->patient_id}}',
            praticien_id: '{{$devis->praticien_id}}',
            libelle: '{{$devis->libelle}}',
            codes_ccam: '{{$devis->codes_ccam}}',
            date: '{{$devis->date}}',
            creation_date: '{{$datetime}}'
          }, 'callbackDevis');">
            {{tr}}CDevisCodage-title-create{{/tr}}
          </button>
        </div>
      {{/if}}
    </td>
  </tr>
</table>