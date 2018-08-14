<?php
echo rex_view::title($this->i18n('hello'));


$func = rex_request('func', 'string');
$start = rex_request('start', 'int');

    // Domain-Übersicht ANFANG //
    $query = 'SELECT * FROM (SELECT id, domain, cms, cms_version, LEFT(php_version, 6) as php_version FROM `rex_hello_domain` WHERE cms = "REDAXO" AND cms_version > 5 ORDER BY domain ASC) AS D
    LEFT JOIN (SELECT domain, `raw` as log_raw FROM rex_hello_domain_log WHERE id IN (SELECT MAX(id) FROM rex_hello_domain_log GROUP BY domain)) AS HLOG
    ON D.domain = HLOG.domain
    ORDER BY D.domain ASC';
    $list = rex_list::factory($query, 1000);
    $list->addTableAttribute('class', 'table-striped');
    $list->setNoRowsMessage($this->i18n('hello_domain_norows_message'));
    
    // icon column (Domain hinzufügen bzw. bearbeiten)
    $thIcon = '<a href="'.$list->getUrl(['func' => 'domain_add','start' => $start]).'"><i class="rex-icon rex-icon-structure-root-level"></i></a>';
    $tdIcon = '<i class="rex-icon rex-icon-structure-root-level"></i>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'domain_edit', 'id' => '###id###','start' => $start]);
    $list->setColumnFormat($thIcon, 'custom', function ($params) {
            return '<img src="/assets/addons/hello/plugins/server/favicon/'.$params['list']->getValue('domain').'.png" />';
    });
            
    $list->addColumn($this->i18n('domain'), '###domain###', 3);
    $list->setColumnParams($this->i18n('domain'), ['page' => 'hello/server-details', 'func' => 'updateinfos', 'domain' => '###domain###']);
    
    $list->setColumnLabel('domain', $this->i18n('project'));
    $list->setColumnLayout('domain', ['<th data-sorter="text">###VALUE###</th>', '<td>###VALUE###</td>']);

    $list->setColumnLabel('cms_version', $this->i18n('cms_version'));
    $list->setColumnLayout('cms_version', ['<th data-sorter="digit" data-string="min">###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnFormat('cms_version', 'custom', function ($params) {
        if($params['list']->getValue('cms') == "REDAXO") {
            if (rex_string::versionCompare($params['list']->getValue('cms_version'), "5.6.0", '>=') && rex_string::versionCompare($params['list']->getValue('cms_version'), "5", '>')) {
                return '<span class="rex-icon fa-check text-success"></span> '. $params['list']->getValue('cms_version');
            } else if (rex_string::versionCompare($params['list']->getValue('cms_version'), "5.6.2", '<') && rex_string::versionCompare($params['list']->getValue('cms_version'), "5", '>')) {
                return '<span class="rex-icon fa-question text-danger"></span> '. $params['list']->getValue('cms_version');
            } else if (rex_string::versionCompare($params['list']->getValue('cms_version'), "4", '>') && rex_string::versionCompare($params['list']->getValue('cms_version'), "4.7", '<')) {
                return '<span class="rex-icon fa-question text-danger"></span> '.$params['list']->getValue('cms_version');
            } 
        } else {
            return $params['list']->getValue('cms') . " " . $params['list']->getValue('cms_version');
        }
    });

    $list->setColumnLabel('php_version', $this->i18n('php_version'));
    $list->setColumnLayout('php_version', ['<th data-sorter="digit">###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnFormat('php_version', 'custom', function ($params) {
        if($params['list']->getValue('cms') == "REDAXO") {
            if (rex_string::versionCompare($params['list']->getValue('php_version'), "5.7", '>') && rex_string::versionCompare($params['list']->getValue('cms_version'), "5.0", '>')) {
                return '<span class="rex-icon fa-check text-success"></span> '. $params['list']->getValue('php_version');
            } else if (rex_string::versionCompare($params['list']->getValue('php_version'), "7", '<') && rex_string::versionCompare($params['list']->getValue('cms_version'), "5.0", '>')) {
                return '<span class="rex-icon fa-question text-danger"></span> '. $params['list']->getValue('php_version');
            } else if (rex_string::versionCompare($params['list']->getValue('php_version'), "5.7", '>') && rex_string::versionCompare($params['list']->getValue('cms_version'), "4.7", '<')) {
                return '<span class="rex-icon fa-question text-danger"></span> '.$params['list']->getValue('php_version');
            } else if (rex_string::versionCompare($params['list']->getValue('php_version'), "5.6", '<')) {
                return '<span class="rex-icon fa-question text-danger"></span> '.$params['list']->getValue('php_version');
            }
        } else {
                return $params['list']->getValue('php_version');
        }
    });

    $list->setColumnLabel('hello_version', $this->i18n('hello_version'));
    $list->setColumnLayout('hello_version', ['<th data-sorter="digit">###VALUE###</th>', '<td>###VALUE###</td>']);

    $list->setColumnLabel('status', $this->i18n('status'));
    $list->setColumnFormat('status', 'custom', function ($params) {
        if ($params['list']->getValue('status') == "1") {
            return '<span class="rex-icon fa-check"></span>';
        } else if ($params['list']->getValue('status') == "0") { 
            return '<span class="rex-icon fa-question"></span>';
        } else if ($params['list']->getValue('status') == "-1") { 
            return '<span class="rex-icon fa-exclamation-triangle"></span>';
        } else { 
            return "?";
        }
    });
    $list->setColumnLayout('status', ['<th data-sorter="digit">###VALUE###</th>', '<td>###VALUE###</td>']);

    $list->addColumn("debug_mode", false, -1, ['<th class="rex-table-icon">###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnLabel('debug_mode', $this->i18n('debug_mode'));
    $list->setColumnFormat("debug_mode", 'custom', function ($params) {
        if($params['list']->getValue('log_raw')) {
            $log = json_decode($params['list']->getValue('log_raw'), true);
            if(json_last_error() === JSON_ERROR_NONE) {
                $config = rex_string::yamlDecode($log["config"]);
                if(isset($config["debug"]["enabled"]) && $config["debug"]["enabled"] == "enabled") {
                    return '<i title="" class="rex-icon fa-check text-danger"></i> '.$config["debug"]["enabled"].'';
                } else if(isset($config["debug"]["enabled"])) {
                    return '<span class="rex-icon fa-check text-success"></span>';
                } else {
                    return "?";
                }
            } else {
                return "";
            }
        }
    });
    
        $list->addColumn('syslog', false, -1, ['<th class="rex-table-icon">###VALUE###</th>', '<td>###VALUE###</td>']);
        $list->setColumnLabel('syslog', ($addon));
        $list->setColumnFormat('syslog', 'custom', function ($params) {
            if($params['list']->getValue('log_raw')) {
                $log = json_decode($params['list']->getValue('log_raw'), true);
                if(json_last_error() === JSON_ERROR_NONE && $log['syslog']) {

                    $output = '<table class="hello-syslog-table table table-striped"><thead><tr><th>Zeitstempel</th><th>Typ</th><th>Nachricht</th><th>Datei</th></tr></thead><tbody>';
                    $i = 0;
                    foreach ($log['syslog'] as $entry) {
                        $output .= '<tr>';
                        $output .= '<td>'.$entry["timestamp"].'</td>';
                        $output .= '<td>'.$entry["syslog_type"].'</td>';
                        $output .= '<td><span class="teaser">'.$entry["syslog_message"].'</span></td>';
                        $output .= '<td><span class="teaser">'.$entry["syslog_file"].'</span></td>';
                        $output .= '</tr>';
                        $i++;
                        if($i >= 3) { break; }
                    } 
                    $output .= '</tbody></table>';

                    return $output;
                }
            }
        });

    $list->removeColumn('id');
    $list->removeColumn('cms');
    $list->removeColumn('domain');
    $list->removeColumn('createdate');
    $list->removeColumn('updatedate');
    $list->removeColumn('log_raw');

    $content1 = $list->get();
    $content1 = str_replace('<table class="', '<table class="hello-tablesorter ', $content1);    
    
    $fragment = new rex_fragment();
    $fragment->setVar('class', "info", false);
    $fragment->setVar('title', $this->i18n('hello_domain_list_title'), false);
    $fragment->setVar('content', $content1, false);
    $content1 = $fragment->parse('core/page/section.php');
    
    echo $content1;
    echo "<style>.hello-syslog-table .teaser {
        max-width: 180px;
        white-space: nowrap;
        display: block;
        text-overflow: ellipsis;
        overflow: hidden;} th, td {white-space: nowrap; }</style>";
    // Domain-Übersicht ENDE //