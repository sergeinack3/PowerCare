{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=merge.log}}
{{mb_script module=mediusers script=CMediusers}}

{{assign var=form value='search-merge-logs'}}

<script>
  Main.add(function() {
    var form = MergeLog.getSearchForm();
    MergeLog.search(form);

    // Todo: Remove Mediusers dependency
    CMediusers.standardAutocomplete('{{$form}}', 'user_id', '_user_autocomplete');

    form.order_col.observe('change', MergeLog.resetPageOffset);
    form.order_col.observe('ui:change', MergeLog.resetPageOffset);
    form.order_way.observe('change', MergeLog.resetPageOffset);
    form.order_way.observe('ui:change', MergeLog.resetPageOffset);

    form.user_id.observe('change', MergeLog.resetPageOffset);
    form.user_id.observe('ui:change', MergeLog.resetPageOffset);

    form.object_class.observe('change', MergeLog.resetPageOffset);
    form.object_class.observe('ui:change', MergeLog.resetPageOffset);

    form.base_object_id.observe('change', MergeLog.resetPageOffset);
    form.base_object_id.observe('ui:change', MergeLog.resetPageOffset);

    form._min_date_start_merge.observe('change', MergeLog.resetPageOffset);
    form._min_date_start_merge.observe('ui:change', MergeLog.resetPageOffset);
    form._max_date_start_merge.observe('change', MergeLog.resetPageOffset);
    form._max_date_start_merge.observe('ui:change', MergeLog.resetPageOffset);

    form._min_date_end_merge.observe('change', MergeLog.resetPageOffset);
    form._min_date_end_merge.observe('ui:change', MergeLog.resetPageOffset);
    form._max_date_end_merge.observe('change', MergeLog.resetPageOffset);
    form._max_date_end_merge.observe('ui:change', MergeLog.resetPageOffset);

    ObjectSelector.initMergeLog = function() {
      this.sForm     = '{{$form}}';
      this.sId       = 'base_object_id';
      this.sView     = "_base_object_id";
      this.sClass    = 'object_class';
      this.onlyclass = 'false';
      this.pop();
    }
  });
</script>

<form name="{{$form}}" method="get" onsubmit="return onSubmitFormAjax(this, null, 'merge-logs-results');">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="a" value="ajax_search_merge_logs" />

  <input type="hidden" name="start" value="0" />
  <input type="hidden" name="step" value="50" />

  <input type="hidden" name="order_col" value="date_start_merge" />
  <input type="hidden" name="order_way" value="DESC" />

  <table class="main form">
    <col style="width: 10%;" />

    <tr>
      <th>{{mb_label class=CMergeLog field=date_start_merge}}</th>
      <td>
        {{mb_field class=CMergeLog field=_min_date_start_merge form=$form register=true}}
        &raquo;
        {{mb_field class=CMergeLog field=_max_date_start_merge form=$form register=true}}
      </td>

      <th>{{mb_label class=CMergeLog field=object_class}}</th>
      <td>
        {{mb_field class=CMergeLog field=object_class canNull=true form=$form autocomplete="true,1,50,true,true"}}
      </td>

      <th>{{tr}}common-Status{{/tr}}</th>
      <td>
        <label>
          <input type="radio" name="status" value="all" checked />
          Tous
        </label>

        <label>
          <input type="radio" name="status" value="ok" />
          Succès
        </label>

        <label>
          <input type="radio" name="status" value="ko" />
          Erreur
        </label>
      </td>
    </tr>

    <tr>
      <th>{{mb_label class=CMergeLog field=date_end_merge}}</th>
      <td>
        {{mb_field class=CMergeLog field=_min_date_end_merge form=$form register=true}}
        &raquo;
        {{mb_field class=CMergeLog field=_max_date_end_merge form=$form register=true}}
      </td>

      <th>{{mb_label class=CMergeLog field=base_object_id}}</th>
      <td>
        {{mb_field class=CMergeLog field=base_object_id hidden=true canNull=true}}

        <input type="text" name="_base_object_id" value="" />
        <button type="button" class="search" onclick="ObjectSelector.initMergeLog();">
          Rechercher un objet
        </button>
      </td>

      <th>{{mb_label class=CMergeLog field=user_id}}</th>
      <td>
        {{mb_field class=CMergeLog field=user_id hidden=true canNull=true}}

        <input type="text" name="_user_autocomplete" value="" />
        <button type="button" class="erase notext" onclick="$V(this.form.user_id, ''); $V(this.form._user_autocomplete, '');">
          {{tr}}common-action-Reset{{/tr}}
        </button>
      </td>
    </tr>

    <tr>
      <td colspan="6" class="button">
        <button type="submit" class="search">
          {{tr}}common-action-Search{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<div id="merge-logs-results"></div>
