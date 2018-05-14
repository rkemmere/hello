<?php

rex_sql_table::get(rex::getTable('hello_domain'))
    ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('domain', 'text'))
    ->ensureColumn(new rex_sql_column('url', 'text'))
    ->ensureColumn(new rex_sql_column('api_key', 'text'))
    ->ensureColumn(new rex_sql_column('createdate', 'timestamp', false, '0000-00-00 00:00:00', 'on update CURRENT_TIMESTAMP'))
    ->setPrimaryKey('id')
    ->ensure();

    rex_sql_table::get(rex::getTable('hello_domain_log'))
    ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('domain', 'text'))
    ->ensureColumn(new rex_sql_column('status', 'text'))
    ->ensureColumn(new rex_sql_column('raw', 'text'))
    ->ensureColumn(new rex_sql_column('createdate', 'timestamp', false, '0000-00-00 00:00:00', 'on update CURRENT_TIMESTAMP'))
    ->setPrimaryKey('id')
    ->ensure();
