<?php

// Aufruf: 
// /?rex-api-call=hello&api_key=###

class rex_api_hello extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        ob_end_clean();
        $api_key = rex_request('api_key','string');
        

        if($api_key == rex_config::get('hello', 'hello_api_key')) {

            
            # REDAXO / SERVER / ALLGEMEIN

            $params['hello_version']    = rex_addon::get('hello')->getProperty('version');
            $params['rex_version']      = rex::getVersion();
            $params['php_version']      = phpversion();
            $params['status']           = 1;

            # / REDAXO / SERVER / ALLGEMEIN

            # ADDONS

            $rex_addons = rex_addon::getAvailableAddons();
            
            rex_install_webservice::deleteCache();
            
            try {
                $installer_addons = rex_install_packages::getAddPackages();
            } catch (rex_functional_exception $e) {
                $params['message'][] = $e->getMessage();
            }
        
            foreach($rex_addons as $key => $addon) {
                $params['rex_addons'][$key]['name'] = $addon->getName();
                $params['rex_addons'][$key]['install'] = $addon->getProperty('install');
                $params['rex_addons'][$key]['status'] = $addon->getProperty('status');
                $params['rex_addons'][$key]['version_current'] = $addon->getProperty('version');             
                if(!empty($installer_addons[$key])) {
                    $params['rex_addons'][$key]['version_latest'] = current($installer_addons[$key]["files"])["version"]; 
                } else {
                    $params['rex_addons'][$key]['version_latest'] = 0; 
                }
            }

            # / ADDONS

            # DOMAINS / WEBSITES

            $params['domains'][rex::getServer()]['name'] = rex::getServer();
            $params['domains'][rex::getServer()]['url'] = rex_getUrl(rex_article::getSiteStartArticleId());
            $params['domains'][rex::getServer()]['url_404'] = rex_getUrl(rex_article::getNotfoundArticleId());

            if(rex_addon::get('yrewrite')->isAvailable()) {

                $yrewrite_domains = rex_yrewrite::getDomains(true);
                foreach($yrewrite_domains as $key => $domain) {
                    $params['domains'][$key]['name'] = $domain->getName();
                    $params['domains'][$key]['url'] = $domain->getUrl();
                    $params['domains'][$key]['url_404'] = rex_yrewrite::getFullUrlByArticleId($domain->getNotfoundId());
                }
            }

            # / DOMAINS / WEBSITES

            # SYSLOG 

            $log = new rex_log_file(rex_path::coreData('system.log'));

            foreach (new LimitIterator($log, 0, 30) as $entry) {
                $data = $entry->getData();
                $params['syslog'][]['timestamp'] = $entry->getTimestamp('%d.%m.%Y %H:%M:%S');
                $params['syslog'][]['syslog_type'] = $data[0];
                $params['syslog'][]['syslog_message'] = $data[1];
                $params['syslog'][]['syslog_file'] = (isset($data[2]) ? $data[2] : '');
                $params['syslog'][]['syslog_line'] = (isset($data[3]) ? $data[3] : '');
            }

            # / SYSLOG


        } else {
            $params['status']       = 0;
            $params['message'][]    = "Falscher API-Schlüssel.";
        }
        
        header('Content-Type: application/json; charset=UTF-8');  
        $hello = json_encode($params, true);
        echo $hello;
        exit();
    }
}

?>