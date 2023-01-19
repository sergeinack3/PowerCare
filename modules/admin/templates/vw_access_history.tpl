{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients   script=pat_selector}}

<script>
    function changePageAccessHistory(start) {
        var form = getForm('search-log_access');
        $V(form.elements.start, start);
        form.onsubmit();
    }

    PatSelector.init = function () {
        window.bOldPat = $V(getForm('search-log_access').patient_id);
        this.sForm = 'search-log_access';
        this.sFormEasy = 'editOpEasy';

        this.sView_easy = '_patient_view';
        this.sId_easy = 'patient_id';

        this.sId = 'patient_id';
        this.sView = '_patient_view';
        this.sSexe = '_patient_sexe';
        this.sTutelle = 'tutelle';

        this.pop();
    };

    function printLog () {
        let form = getForm('search-log_access');
        new Url('admin', 'ajax_search_log_access')
        .addParam('user_id', form.user_id)
        .addParam('start', form.start)
        .addParam('patient_id', form.patient_id)
        .addParam('print', 1)
        .pop(1000, 600);
    };

    Main.add(function () {
        var form = getForm('search-log_access');

        var element = form.elements._user_id_autocomplete_view;
        var url = new Url("system", "ajax_seek_autocomplete");

        url.addParam("object_class", "CMediusers");
        url.addParam("input_field", element.name);
        url.autoComplete(element, null, {
            minChars: 3,
            method: "get",
            select: "view",
            dropdown: true,
            afterUpdateElement: function (field, selected) {
                var id = selected.getAttribute("id").split("-")[2];
                $V(form.user_id, id);

                if ($V(element) == "") {
                    $V(element, selected.down('.view').innerHTML);
                }
            }
        });

        form.onsubmit();
    });
</script>


<form name="search-log_access" action="" method="get"
      onsubmit="return onSubmitFormAjax(this, null, 'populate_results')">
    <input type="hidden" name="m" value="admin"/>
    <input type="hidden" name="a" value="ajax_search_log_access"/>
    <input type="hidden" name="user_id" value=""/>
    <input type="hidden" name="start" value="0"/>
    <input type="hidden" name="patient_id"/>

    <table class="main form">
        <tr class="">
            <td>
                {{mb_field object=$log_access field=object_class}}

                {{tr}}common-Date{{/tr}}:
                {{mb_field object=$log_access field=_date_min register=true form="search-log_access" canNull=false}}
                &raquo;
                {{mb_field object=$log_access field=_date_max register=true form="search-log_access" canNull=false}}

                <input type="text" class="autocomplete" name="_user_id_autocomplete_view" value=""/>
                <input type="text" name="_patient_view" style="width: 15em" readonly="readonly"
                       placeholder="{{tr}}CPatient.select{{/tr}}" onfocus="PatSelector.init();"/>
                <button type="button" class="search notext me-tertiary"
                        placeholder="{{tr}}CPatient.select{{/tr}}" onclick="PatSelector.init();">
                  {{tr}}CPatient.select{{/tr}}
                </button>
                <button type="button" class="erase notext"
                        onclick="$V(this.form.elements.user_id, '');
                          $V(this.form.elements._user_id_autocomplete_view, '');">
                </button>
                <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
                <button type="button" class="print me-tertiary" onclick="printLog();">
                    {{tr}}Print{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</form>


<div id="populate_results" class="me-padding-0"></div>
