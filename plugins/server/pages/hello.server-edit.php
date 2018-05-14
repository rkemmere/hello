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
    $query = 'SELECT id, domain, api_key FROM `rex_hello_domain` ORDER BY domain';
    $list = rex_list::factory($query);
    $list->addTableAttribute('class', 'table-striped');
    $list->setNoRowsMessage($this->i18n('hello_domain_norows_message'));
    
    // icon column (Domain hinzufügen bzw. bearbeiten)
    $thIcon = '<a href="'.$list->getUrl(['func' => 'domain_add','start' => $start]).'"><i class="rex-icon rex-icon-add-action"></i></a>';
    $tdIcon = '<i class="rex-icon fa-file-text-o"></i>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'domain_edit', 'id' => '###id###','start' => $start]);
    
    $list->setColumnLabel('domain', $this->i18n('hello_domain_column_domain'));
    $list->setColumnParams('domain', ['id' => '###id###', 'func' => 'domain_edit']);
        
    $list->setColumnLabel('api_key', $this->i18n('hello_domain_column_api_key'));

    $list->setColumnLabel('logdate', $this->i18n('hello_domain_column_last_call'));
    $list->setColumnFormat('logdate', 'custom', function ($params) {
        if ($params['list']->getValue('logdate') != "") {
            return $params['list']->getValue('logdate');
        } else { 
            return rex_i18n::msg("hello_domain_column_last_call_none");
        }
    });
    
    $list->addColumn('domain_delete', '<i class="rex-icon rex-icon-delete"></i> ' . $this->i18n('hello_domain_column_delete'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('domain_delete', ['func' => 'domain_delete', 'oid' => '###id###', 'domain' => '###domain###','start' => $start]);
    $list->addLinkAttribute('domain_delete', 'data-confirm', $this->i18n('hello_domain_delete_confirm'));

    $list->removeColumn('id');
    $list->removeColumn('updatedate');
    
    $content1 = $list->get();
    
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
    //End - add domain-field

    //Start - add domain-field
    $field = $form->addTextField('api_key');
    $field->setLabel($this->i18n('hello_domain_column_api_key'));
    $field->setNotice($this->i18n('hello_domain_column_api_key_note', md5(time())));
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