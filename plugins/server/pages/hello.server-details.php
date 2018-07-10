<?php
echo rex_view::title($this->i18n('hello'));


$domain = rex_request('domain', 'string', "boehringer.net");
$csrfToken = rex_csrf_token::factory('hello_server_details');

$sel_editor = new rex_select();
$sel_editor->setName('domain');
$sel_editor->setId('rex-hello-domain');
$sel_editor->setAttribute('class', 'form-control selectpicker');
$sel_editor->setSize(1);
$sel_editor->setSelected($domain);
$sel_editor->addDBSqlOptions("select domain as name, domain as id FROM rex_hello_domain ORDER BY domain");

$formElements = [];
$n = [];
$n['label'] = '<label for="rex-id-editor">' . rex_i18n::msg('system_editor') . '</label>';
$n['field'] = $sel_editor->get();
$n['note'] = rex_i18n::msg('system_editor_note');
$formElements[] = $n;
    

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content = $fragment->parse('core/form/form.php');


$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="sendit">' . 'Anzeigen' . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('system_settings'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

    
$content = '
<form id="rex-form-system-setup" action="' . rex_url::currentBackendPage() . '" method="post">
    <input type="hidden" name="func" value="updateinfos" />
    ' . $csrfToken->getHiddenField() . '
    ' . $content . '
</form>';

    echo $content;

// Domain-Übersicht ANFANG //
$query = 'SELECT * FROM `rex_hello_domain_log` WHERE domain = ?';
$item = array_shift(rex_sql::factory()->setDebug(0)->getArray($query, [$domain]));

$raw = json_decode($item['raw'], true);


$output = '<table>';
    $output .= '<tr><td>Hello-Version</td><td>'.$raw['hello_version'].'</td></tr>';
    $output .= '<tr><td>REDAXO-Version</td><td>'.$raw['rex_version'].'</td></tr>';
    $output .= '<tr><td>PHP-Version</td><td>'.$raw['php_version'].'</td></tr>';
$output .= '</table>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'danger', false);
$fragment->setVar('title', "Hello-Version", false);
$fragment->setVar('body', $output, false);
$content3 .= '<div class="col-md-6">'.$fragment->parse('core/page/section.php').'</div>';


$domains = $raw['domains'];

$output = '<table>';
foreach($domains as $key => $value) {
    $output .= '<tr>';
    $output .= '<td>'.$value['name'].'</td>';
    $output .= '<td>'.$value['url'].'</td>';
    $output .= '<td>'.$value['url_404'].'</td>';
    $output .= '</tr>';
} 
$output .= '</table>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', "Details zu ".$domain, false);
$fragment->setVar('body', $output, false);
$content3 .= '<div class="col-md-6">'.$fragment->parse('core/page/section.php').'</div>';

$addons = $raw['rex_addons'];

$output = '<table>';
foreach($addons as $key => $value) {
    $output .= '<tr>';
    $output .= '<td>'.$value['name'].'</td>';
    $output .= '<td>'.$value['install'].'</td>';
    $output .= '<td>'.$value['status'].'</td>';
    $output .= '<td>'.$value['version_current'].'</td>';
    $output .= '<td>'.$value['version_latest'].'</td>';
    $output .= '</tr>';
} 
$output .= '</table>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', "Details zu ".$domain, false);
$fragment->setVar('body', $output, false);
$content3 .= '<div class="col-md-6">'.$fragment->parse('core/page/section.php').'</div>';

$syslog = $raw['syslog'];


$output = '<table>';
foreach($syslog as $key => $value) {
    $output .= '<tr>';
    $output .= '<td>'.key($value).'</td>';
    $output .= '<td>'.array_shift($value).'</td>';
    $output .= '</tr>';
} 
$output .= '</table>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', "Syslog", false);
$fragment->setVar('body', $output, false);
$content3 .= '<div class="col-md-6">'.$fragment->parse('core/page/section.php').'</div>';


echo '<div class="row">'.$content3."</div>";
// Domain bearbeiten ENDE //