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
$query = 'SELECT * FROM `rex_hello_domain_log` WHERE domain = ? ORDER BY id DESC LIMIT 1';
$item = array_shift(rex_sql::factory()->setDebug(0)->getArray($query, [$domain]));

$raw = json_decode($item['raw'], true);


$output = '<table class="table table-striped"><thead><tr><th>Version</th><th>Details</th></tr></thead><tbody>';
    $output .= '<tr><td>Hello-Version</td><td>'.$raw['hello_version'].'</td></tr>';
    $output .= '<tr><td>REDAXO-Version</td><td>'.$raw['rex_version'].'</td></tr>';
    $output .= '<tr><td>PHP-Version</td><td>'.$raw['php_version'].'</td></tr>';
    $output .= '</tbody></table>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'danger', false);
$fragment->setVar('title', "Hello-Version", false);
$fragment->setVar('body', $output, false);
$content3 .= '<div class="col-md-6">'.$fragment->parse('core/page/section.php').'</div>';


$domains = $raw['domains'];

$output = '<table class="table table-striped"><thead><tr><th>Domain</th><th>URL</th><th>404</th></tr></thead><tbody>';
foreach($domains as $key => $value) {
    $output .= '<tr>';
    $output .= '<td>'.$value['name'].'</td>';
    $output .= '<td>'.$value['url'].'</td>';
    $output .= '<td>'.$value['url_404'].'</td>';
    $output .= '</tr>';
} 
$output .= '</tbody></table>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', "Details zu ".$domain, false);
$fragment->setVar('body', $output, false);
$content3 .= '<div class="col-md-6">'.$fragment->parse('core/page/section.php').'</div>';

$addons = $raw['rex_addons'];

$output = '<table class="table table-striped"><thead><tr><th>Name</th><th>installiert?</th><th>aktiv?</th><th>Version</th><th>Installer</th></tr></thead><tbody>';
foreach($addons as $key => $value) {
    $output .= '<tr>';
    $output .= '<td>'.$value['name'].'</td>';
    $output .= '<td>'.$value['install'].'</td>';
    $output .= '<td>'.$value['status'].'</td>';
    if($value['version_current'] != $value['version_latest']) {
        $output .= '<td><i title="" class="rex-icon fa-exclamation-triangle"></i> '.$value['version_current'].'</td>';
    } else {
        $output .= '<td>'.$value['version_current'].'</td>';
    }
    $output .= '<td>'.$value['version_latest'].'</td>';
    $output .= '</tr>';
} 
$output .= '</tbody></table>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', "Details zu ".$domain, false);
$fragment->setVar('body', $output, false);
$content3 .= '<div class="col-md-12">'.$fragment->parse('core/page/section.php').'</div>';

$syslog = $raw['syslog'];


$output = '<table class="table table-striped"><thead><tr><th>Zeitstempel</th><th>Typ</th><th>Nachricht</th><th>Datei</th><th>Zeile</th></tr></thead><tbody>';
for ($i = 0; $i < count($syslog)-5; $i++) {
    $output .= '<tr>';
    $output .= '<td>'.array_shift($syslog[$i++]).'</td>';
    $output .= '<td>'.array_shift($syslog[$i++]).'</td>';
    $output .= '<td>'.array_shift($syslog[$i++]).'</td>';
    $output .= '<td>'.array_shift($syslog[$i++]).'</td>';
    $output .= '<td>'.array_shift($syslog[$i]).'</td>';
    $output .= '</tr>';
    
} 
$output .= '</tbody></table>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', "Syslog", false);
$fragment->setVar('body', $output, false);
$content3 .= '<div class="col-md-12">'.$fragment->parse('core/page/section.php').'</div>';


echo '<div class="row">'.$content3."</div>";
// Domain bearbeiten ENDE //