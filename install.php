<?php

if (!$this->hasConfig()) {
    $this->setConfig('hello_api_key', md5(time()));
}    