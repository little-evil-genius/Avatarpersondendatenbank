<?php
// Direktzugriff auf die Datei aus Sicherheitsgründen sperren
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// HOOKS
// Teambenachrichtigung auf dem Index
$plugins->add_hook('global_start', 'faceclaims_database_global');
// Mod-CP
$plugins->add_hook('modcp_nav', 'faceclaims_database_modcp_nav');
$plugins->add_hook("modcp_start", "faceclaims_database_modcp");
// MyAlerts
if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
	$plugins->add_hook("global_start", "faceclaims_database_myalert_alerts");
}

// Die Informationen, die im Pluginmanager angezeigt werden
function faceclaims_database_info(){
	return array(
		"name"		=> "Avatarpersonendatenbank",
		"description"	=> "Dieses Plugin erweitert das Board, um eine eigne Avatarpersonendatenbank. Ausgewählte Gruppen können Avatarpersonen hinzufügen, welche vom Team freigegeben werden müssen. Beim hinzufügen der Avatarperson werden verschiedene Informationen abgefragt. Man hat die Möglichkeit die Avatarpersonen nach verschiedenen Optionen zu filtern. Auch kann eingestellt werden, ob vergebene und reservierte Avatarpersonen besonders dargestellt werden.",
		"website"	=> "https://github.com/little-evil-genius/Avatarpersondendatenbank",
		"author"	=> "little.evil.genius",
		"authorsite"	=> "https://storming-gates.de/member.php?action=profile&uid=1712",
		"version"	=> "1.0.1",
		"compatibility" => "18*"
	);
}
 
// Diese Funktion wird aufgerufen, wenn das Plugin installiert wird (optional).
function faceclaims_database_install(){

	global $db, $cache, $mybb;

    // DATENBANK HINZUFÜGEN
	$db->query("CREATE TABLE ".TABLE_PREFIX."faceclaims_database(
        `fdid` int(10) NOT NULL AUTO_INCREMENT,
		`faceclaim` VARCHAR(500) NOT NULL,
		`image` VARCHAR(500) NOT NULL,
		`birthday` VARCHAR(10) NOT NULL,
		`origin` VARCHAR(500) NOT NULL,
		`haircolor` VARCHAR(500) NOT NULL,
        `gender` VARCHAR(500) NOT NULL,
		`special` VARCHAR(500) NOT NULL,
		`mediabase` VARCHAR(1000) NOT NULL,
		`gallery` VARCHAR(500) NOT NULL,
		`accepted` int(1) NOT NULL,
		`sendedby` int(10) NOT NULL,
        PRIMARY KEY(`fdid`),
        KEY `fdid` (`fdid`)
        )
        ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1
        ");

	// EINSTELLUNGEN HINZUFÜGEN
	$setting_group = array(
		'name'          => 'faceclaims_database',
		'title'         => 'Avatarpersonendatenbank',
		'description'   => 'Einstellungen für die Avatarpersonendatenbank',
		'disporder'     => 1,
		'isdefault'     => 0
	);
			
	$gid = $db->insert_query("settinggroups", $setting_group); 
			
	$setting_array = array(
		// Erlaubte Gruppen
		'faceclaims_database_allow_groups' => array(
			'title' => 'Erlaubte Gruppen',
			'description' => 'Welche Gruppen dürfen neue Avatarpersonen Einträge hinzufügen?',
			'optionscode' => 'groupselect',
			'value' => '4', // Default
			'disporder' => 1
		),
	    // Avatarprofilfeld
		'faceclaims_database_faceclaim' => array(
			'title' => 'Avatarperson-Profilfeld',
			'description' => 'Wie lautet die FID von dem Profilfeld, wo die Avatarperson hinterlegt wird?',
			'optionscode' => 'text',
			'value' => '15', // Default
			'disporder' => 2
		),
        // Schreibweise
        'faceclaims_database_faceclaimtype' => array(
            'title' => 'Schreibweise der Avatarperson',
            'description' => 'Werden die Avatarpersonen innerhalb des Profilfeldes per Vorname Nachname oder per Nachname, Vorname angegeben?',
            'optionscode' => "select\n0=Vorname Nachname\n1=Nachname, Vorname",
            'value' => 2,
            'disporder' => 3
        ),
	    // Geschlechtsmöglichkeiten
		'faceclaims_database_gender' => array(
			'title' => 'Geschlechtsmöglichkeiten',
			'description' => 'In welche Geschlechter können die Avatarpersonen eingeteilt werden?',
			'optionscode' => 'text',
			'value' => 'Weiblich, Männlich, Divers', // Default
			'disporder' => 4
		),
	    // Herkunftsmöglichkeiten
		'faceclaims_database_origin' => array(
			'title' => 'Herkunftsmöglichkeiten',
			'description' => 'Welche Herkünfte können die Avatarpersonen besitzen?',
			'optionscode' => 'text',
			'value' => 'Nordamerika, Südamerika, Europa, Afrika, Asien, Australien', // Default
			'disporder' => 5
		),
	    // Haarfarbenmöglichkeiten
		'faceclaims_database_haircolor' => array(
			'title' => 'Haarfarbenmöglichkeiten',
			'description' => 'Welche Haarfarben können die Avatarpersonen haben?',
			'optionscode' => 'text',
			'value' => 'Schwarzhaarig, Blond, Brünett, Rothaarig, Weiß-/Grauhaarig, Glatzköpfig, andere Haarfarbe, verschiedene Haarfarben', // Default
			'disporder' => 6
		),
		// Altersbegrenzung
		'faceclaims_database_age_limit' => array(
			'title' => 'Altersbegrenzung',
			'description' => 'Gibt es Einschränkungen beim Alter der Avatarperson im Board, das sich das Alter der Avatarperson nur X Jahre vom Charakteralter unterscheiden darf?',
			'optionscode' => 'yesno',
			'value' => '1', // Default
			'disporder' => 7
		),
		// Altersbegrenzung - Zahl
		'faceclaims_database_age_limit_number' => array(
			'title' => 'Altersbegrenzung - Jahre',
			'description' => 'Wie viele Jahre dürfen zwischen dem Alter liegen?',
			'optionscode' => 'text',
			'value' => '7', // Default
			'disporder' => 8
		),
		// Movie Base
		'faceclaims_database_mediabase' => array(
			'title' => 'Filme und Serien',
			'description' => 'Sollen beim erstellen einer neuen Avatarperson die Möglichkeit bestehen, dass Serien und Filme von dieser angegeben werden kann?',
			'optionscode' => 'yesno',
			'value' => '0', // Default
			'disporder' => 9
		),
        // Galerie-Link
        'faceclaims_database_gallery' => array(
            'title' => 'Galerie Link',
            'description' => 'Sollen beim hinzufügen einer Avatarperson die Möglichkeit bestehen, dass ein Galerie-Link hinterlegt werden kann?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 10
        ),
		// Vergebene Avatarpersonen
		'faceclaims_database_awarded_faceclaims' => array(
			'title' => 'Vergebene Avatarpersonen',
			'description' => 'Sollen vergebene Avatarpersonen gesondert markiert werden? Sie werden verblasst als normale Einträge angezeigt.',
			'optionscode' => 'yesno',
			'value' => '1', // Default
			'disporder' => 11
		),
		// Reservierungsplugin
		'faceclaims_database_reserved_faceclaims' => array(
			'title' => 'Reservierte Avatarpersonen',
			'description' => 'Sollen reservierte Avatarpersonen gesondert markiert werden? Sie werden verblasst als normale Einträge angezeigt. Dafür muss das Reservierungsplugin von Risuena installiert sein!',
			'optionscode' => 'yesno',
			'value' => '0', // Default
			'disporder' => 12
		),
		// Multipage 
        'faceclaims_database_multipage' => array(
            'title' => 'Multipage-Navigation',
            'description' => 'Sollen die Avatarpersonen ab einer bestimmten Anzahl auf mehrere Seiten aufgeteilt werden?',
            'optionscode' => 'yesno',
            'value' => '0', // Default
            'disporder' => 13
        ),
		// Anzahl der Multipage
        'faceclaims_database_multipage_show' => array(
            'title' => 'Anzahl der Avatarpersonen (Multipage-Navigation)',
            'description' => 'Wie viele Avatarpersonen sollen auf einer Seite angezeigt werden?',
            'optionscode' => 'text',
            'value' => '6', // Default
            'disporder' => 14
        ),
		// Random Seite
        'faceclaims_database_randompage' => array(
            'title' => 'Zufällige Seite',
            'description' => 'Soll es eine Seite geben, wo einem eine bestimmte Anzahl von zufälligen Avatarpersonen angezeigt wird?',
            'optionscode' => 'yesno',
            'value' => '0', // Default
            'disporder' => 15
        ),
		// Anzahl der Random Avatare
        'faceclaims_database_randompage_show' => array(
            'title' => 'Anzahl der Random Avatarpersonen',
            'description' => 'Wie viele Avatarpersonen sollen zufällig angezeigt werden?',
            'optionscode' => 'text',
            'value' => '10', // Default
            'disporder' => 16
        ),
        // Listen-Navigation
        'faceclaims_database_lists' => array(
            'title' => 'Listen PHP (Navigation Ergänzung)',
            'description' => 'Wie heißt die Hauptseite der Listen-Seite? Dies dient zur Ergänzung der Navigation. Falls nicht gewünscht einfach leer lassen.',
            'optionscode' => 'text',
            'value' => 'listen.php', // Default
            'disporder' => 17
        ),
	);
			
	foreach($setting_array as $name => $setting)
	{
		$setting['name'] = $name;
		$setting['gid']  = $gid;
		$db->insert_query('settings', $setting);
	}
	rebuild_settings();

    // TEMPLATES ERSTELLEN
    $insert_array = array(
        'title'		=> 'faceclaims_database_faceclaim_filters',
        'template'	=> $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$filter_title} {$lang->faceclaims_database_faceclaim_title}</title>
		{$headerinclude}
		</head>
		<body>
		{$header}
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
		<td class="thead" colspan="2"><strong>{$filter_title} {$lang->faceclaims_database_faceclaim_title}</strong></td>
		</tr>
		<tr>
		<td class="trow1" align="center" valign="top" width="10%">
		{$fd_menu}
				</td>
				<td class="trow1" align="center" valign="top" width="40%">
					<h1 style="margin: 2px 0px;">{$lang->faceclaims_database_faceclaim_count}</h1>
					{$multipage}
					{$fd_faceclaim_bit}
				   {$fd_faceclaim_none}
				   <div style="clear:both;"></div>
				   {$multipage}
				</td>
				</tr>
		</table>
		</td>
		</tr>
		</table>
		{$footer}
		</body>
		</html>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_menu_cat',
        'template'	=> $db->escape_string('<table width="100%">
	  {$entry}
	  </table>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_menu',
        'template'	=> $db->escape_string('<table width="100%">
		<tbody>
			<tr><td class="thead">{$lang->faceclaims_database_menu}</td></tr>
			<tr><td class="trow1">{$lang->faceclaims_database_menu_main}</td></tr>
			{$fd_menu_add}
			<tr><td class="trow1">{$lang->faceclaims_database_menu_all}</td></tr>
			<tr><td class="trow1">{$lang->faceclaims_database_menu_filterpage}</td></tr>
			{$fd_menu_random}
			<tr><td class="thead">{$lang->faceclaims_database_menu_filters}</td></tr>
			<tr><td class="tcat">{$lang->faceclaims_database_menu_filters_gender}</td></tr>
			<tr><td>{$fd_menu_gender}</td></tr>
			<tr><td class="tcat">{$lang->faceclaims_database_menu_filters_origin}</td></tr>
			<tr><td>{$fd_menu_origin}</td></tr>
			<tr><td class="tcat">{$lang->faceclaims_database_menu_filters_haircolor}</td></tr>
			<tr><td>{$fd_menu_haircolor}</td></tr>
			<tr><td class="tcat">{$lang->faceclaims_database_menu_filters_age}</td></tr>
			<tr><td>{$fd_menu_age}</td></tr>
		</tbody>
	 </table>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_mainpage',
        'template'	=> $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->faceclaims_database_mainpage_nav}</title>
		{$headerinclude}
		</head>
		<body>
		{$header}
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
		<td class="thead" colspan="2"><strong>{$lang->faceclaims_database_mainpage_nav}</strong></td>
		</tr>
		<tr>
		<td class="trow1" align="center" valign="top" width="10%">
		{$fd_menu}
				</td>
				<td class="trow1" align="center" valign="top" width="40%">
					<h1 style="margin: 2px 0px;">{$lang->faceclaims_database_mainpage_title}</h1>
					<div style="padding: 20px;" align="justify" >{$lang->faceclaims_database_mainpage_desc}</div>
				</td>
				</tr>
		</table>
		</td>
		</tr>
		</table>
		{$footer}
		</body>
		</html>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_faceclaim_bit',
        'template'	=> $db->escape_string('<div class="fd_box" {$awarded}>
		<div class="fd_pic">{$image}</div>
		<div class="fd_ue">{$name}</div>
		<div class="fd_fact"><i class="fas fa-transgender-alt"></i> {$gender} <br>
			<i class="fas fa-birthday-cake"></i> {$birthday} ({$age} Jahre)<br>
			<i class="fas fa-map-marked-alt"></i> {$origin} <br>
			<i class="fas fa-palette"></i> {$haircolor}
			{$fd_gallery}
			{$fd_teamoption}
		</div>
		<div style="clear:both;"></div>
		<div class="fd_descr">
			<div style="text-align:center;">{$special}</div>
		</div>
		{$fd_mediabase}
		<div class="fd_fact1">{$age_limit}</div>
	 </div>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_faceclaim_bit_mediabase',
        'template'	=> $db->escape_string('<div class="fd_descr">
		<div style="text-align:center;margin:10px auto;">{$mediabase}</div>
	 </div>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_faceclaim_bit_gallery',
        'template'	=> $db->escape_string('<br><i class="fas fa-images"></i> {$gallery}'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_faceclaim_bit_teamoption',
        'template'	=> $db->escape_string('<br><i class="fas fa-tools"></i> {$edit} & {$delete}'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_add',
        'template'	=> $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->faceclaims_database_add_nav}</title>
		{$headerinclude}
		</head>
		<body>
		{$header}
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
		<td class="thead" colspan="2"><strong>{$lang->faceclaims_database_add_nav}</strong></td>
		</tr>
		<tr>
		<td class="trow1" align="center" valign="top" width="10%">
		{$fd_menu}
				</td>
				<td class="trow1" align="center" valign="top" width="40%">
				<form action="faceclaims_database.php?action=do_add" method="post">
				<table width="100%">
					<tr>
						<td class="tcat" colspan="2"><strong>{$lang->faceclaims_database_add_nav}</strong></td>
					</tr>
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_faceclaim}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_faceclaim_desc}</div>
						</td>
						<td class="trow2"><input type="text" name="faceclaim" id="faceclaim" placeholder="Vorname Nachname" class="textbox" required /></td>
					</tr>
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_gender}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_gender_desc}</div>
						</td>
						<td class="trow2">
							<select name="gender" required>
							<option value="%">Geschlecht wählen</option>
							{$gender_select_add}
							</select> 
						</td>
					</tr>	
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_birthday}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_birthday_desc}</div>
						</td>
						<td class="trow2">
							<input type="nummeric" name="birthday" id="birthday" placeholder="1991" class="textbox" required />
						</td>
					</tr>
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_origin}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_origin_desc}</div>
						</td>
						<td class="trow2">
							<select name="origin[]" size="3" multiple="multiple" required>
								{$origin_select_add}
							</select>
						</td>
					</tr>
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_haircolor}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_haircolor_desc}</div>
						</td>
						<td class="trow2">
							<select name="haircolor[]" size="3" multiple="multiple" required>
								{$haircolor_select_add}
							</select>
						</td>
					</tr>	
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_image}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_image_desc}</div>
						</td>
						<td class="trow2">
							<input type="text" name="image" id="image" placeholder="https://" class="textbox" required />
						</td>
					</tr>
					{$fd_add_gallery}
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_special}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_special_desc}</div>
						</td>
						<td class="trow2">
							<textarea class="textarea" name="special" id="special" rows="6" cols="30" style="width: 95%"></textarea>
						</td>
					</tr>
					{$fd_add_mediabase}
					<tr>
						<td colspan="2" align="center">
							<input type="submit" name="faceclaim_add" value="{$lang->faceclaims_database_add_button}" class="button">
						</td>
					</tr>
				</table>
		</form>
				</td>
				</tr>
		</table>
		</td>
		</tr>
		</table>
		{$footer}
		</body>
		</html>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_add_mediabase',
        'template'	=> $db->escape_string('<tr>
		<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_mediabase}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_mediabase_desc}</div>
		</td>
		<td class="trow2">
			<textarea class="textarea" name="mediabase" id="mediabase" rows="6" cols="30" style="width: 95%">{$mediabase}</textarea>
		</td>
	 </tr>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_add_gallery',
        'template'	=> $db->escape_string('<tr>
		<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_gallery}</strong>
					<div class="smalltext">{$lang->faceclaims_database_add_gallery_desc}</div>
		</td>
		<td class="trow2">
			<input type="text" name="gallery" id="gallery" {$value} class="textbox" />
		</td></tr>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_faceclaim_all',
        'template'	=> $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->faceclaims_database_faceclaim_all}</title>
		{$headerinclude}
		</head>
		<body>
		{$header}
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
		<td class="thead" colspan="2"><strong>{$lang->faceclaims_database_faceclaim_all}</strong></td>
		</tr>
		<tr>
		<td class="trow1" align="center" valign="top" width="10%">
		{$fd_menu}
				</td>
				<td class="trow1" align="center" valign="top" width="40%">
					<h1 style="margin: 2px 0px;">{$lang->faceclaims_database_faceclaim_count}</h1>
					{$multipage}
					{$fd_faceclaim_bit}
				   {$fd_faceclaim_none}
				   <div style="clear:both;"></div>
				   {$multipage}
				</td>
				</tr>
		</table>
		</td>
		</tr>
		</table>
		{$footer}
		</body>
		</html>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_faceclaim_none',
        'template'	=> $db->escape_string('<div style="text-align:center;margin:10px auto;">{$lang->faceclaims_database_faceclaim_none}</div>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_edit',
        'template'	=> $db->escape_string('	<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->faceclaims_database_edit_nav}</title>
		{$headerinclude}
		</head>
		<body>
		{$header}
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
		<td class="thead" colspan="2"><strong>{$lang->faceclaims_database_edit_nav}</strong></td>
		</tr>
		<tr>
		<td class="trow1" align="center" valign="top" width="10%">
		{$fd_menu}
				</td>
				<td class="trow1" align="center" valign="top" width="40%">
				<form action="faceclaims_database.php?action=faceclaim_edit&fdid={$fdid}" method="post">
					<input type="hidden" name="fdid" id="fdid" value="{$fdid}" />
				<table width="100%">
					<tr>
						<td class="tcat" colspan="2"><strong>{$lang->faceclaims_database_edit_nav}</strong></td>
					</tr>
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_faceclaim}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_faceclaim_desc}</div>
						</td>
						<td class="trow2"><input type="text" name="faceclaim" id="faceclaim" value="{$name}" class="textbox" required /></td>
					</tr>
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_gender}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_gender_desc}</div>
						</td>
						<td class="trow2">
							<select name="gender" required>
							<option value="{$gender}">{$gender}</option>
							{$gender_select_edit}
							</select> 
						</td>
					</tr>	
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_birthday}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_birthday_desc}</div>
						</td>
						<td class="trow2">
							<input type="nummeric" name="birthday" id="birthday" value="{$birthday}" class="textbox" required />
						</td>
					</tr>
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_origin}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_origin_desc}</div>
						</td>
						<td class="trow2">
							<select name="origin[]" size="3" multiple="multiple" required>
								{$origin_select_edit}
							</select>
						</td>
					</tr>
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_haircolor}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_haircolor_desc}</div>
						</td>
						<td class="trow2">
							<select name="haircolor[]" size="3" multiple="multiple" required>
								{$haircolor_select_edit}
							</select>
						</td>
					</tr>	
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_image}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_image_desc}</div>
						</td>
						<td class="trow2">
							<input type="text" name="image" id="image" value="{$image}" class="textbox" required />
						</td>
					</tr>
					{$fd_add_gallery}
					<tr>
						<td class="trow1" width="40%"><strong>{$lang->faceclaims_database_add_special}</strong>
						<div class="smalltext">{$lang->faceclaims_database_add_special_desc}</div>
						</td>
						<td class="trow2">
							<textarea class="textarea" name="special" id="special" rows="6" cols="30" style="width: 95%">{$special}</textarea>
						</td>
					</tr>
					{$fd_add_mediabase}
					<tr>
						<td colspan="2" align="center">
							<input type="submit" name="edit_faceclaim" value="{$lang->faceclaims_database_edit_button}" class="button">
						</td>
					</tr>
				</table>
		</form>
				</td>
				</tr>
		</table>
		</td>
		</tr>
		</table>
		{$footer}
		</body>
		</html>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_filterpage',
        'template'	=> $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->faceclaims_database_filterpage_nav}</title>
		{$headerinclude}
		</head>
		<body>
		{$header}
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
		<td class="thead" colspan="2"><strong>{$lang->faceclaims_database_filterpage_nav}</strong></td>
		</tr>
		<tr>
		<td class="trow1" align="center" valign="top" width="10%">
		{$fd_menu}
				</td>
				<td class="trow1" align="center" valign="top" width="40%">
					{$fd_filters}
					<div class="thead">{$lang->faceclaims_database_faceclaim_count}</div>
					{$multipage}
					{$filters_faceclaims}
					{$filters_faceclaims_none}
				   <div style="clear:both;"></div>
				   {$multipage}
				</td>
				</tr>
		</table>
		</td>
		</tr>
		</table>
		{$footer}
		</body>
		</html>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_filterpage_filters',
        'template'	=> $db->escape_string('<table cellspacing="3" cellpadding="3" width="100%">
		<form id="faceclaim_filter" method="get" action="faceclaims_database.php">
				<input type="hidden" name="action" value="filter" /> 
			<tr>
				<td class="thead" colspan="2">
					{$lang->faceclaims_database_filterpage_nav}
				</td>
			</tr>
			<tr>
				<td class="trow2">
					<strong>{$lang->faceclaims_database_filterpage_gender}</strong>
				</td>
				<td class="trow2">
						<select name="filter_gender">
							<option value="%">Geschlecht wählen</option>
							{$gender_select_filter}
						</select> 
				</td>
			</tr>
			<tr>
				<td class="trow2">
					<strong>{$lang->faceclaims_database_filterpage_origin}</strong>
				</td>
				<td class="trow2">
						<select name="filter_origin">
							<option value="%">Herkunft wählen</option>
							{$origin_select_filter}
						</select> 
				</td>
			</tr>
			<tr>
				<td class="trow2">
					<strong>{$lang->faceclaims_database_filterpage_haircolor}</strong>
				</td>
				<td class="trow2">
						<select name="filter_haircolor">
							<option value="%">Haarfarbe wählen</option>
							{$haircolor_select_filter}
						</select> 
				</td>
			</tr>
			<tr>
				<td class="trow2">
					<strong>{$lang->faceclaims_database_filterpage_age}</strong>
				</td>
				<td class="trow2">
					{$lang->faceclaims_database_filterpage_age_start}
					<input type="text" class="textbox" name="age_start" id="age_start" size="40" maxlength="1155" style="width: 20px;" /> 
					{$lang->faceclaims_database_filterpage_age_and}
					<input type="text" class="textbox" name="age_end" id="age_end" size="40" maxlength="1155" style="width: 20px;" /> 
					{$lang->faceclaims_database_filterpage_age_end}
				</td>
			</tr>
			<tr>
				<td class="trow2">
					<strong>{$lang->faceclaims_database_filterpage_name}</strong>
				</td>
				<td class="trow2">
					{$lang->faceclaims_database_filterpage_firstletter}
					<input type="text" class="textbox" name="firstletter" id="firstletter" size="1" maxlength="1" style="width: 87px;" /> 
					<br>
					{$lang->faceclaims_database_filterpage_fullname}
					<input type="text" class="textbox" name="fullname" id="fullname" size="40" maxlength="1155" style="width: 115px;" /> 
				</td>
			</tr>
			<tr>
				<td class="trow1" colspan="2" align="center">
					<input type="submit"  class="button" value="{$lang->faceclaims_database_filterpage_button}" />
				</td>
			</tr>
		</form>
		</table>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_modcp',
        'template'	=> $db->escape_string('<html>
        <head>
        <title>{$mybb->settings[\'bbname\']} -  {$lang->faceclaims_database_modcp_nav}</title>
        {$headerinclude}
        </head>
        <body>
        {$header}
        <table width="100%" border="0" align="center">
            <tr>
                {$modcp_nav}
                <td valign="top">
                    <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                        <tr>
                            <td class="thead">
                                <strong>{$lang->faceclaims_database_modcp_nav}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="trow2" align="center" valign="top" >{$fd_modcp_bit}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        {$footer}
        </body>
        </html>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_modcp_bit',
        'template'	=> $db->escape_string('<div class="fd_box">
		<div class="fd_pic">{$image}</div>
		<div class="fd_ue">{$name}</div>
		<div class="fd_fact"><i class="fas fa-transgender-alt"></i> {$gender} <br>
			<i class="fas fa-birthday-cake"></i> {$birthday} ({$age} Jahre)<br>
			<i class="fas fa-map-marked-alt"></i> {$origin} <br>
			<i class="fas fa-palette"></i> {$haircolor}
			{$fd_gallery}<br>
			<i class="fas fa-paper-plane"></i> eingesendet von {$createdby}
		</div>
		<div style="clear:both;"></div>
		<div class="fd_descr">
			<div style="text-align:center;">{$special}</div>
		</div>
		{$fd_mediabase}
		<div class="fd_fact1"><a href="modcp.php?action=faceclaims_database&delete={$fdid}" class="button">{$lang->faceclaims_database_modcp_delete}</a> <a href="modcp.php?action=faceclaims_database&accept={$fdid}" class="button">{$lang->faceclaims_database_modcp_accept}</a></div>
	 </div>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_modcp_nav',
        'template'	=> $db->escape_string('<tr>
		<td class="trow1 smalltext"><a href="modcp.php?action=faceclaims_database" class="modcp_nav_item modcp_nav_reports">{$lang->faceclaims_database_modcp_nav}</td>
	 </tr>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'		=> 'faceclaims_database_randompage',
        'template'	=> $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->faceclaims_database_randompage_nav}</title>
		{$headerinclude}
		</head>
		<body>
		{$header}
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
		<td class="thead" colspan="2"><strong>{$lang->faceclaims_database_randompage_nav}</strong></td>
		</tr>
		<tr>
		<td class="trow1" align="center" valign="top" width="10%">
		{$fd_menu}
				</td>
				<td class="trow1" align="center" valign="top" width="40%">
					<h1 style="margin: 2px 0px;">{$lang->faceclaims_database_randompage_count}</h1>
					{$fd_random_bit}
				</td>
				</tr>
		</table>
		</td>
		</tr>
		</table>
		{$footer}
		</body>
		</html>'),
        'sid'		=> '-1',
        'version'	=> '',
        'dateline'	=> TIME_NOW
    );
    $db->insert_query("templates", $insert_array);
}
 
// Funktion zur Überprüfung des Installationsstatus; liefert true zurürck, wenn Plugin installiert, sonst false (optional).
function faceclaims_database_is_installed(){

	global $db, $mybb;

    if ($db->table_exists("faceclaims_database")) {
        return true;
    }
    return false;
} 
 
// Diese Funktion wird aufgerufen, wenn das Plugin deinstalliert wird (optional).
function faceclaims_database_uninstall(){
	
	global $db;

    //DATENBANK LÖSCHEN
    if($db->table_exists("faceclaims_database"))
    {
        $db->drop_table("faceclaims_database");
    }
    
    // EINSTELLUNGEN LÖSCHEN
    $db->delete_query('settings', "name LIKE 'faceclaims_database%'");
    $db->delete_query('settinggroups', "name = 'faceclaims_database'");

    rebuild_settings();

    // TEMPLATES LÖSCHEN
    $db->delete_query("templates", "title LIKE '%faceclaims_database%'");
}
 
// Diese Funktion wird aufgerufen, wenn das Plugin aktiviert wird.
function faceclaims_database_activate(){

	global $db, $cache;
    
	require_once MYBB_ADMIN_DIR."inc/functions_themes.php";
    require MYBB_ROOT."/inc/adminfunctions_templates.php";

	// VARIABLEN EINFÜGEN
    find_replace_templatesets('header', '#'.preg_quote('{$bannedwarning}').'#', '{$bannedwarning} {$newentry_faceclaims_database}');
	find_replace_templatesets('modcp_nav_users', '#'.preg_quote('{$nav_ipsearch}').'#', '{$nav_ipsearch} {$nav_faceclaims_database}');

    // STYLESHEET HINZUFÜGEN
    $css = array(
		'name' => 'faceclaims_database.css',
		'tid' => 1,
		'attachedto' => '',
		"stylesheet" =>	'.fd_box {
			width: 426px;
			margin: 10px 0px;
			text-transform:uppercase;
			padding: 0px;
			float: left;
			}
		
		.fd_ue {
			float: left;
			font-size: 20px;
			text-transform: uppercase;
			font-family: Playfair Display;
			width: 293px;
		}
		
		.fd_pic {
			float: left;
			margin-right: 7px;
		}
		
		.fd_fact {
			float: left;
			width: 280px;
			font-size:10px;
		}
		
		.fd_fact i {
			float: left;
		}
		
		.fd_fact1 {
			margin-top: 5px;
			text-align: center;
			font-size:10px;
		}
		
		.fd_descr {
			text-transform: none;
			font-size: 12px;
			text-align: justify;
			height: 100px;
			overflow: auto;
		}
		
		.fd_special {
			text-transform: none;
			font-size: 12px;
			height: 55px;
			overflow: auto;
			float: left;
			width: 333px;
			letter-spacing: 1px;
			font-size: 10px;
		}',
		'cachefile' => $db->escape_string(str_replace('/', '', 'faceclaims_database.css')),
		'lastmodified' => time()
	);
    
    $sid = $db->insert_query("themestylesheets", $css);
	$db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=".$sid), "sid = '".$sid."'", 1);

	$tids = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($tids)) {
		update_theme_stylesheet_list($theme['tid']);
	}
	
	// MyALERTS STUFF
   
	if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

		if (!$alertTypeManager) {
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
		}

        // Alert annehmen
		$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
		$alertType->setCode('faceclaims_database_accept'); // The codename for your alert type. Can be any unique string.
		$alertType->setEnabled(true);
		$alertType->setCanBeUserDisabled(true);

		$alertTypeManager->add($alertType);

        // Alert ablehnen
        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
		$alertType->setCode('faceclaims_database_delete'); // The codename for your alert type. Can be any unique string.
		$alertType->setEnabled(true);
		$alertType->setCanBeUserDisabled(true);

		$alertTypeManager->add($alertType);
    }

}
 
// Diese Funktion wird aufgerufen, wenn das Plugin deaktiviert wird.
function faceclaims_database_deactivate(){

	global $db, $cache;

	require_once MYBB_ADMIN_DIR."inc/functions_themes.php";
    require MYBB_ROOT."/inc/adminfunctions_templates.php";

    // VARIABLEN ENTFERNEN
    find_replace_templatesets("header", "#".preg_quote('{$newentry_faceclaims_database}')."#i", '', 0);
	find_replace_templatesets("modcp_nav_users", "#".preg_quote('{$nav_faceclaims_database}')."#i", '', 0);

    // STYLESHEET ENTFERNEN
	$db->delete_query("themestylesheets", "name = 'faceclaims_database.css'");
	$query = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($query)) {
		update_theme_stylesheet_list($theme['tid']);
	}

    // MyALERT STUFF
    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

		if (!$alertTypeManager) {
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
		}

		$alertTypeManager->deleteByCode('faceclaims_database_delete');
        $alertTypeManager->deleteByCode('faceclaims_database_accept');
	}
}

#####################################
### THE BIG MAGIC - THE FUNCTIONS ###
#####################################

// ADMIN-CP PEEKER
$plugins->add_hook('admin_config_settings_change', 'faceclaims_database_settings_change');
$plugins->add_hook('admin_settings_print_peekers', 'faceclaims_database_settings_peek');
function faceclaims_database_settings_change(){
    global $db, $mybb, $faceclaims_database_settings_peeker;

    $result = $db->simple_select('settinggroups', 'gid', "name='faceclaims_database'", array("limit" => 1));
    $group = $db->fetch_array($result);
    $faceclaims_database_settings_peeker = ($mybb->get_input('gid') == $group['gid']) && ($mybb->request_method != 'post');
}
function faceclaims_database_settings_peek(&$peekers){
    global $mybb, $faceclaims_database_settings_peeker;

    if ($faceclaims_database_settings_peeker) {
       $peekers[] = 'new Peeker($(".setting_faceclaims_database_age_limit"), $("#row_setting_faceclaims_database_age_limit_number"),/1/,true)';
    }
	if ($faceclaims_database_settings_peeker) {
       $peekers[] = 'new Peeker($(".setting_faceclaims_database_multipage"), $("#row_setting_faceclaims_database_multipage_show"),/1/,true)';
    }
	if ($faceclaims_database_settings_peeker) {
       $peekers[] = 'new Peeker($(".setting_faceclaims_database_randompage"), $("#row_setting_faceclaims_database_randompage_show"),/1/,true)';
    }
}

// TEAMHINWEIS
function faceclaims_database_global() {

    global $db, $cache, $mybb, $templates, $lang, $newentry_faceclaims_database;
	
	// SPRACHDATEI
	$lang->load('faceclaims_database');

    $countfaceclaims = $db->fetch_field($db->query("SELECT COUNT(fdid) AS faceclaims FROM ".TABLE_PREFIX."faceclaims_database WHERE accepted = '0'"), "faceclaims");
      
    if ($mybb->usergroup['canmodcp'] == "1" && $countfaceclaims == "1") {   
        $newentry_faceclaims_database = $lang->sprintf($lang->newentry_faceclaims_database_headerbanner, '<b>Ein</b>', 'neuer', 'Eintrag', 'muss');
    } elseif ($mybb->usergroup['canmodcp'] == "1" && $countfaceclaims > "1") {
        $newentry_faceclaims_database = $lang->sprintf($lang->newentry_faceclaims_database_headerbanner, $countfaceclaims, 'neue', 'Einträge', 'müssen');
    }
}

// MOD-CP - NAVIGATION
function faceclaims_database_modcp_nav() {

    global $db, $mybb, $templates, $theme, $header, $headerinclude, $footer, $lang, $modcp_nav, $nav_faceclaims_database;

	// SPRACHDATEI
	$lang->load('faceclaims_database');
    
    eval("\$nav_faceclaims_database = \"".$templates->get ("faceclaims_database_modcp_nav")."\";");
}

// MOD-CP - SEITE
function faceclaims_database_modcp() {
   
    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $db, $page, $modcp_nav, $fd_modcp_bit;

	// EINSTELLUNGEN
	$faceclaims_database_age_limit = $mybb->settings['faceclaims_database_age_limit'];
	$faceclaims_database_age_limit_number = $mybb->settings['faceclaims_database_age_limit_number'];
	$faceclaims_database_mediabase = $mybb->settings['faceclaims_database_mediabase'];
	$faceclaims_database_gallery = $mybb->settings['faceclaims_database_gallery'];
	$aktuellesJahr = date("Y");

	// SPRACHDATEI
	$lang->load('faceclaims_database');

    if($mybb->get_input('action') == 'faceclaims_database') {

        // Add a breadcrumb
        add_breadcrumb($lang->nav_modcp, "modcp.php");
        add_breadcrumb("Avatarpersonen freischaltem", "modcp.php?action=faceclaims_database");

        $modcp_query = $db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database 
		WHERE accepted = '0'
        ORDER BY faceclaim ASC
        ");

        while($modcp = $db->fetch_array($modcp_query)) {
   
			// LEER LAUFEN LASSEN
			$fdid = "";
			$image = "";
			$name = "";
			$gender = "";
			$birthday = "";
			$origin = "";
			$haircolor = "";
			$age = "";
			$fd_gallery = "";
			$fd_mediabase = "";
			$fd_teamoption = "";
        
			// MIT INFORMATIONEN FÜLLEN
			$fdid = $modcp['fdid'];
			$image = "<img src=".$modcp['image']." style=\"width: 150px;\" / >";
			$name = $modcp['faceclaim'];
			$gender = $modcp['gender'];
			$birthday = $modcp['birthday'];
			$origin = $modcp['origin'];
			$haircolor = $modcp['haircolor'];
			$age = $aktuellesJahr - $birthday;
			$minage = $age - $faceclaims_database_age_limit_number;        
			$maxage = $age + $faceclaims_database_age_limit_number;

			if ($faceclaims_database_age_limit == 1) {
				$age_limit = "von <b>minimal ". $minage ." Jahre</b> bis <b>maximal ". $maxage ." Jahre</b>";
			} else {
				$age_limit = "";        
			}

			if ($modcp['special'] != '') {
				$special = "Diese Avatarperson besitzt folgende Besonderheiten:<br>".$modcp['special'];
			} else {
				$special = "Diese Avatarperson besitzt keine eingetragenen Besonderheiten!";
			}
        
			if ($faceclaims_database_mediabase == 1) {

				if ($modcp['mediabase'] != '') {
					$mediabase = "Diese Avatarperson besitzt folgende Mediabase:<br>".$modcp['mediabase'];
				} else {
					$mediabase = "Diese Avatarperson besitzt keine eingetragenen Mediabase!";        
				}

				eval("\$fd_mediabase .= \"".$templates->get("faceclaims_database_faceclaim_bit_mediabase")."\";");
			} else {
				$fd_mediabase = "";
			}
        
			if ($faceclaims_database_gallery == 1) {

				if ($modcp['gallery'] != '') {
					$gallery = "<a href=".$modcp['gallery'].">Zur Galerie</a>";
				} else {
					$gallery = "keine eingetragene Galerie!";        
				}

				eval("\$fd_gallery .= \"".$templates->get("faceclaims_database_faceclaim_bit_gallery")."\";");
			} else {
				$fd_gallery = "";
			}
   
            // User der das eingesendet hat
            $modcp['sendedby'] = htmlspecialchars_uni($modcp['sendedby']);
            $user = get_user($modcp['sendedby']);
            $user['username'] = htmlspecialchars_uni($user['username']);
            $createdby = build_profile_link($user['username'], $modcp['sendedby']);
   
            eval("\$fd_modcp_bit .= \"".$templates->get("faceclaims_database_modcp_bit")."\";");
        }

        $team_uid = $mybb->user['uid'];

        //Der Eintrag wurde vom Team abgelehnt
        if($delete = $mybb->input['delete']){

            // MyALERTS STUFF
         $query_alert = $db->simple_select("faceclaims_database", "*", "fdid = '{$delete}'");
         while ($alert_del = $db->fetch_array ($query_alert)) {
            if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $user = get_user($alert['sendedby']);
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('faceclaims_database_delete');
                 if ($alertType != NULL && $alertType->getEnabled()) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$alert_del['sendedby'], $alertType, (int)$delete);
                    $alert->setExtraDetails([
                        'username' => $user['username'],
                        'faceclaim' => $alert_del['faceclaim']
                    ]);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }
        }

            $db->delete_query("faceclaims_database", "fdid = '$delete'");
            redirect("modcp.php?action=faceclaims_database", $lang->faceclaims_database_redirect_modcp_delete);
        }

        //Der Eintag wurde vom Team angenommen
        if($accept = $mybb->input['accept']){

                // MyALERTS STUFF
         $query_alert = $db->simple_select("faceclaims_database", "*", "fdid = '{$accept}'");
         while ($alert_acc = $db->fetch_array ($query_alert)) {
            if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $user = get_user($alert['sendedby']);
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('faceclaims_database_accept');
                 if ($alertType != NULL && $alertType->getEnabled()) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$alert_acc['sendedby'], $alertType, (int)$accept);
                    $alert->setExtraDetails([
                        'username' => $user['username'],
                        'faceclaim' => $alert_acc['faceclaim']
                    ]);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }
        }

            $db->query("UPDATE ".TABLE_PREFIX."faceclaims_database SET accepted = 1 WHERE fdid = '".$accept."'");
            redirect("modcp.php?action=faceclaims_database", $lang->faceclaims_database_redirect_modcp_accept);
        }
		 
        // TEMPLATE FÜR DIE SEITE
        eval("\$page = \"".$templates->get("faceclaims_database_modcp")."\";");
        output_page($page);
        die();
    }
}

function faceclaims_database_myalert_alerts() {
	global $mybb, $lang;
	$lang->load('faceclaims_database');

    // ANNEHMEN
    /**
	 * Alert formatter for my custom alert type.
	 */
	class MybbStuff_MyAlerts_Formatter_faceclaimsdatabaseAcceptFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
	{
	    /**
	     * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
	     *
	     * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
	     *
	     * @return string The formatted alert string.
	     */
	    public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
	    {
			global $db;
			$alertContent = $alert->getExtraDetails();
            $userid = $db->fetch_field($db->simple_select("users", "uid", "username = '{$alertContent['username']}'"), "uid");
            $user = get_user($userid);
            $alertContent['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
	        return $this->lang->sprintf(
	            $this->lang->faceclaims_database_accept,
				$outputAlert['from_user'],
				$alertContent['username'],
	            $outputAlert['dateline'],
				$alertContent['faceclaim']
	        );
	    }

	    /**
	     * Init function called before running formatAlert(). Used to load language files and initialize other required
	     * resources.
	     *
	     * @return void
	     */
	    public function init()
	    {
	        if (!$this->lang->faceclaims_database) {
	            $this->lang->load('faceclaims_database');
	        }
	    }

	    /**
	     * Build a link to an alert's content so that the system can redirect to it.
	     *
	     * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
	     *
	     * @return string The built alert, preferably an absolute link.
	     */
	    public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
	    {
	        return $this->mybb->settings['bburl'] . '/faceclaims_database.php?action=all';
	    }
	}

	if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
		$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

		if (!$formatterManager) {
			$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
		}

		$formatterManager->registerFormatter(
				new MybbStuff_MyAlerts_Formatter_faceclaimsdatabaseAcceptFormatter($mybb, $lang, 'faceclaims_database_accept')
		);
	}


	// ABLEHNEN
    /**
	 * Alert formatter for my custom alert type.
	 */
	class MybbStuff_MyAlerts_Formatter_faceclaimsdatabaseDeleteFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
	{
	    /**
	     * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
	     *
	     * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
	     *
	     * @return string The formatted alert string.
	     */
	    public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
	    {
			global $db;
			$alertContent = $alert->getExtraDetails();
            $userid = $db->fetch_field($db->simple_select("users", "uid", "username = '{$alertContent['username']}'"), "uid");
            $user = get_user($userid);
            $alertContent['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
	        return $this->lang->sprintf(
	            $this->lang->faceclaims_database_delete,
				$outputAlert['from_user'],
				$alertContent['username'],
	            $outputAlert['dateline'],
				$alertContent['faceclaim']
	        );
	    }

	    /**
	     * Init function called before running formatAlert(). Used to load language files and initialize other required
	     * resources.
	     *
	     * @return void
	     */
	    public function init()
	    {
	        if (!$this->lang->faceclaims_database) {
	            $this->lang->load('faceclaims_database');
	        }
	    }

	    /**
	     * Build a link to an alert's content so that the system can redirect to it.
	     *
	     * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
	     *
	     * @return string The built alert, preferably an absolute link.
	     */
	    public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
	    {
	        return $this->mybb->settings['bburl'] . '/faceclaims_database.php?action=all';
	    }
	}

	if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
		$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

		if (!$formatterManager) {
			$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
		}

		$formatterManager->registerFormatter(
				new MybbStuff_MyAlerts_Formatter_faceclaimsdatabaseDeleteFormatter($mybb, $lang, 'faceclaims_database_delete')
		);
	}
    
}

// ONLINE LOCATION
$plugins->add_hook("fetch_wol_activity_end", "faceclaims_database_online_activity");
$plugins->add_hook("build_friendly_wol_location_end", "faceclaims_database_online_location");

function faceclaims_database_online_activity($user_activity) {
global $parameters, $user;

    $split_loc = explode(".php", $user_activity['location']);
    if($split_loc[0] == $user['location']) {
        $filename = '';
    } else {
        $filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
    }
    
    switch ($filename) {
        case 'faceclaims_database':
        if($parameters['action'] == "main" && empty($parameters['site'])) {
            $user_activity['activity'] = "main";
        }
        if($parameters['action'] == "add" && empty($parameters['site'])) {
            $user_activity['activity'] = "add";
        }
        if($parameters['action'] == "all" && empty($parameters['site'])) {
            $user_activity['activity'] = "all";
        }
        if($parameters['action'] == "filter" && empty($parameters['site'])) {
            $user_activity['activity'] = "filter";
        }
        if($parameters['action'] == "faceclaim_edit" && empty($parameters['site'])) {
            $user_activity['activity'] = "edit";
        }
        if($parameters['filters'] && empty($parameters['site'])) {
            $user_activity['activity'] = "filters";
        }
        break;
    }
      
return $user_activity;
}

function faceclaims_database_online_location($plugin_array) {
global $mybb, $theme, $lang;

	if($plugin_array['user_activity']['activity'] == "main") {
		$plugin_array['location_name'] = "Sieht sich die <a href=\"faceclaims_database.php?action=main\">Avatarpersonendatenbank Hauptseite</a> an.";
	}
    if($plugin_array['user_activity']['activity'] == "add") {
		$plugin_array['location_name'] = "Fügt eine neue Avatarperson in die Datenbank ein.";
	}
    if($plugin_array['user_activity']['activity'] == "all") {
		$plugin_array['location_name'] = "Sieht sich <a href=\"faceclaims_database.php?action=all\">alle Avatarpersonen der Datenbank</a> an.";
	}
    if($plugin_array['user_activity']['activity'] == "filter") {
		$plugin_array['location_name'] = "Filtert speziell nach einer <a href=\"faceclaims_database.php?action=filter\">Avatarperson</a> innerhalb der Datenbank.";
	}
    if($plugin_array['user_activity']['activity'] == "edit") {
		$plugin_array['location_name'] = "Bearbeitet eine Avatarperson innerhalb der Avatarpersonendatenbank.";
	}
    if($plugin_array['user_activity']['activity'] == "filters") {
		$plugin_array['location_name'] = "Sieht sich die <a href=\"faceclaims_database.php?action=main\">Avatarpersonendatenbank</a> mit einem Filter an.";
	}

return $plugin_array;
}
