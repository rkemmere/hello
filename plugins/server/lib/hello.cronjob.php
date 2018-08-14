<?php

class rex_cronjob_hello extends rex_cronjob
{

    public function execute()
    {

        $websites = rex_sql::factory()->setDebug(0)->getArray('SELECT * FROM rex_hello_domain ORDER BY updatedate asc LIMIT 25'); 

        /* Hello Addon-Abruf */
        $multi_curl = curl_multi_init();
        $resps = array();
        $options = array(
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true, 
            CURLOPT_MAXREDIRS    => 4, 
            CURLOPT_HEADER         => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 1000
        );

        foreach($websites as $website) {
            $domain = $website['domain'];
            $url_hello = $domain."/hello.php?rex-api-call=hello&api_key=".$website['api_key'];
            $url_domain = $domain."/";
            $resps[$domain.";hello"] = curl_init($url_hello);
            $resps[$domain.";domain"] = curl_init($url_domain);
            curl_setopt_array($resps[$domain.";hello"], $options);
            curl_setopt_array($resps[$domain.";domain"], $options);
            curl_multi_add_handle($multi_curl, $resps[$domain.";hello"]);
            curl_multi_add_handle($multi_curl, $resps[$domain.";domain"]);
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

            $domain = explode(";", $key)[0];
            $mode = explode(";", $key)[1];

            $resp = curl_multi_getcontent($response);
            curl_multi_remove_handle($multi_curl, $response);

            $json = json_decode($resp, true);

            if($mode == "hello") {
                if(json_last_error() === JSON_ERROR_NONE && $json !== null) {
                    rex_sql::factory()->setDebug(0)->setQuery('INSERT INTO rex_hello_domain_log (`domain`, `status`, `createdate`, `raw`) VALUES(?,?,NOW(),?)', [$domain, 1, $resp] );
                    rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET hello_version = ? WHERE domain = ?", [$json['hello_version'], $domain]); 
                    rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET cms_version = ? WHERE domain = ?", [$json['cms_version'], $domain]); 
                    rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET cms = ? WHERE domain = ?", [$json['cms'], $domain]); 
                    rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET php_version = ? WHERE domain = ?", [$json['php_version'], $domain]); 
                    rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET status = ? WHERE domain = ?", [$json['status'], $domain]); 
                    } else {
                    rex_sql::factory()->setDebug(0)->setQuery('INSERT INTO rex_hello_domain_log (`domain`, `status`, `createdate`, `raw`) VALUES(?,?,NOW(),?)', [$domain, -1, $resp] );
                    rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET status = ? WHERE domain = ?", [-1, $domain]); 
                }
            } else if($mode == "domain") {
                rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET http_code = ? WHERE domain = ?", [$meta[$domain]['http_code'], $domain]); 
                if($meta[$key]['primary_port'] === 443) {
                    rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET is_ssl = ? WHERE domain = ?", [1, $domain]); 
                } else {
                    rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET is_ssl = ? WHERE domain = ?", [-1, $domain]); 
                }
                rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET ip = ? WHERE domain = ?", [$meta[$domain]['primary_ip'], $domain]); 
            }

            rex_sql::factory()->setDebug(0)->setQuery("UPDATE rex_hello_domain SET updatedate = NOW() WHERE domain = ?", [$domain]); 
            


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