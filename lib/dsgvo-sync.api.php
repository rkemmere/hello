<?php

// Aufruf: 
// /?rex-api-call=store_status&param=close

class rex_api_hello extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        ob_end_clean();
        $api_key = rex_request('api_key','string');
        

        if($api_key == $this->getConfig('hello_api_key')) {

            
            $params['hello_version'] = rex_addon::get('hello')->getProperty('version');
            $params['rex_version']   = rex::getVersion();

            $hello = json_decode($params, true);

        }
    }
}

?>