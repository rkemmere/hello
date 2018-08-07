<?php

/**
 * @var rex_addon $this
 */

 if (rex_addon::get('cronjob')->isAvailable()) {
    rex_cronjob_manager::registerType('rex_cronjob_hello');
}

if (rex_addon::get('cronjob')->isAvailable()) {
    rex_cronjob_manager::registerType('rex_cronjob_hello_favicon');
 }

 if (rex_addon::get('cronjob')->isAvailable()) {
    rex_cronjob_manager::registerType('rex_cronjob_hello_pagespeed');
 }

rex_view::addCssFile( $this->getAssetsUrl('css/theme.default.min.css') );
rex_view::addJsFile( $this->getAssetsUrl('js/jquery.tablesorter.combined.min.js') );
rex_view::addJsFile($this->getAssetsUrl('js/tablesorter-custom.js'));