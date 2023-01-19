{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th>Expression</th>
    <th>Résultat attendu</th>
    <th>Résultat serveur</th>
    <th>Résultat client</th>
  </tr>
  
  {{foreach from=$expressions key=_expression item=_expected}}
    <tr>
      <td><code>{{$_expression}}</code></td>
      <td>{{$_expected}}</td>
      <td class="{{if $_expected == $server_side.$_expression}}ok{{else}}warning{{/if}}">
        {{$server_side.$_expression}}
      </td>
      <td id="result-{{$_expression|md5}}">
        <script>
          Main.add(function(){
            var customOps = {
              Min: ExObject.dateOperator.curry(Date.minute),
              H:   ExObject.dateOperator.curry(Date.hour),
              J:   ExObject.dateOperator.curry(Date.day),
              Sem: ExObject.dateOperator.curry(Date.week),
              M:   ExObject.dateOperator.curry(Date.month),
              A:   ExObject.dateOperator.curry(Date.year)
            };
              
            var parser = new Parser;
            parser.ops1 = Object.extend(customOps, parser.ops1);

            var expression = {{$_expression|@json}};
            var variables = {{$variables|@json}};
            var expected = {{$server_side.$_expression|@json}};
            
            try {
              var cell = $('result-{{$_expression|md5}}');
              var expr = parser.parse(expression.replace(/\$/g, ''));
              var result = expr.evaluate(variables);
              cell.update(result);
              
              // less than 0.000000000000001 difference (floats ...)
              var correct = Math.abs(result - expected) < 10e-15;
              cell.addClassName(correct ? "ok" : "warning");
            }
            catch (e) {
              console.error(e);
            }
          });
        </script>
      </td>
    </tr>
  {{/foreach}}
</table> 