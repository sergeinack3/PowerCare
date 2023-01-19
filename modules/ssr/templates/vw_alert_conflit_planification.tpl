{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=remplacement value=0}}

{{assign var=lock_add_evt_conflit value="ssr general lock_add_evt_conflit"|gconf}}
{{if $lock_add_evt_conflit}}
  <script>
    Main.add(function() {
      var count_conflit = '{{$conflits|@count}}';
      var button_submit = null;
      if ($('kines')) {
        button_submit = $('kines').down('button.submit');
      }
      else {
        button_submit = $('warning_conflit_planification').up('td').down('button.submit');
      }
      button_submit.disabled = (count_conflit == '0') ? '' : 'disabled';
    });
  </script>
{{/if}}

{{if $conflits|@count}}
  <div class="small-{{if $lock_add_evt_conflit}}error{{else}}warning{{/if}}">
    {{$conflits|@count}} {{tr}}CEvenementSSR-conflits{{/tr}}
    <ul>
      {{foreach from=$conflits item=_evt_conflit}}
        <li>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_evt_conflit->_guid}}')">
            {{if $_evt_conflit->type_seance == "collective" && !$_evt_conflit->seance_collective_id}}
              {{tr}}CEvenementSSR-seance_collective_id{{/tr}}
            {{else}}
              {{$_evt_conflit->_ref_sejour->_ref_patient->_view}}
            {{/if}}
            - {{$_evt_conflit->_ref_prescription_line_element->_ref_element_prescription->_view}}
             - {{mb_value object=$_evt_conflit field=debut}}
            ({{mb_value object=$_evt_conflit field=_duree}} {{tr}}common-minute|pl{{/tr}})
          </span>
        </li>
      {{/foreach}}
    </ul>
  </div>
{{elseif $remplacement}}
  <div class="small-info">
    {{tr}}CEvenementSSR-no_conflits_detected{{/tr}}
  </div>
{{/if}}