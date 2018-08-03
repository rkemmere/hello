<?php

class rex_cronjob_hello extends rex_cronjob
{

    public function execute()
    {

        $domains = rex_sql::factory()->setDebug(0)->getArray("SELECT * FROM rex_hello_domain ORDER BY updatedate asc LIMIT 50"); 

        /* Hello Addon-Abruf */
        $multi_curl = curl_multi_init();
        $resps = array();
        $options = array(
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true, 
            CURLOPT_HEADER         => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 1000
        );
        $fstreams = array();

        foreach($domains as $domain) {
            $i = $domain['domain'];
            $url = $domain['domain']."/?rex-api-call=hello&api_key=".$domain['api_key'];
            $resps[$i] = curl_init($url);
            curl_setopt_array($resps[$i], $options);
            curl_multi_add_handle($multi_curl, $resps[$i]);
        }
        $active = null;
        do {
            curl_multi_exec($multi_curl, $active);
        } while ($active > 0);
        
        while ($curl_handle = curl_multi_info_read($multi_curl)) {
            $info = curl_getinfo($curl_handle['handle']);
            $host = ltrim(parse_url($info['url'], PHP_URL_HOST), 'www.');
            $meta[$host] = $info;
        }

        foreach ($resps as $key => $response) {

            $resp = curl_multi_getcontent($response);
            curl_multi_remove_handle($multi_curl, $response);

            $json = json_decode($resp, true);

            if(json_last_error() === JSON_ERROR_NONE) {
                rex_sql::factory()->setDebug(0)->setQuery('INSERT INTO rex_hello_domain_log (`domain`, `status`, `createdate`, `raw`) VALUES(?,?,NOW(),?)', [$key, 1, $resp] );
                rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET hello_version = ? WHERE domain = ?", [$json['hello_version'], $key]); 
                rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET rex_version = ? WHERE domain = ?", [$json['rex_version'], $key]); 
                rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET php_version = ? WHERE domain = ?", [$json['php_version'], $key]); 
                } else {
                rex_sql::factory()->setDebug(0)->setQuery('INSERT INTO rex_hello_domain_log (`domain`, `status`, `createdate`, `raw`) VALUES(?,?,NOW(),?)', [$key, 0, $resp] );
            }
        rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET http_code = ? WHERE domain = ?", [$meta[$key]['http_code'], $key]); 
        rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET is_ssl = ? WHERE domain = ?", [0, $key]); 
        rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET ip = ? WHERE domain = ?", [$meta[$key]['primary_ip'], $key]); 
        rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET updatedate = NOW() WHERE domain = ?", [$key]); 
            


        }
        
        curl_multi_close($multi_curl);

        return true;

    }
    public function getTypeName()
    {
        return rex_i18n::msg('hello_cronjob_name');
    }

    public function getParamFields()
    {
        return [];
    }
}
?>