{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  $$("a[href='#fields-events']")[0].set("count", {{$ex_class->_ref_events|@count}});
</script>

<table class="main layout">
  <tr>
    <td style="width: 20em;">
      <button type="button" class="new me-margin-top-4 me-margin-bottom-4" style="float: right;" onclick="ExClassEvent.create({{$ex_class->_id}})">
        {{tr}}CExClassEvent-title-create{{/tr}}
      </button>

      <table class="main tbl me-no-align me-no-box-shadow">
        <tr>
          <th rowspan="2">{{mb_title class=CExClassEvent field=event_name}}</th>
          <th colspan="2">{{tr}}CExClassEvent-back-constraints{{/tr}}</th>
        </tr>

        <tr>
          <th class="section" title="Contraintes d'ouverture">
            <i class="far fa-file fa-lg"></i>
          </th>

          <th class="section" title="{{tr}}CExClassMandatoryConstraint|pl{{/tr}}">
            <i class="fa fa-exclamation-triangle fa-lg"></i>
          </th>
        </tr>

        {{foreach from=$ex_class->_ref_events item=_event}}
          <tr data-event_id="{{$_event->_id}}" {{if $_event->disabled}} class="opacity-50" {{/if}}>
            <td class="text">
              <a href="#1" onclick="ExClassEvent.edit({{$_event->_id}}); return false;">
                {{$_event}}
              </a>
            </td>

            <td style="text-align: center;">{{$_event->_count.constraints}}</td>
            <td style="text-align: center;">{{$_event->_count.mandatory_constraints}}</td>
          </tr>
          {{foreachelse}}
          <tr>
            <td colspan="2" class="empty">{{tr}}CExClassEvent.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>

    <td id="exClassEventEditor">
      <div class="small-info">
        Veuillez cliquer sur un évènement pour le modifier.
      </div>
    </td>
  </tr>
</table>