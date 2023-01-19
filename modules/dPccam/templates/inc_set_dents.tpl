{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=teeth_checked value=0}}
{{if is_countable($acte->_dents) && $acte->_dents|@count}}
  {{assign var=teeth_checked value=$acte->_dents|@count}}
{{/if}}

<script type="text/javascript">
  setPositionDentaire = function() {
    var form_act = getForm('codageActe-{{$acte_view}}');
    var form_teeth = getForm('codageActePostionDentaire-{{$acte_view}}');

    if (parseInt($V(form_teeth.elements['count_teeth_checked'])) != parseInt('{{$phase->nb_dents}}')) {
      Modal.alert($T('CActeCCAM-error-incorrect_teeth_number_checked', '{{$phase->nb_dents}}'));
      return false;
    }
    $V(form_act.position_dentaire, $V(form_teeth.elements['position_dentaire']));

    form_act.onsubmit();
    Control.Modal.close();
  };

  syncDentField = function(input) {
    var dents = $V(input.form.position_dentaire);
    var num_dent = input.getAttribute('data-localisation');

    if (dents != '') {
      dents = dents.split('|');
    }
    else {
      dents = [];
    }

    if (input.checked) {
      dents.push(num_dent);
    }
    else if (!input.checked && dents.indexOf(num_dent) != -1) {
      dents.splice(dents.indexOf(num_dent), 1);
    }

    $('checked_teeth-{{$acte_view}}').innerHTML = dents.length;
    $V(input.form.elements['count_teeth_checked'], dents.length);
    if (dents.length != parseInt('{{$phase->nb_dents}}')) {
      $('checked_teeth-{{$acte_view}}').setStyle({color: 'firebrick'});
    }
    else {
      $('checked_teeth-{{$acte_view}}').setStyle({color: 'forestgreen'});
    }

    $V(input.form.position_dentaire, dents.join('|'));
  };
</script>

<form name="codageActePostionDentaire-{{$acte_view}}" action="?" method="post" onsubmit="return false;">
  <table class="form">
    <tr>
      <th class="title" colspan="2">
        Saisie des dents concernées pour l'acte {{$acte->code_acte}}
      </th>
    </tr>
    <tr>
      <th>
        <label for="position_dentaire">
          Dent(s) concernée(s) (<span id="checked_teeth-{{$acte_view}}">0</span> / {{$phase->nb_dents}} cochée(s))
        </label>
      </th>
      <td class="text">
        {{mb_include module=salleOp template=inc_schema_dents_ccam liste_dents=$liste_dents phase=$phase acte=$acte}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="add" onclick="setPositionDentaire();">Coter l'acte</button>
        {{if $nullable}}
          <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
