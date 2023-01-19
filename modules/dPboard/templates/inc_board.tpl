{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=board script=board ajax=$ajax}}

{{if ($perm_fonct != 'only_me')}}
  <script>
    Main.add(function() {
      var url = new Url('mediusers', 'ajax_users_autocomplete');
      url.addParam('praticiens', 1);
      {{if $perm_fonct == 'same_function'}}
        url.addParam('function', '{{$user->function_id}}');
        url.addParam('edit', '0');
      {{elseif $perm_fonct == 'write_right' || $perm_fonct == 'only_me'}}
        url.addParam('edit', '1');
      {{else}}
        url.addParam('edit', '0');
      {{/if}}
      url.addParam('input_field', '_chir_view');
      url.autoComplete(getForm('ChoixPraticien').elements['_chir_view'], null, {
        minChars: 0,
        method: 'get',
        select: 'view',
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          if ($V(field) == '') {
            $V(field, selected.down('.view').innerHTML);
          }

          $V(field.form.elements['praticien_id'], selected.getAttribute('id').split('-')[2]);
        }
      });

      {{* Si on est sur la page vue journée, on affiche le filtre par cabinet *}}
      {{if $a === 'viewDay' || $tab === 'viewDay'}}
        var urlFunction = new Url('mediusers', 'ajax_functions_autocomplete');
        urlFunction.addParam('type', 'cabinet');
        {{if $perm_fonct == 'write_right'}}
        urlFunction.addParam('edit', '1');
        {{else}}
        urlFunction.addParam('edit', '0');
        {{/if}}
        urlFunction.addParam('input_field', '_function_view');
        urlFunction.autoComplete(getForm('ChoixPraticien').elements['_function_view'], null, {
          minChars: 0,
          method: 'get',
          select: 'view',
          dropdown: true,
          afterUpdateElement: function(field, selected) {
            if ($V(field) == '') {
              $V(field, selected.down('.view').innerHTML);
            }

            $V(field.form.elements['function_id'], selected.getAttribute('id').split('-')[2]);
          }
        });
      {{/if}}

    });
  </script>

  <form name="ChoixPraticien" method="get" action="?">
    <input type="hidden" name="m" value="{{$m}}"/>
    <input type="hidden" name="tab" value="{{$tab}}" />
    <label for="praticien_id" title="{{tr}}select-prat-stats{{/tr}}">{{tr}}Praticien{{/tr}}</label>
    <input type="hidden" name="praticien_id" value="{{$prat->_id}}" onchange="Board.onSelectFilter(this);"
           {{if $perm_fonct == 'only_me'}} disabled{{/if}}>
    <input onclick="$V(this, '')" type="text" name="_chir_view" class="me-small" value="{{if $prat->_id}}{{$prat}}{{else}}{{tr}}CMediusers-select-praticien{{/tr}}{{/if}}">

    {{* Si on est sur la page vue journée, on affiche le filtre par cabinet *}}
    {{if $a === 'viewDay' || $tab === 'viewDay'}}
      <label for="function_id">{{tr}}Cabinet{{/tr}}</label>
      <input type="hidden" name="function_id" value="{{$function->_id}}" onchange="Board.onSelectFilter(this);"
              {{if $perm_fonct == 'only_me'}} disabled{{/if}}>
      <input onclick="$V(this, '')" type="text" name="_function_view" class="me-small" value="{{if $function->_id}}{{$function}}{{else}}{{tr}}CFunctions-select{{/tr}}{{/if}}">
    {{/if}}
  </form>


  {{if !$prat->_id && !$function->_id}}
    <div class="small-warning">
      Les vues du tableau de bord sont spécifiques à chaque praticien.
      <br />Merci d'en <strong>sélectionner</strong> un dans la liste ci-dessus.
    </div>
  {{/if}}
{{/if}}
