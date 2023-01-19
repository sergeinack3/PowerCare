{{*
 * @package Mediboard\dPcabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=see_absence value=false}}

<script type="text/javascript">
  Main.add(function () {
    $$('li.type_antecedent').each(function (type) {
      var has_antecedents = 0;
      type.select('li.appareil_antecedent').each(function (appareil) {
        if (appareil.down('li:not(.cancelled)')) {
          has_antecedents = 1;
        }
        else {
          appareil.addClassName('cancelled');
          appareil.hide();
          appareil.select('li.cancelled').each(function (element) {
            /* We reset the opacity of the child elements, because opacity stacks among the descendants
             * And if we put 1, the opacity is not set */
            element.setOpacity(0.99);
          });
        }
      });

      if (!has_antecedents) {
        type.addClassName('cancelled');
        type.hide();
        type.select('li.cancelled').each(function (element) {
          /* We reset the opacity of the child elements, because opacity stacks among the descendants
           * And if we put 1, the opacity is not set */
          element.setOpacity(0.99);
        });
      }
    });
  });
</script>

{{if !$see_absence}}
  {{assign var=antecedents_by_type_appareil value=$dossier_medical->_ref_antecedents_by_type_appareil}}
{{else}}
  {{assign var=antecedents_by_type_appareil value=$dossier_medical->_ref_antecedents_by_type_appareil_absence}}
{{/if}}

{{foreach from=$antecedents_by_type_appareil key=_type item=_antecedents_by_appareil}}
  {{if $_type == '' || count($dossier_medical->_ref_antecedents_by_type[$_type]) || $see_absence}}
    <li class="type_antecedent" data-type="{{$_type}}" style="list-style-position: inside;">
      {{if $_type != ''}}
        <strong style="margin-right: 5px;">{{tr}}CAntecedent.type.{{$_type}}{{/tr}}</strong>
      {{else}}
        <strong>{{tr}}CAntecedent-No type{{/tr}}</strong>
      {{/if}}
      <ul>
        {{foreach from=$_antecedents_by_appareil key=_appareil item=_antecedents}}
          {{if count($_antecedents)}}
            <li class="appareil_antecedent" data-appareil="{{$_appareil}}"
                {{if $_appareil == ""}}style="display: inline;"{{/if}}>
              {{if $_appareil != ''}}
                <strong>{{tr}}CAntecedent.appareil.{{$_appareil}}{{/tr}}</strong>
              {{/if}}
              <ul>
                {{foreach from=$_antecedents item=_antecedent}}
                  {{mb_include module=dPcabinet template=antecedents/inc_ant antecedent=$_antecedent}}
                {{/foreach}}
              </ul>
            </li>
          {{/if}}
        {{/foreach}}
      </ul>
    </li>
  {{/if}}
{{/foreach}}
