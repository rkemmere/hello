<?php
echo rex_view::title($this->i18n('hello'));


$func = rex_request('func', 'string');
$start = rex_request('start', 'int');

if (($func == '') || $func == "domain_delete") { 

    if($func == 'domain_delete') {
        $oid = rex_request('oid', 'int');
        $delete = rex_sql::factory()->setQuery('DELETE FROM rex_hello_domain WHERE id = :oid',array(':oid' => $oid));
        $delete = rex_sql::factory()->setDebug(0)->setQuery('DELETE FROM rex_hello_domain WHERE domain = :domain',array(':domain' => $domain));
        echo rex_view::success( $this->i18n('hello_domain_deleted'));
    }	

    // Domain-Übersicht ANFANG //
    $query = 'SELECT * FROM (SELECT *, unix_timestamp(updatedate) AS logdate FROM `rex_hello_domain` ORDER BY domain) AS D
    LEFT JOIN (SELECT IpAddress, AsName AS hoster FROM `rex_hello_domain_mxtoolbox` GROUP BY IpAddress) AS MX
    ON D.ip = MX.IpAddress
LEFT JOIN (SELECT * FROM `rex_hello_domain_netbuild` ORDER BY domain) as NBP
    ON D.domain = NBP.domain
    LEFT JOIN (SELECT domain, `raw` as log_raw FROM rex_hello_domain_log WHERE id IN (SELECT MAX(id) FROM rex_hello_domain_log GROUP BY domain)) AS HLOG
    ON D.domain = HLOG.domain
    ORDER BY D.domain, D.ip';
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

    $list->setColumnLabel('api_key', $this->i18n('api_key'));
    $list->setColumnFormat('api_key', 'custom', function ($params) {
        return '<a href="?page=hello/server-edit&id='.$params['list']->getValue('id').'&domain='.$params['list']->getValue('domain').'&func=domain_edit"><span class="rex-icon fa-edit"></span>&nbsp;'.substr($params['list']->getValue('api_key'),0,5)."...".'</a>';

    });

    $list->setColumnLabel('ip', $this->i18n('ip'));
    $list->setColumnLayout('ip', ['<th data-sorter="false">###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnFormat('ip', 'custom', function ($params) {
        if ($params['list']->getValue('ip') && $params['list']->getValue('paket')) {
            return '<span>'.$params['list']->getValue('ip')."<br /><sup>".$params['list']->getValue('hoster').'<br /><a traget="_blank" href="http://www.net-server.de/?user='.$params['list']->getValue('paket').'">'.$params['list']->getValue('paket').'</a></sup></span>';
        } else if ($params['list']->getValue('ip')) {
                return '<span>'.$params['list']->getValue('ip')."<br /><sup>".$params['list']->getValue('hoster').'</sup></span>';
        } else { 
                return 'offline';
        }
    });

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

    
    $list->setColumnLabel('http_code', $this->i18n('http_code'));
    $list->setColumnLayout('http_code', ['<th data-sorter="digit">###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnFormat('http_code', 'custom', function ($params) {
        if ($params['list']->getValue('http_code') == "200") {
            return '<span class="rex-icon fa-check text-success"></span>';
        } else if(!$params['list']->getValue('http_code')) {
            return false;
        } else {
            return '<span class="rex-icon fa-exclamation-triangle text-danger"></span> <a href="http://www.'.$params['list']->getValue('domain').'/?rex-api-call=hello&api_key='.$params['list']->getValue('api_key').'">'. $params['list']->getValue('http_code').'</a>';
        }
    });

    $list->setColumnLabel('is_ssl', $this->i18n('is_ssl'));
    $list->setColumnLayout('is_ssl', ['<th data-sorter="digit">###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnFormat('is_ssl', 'custom', function ($params) {
        if (!$params['list']->getValue('ip')) { 
            return "";
        } else if ($params['list']->getValue('is_ssl') == "1") {
            return '<span class="rex-icon fa-lock text-success"></span>';
        } else if ($params['list']->getValue('is_ssl') == "-1") { 
            return '<span class="rex-icon fa-unlock text-danger"></span>';
        } else { 
            return "?";
        }
    });

    $list->setColumnLabel('logdate', $this->i18n('hello_domain_column_last_call'));
    $list->setColumnLayout('logdate', ['<th sorter="shortDate" data-date-format="dd.mm.yyyy">###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnFormat('logdate', 'custom', function ($params) {
        if ($params['list']->getValue('logdate') != "") {
            return str_replace(' ', '&nbsp;', date("d.m.Y H:i", $params['list']->getValue('logdate')));
        } else { 
            return rex_i18n::msg("hello_domain_column_last_call_none");
        }
    });

    $list->addColumn("hello_message", false, -1, ['<th class="rex-table-icon">###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnLabel('hello_message', $this->i18n('hello_message'));
    $list->setColumnFormat("hello_message", 'custom', function ($params) {
        if($params['list']->getValue('log_raw')) {
            $log = json_decode($params['list']->getValue('log_raw'), true);
            if(json_last_error() === JSON_ERROR_NONE) {
                if(isset($log['message'][0])) {
                    return '<i title="" class="rex-icon fa-exclamation-triangle"></i> '.$log['message'][0];
                } else {
                    return "";
                }        
            } else {
                return "?";
            }
        }
    });

    
    $list->addColumn('domain_delete', '<i class="rex-icon rex-icon-delete"></i> ' . $this->i18n('hello_domain_column_delete'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('domain_delete', ['func' => 'domain_delete', 'oid' => '###id###', 'domain' => '###domain###','start' => $start]);
    $list->addLinkAttribute('domain_delete', 'data-confirm', $this->i18n('hello_domain_delete_confirm'));


    $list->removeColumn('id');
    $list->removeColumn('alias_id');
    $list->removeColumn('domain');
    $list->removeColumn('debug_mode');
    $list->removeColumn('createdate');
    $list->removeColumn('updatedate');
    $list->removeColumn('IpAddress');
    $list->removeColumn('hoster');
    $list->removeColumn('paket');
    $list->removeColumn('gsc_has_http');
    $list->removeColumn('gsc_has_http_www');
    $list->removeColumn('gsc_has_https');
    $list->removeColumn('gsc_has_https_www');
    $list->removeColumn('psi_score_desktop');
    $list->removeColumn('psi_score_mobile');
    $list->removeColumn('log_raw');

    $content1 = $list->get();
    $content1 = str_replace('<table class="', '<table class="hello-tablesorter ', $content1);    
    
    $fragment = new rex_fragment();
    $fragment->setVar('class', "info", false);
    $fragment->setVar('title', $this->i18n('hello_domain_list_title'), false);
    $fragment->setVar('content', $content1, false);
    $content1 = $fragment->parse('core/page/section.php');
    
    echo $content1;
    // Domain-Übersicht ENDE //

} else if ($func == 'domain_add' || $func == 'domain_edit') { 
    
    // Domain bearbeiten ANFANG //

    $id = rex_request('id', 'int');
    
    if ($func == 'domain_edit') {
        $formLabel = $this->i18n('hello_domain_text_edit');
    } elseif ($func == 'domain_add') {
        $formLabel = $this->i18n('hello_domain_text_add');
    }
    
    $form = rex_form::factory(rex::getTablePrefix().'hello_domain', '', 'id='.$id);
    $form->addParam('start', $start);

    //Start - add domain-field
    $field = $form->addTextField('domain');
    $field->setLabel($this->i18n('hello_domain_column_domain'));
    $field->setNotice($this->i18n('hello_domain_column_domain_note'));
    $field->getValidator()->add('notEmpty', $this->i18n('hello_column_domain_empty'));
    //End - add domain-field

    //Start - add domain-field
    $field = $form->addTextField('api_key');
    $field->setLabel($this->i18n('hello_domain_column_api_key'));
    $field->setNotice($this->i18n('hello_domain_column_api_key_note', md5(time())));
    $field->getValidator()->add('notEmpty', $this->i18n('hello_column_api_key_empty'));


    //End - add domain-field

    //Start - add alias-field
    $field = $form->addSelectField('alias_id','',['class'=>'form-control selectpicker']); 
    $field->setLabel($this->i18n('hello_column_alias'));
    $select = $field->getSelect();
    $select->setSize(1);
    $select->addOption("Bitte wählen", "");
    $select->addDBSqlOptions('select domain as name, id as id FROM rex_hello_domain WHERE alias_id = "" ORDER BY domain');
    $select->setSelected($domain);
    $field->setNotice($this->i18n('hello_column_alias_note'));
    //End - add domain-field

    if ($func == 'domain_edit') {
        $form->addParam('id', $id);
    }

    $content3 = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $formLabel, false);
    $fragment->setVar('body', $content3, false);
    $content3 = $fragment->parse('core/page/section.php');

    echo $content3;
    // Domain bearbeiten ENDE //

} 