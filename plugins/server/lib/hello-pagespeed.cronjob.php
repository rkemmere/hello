<?php

class rex_cronjob_hello_pagespeed extends rex_cronjob
{

    public function execute()
    {

        $websites = rex_sql::factory()->setDebug(0)->getArray('SELECT D.domain AS domain FROM
        (SELECT domain, createdate FROM `rex_hello_domain_psi`) AS PSI
        RIGHT JOIN
        (SELECT domain, updatedate FROM rex_hello_domain WHERE ip != "") AS D
        ON
        PSI.domain = D.DOMAIN
        ORDER BY PSI.createdate ASC LIMIT 5'); 
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
        $fstreams = array();

        foreach($websites as $website) {
            $domain = $website['domain'];
            if($website['is_ssl']) {
                $prefix = "https://www.";
            } else {
                $prefix = "http://www.";
            }
            $url_desktop = 'https://www.googleapis.com/pagespeedonline/v4/runPagespeed?filter_third_party_resources=false&locale=de_DE&screenshot=true&snapshots=false&strategy=desktop&key='.rex_config::get('hello/server', 'hello_google_api_key').'&url='.urlencode($prefix.$website['domain']);
            $url_mobile = 'https://www.googleapis.com/pagespeedonline/v4/runPagespeed?filter_third_party_resources=false&locale=de_DE&screenshot=true&snapshots=false&strategy=mobile&key='.rex_config::get('hello/server', 'hello_google_api_key').'&url='.urlencode($prefix.$website['domain']);
            $resps[$domain.";desktop"] = curl_init($url_desktop);
            $resps[$domain.";mobile"] = curl_init($url_mobile);
            curl_setopt_array($resps[$domain.";desktop"], $options);
            curl_setopt_array($resps[$domain.";mobile"], $options);
            curl_multi_add_handle($multi_curl, $resps[$domain.";desktop"]);
            curl_multi_add_handle($multi_curl, $resps[$domain.";mobile"]);
        }
        $active = null;
        do {
            curl_multi_exec($multi_curl, $active);
        } while ($active > 0);

   
        foreach ($resps as $key => $response) {
            $domain = explode(";", $key)[0];
            $mode = explode(";", $key)[1];
            $resp = curl_multi_getcontent($response);
            curl_multi_remove_handle($multi_curl, $response);

            $pagespeed = json_decode($resp, true);
            $data = str_replace(["_", "-"], ["/", "+"], $pagespeed['screenshot']['data']);
            $image = 'data:'.$pagespeed['screenshot']['mime_type'].';base64,'.$data;
            // echo '<img src="' . $img . '" />';

            if(json_last_error() === JSON_ERROR_NONE && !is_array($pagespeed['error'])) {
                if($mode == "desktop") {
                    rex_sql::factory()->setDebug(0)->setQuery('INSERT INTO rex_hello_domain_psi (`domain`, `raw`, `createdate`, `score_desktop`) VALUES(:domain, :resp, NOW(), :score_desktop) 
                    ON DUPLICATE KEY UPDATE domain = :domain, `raw` = :resp, createdate = NOW(), `score_desktop` = :score_desktop', [":domain" => $domain, ":resp" => $resp, ":score_desktop" => $pagespeed['ruleGroups']["SPEED"]["score"]] );
                } else  if($mode == "mobile") {
                    rex_sql::factory()->setDebug(0)->setQuery('INSERT INTO rex_hello_domain_psi (`domain`, `raw`, `createdate`, `score_mobile`) VALUES(:domain, :resp, NOW(), :score_mobile) 
                    ON DUPLICATE KEY UPDATE domain = :domain, `raw` = :resp, createdate = NOW(), `score_mobile` = :score_mobile', [":domain" => $domain, ":resp" => $resp, ":score_mobile" => $pagespeed['ruleGroups']["SPEED"]["score"]] );
                }
            } else {
                return false;
            }
        }

        curl_multi_close($multi_curl);

        return true;

    }
    public function getTypeName()
    {
        return rex_i18n::msg('hello_cronjob_pagespeed_name');
    }

    public function getParamFields()
    {
        return [];
    }
}
?>