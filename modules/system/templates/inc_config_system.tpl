{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    migrateConfigs = function () {
        if (!confirm("Voulez-vous vraiment migrer les configurations en base de données ?")) {
            return;
        }

        new Url("system", "ajax_migrate_configs")
                .requestUpdate("migration_config_db");
    };

    Main.add(function () {
        var form = getForm('editConfig-system');
        var input = form["migration[limit_date]"];
        input.className = "date";
        input.type = "hidden";
        Calendar.regField(input);

        input = form["offline_time_start"];
        input.className = "time";
        input.type = "hidden";
        var value = $V(input);
        if (/^\d{2}:\d{2}$/.test(value)) {
            value += ':00';
            $V(input, value);
        }
        Calendar.regField(input);
        $V(form['offline_time_start_da'], value.substring(0, value.lastIndexOf(':')))

        input = form["offline_time_end"];
        input.className = "time";
        input.type = "hidden";
        var value = $V(input);
        if (/^\d{2}:\d{2}$/.test($V(value))) {
            value += ':00';
            $V(input, $V(input) + ':00');
        }
        Calendar.regField(input);
        $V(form['offline_time_end_da'], value.substring(0, value.lastIndexOf(':')))
    });
</script>

<form name="editConfig-system" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
    {{mb_configure module=$m}}

    <table class="form" style="table-layout: fixed;">
        {{mb_include module=system template=inc_config_str var=root_dir size=40}}
        {{mb_include module=system template=inc_config_enum var=instance_role values="prod|qualif"}}
        {{mb_include module=system template=inc_config_str var=base_url size=40}}
        {{mb_include module=system template=inc_config_str var=external_url size=40}}
        {{mb_include module=system template=inc_config_str var=product_name}}
        {{mb_include module=system template=inc_config_str var=mb_id}}
        {{mb_include module=system template=inc_config_str var=mb_oid}}
        {{mb_include module=system template=inc_config_str var=servers_ip size=40}}
        {{if $conf.instance_role == 'qualif'}}
            {{mb_include module=system template=inc_config_str var=other_databases size=40}}
        {{/if}}
        {{mb_include module=system template=inc_config_bool var=debug}}
        {{mb_include module=system template=inc_config_bool var=readonly}}
        {{mb_include module=system template=inc_config_bool var=offline_non_admin}}

        {{mb_include module=system template=inc_config_str var=weinre_debug_host}}
        {{mb_include module=system template=inc_config_str var=offline_time_start}}
        {{mb_include module=system template=inc_config_str var=offline_time_end}}
        {{mb_include module=system template=inc_config_bool var=config_db}}

        {{if $conf.config_db}}
            <tr>
                <th></th>
                <td id="migration_config_db">
                    <button type="button" onclick="migrateConfigs()" class="send">Migrer les configurations</button>
                </td>
            </tr>
        {{/if}}

        {{mb_include module=system template=inc_config_bool var=intercept_database_engine_instruction}}

        {{mb_include module=system template=inc_config_str var=dataminer_limit numeric=true}}
        {{mb_include module=system template=inc_config_str var=aio_output_path size=50}}

        <tr>
            <th colspan="2" class="title">
                {{tr}}common-Logging{{/tr}}
            </th>
        </tr>

        {{mb_include module=system template=inc_config_num m=system var=max_log_duration}}

        {{mb_include module=system template=inc_config_bool var=log_access}}
        {{mb_include module=system template=inc_config_num var=access_log_buffer_lifetime numeric=true}}
        {{mb_include module=system template=inc_config_num var=aggregate_lifetime numeric=true}}
        {{mb_include module=system template=inc_config_bool var=log_datasource_metrics}}
        {{mb_include module=system template=inc_config_str var=human_long_request_level numeric=true}}
        {{mb_include module=system template=inc_config_str var=bot_long_request_level numeric=true}}
        {{mb_include module=system template=inc_config_bool var=log_all_queries}}
        {{mb_include module=system template=inc_config_str var=long_request_whitelist textarea=true}}
        {{mb_include module=system template=inc_config_str var=logged_handler_calls_list textarea=true}}

        {{mb_include module=system template=inc_config_bool var=activer_user_action}}
        {{mb_include module=system template=inc_config_bool var=activer_compression_diff}}
        <tr>
            <th>Migration cron-job</th>
            <td>
                <span class="warning">{{tr}}config-activer_migration_log_to_action_warning{{/tr}}</span>
                <br>
                <span class="info">Script : index.php?m=system&a=ajax_migration_log_to_action</span>
            </td>
        </tr>
        {{mb_include module=system template=inc_config_bool var=activer_migration_log_to_action}}


        {{mb_include module=system template=inc_config_num var=migration_log_to_action_probably numeric=true}}
        {{mb_include module=system template=inc_config_num var=migration_log_to_action_nbr numeric=true}}
        {{if $stat_migration_log_to_action}}
            <tr>_
                <th>Avancement de la migration</th>
                <td>{{$stat_migration_log_to_action|smarty:nodefaults}}</td>
            </tr>
        {{/if}}

      <tr>
        <th colspan="2" class="title">
            {{tr}}common-NoSQL-Logging{{/tr}}
        </th>
      </tr>
        {{mb_include module=system template=inc_config_bool var=application_log_using_nosql}}
        {{mb_include module=system template=inc_config_bool var=error_log_using_nosql}}
      <tr>
            <th colspan="2" class="title">
                {{tr}}common-Purge{{/tr}}
            </th>
        </tr>

        {{mb_include module=system template=inc_config_num var=CAlert_purge_lifetime numeric=true}}
        {{mb_include module=system template=inc_config_enum var=CAlert_purge_delay values='30|60|90|120'}}

        {{mb_include module=system template=inc_config_enum var=CViewAccessToken_purge_delay values='7|14|30'}}

        {{mb_include module=system template=inc_config_num var=CCronJobLog_purge_probability numeric=true}}
        {{mb_include module=system template=inc_config_enum var=CCronJobLog_purge_delay values='30|60|90|120'}}

        <tr>
            <th colspan="2" class="title">
                Sécurité
            </th>
        </tr>

        {{mb_include module=system template=inc_config_bool var=purify_text_input}}
        {{mb_include module=system template=inc_config_str var=app_private_key_filepath size=50}}
        {{mb_include module=system template=inc_config_str var=app_public_key_filepath size=50}}
        {{mb_include module=system template=inc_config_str var=app_master_key_filepath size=50}}
        {{mb_include module=system template=inc_config_bool var=anti_csrf_enable}}

        <tr>
            <th colspan="2" class="title">
                Compression des scripts et feuilles de style
            </th>
        </tr>
        {{mb_include module=system template=inc_config_enum var=minify_javascript values="0|1"}}
        {{mb_include module=system template=inc_config_enum var=minify_css values="1|2"}}

        <tr>
            <th colspan="2" class="title">
                Fusion des objets
            </th>
        </tr>
        {{mb_include module=system template=inc_config_bool var=merge_prevent_base_without_idex}}

        <tr>
            <th colspan="2" class="title">
                Paramètres réseau
            </th>
        </tr>

        {{* Not to place in module/system configurations *}}
        {{mb_include module=system template=inc_config_bool var=check_server_connectivity}}

        {{assign var="m" value="system"}}
        {{mb_include module=system template=inc_config_str var=reverse_proxy}}
        {{mb_include module=system template=inc_config_str var=website_url size=40}}

        <tr>
            <th colspan="2" class="title">
                Mode migration
            </th>
        </tr>

        <tr>
            <th colspan="2" class="title">
                Mode esclave
            </th>
        </tr>

        {{assign var="m" value=""}}
        {{mb_include module=system template=inc_config_bool var=enslaving_active}}
        {{mb_include module=system template=inc_config_num var=enslaving_ratio}}

        <tr>
            <td class="button" colspan="2">
                <button class="modify">{{tr}}Save{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>

{{mb_include module=system template=configure_dsn dsn=slave}}
{{mb_include module=system template=configure_dsn dsn=cluster}}
