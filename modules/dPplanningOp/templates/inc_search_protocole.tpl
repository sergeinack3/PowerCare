{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=formOp value="editOp"}}
{{mb_default var=formSecondOp value="editOpEasy"}}
{{mb_default var=id_protocole value="get_protocole"}}
{{mb_default var=keep_protocol value=false}}

{{mb_default var=for_sejour value=false}}

<input type="text" name="search_protocole" style="width: 15em;" placeholder="{{tr}}fast-search{{/tr}} {{tr}}CProtocole{{/tr}}" onblur="$V(this, '')"/>
<input type="checkbox" name="search_all_chir" title="Étendre la recherche à tous les praticiens" />
<div style="display:none;" id="{{$id_protocole}}"></div>
<input type="hidden" name="_protocole_id" value=""{{if $keep_protocol}} onchange="showKeepProtocol(this);"{{/if}}/>

<script>
  ajoutProtocole = function(protocole_id) {
    if (aProtocoles[protocole_id]) {
      ProtocoleSelector.set(aProtocoles[protocole_id]);
    }
  };

  Main.add(function () {
    aProtocoles = {};

    ProtocoleSelector.init(true);

    var oForm = getForm('{{$formOp}}');
    var url = new Url('planningOp', 'ajax_protocoles_autocomplete');
    url.addParam('field'          , 'protocole_id');
    url.addParam('input_field'    , 'search_protocole');
    url.addParam('for_sejour', {{$for_sejour|ternary:'1':'0'}});
    url.autoComplete(oForm.elements.search_protocole, null, {
      minChars: 3,
      method: 'get',
      select: 'view',
      dropdown: true,
      width: '400px',
      afterUpdateElement: function(field, selected) {
        ajoutProtocole(selected.get('id'));
        $V(field.form.elements.search_protocole, "");

        if('{{$formOp}}' == "editOpEasy"){
          $V(oForm.protocole_id, selected.get('id'));
        }

      },
      callback: function(input, queryString) {
        var chir_id = ProtocoleSelector.sChir_id;

        return queryString +
          (input.form.search_all_chir.checked ? "" : "&chir_id=" + $V(input.form[chir_id]));
      }
    });
  });
</script>