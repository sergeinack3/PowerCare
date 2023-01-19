{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  hideShowElem = function (elem) {
    var checked = elem.get('checked');
    var deepness = elem.get('deepness');

    var stop = false;
    elem.up('tr').nextSiblings().each(function (child_elem) {
      if (!stop) {
        var child = child_elem.down('span');

        if (child.get('deepness') === deepness) {
          stop = true;
          return false;
        }

        var next_deepness = parseInt(deepness) + 1;
        if (child.get('deepness') <= deepness || (checked === '0' && next_deepness < child.get('deepness'))) {
          return false;
        }

        if (checked === '0') {
          child_elem.show();
        }
        else {
          child_elem.hide();
        }

        child.set('checked', (checked === '0') ? '0' : '1');
      }

    });

    elem.set('checked', (checked === '0') ? '1' : '0');
  };

  displayKeyDetails = function(button, key) {
    if (!button) {
      return;
    }

    var key_parts = [];
    key_parts.push(key);

    var elem_deepness = button.up('span').get('deepness');
    button.up('tr').previousSiblings().each(function (elem) {
      var child = elem.down('span');
      if (!child) {
        return false;
      }

      var child_deepness = child.get('deepness');

      if (parseInt(elem_deepness) - 1 == child_deepness) {
        elem_deepness = child_deepness;
        key_parts.push(child.get('key'));
      }
    });

    key_parts.reverse();

    var file_name = $('file_infos').value;
    var url = new Url('dPdeveloppement', 'vw_redis_logs_details');
    url.addParam('key', key_parts.join('-'));
    url.addParam('file_name', file_name);
    url.requestModal('80%', '80%');

  }
</script>

<input type="hidden" id="file_infos" value="{{$file_name}}"/>

<div class="small-info">
  <table class="layout" style="table-layout: fixed">
    <tr>
      <th style="width: 20em;">
        <strong>{{tr}}dPdeveloppement-logs file date start{{/tr}}</strong>
      </th>
      <td>{{$date_min}}</td>
    </tr>

    <tr>
      <th style="width: 20em;">
        <strong>{{tr}}dPdeveloppement-logs file date stop{{/tr}}</strong>
      </th>
      <td>{{$date_max}}</td>
    </tr>

    <tr>
      <th style="width: 20em;">
        <strong>{{tr}}dPdeveloppement-logs duration{{/tr}}</strong>
      </th>
      <td>{{$duration|number_format:2:',':' '}}</td>
    </tr>

    <tr>
      <th style="width: 20em;">
        <strong>{{tr}}dPdeveloppement-logs parsing duration{{/tr}}</strong>
      </th>
      <td>{{$parsing_time|number_format:2:',':' '}}</td>
    </tr>

    <tr>
      <th style="width: 20em;">
        <strong>{{tr}}dPdeveloppement-logs nb lignes{{/tr}}</strong>
      </th>
      <td>{{$nb_lines|integer}}</td>
    </tr>
  </table>
</div>

<table class="main tbl">
  <tr>
    <th class="text">{{tr}}dPdeveloppement-logs aggregated key{{/tr}}</th>
    {{foreach from=$calls item=_call_name}}
      <th class="narrow" title="{{$_call_name}}" colspan="2">
        {{$_call_name}}
      </th>
    {{/foreach}}
  </tr>

  {{mb_include module=dPdeveloppement template=inc_redis_recursive_log_parser tbl=$result idx=0}}
</table>