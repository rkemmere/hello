<?php

class rex_cronjob_hello extends rex_cronjob
{

    public function execute()
    {

        $domains = rex_sql::factory()->setDebug(0)->getArray("SELECT * FROM rex_hello_domain"); 

        foreach($domains as $domain) {
        $curl = curl_init();
        $url = $domain['url']."/?rex-api-call=hello&api_key=".$domain['api_key'];
        curl_setopt_array($curl,array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true));
        $resp = curl_exec($curl);

            if (!curl_errno($curl)) { 

                if(json_last_error() === JSON_ERROR_NONE) {
                    rex_sql::factory()->setDebug(0)->setQuery('INSERT INTO rex_hello_domain_log (`domain`, `status`, `createdate`, `raw`) VALUES(?,?,NOW(),?)', [$domain['domain'], 1, $resp] );
                } else {
                    rex_sql::factory()->setDebug(0)->setQuery('INSERT INTO rex_hello_domain_log (`domain`, `status`, `createdate`, `raw`) VALUES(?,?,NOW(),?)', [$domain['domain'], 0, $resp] );
                }
            }

        }

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