<?php
echo rex_view::title($this->i18n('hello'));


$func = rex_request('func', 'string');
$start = rex_request('start', 'int');

    // Domain-Übersicht ANFANG //
    $query = 'SELECT * FROM (SELECT * FROM `rex_hello_domain` ORDER BY domain) AS D
LEFT JOIN (SELECT domain, score_desktop AS psi_score_desktop, score_mobile AS psi_score_mobile FROM `rex_hello_domain_psi` WHERE id IN (SELECT MAX(id) FROM rex_hello_domain_psi GROUP BY domain)) as PSI
    ON D.domain = PSI.domain
    LEFT JOIN (SELECT domain, 1 AS in_dnsklix FROM rex_hello_pixelfirma_dnsklix) AS PXDNS
    ON D.domain = PXDNS.domain
    LEFT JOIN (SELECT domain, `raw` as log_raw FROM rex_hello_domain_log WHERE id IN (SELECT MAX(id) FROM rex_hello_domain_log GROUP BY domain)) AS HLOG
    ON D.domain = HLOG.domain
LEFT JOIN (SELECT 
SUM(IF(siteUrl = CONCAT("http://",domain,"/"),1,0)) AS gsc_has_http, 
SUM(IF(siteUrl = CONCAT("http://www.",domain,"/"),1,0)) AS gsc_has_http_www, 
SUM(IF(siteUrl = CONCAT("https://",domain,"/"),1,0)) AS gsc_has_https, 
SUM(IF(siteUrl = CONCAT("https://www.",domain,"/"),1,0)) AS gsc_has_https_www, 
domain, count(domain) AS gsc_domains FROM `rex_hello_domain_gsc` GROUP BY domain ORDER BY domain) as GSC
    ON D.domain = GSC.domain';
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
    
    $list->setColumnLabel('gsc_domains', $this->i18n('gsc_domains'));
    $list->setColumnLayout('gsc_domains', ['<th data-sorter="false"><span class="rex-icon fa-google"></span> Search Console</th>', '<td>###VALUE###</td>']);
    $list->setColumnFormat('gsc_domains', 'custom', function ($params) {
        if($params['list']->getValue('ip')) {
            $gsc_check = '<a href="https://www.google.com/webmasters/tools/home?hl=de" target="_blank">';        
        }
        if($params['list']->getValue('gsc_has_http')) {
            $gsc_check .= '<span class="rex-icon fa-check text-success"></span>';
        } else {
            $gsc_check .= '<span class="rex-icon fa-exclamation-triangle text-danger"></span>';
        }
        if($params['list']->getValue('gsc_has_http_www')) {
            $gsc_check .= '<span class="rex-icon fa-check text-success"></span>';
        } else {
            $gsc_check .= '<span class="rex-icon fa-exclamation-triangle text-danger"></span>';
        }

        if ($params['list']->getValue('is_ssl') == 1) {
            if($params['list']->getValue('gsc_has_https')) {
                $gsc_check .= '<span class="rex-icon fa-check text-success"></span>';
            } else {
                $gsc_check .= '<span class="rex-icon fa-exclamation-triangle text-danger"></span>';
            }
            if($params['list']->getValue('gsc_has_https_www')) {
                $gsc_check .= '<span class="rex-icon fa-check text-success"></span>';
            } else {
                $gsc_check .= '<span class="rex-icon fa-exclamation-triangle text-danger"></span>';
            }
        } else { 
            $gsc_check .= '';
        }

        if($params['list']->getValue('ip')) {
            $gsc_check .= '</a>';
        }
        
        return $gsc_check;
    });

    $list->addColumn("Pagespeed", false, -1, ['<th class="rex-table-icon"><span class="rex-icon fa-google"></span> PageSpeed</th>', '<td>###VALUE###</td>']);
    $list->setColumnFormat("Pagespeed", 'custom', function ($params) {
        if($params['list']->getValue('ip')) {
            if($params['list']->getValue('psi_score_desktop') < 70) {
                $return = '<span class="rex-icon fa-desktop text-danger"></span> '.$params['list']->getValue('psi_score_desktop');
            } else if($params['list']->getValue('psi_score_desktop') < 90) {
                $return = '<span class="rex-icon fa-desktop text-warning"></span> '.$params['list']->getValue('psi_score_desktop');
            } else {
                $return = '<span class="rex-icon fa-desktop text-success"></span> '.$params['list']->getValue('psi_score_desktop');
            }
            $return .= " | ";
            if($params['list']->getValue('psi_score_mobile') < 70) {
                $return .= '<span class="rex-icon fa-mobile text-danger"></span> '.$params['list']->getValue('psi_score_mobile');
            } else if($params['list']->getValue('psi_score_mobile') < 90) {
                $return .= '<span class="rex-icon fa-mobile text-warning"></span> '.$params['list']->getValue('psi_score_mobile');
            } else {
                $return .= '<span class="rex-icon fa-mobile text-success"></span> '.$params['list']->getValue('psi_score_mobile');
            }
            return $return;
        }
    });

    
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

    $list->addColumn("last_change", false, -1, ['<th class="rex-table-icon">###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnLabel('last_change', $this->i18n('last_change'));
    $list->setColumnFormat("last_change", 'custom', function ($params) {
        if($params['list']->getValue('log_raw')) {
            $log = json_decode($params['list']->getValue('log_raw'), true);
            if(json_last_error() === JSON_ERROR_NONE && $log['article'][0]) {
                    return $log['article'][0]['name'] . "<br /> am " . $log['article'][0]['updatedate'];
            } else {
                return "";
            }
        }
    }); 
    
    $list->removeColumn('id');
    $list->removeColumn('api_key');
    $list->removeColumn('alias_id');
    $list->removeColumn('domain');
    $list->removeColumn('createdate');
    $list->removeColumn('updatedate');
    $list->removeColumn('hoster');
    $list->removeColumn('paket');
    $list->removeColumn('log_raw');
    $list->removeColumn('in_dnsklix');
    $list->removeColumn('cms_version');
    $list->removeColumn('hello_version');
    $list->removeColumn('php_version');
    $list->removeColumn('status');
    $list->removeColumn('ip');
    $list->removeColumn('gsc_has_http');
    $list->removeColumn('gsc_has_http');
    $list->removeColumn('gsc_has_http_www');
    $list->removeColumn('gsc_has_https');
    $list->removeColumn('gsc_has_https_www');
    $list->removeColumn('psi_score_desktop');
    $list->removeColumn('psi_score_mobile');


    $content1 = $list->get();
    $content1 = str_replace('<table class="', '<table class="hello-tablesorter ', $content1);    
    
    $fragment = new rex_fragment();
    $fragment->setVar('class', "info", false);
    $fragment->setVar('title', $this->i18n('hello_domain_list_title'), false);
    $fragment->setVar('content', $content1, false);
    $content1 = $fragment->parse('core/page/section.php');
    
    echo $content1;
    echo "<style>th, td {white-space: nowrap; }</style>";
    // Domain-Übersicht ENDE //