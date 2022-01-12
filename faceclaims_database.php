<?php
define('IN_MYBB', 1);
require_once './global.php';

global $db, $cache, $mybb, $lang, $templates, $theme, $header, $headerinclude, $footer, $awarded, $claim_name, $fd_random_bit;

// user is visiting the site and plugin isn't installed
if (!$db->table_exists("faceclaims_database")) {
    redirect('index.php', $lang->faceclaims_database_redirect_uninstall);
}

// EINSTELLUNGEN
$faceclaims_database_lists_setting = $mybb->settings['faceclaims_database_lists'];
$faceclaims_database_allow_groups = $mybb->settings['faceclaims_database_allow_groups'];
$faceclaims_database_gender_setting = $mybb->settings['faceclaims_database_gender'];
$faceclaims_database_gender = explode (", ", $faceclaims_database_gender_setting);
$faceclaims_database_origin_setting = $mybb->settings['faceclaims_database_origin'];
$faceclaims_database_origin = explode (", ", $faceclaims_database_origin_setting);
$faceclaims_database_haircolor_setting = $mybb->settings['faceclaims_database_haircolor'];
$faceclaims_database_haircolor= explode (", ", $faceclaims_database_haircolor_setting);
$faceclaims_database_age_limit = $mybb->settings['faceclaims_database_age_limit'];
$faceclaims_database_age_limit_number = $mybb->settings['faceclaims_database_age_limit_number'];
$faceclaims_database_mediabase = $mybb->settings['faceclaims_database_mediabase'];
$faceclaims_database_gallery = $mybb->settings['faceclaims_database_gallery'];
$faceclaims_database_multipage_setting = $mybb->settings['faceclaims_database_multipage']; 
$faceclaims_database_multipage_show_setting = $mybb->settings['faceclaims_database_multipage_show'];
$faceclaims_database_awarded_faceclaims = $mybb->settings['faceclaims_database_awarded_faceclaims']; 
$faceclaimfid = "fid".$mybb->settings['faceclaims_database_faceclaim'];
$faceclaims_database_reserved_faceclaims = $mybb->settings['faceclaims_database_reserved_faceclaims'];
$faceclaims_database_faceclaimtype = $mybb->settings['faceclaims_database_faceclaimtype'];
$faceclaims_database_randompage_setting = $mybb->settings['faceclaims_database_randompage']; 
$faceclaims_database_randompage_show_setting = $mybb->settings['faceclaims_database_randompage_show'];

$aktuellesJahr = date("Y");

// UMLAUTE UMFORMEN
$tempstr = Array("Ä" => "AE", "Ö" => "OE", "Ü" => "UE", "ä" => "ae", "ö" => "oe", "ü" => "ue", "ß" => "ss");
$tempstr2 = Array("AE" => "Ä", "OE" => "Ö", "UE" => "Ü", "ae" => "ä", "oe" => "ö", "ue" => "ü", "ss" => "ß");

$claim_name ="";
// Kontrolliere, ob das Avatar schon vergeben ist
$faceclaims_awarded = $db->query("SELECT $faceclaimfid FROM ".TABLE_PREFIX."userfields");
while ($faceclaim_awarded = $db->fetch_array($faceclaims_awarded)){
    
    $claim = "";
    $claim = $faceclaim_awarded[$faceclaimfid];
  
    // echo "faceclaims_database_faceclaimtype ist:". $faceclaims_database_faceclaimtype ."<br>";
    if($faceclaims_database_faceclaimtype == 1){
        $claim_explode = explode(", ", $claim);
        $claim_name .= $claim_explode[1]." ".$claim_explode[0];
    } else {
        $claim_name .= $claim;
    }
    
}

$reserved_name = "";
// Kontrolliere, ob das Avatar schon reserviert ist
if ($faceclaims_database_reserved_faceclaims == 1) {
    $faceclaims_reserved = $db->query("SELECT content FROM ".TABLE_PREFIX."reservationsentry");
    while ($faceclaim_reserved = $db->fetch_array($faceclaims_reserved)){
    
        $reserved = "";  
        $reserved = $faceclaim_reserved['content'];
  
        if($faceclaims_database_faceclaimtype == 1){
            $reserved_explode = explode(", ", $reserved);
            $reserved_name .= $reserved_explode[1]." ".$reserved_explode[0];
        } else {
            $reserved_name .= $reserved;
        }
    }
}

// NAVIGATION
if(!empty($faceclaims_database_lists_setting)){
    add_breadcrumb("Listen", "$faceclaims_database_lists_setting");
    add_breadcrumb($lang->faceclaims_database_mainpage_nav, "faceclaims_database.php?action=main");
} else{
    add_breadcrumb($lang->faceclaims_database_mainpage_nav, "faceclaims_database.php?action=main");
}

// GESCHLECHT - MENÜ
foreach ($faceclaims_database_gender as $fd_gender_menu) {

    $fd_gender_link = strtr($fd_gender_menu, $tempstr);
    $fd_gender_link = strtolower($fd_gender_link);
    $entry = $lang->sprintf($lang->faceclaims_database_menu_filters_gender_entry, $fd_gender_link, $fd_gender_menu);

    eval("\$fd_menu_gender .= \"".$templates->get("faceclaims_database_menu_cat")."\";");
}
// HERKUNFT - MENÜ
foreach ($faceclaims_database_origin as $fd_origin_menu) {

    $fd_origin_link = strtr($fd_origin_menu, $tempstr);
    $fd_origin_link = strtolower($fd_origin_link);
    $entry = $lang->sprintf($lang->faceclaims_database_menu_filters_origin_entry, $fd_origin_link, $fd_origin_menu);

    eval("\$fd_menu_origin .= \"".$templates->get("faceclaims_database_menu_cat")."\";");
}
// HAARFARBE - MENÜ
foreach ($faceclaims_database_haircolor as $fd_haircolor_menu) {

    $fd_haircolor_link = strtr($fd_haircolor_menu, $tempstr);
    $fd_haircolor_link = strtolower($fd_haircolor_link);
    $entry = $lang->sprintf($lang->faceclaims_database_menu_filters_haircolor_entry, $fd_haircolor_link, $fd_haircolor_menu);
    
    eval("\$fd_menu_haircolor .= \"".$templates->get("faceclaims_database_menu_cat")."\";");
}
// ALTER - MENÜ
$age_string = "1, 10, 20, 30, 40, 50, 60, 70, 80, 90";
$faceclaims_database_age_string = explode (", ", $age_string);
foreach ($faceclaims_database_age_string as $fd_age_menu) {

    $fd_age_end = $fd_age_menu + 9;

    if ($fd_age_menu == 1) {
        $entry = $lang->sprintf($lang->faceclaims_database_menu_filters_age_entry, $fd_age_menu, $fd_age_end, "01");
    } else {
        $entry = $lang->sprintf($lang->faceclaims_database_menu_filters_age_entry, $fd_age_menu, $fd_age_end, $fd_age_menu);
    }
    
    eval("\$fd_menu_age .= \"".$templates->get("faceclaims_database_menu_cat")."\";");
}

// HINZUFÜGE LINK
if(is_member($faceclaims_database_allow_groups)) { 
    $fd_menu_add = "<tr><td class='trow1'>{$lang->faceclaims_database_menu_add}</td></tr>";
}

// RANDOM PAGE LINK
if($faceclaims_database_randompage_setting == 1) { 
    $fd_menu_random = "<tr><td class='trow1'>{$lang->faceclaims_database_menu_random}</td></tr>";
}

// lade das Template für die Navigation
eval("\$fd_menu = \"".$templates->get("faceclaims_database_menu")."\";");

// DIE HAUPTSEITE VOM DER DATENBANK
if($mybb->input['action'] == "main") {
    add_breadcrumb($lang->faceclaims_database_mainpage_nav2);
    
    eval("\$page = \"".$templates->get("faceclaims_database_mainpage")."\";");
    output_page($page);
    die();
}

// AVATARPERSON HINZUFÜGEN - SEITE
if($mybb->input['action'] == "add") {
    add_breadcrumb($lang->faceclaims_database_add_nav);

    if(!is_member($faceclaims_database_allow_groups)) { 
        redirect('faceclaims_database.php?action=main', $lang->faceclaims_database_redirect_add_error);
        return;
    }

    // AUSWAHLMÖGLICHKEIT DROPBOX GENERIEREN
	// Geschlecht
    $gender_select_add = ""; 
	foreach ($faceclaims_database_gender as $gender_add) {
		$gender_select_add .= "<option value='{$gender_add}'>{$gender_add}</option>";
	}
    // Herkunft
    $origin_select_add = "";
	foreach ($faceclaims_database_origin as $origin_add) {
		$origin_select_add .= "<option value='{$origin_add}'>{$origin_add}</option>";
	}
    // Haarfarbe
    $haircolor_select_add = "";
	foreach ($faceclaims_database_haircolor as $haircolor_add) {
		$haircolor_select_add .= "<option value='{$haircolor_add}'>$haircolor_add</option>";
	}

    if ($faceclaims_database_mediabase == 1) {
        $value = 'placeholder="https://"';
        eval("\$fd_add_mediabase .= \"".$templates->get("faceclaims_database_add_mediabase")."\";");
    } else {
        $fd_add_mediabase = "";
    }

    if ($faceclaims_database_gallery == 1) {
        $value = 'placeholder="https://"';
        eval("\$fd_add_gallery .= \"".$templates->get("faceclaims_database_add_gallery")."\";");
    } else {
        $fd_add_gallery = "";
    }

    eval("\$page = \"".$templates->get("faceclaims_database_add")."\";");
    output_page($page);
    die();
}

// Hinzufügen - Datenbank Update
if($mybb->input['action'] == "do_add") {

    if(isset($_POST['faceclaim_add'])) {

        // Wenn das Team Einträge erstellt, dann wink doch einfach durch. Sonst bitte nochmal zum Prüfung :D
        if($mybb->usergroup['canmodcp'] == '1'){
            $accepted = 1;
        } else {
            $accepted = 0;
        }

        // Multiselect Herkunft
        if($_POST['origin'] != ''){
            $origins_string = implode(', ', $_POST['origin']);
        }
    
        // Multiselect Haarfarbe
        if($_POST['haircolor'] != ''){
            $haircolors_string = implode(', ', $_POST['haircolor']); 
        }

        $faceclaims_add =$db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd");
        while ($faceclaim_name = $db->fetch_array ($faceclaims_add)) {
            $name_add .= $faceclaim_name['faceclaim']." ";
        }

        // Überprüfung der Avatarperson
        if (strpos($name_add, $_POST['faceclaim']) !== FALSE) {
            error($lang->faceclaims_database_add_error);
        } elseif (strpos($name, $_POST['faceclaim']) === FALSE) {
            $new_faceclaim = array(
                "faceclaim" => $_POST['faceclaim'],
                "image" => $_POST['image'],
                "birthday" => $_POST['birthday'],
                "origin" => $origins_string,
                "haircolor" => $haircolors_string,
                "gender" => $_POST['gender'],
                "special" => $_POST['special'],
                "mediabase" => $_POST['mediabase'], 
                "gallery" => $_POST['gallery'],
                "sendedby" => (int)$mybb->user['uid'],
                "accepted" => $accepted,
            );  
        }

        $db->insert_query("faceclaims_database", $new_faceclaim);
        redirect("faceclaims_database.php?action=main", $lang->faceclaims_database_redirect_add); 
    }
} 

// AVATARPERSON BEARBEITEN
if($mybb->input['action'] == "faceclaim_edit") {

    // NAVIGATION
    add_breadcrumb ("Avatarperson bearbeiten");

    if($mybb->usergroup['canmodcp'] != "1") { 
        redirect('faceclaims_database.php?action=main', $lang->faceclaims_database_redirect_edit_error);
        return;
    }

    $fdid =  $mybb->get_input('fdid', MyBB::INPUT_INT);

    $edit_query = $db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database      
    WHERE fdid = '".$fdid."'
    ");

    $edit = $db->fetch_array($edit_query);

    // LEER LAUFEN LASSEN
    $fdid = "";
    $image = "";
    $name = "";
    $gender = "";
    $birthday = "";
    $origin = "";
    $haircolor = "";
    $special = "";
    $mediabase = "";
    $gallery = "";

    // MIT INFORMATIONEN FÜLLEN
    $fdid = $edit['fdid'];
    $image = $edit['image'];
    $name = $edit['faceclaim'];
    $gender = $edit['gender'];
    $birthday = $edit['birthday'];
    $special = $edit['special'];
    $mediabase = $edit['mediabase'];
    $gallery = $edit['gallery'];
    $haircolor = explode(", ", $edit['haircolor']);
    $origin = explode(", ", $edit['origin']);

    // AUSWAHLMÖGLICHKEIT DROPBOX GENERIEREN
	// Geschlecht
    $gender_select_edit = ""; 
	foreach ($faceclaims_database_gender as $gender_edit) {
		$gender_select_edit .= "<option value='{$gender_edit}'>{$gender_edit}</option>";
	}
    // Herkunft
    $origin_select_edit = "";
    foreach ($faceclaims_database_origin as $origin_edit) {
        $origin_selected_edit = "";
        foreach($origin as $or){
            if($origin_edit == $or) {
                $origin_selected_edit = "selected=\"selected\"";
            } 
        }
        $origin_select_edit .= "<option value=\"{$origin_edit}\" $origin_selected_edit>{$origin_edit}</option>";
    }
    // Haarfarbe
    $haircolor_select_edit = "";
    foreach ($faceclaims_database_haircolor as $haircolor_edit) {
        $haircolor_selected_edit = "";
        foreach($haircolor as $hair){
            if($haircolor_edit == $hair) {
                $haircolor_selected_edit = "selected=\"selected\"";
            } 
        }
        $haircolor_select_edit .= "<option value=\"{$haircolor_edit}\" $haircolor_selected_edit>{$haircolor_edit}</option>";
    }

    if ($faceclaims_database_mediabase == 1) {
        eval("\$fd_add_mediabase .= \"".$templates->get("faceclaims_database_add_mediabase")."\";");
    } else {
        $fd_add_mediabase = "";
    }

    if ($faceclaims_database_gallery == 1) {
        if ($gallery != '') {
            $value = "value=\"".$gallery."\"";
        } else {
            $value = 'placeholder="https://"';
        }
        eval("\$fd_add_gallery .= \"".$templates->get("faceclaims_database_add_gallery")."\";");
    } else {
        $fd_add_gallery = "";
    }

    //Der neue Inhalt wird nun in die Datenbank eingefügt bzw. die alten Daten überschrieben.        
    if($_POST['edit_faceclaim']){

        $fdid = $mybb->input['fdid'];
   
        // Multiselect Herkunft
        if($_POST['origin'] != ''){
            $origins_string = implode(', ', $_POST['origin']);
        }
    
        // Multiselect Haarfarbe
        if($_POST['haircolor'] != ''){
            $haircolors_string = implode(', ', $_POST['haircolor']);
        }

        $faceclaim_edit = array(
            "faceclaim" => $_POST['faceclaim'],
            "image" => $_POST['image'],
            "birthday" => $_POST['birthday'],
            "origin" => $origins_string,
            "haircolor" => $haircolors_string,
            "gender" => $_POST['gender'],
            "special" => $_POST['special'],
            "mediabase" => $_POST['mediabase'],
            "gallery" => $_POST['gallery']
        );
        
        $db->update_query("faceclaims_database", $faceclaim_edit, "fdid = '".$fdid."'");
        redirect("faceclaims_database.php?action=main", $lang->faceclaims_database_redirect_edit);
    }

    // TEMPLATE FÜR DIE SEITE
    eval("\$page = \"".$templates->get("faceclaims_database_edit")."\";");
    output_page($page);
    die();

}

// FILTERSEITE
if($mybb->input['action'] == "filter") {
    add_breadcrumb($lang->faceclaims_database_filterpage_nav);

    // AUSWAHLMÖGLICHKEIT DROPBOX GENERIEREN
	// Geschlecht
    $gender_select_filter = ""; 
	foreach ($faceclaims_database_gender as $gender_filter) {
		$gender_select_filter .= "<option value='{$gender_filter}'>{$gender_filter}</option>";
	}
    // Herkunft
    $origin_select_filter = "";
	foreach ($faceclaims_database_origin as $origin_filter) {
		$origin_select_filter .= "<option value='{$origin_filter}'>{$origin_filter}</option>";
	}
    // Haarfarbe
    $haircolor_select_filter = "";
	foreach ($faceclaims_database_haircolor as $haircolor_filter) {
		$haircolor_select_filter .= "<option value='{$haircolor_filter}'>{$haircolor_filter}</option>";
	}

    eval("\$fd_filters .= \"".$templates->get("faceclaims_database_filterpage_filters")."\";");

    $haircolor_filters = $mybb->input['filter_haircolor'];
    if(empty($haircolor_filters)){
        $haircolor_filters = "%";
    }
    $origin_filters = $mybb->input['filter_origin'];
    if(empty($origin_filters)){
        $origin_filters = "%";
    } 
    $gender_filters = $mybb->input['filter_gender'];
    if(empty($gender_filters)){
        $gender_filters = "%";
    } 

    $age_start = $mybb->get_input('age_start');
    $age_end = $mybb->get_input('age_end');
    if(!empty($age_start) || !empty($age_end)){

        if ($faceclaims_database_age_limit == 1) {
            $year_start = $aktuellesJahr - $age_start - $faceclaims_database_age_limit_number; 
            $year_end = $aktuellesJahr - $age_end - $faceclaims_database_age_limit_number;
        } else {
            $year_start = $aktuellesJahr - $age_start; 
            $year_end = $aktuellesJahr - $age_end;
        }

        $age_sql = "AND birthday BETWEEN '$year_end' AND '$year_start'";
    } else {
        $age_start = "";
        $age_end = "";
        $age_sql = "";
    }

    $firstletter = $mybb->input['firstletter'];
    if(!empty($firstletter)){
        $firstletter_sql = "AND faceclaim LIKE '$firstletter%'";
    } else {
        $firstletter_sql = "";
    }

    $fullname = $mybb->input['fullname'];
    if(!empty($fullname)){
        $fullname_sql = "AND faceclaim LIKE '$fullname%'";
    } else {
        $fullname_sql = "";
    }

    $faceclaims_count = 0;

    $type_url = htmlspecialchars_uni("faceclaims_database.php?action=filter&filter_gender=$gender_filters&filter_origin=$origin_filters&filter_haircolor=$haircolor_filters&age_start=$age_start&age_end=$age_end&firstletter=$firstletter&fullname=$fullname");

    $countfaceclaims = $db->fetch_field($db->query("SELECT COUNT(fdid) AS faceclaims FROM ".TABLE_PREFIX."faceclaims_database 
    WHERE gender LIKE '".$gender_filters."' 
    AND origin LIKE '%".$origin_filters."%' 
    AND haircolor LIKE '%".$haircolor_filters."%' 
    ".$age_sql." 
    ".$firstletter_sql."
    ".$fullname_sql."
    AND accepted != '0'
    "), "faceclaims");

    if ($countfaceclaims == 1) {
        $lang->faceclaims_database_faceclaim_count = $lang->sprintf($lang->faceclaims_database_faceclaim_count, $countfaceclaims, 'Avatarperson');
    } else {
        $lang->faceclaims_database_faceclaim_count = $lang->sprintf($lang->faceclaims_database_faceclaim_count, $countfaceclaims, 'Avatarpersonen');
    }

    // MULTIPAGE
	$perpage = $faceclaims_database_multipage_show_setting;
	$page = intval($mybb->input['page']);
	if($page) {
		$start = ($page-1) *$perpage;
	}
	else {
		$start = 0;
		$page = 1;
	}
	$end = $start + $perpage;
	$lower = $start+1;
	$upper = $end;
	if($upper > $countfaceclaims) {
		$upper = $countfaceclaims;
	}

	if ($faceclaims_database_multipage_setting == 1) {
		$multipage = multipage($countfaceclaims, $perpage, $page, $type_url);
	} else {
		$multipage = "";
	}

	// ABFRAGE ALLER AVATARPERSONEN - MULTIPAGE
	if ($faceclaims_database_multipage_setting == 1) {
        $filters_faceclaims_query = $db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd
        WHERE gender LIKE '".$gender_filters."'
        AND origin LIKE '%".$origin_filters."%'
        AND haircolor LIKE '%".$haircolor_filters."%'
        ".$age_sql."
        ".$firstletter_sql."
        ".$fullname_sql."
        AND accepted != '0'
        ORDER BY faceclaim ASC
        LIMIT $start, $perpage
        ");
	} 
	// ABFRAGE ALLER AVATARPERSONEN - OHNE MULTIPAGE
	else {
        $filters_faceclaims_query = $db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd
        WHERE gender LIKE '".$gender_filters."'
        AND origin LIKE '%".$origin_filters."%'
        AND haircolor LIKE '%".$haircolor_filters."%'
        ".$age_sql."
        ".$firstletter_sql."
        ".$fullname_sql."
        AND accepted != '0'
        ORDER BY faceclaim ASC
        ");
	}

    eval("\$filters_faceclaims_none .= \"".$templates->get("faceclaims_database_faceclaim_none")."\";");

    while($filters = $db->fetch_array($filters_faceclaims_query)){
        $faceclaims_count++;
        
        $filters_faceclaims_none = "";

        // LEER LAUFEN LASSEN
        $image = "";
        $name = "";
        $gender = "";
        $birthday = "";
        $origin = "";
        $haircolor = "";
        $age = "";
        $fd_gallery = "";
        $fd_mediabase = "";

        // MIT INFORMATIONEN FÜLLEN
        $image = "<img src=".$filters['image']." style=\"width: 125px;\" / >";
        $name = $filters['faceclaim'];
        $gender = $filters['gender'];
        $birthday = $filters['birthday'];
        $origin = $filters['origin'];
        $haircolor = $filters['haircolor'];
        $special1 = $filters['special'];
        $mediabase1 = $filters['mediabase'];
        $age = $aktuellesJahr - $birthday;
        $minage = $age - $faceclaims_database_age_limit_number;
        $maxage = $age + $faceclaims_database_age_limit_number;

        if ($faceclaims_database_age_limit == 1) {
            $age_limit = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_age_limit, $minage, $maxage);
        } else {
            $age_limit = "";
        }

        if ($filters['special'] != '') {
            $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special, $special1);
        } else {
            $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special_none);
        }

        if ($faceclaims_database_mediabase == 1) {

            if ($filters['mediabase'] != '') {
                $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase, $mediabase1);
            } else {
                $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase_none);
            }

            eval("\$fd_mediabase .= \"".$templates->get("faceclaims_database_faceclaim_bit_mediabase")."\";");
        } else {
            $fd_mediabase = "";
        }

        if ($faceclaims_database_gallery == 1) {

            if ($faceclaim['gallery'] != '') {
                $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery, $filters['gallery']);
            } else {
                $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery_none);
            }

            eval("\$fd_gallery .= \"".$templates->get("faceclaims_database_faceclaim_bit_gallery")."\";");
        } else {
            $fd_gallery = "";
        }

        // Vergeben & Reserviert 
        if ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims == 1) {
           
            if (strpos($claim_name, $filters['faceclaim']) !== FALSE OR strpos($reserved_name, $filters['faceclaim']) !== FALSE) {
                $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }

        } 
        // Nur Vergeben
        elseif ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims != 1) {
            
            if (strpos($claim_name, $filters['faceclaim']) !== FALSE) {
                     $awarded = 'style="opacity: 0.5;"';
                } else {
                     $awarded = '';
                }

        } 
        // Nur Reserviert
        elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims == 1) {
            
            if (strpos($reserved_name, $filters['faceclaim']) !== FALSE) {
                $awarded = 'style="opacity: 0.5;"';
            } else {
                $awarded = '';
            }

        } 
        // Gar nicht verblasst
        elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims != 1) {
            $awarded = '';
        } 

        eval("\$filters_faceclaims .= \"".$templates->get("faceclaims_database_faceclaim_bit")."\";");
    }

    eval("\$page = \"".$templates->get("faceclaims_database_filterpage")."\";");
    output_page($page);
    die();
}

// ALLE AVATARPERSONEN
if($mybb->input['action'] == "all") {
    add_breadcrumb($lang->faceclaims_database_faceclaim_all);

    $countfaceclaims = $db->fetch_field($db->query("SELECT COUNT(fdid) AS faceclaims FROM ".TABLE_PREFIX."faceclaims_database WHERE accepted != '0'"), "faceclaims");

    if ($countfaceclaims == 1) {
        $lang->faceclaims_database_faceclaim_count = $lang->sprintf($lang->faceclaims_database_faceclaim_count, $countfaceclaims, 'Avatarperson');
    } else {
        $lang->faceclaims_database_faceclaim_count = $lang->sprintf($lang->faceclaims_database_faceclaim_count, $countfaceclaims, 'Avatarpersonen');
    }

    eval("\$fd_faceclaim_none .= \"".$templates->get("faceclaims_database_faceclaim_none")."\";");

    $type_url = htmlspecialchars_uni("faceclaims_database.php?action=all");

    // MULTIPAGE
	$perpage = $faceclaims_database_multipage_show_setting;
	$page = intval($mybb->input['page']);
	if($page) {
		$start = ($page-1) *$perpage;
	}
	else {
		$start = 0;
		$page = 1;
	}
	$end = $start + $perpage;
	$lower = $start+1;
	$upper = $end;
	if($upper > $countfaceclaims) {
		$upper = $countfaceclaims;
	}

	if ($faceclaims_database_multipage_setting == 1) {
		$multipage = multipage($countfaceclaims, $perpage, $page, $type_url);
	} else {
		$multipage = "";
	}

	// ABFRAGE ALLER AVATARPERSONEN - MULTIPAGE
	if ($faceclaims_database_multipage_setting == 1) {
        $faceclaims_all = $db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd
        WHERE accepted != '0'
        ORDER by faceclaim ASC
        LIMIT $start, $perpage
        ");
	} 
	// ABFRAGE ALLER AVATARPERSONEN - OHNE MULTIPAGE
	else {
        $faceclaims_all = $db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd
        WHERE accepted != '0'
        ORDER by faceclaim ASC
        ");
	}
    
    while ($faceclaim = $db->fetch_array ($faceclaims_all)) {

        $fd_faceclaim_none = "";

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
        $awarded = "";

        // MIT INFORMATIONEN FÜLLEN
        $fdid = $faceclaim['fdid'];
        $image = "<img src=".$faceclaim['image']." style=\"width: 125px;\" / >";
        $name = $faceclaim['faceclaim'];
        $gender = $faceclaim['gender'];
        $birthday = $faceclaim['birthday'];
        $origin = $faceclaim['origin'];
        $haircolor = $faceclaim['haircolor'];
        $special1 = $faceclaim['special'];
        $mediabase1 = $faceclaim['mediabase'];
        $age = $aktuellesJahr - $birthday;
        $minage = $age - $faceclaims_database_age_limit_number;
        $maxage = $age + $faceclaims_database_age_limit_number;

        if ($faceclaims_database_age_limit == 1) {
            $age_limit = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_age_limit, $minage, $maxage);
        } else {
            $age_limit = "";
        }

        if ($faceclaim['special'] != '') {
            $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special, $special1);
        } else {
            $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special_none);
        }

        if ($faceclaims_database_mediabase == 1) {

            if ($faceclaim['mediabase'] != '') {
                $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase, $mediabase1);
            } else {
                $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase_none);
            }

            eval("\$fd_mediabase .= \"".$templates->get("faceclaims_database_faceclaim_bit_mediabase")."\";");
        } else {
            $fd_mediabase = "";
        }

        if ($faceclaims_database_gallery == 1) {

            if ($faceclaim['gallery'] != '') {
                $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery, $faceclaim['gallery']);
            } else {
                $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery_none);
            }

            eval("\$fd_gallery .= \"".$templates->get("faceclaims_database_faceclaim_bit_gallery")."\";");
        } else {
            $fd_gallery = "";
        }

        // Vergeben & Reserviert 
        if ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims == 1) {
           
            if (strpos($claim_name, $faceclaim['faceclaim']) !== FALSE OR strpos($reserved_name, $faceclaim['faceclaim']) !== FALSE) {
                $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }

        } 
        // Nur Vergeben
        elseif ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims != 1) {
            
            if (strpos($claim_name, $faceclaim['faceclaim']) !== FALSE) {
                     $awarded = 'style="opacity: 0.5;"';
                } else {
                     $awarded = '';
                }

        } 
        // Nur Reserviert
        elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims == 1) {
            
            if (strpos($reserved_name, $faceclaim['faceclaim']) !== FALSE) {
                $awarded = 'style="opacity: 0.5;"';
            } else {
                $awarded = '';
            }

        } 
        // Gar nicht verblasst
        elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims != 1) {
            $awarded = '';
        } 

        if ($mybb->usergroup['canmodcp'] == '1') {
    
            $edit = "<a href=\"faceclaims_database.php?action=faceclaim_edit&fdid={$fdid}\">{$lang->faceclaims_database_faceclaim_edit}</a>";
            $delete = "<a href=\"faceclaims_database.php?action=all&delete={$fdid}\">{$lang->faceclaims_database_faceclaim_delete}</a>";

            eval("\$fd_teamoption .= \"".$templates->get("faceclaims_database_faceclaim_bit_teamoption")."\";");
        } else {
            $fd_teamoption = "";
        }

        eval("\$fd_faceclaim_bit .= \"".$templates->get("faceclaims_database_faceclaim_bit")."\";");
    }
    
    // AVATARPERSON LÖSCHEN
    $delete = $mybb->input['delete'];
    if($delete) {
        $db->delete_query("faceclaims_database", "fdid = '$delete'");
        redirect("faceclaims_database.php?action=all", $lang->faceclaims_database_redirect_delete);
    }

    eval("\$page = \"".$templates->get("faceclaims_database_faceclaim_all")."\";");
    output_page($page);
    die();
}

// RANDOM AUSGABE
if($mybb->input['action'] == "random") {

    if ($faceclaims_database_randompage_setting != 1) {
        redirect('faceclaims_database.php?action=main', $lang->faceclaims_database_redirect_random);
    }

    add_breadcrumb($lang->faceclaims_database_randompage_nav);

    $lang->faceclaims_database_randompage_count = $lang->sprintf($lang->faceclaims_database_randompage_count, $faceclaims_database_randompage_show_setting);

    $faceclaims_random = $db->query("SELECT * FROM 
    (SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd ORDER BY rand() LIMIT $faceclaims_database_randompage_show_setting ) T1
    ORDER BY faceclaim ASC 
    ");

    while ($random = $db->fetch_array ($faceclaims_random)) {

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
        $awarded = "";

        // MIT INFORMATIONEN FÜLLEN
        $fdid = $random['fdid'];
        $image = "<img src=".$random['image']." style=\"width: 125px;\" / >";
        $name = $random['faceclaim'];
        $gender = $random['gender'];
        $birthday = $random['birthday'];
        $origin = $random['origin'];
        $haircolor = $random['haircolor'];
        $special1 = $random['special'];
        $mediabase1 = $random['mediabase'];
        $age = $aktuellesJahr - $birthday;
        $minage = $age - $faceclaims_database_age_limit_number;
        $maxage = $age + $faceclaims_database_age_limit_number;

        if ($faceclaims_database_age_limit == 1) {
            $age_limit = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_age_limit, $minage, $maxage);
        } else {
            $age_limit = "";
        }

        if ($random['special'] != '') {
            $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special, $special1);
        } else {
            $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special_none);
        }

        if ($faceclaims_database_mediabase == 1) {

            if ($random['mediabase'] != '') {
                $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase, $mediabase1);
            } else {
                $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase_none);
            }

            eval("\$fd_mediabase .= \"".$templates->get("faceclaims_database_faceclaim_bit_mediabase")."\";");
        } else {
            $fd_mediabase = "";
        }

        if ($faceclaims_database_gallery == 1) {

            if ($random['gallery'] != '') {
                $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery, $random['gallery']);
            } else {
                $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery_none);
            }

            eval("\$fd_gallery .= \"".$templates->get("faceclaims_database_faceclaim_bit_gallery")."\";");
        } else {
            $fd_gallery = "";
        }

        // Vergeben & Reserviert 
        if ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims == 1) {
           
            if (strpos($claim_name, $random['faceclaim']) !== FALSE OR strpos($reserved_name, $random['faceclaim']) !== FALSE) {
                $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }

        } 
        // Nur Vergeben
        elseif ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims != 1) {
            
            if (strpos($claim_name, $random['faceclaim']) !== FALSE) {
                     $awarded = 'style="opacity: 0.5;"';
                } else {
                     $awarded = '';
                }

        } 
        // Nur Reserviert
        elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims == 1) {
            
            if (strpos($reserved_name, $random['faceclaim']) !== FALSE) {
                $awarded = 'style="opacity: 0.5;"';
            } else {
                $awarded = '';
            }

        } 
        // Gar nicht verblasst
        elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims != 1) {
            $awarded = '';
        }

        eval("\$fd_random_bit .= \"".$templates->get("faceclaims_database_faceclaim_bit")."\";");
    }

    eval("\$page = \"".$templates->get("faceclaims_database_randompage")."\";");
    output_page($page);
    die();
}

// FILTER PER INPUT MENÜ
// foreach um die Seiten - 4 Stück
// $mybb->input['filters'] == "{$filteroption}" 
// FILTER - Geschlecht
foreach ($faceclaims_database_gender as $fd_gender) {

    $fd_gender = strtr($fd_gender, $tempstr);
    $fd_gender = strtolower($fd_gender);

    if($mybb->input['filters'] == "$fd_gender") {

        $type_url = htmlspecialchars_uni("faceclaims_database.php?filters=$fd_gender");

        $fd_gender = strtr($fd_gender, $tempstr2);

        add_breadcrumb($fd_gender."e Avatarpersonen");

        $filter_title = $fd_gender."e";

        $countfaceclaims = $db->fetch_field($db->query("SELECT COUNT(fdid) AS faceclaims FROM ".TABLE_PREFIX."faceclaims_database 
        WHERE gender = '$fd_gender' AND accepted != '0'"), "faceclaims");

        if ($countfaceclaims == 1) {
            $lang->faceclaims_database_faceclaim_count = $lang->sprintf($lang->faceclaims_database_faceclaim_count, $countfaceclaims, 'Avatarperson');
        } else {
            $lang->faceclaims_database_faceclaim_count = $lang->sprintf($lang->faceclaims_database_faceclaim_count, $countfaceclaims, 'Avatarpersonen');
        }

        // MULTIPAGE
        $perpage = $faceclaims_database_multipage_show_setting;
        $page = intval($mybb->input['page']);
        if($page) {
            $start = ($page-1) *$perpage;
        }
        else {
            $start = 0;
            $page = 1;
        }

        $end = $start + $perpage;
        $lower = $start+1;
        $upper = $end;
        if($upper > $countfaceclaims) {
            $upper = $countfaceclaims;	
        }

        if ($faceclaims_database_multipage_setting == 1) {
            $multipage = multipage($countfaceclaims, $perpage, $page, $type_url);
        } else {
            $multipage = "";	
        }

        // ABFRAGE ALLER AVATARPERSONEN - MULTIPAGE
        if ($faceclaims_database_multipage_setting == 1) {
            $faceclaims_gender = $db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd
            WHERE gender = '$fd_gender'
            AND accepted != '0'
            ORDER by faceclaim ASC
            LIMIT $start, $perpage
            ");
        } 
        // ABFRAGE ALLER AVATARPERSONEN - OHNE MULTIPAGE
        else {
            $faceclaims_gender = $db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd
            WHERE gender = '$fd_gender'
            AND accepted != '0'
            ORDER by faceclaim ASC
            ");
        }
   
        eval("\$fd_faceclaim_none .= \"".$templates->get("faceclaims_database_faceclaim_none")."\";");

        while ($faceclaim = $db->fetch_array ($faceclaims_gender)) {

            $fd_faceclaim_none = "";
    
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
            $fdid = $faceclaim['fdid'];
            $image = "<img src=".$faceclaim['image']." style=\"width: 125px;\" / >";
            $name = $faceclaim['faceclaim'];
            $gender = $faceclaim['gender'];
            $birthday = $faceclaim['birthday'];
            $origin = $faceclaim['origin'];
            $haircolor = $faceclaim['haircolor'];
            $special1 = $faceclaim['special'];
            $mediabase1 = $faceclaim['mediabase'];
            $age = $aktuellesJahr - $birthday;
            $minage = $age - $faceclaims_database_age_limit_number;
            $maxage = $age + $faceclaims_database_age_limit_number;
    
            if ($faceclaims_database_age_limit == 1) {
                $age_limit = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_age_limit, $minage, $maxage);
            } else {
                $age_limit = "";
            }

            if ($faceclaim['special'] != '') {
                $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special, $special1);
            } else {
                $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special_none);
            }
    
            if ($faceclaims_database_mediabase == 1) {

                if ($faceclaim['mediabase'] != '') {
                    $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase, $mediabase1);
                } else {
                    $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase_none);
                }
    
                eval("\$fd_mediabase .= \"".$templates->get("faceclaims_database_faceclaim_bit_mediabase")."\";");
            } else {
                $fd_mediabase = "";
            }

            if ($faceclaims_database_gallery == 1) {

                if ($faceclaim['gallery'] != '') {
                    $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery, $faceclaim['gallery']);
                } else {
                    $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery_none);
                }
    
                eval("\$fd_gallery .= \"".$templates->get("faceclaims_database_faceclaim_bit_gallery")."\";");
            } else {
                $fd_gallery = "";
            }
    
            // Vergeben & Reserviert 
            if ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims == 1) {
                if (strpos($claim_name, $faceclaim['faceclaim']) !== FALSE OR strpos($reserved_name, $faceclaim['faceclaim']) !== FALSE) {
                    $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }
            } 
            // Nur Vergeben
            elseif ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims != 1) {
                if (strpos($claim_name, $faceclaim['faceclaim']) !== FALSE) {
                    $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }
            } 
            // Nur Reserviert
            elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims == 1) {
                if (strpos($reserved_name, $faceclaim['faceclaim']) !== FALSE) {
                    $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }
            } 
            // Gar nicht verblasst
            elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims != 1) {
                $awarded = '';
            } 

            if ($mybb->usergroup['canmodcp'] == '1') {
    
                $edit = "<a href=\"faceclaims_database.php?action=faceclaim_edit&fdid={$fdid}\">{$lang->faceclaims_database_faceclaim_edit}</a>";
                $delete = "<a href=\"{$type_url}&delete={$fdid}\">{$lang->faceclaims_database_faceclaim_delete}</a>";
    
                eval("\$fd_teamoption .= \"".$templates->get("faceclaims_database_faceclaim_bit_teamoption")."\";");
            } else {
                $fd_teamoption = "";
            }
    
            eval("\$fd_faceclaim_bit .= \"".$templates->get("faceclaims_database_faceclaim_bit")."\";");
        }
        
        // AVATARPERSON LÖSCHEN
        $delete = $mybb->input['delete'];
        if($delete) {
            $db->delete_query("faceclaims_database", "fdid = '$delete'");
            redirect("$type_url", $lang->faceclaims_database_redirect_delete);
        }
       
        eval("\$page = \"".$templates->get("faceclaims_database_faceclaim_filters")."\";");
        output_page($page);
        die();
    }
}

// FILTER - Herkunft
foreach ($faceclaims_database_origin as $fd_origin) {

    $fd_origin = strtr($fd_origin, $tempstr);
    $fd_origin = strtolower($fd_origin);
       
    if($mybb->input['filters'] == "$fd_origin") {

        $type_url = htmlspecialchars_uni("faceclaims_database.php?filters=$fd_origin");

        $fd_origin = strtr($fd_origin, $tempstr2);
 
        add_breadcrumb("$fd_origin Avatarpersonen");  

        $filter_title = $fd_origin;

        $countfaceclaims = $db->fetch_field($db->query("SELECT COUNT(fdid) AS faceclaims FROM ".TABLE_PREFIX."faceclaims_database  
        WHERE origin LIKE '%$fd_origin%' AND accepted != '0'"), "faceclaims");

        if ($countfaceclaims == 1) {
            $lang->faceclaims_database_faceclaim_count = $lang->sprintf($lang->faceclaims_database_faceclaim_count, $countfaceclaims, 'Avatarperson');
        } else {
            $lang->faceclaims_database_faceclaim_count = $lang->sprintf($lang->faceclaims_database_faceclaim_count, $countfaceclaims, 'Avatarpersonen');
        }

        // MULTIPAGE
        $perpage = $faceclaims_database_multipage_show_setting;
        $page = intval($mybb->input['page']);
        if($page) {
            $start = ($page-1) *$perpage;
        }
        else {
            $start = 0;
            $page = 1;
        }

        $end = $start + $perpage;
        $lower = $start+1;
        $upper = $end;
        if($upper > $countfaceclaims) {
            $upper = $countfaceclaims;	
        }

        if ($faceclaims_database_multipage_setting == 1) {
            $multipage = multipage($countfaceclaims, $perpage, $page, $type_url);
        } else {
            $multipage = "";	
        }

        // ABFRAGE ALLER AVATARPERSONEN - MULTIPAGE
        if ($faceclaims_database_multipage_setting == 1) {
            $faceclaims_origin =$db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd
            WHERE origin LIKE '%$fd_origin%'
            AND accepted != '0'
            ORDER BY faceclaim ASC 
            LIMIT $start, $perpage
            ");
        } 
        // ABFRAGE ALLER AVATARPERSONEN - OHNE MULTIPAGE
        else {
            $faceclaims_origin =$db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd
            WHERE origin LIKE '%$fd_origin%'
            AND accepted != '0'
            ORDER BY faceclaim ASC        
            ");
        }

        eval("\$fd_faceclaim_none .= \"".$templates->get("faceclaims_database_faceclaim_none")."\";");
         
        while ($faceclaim = $db->fetch_array ($faceclaims_origin)) {

            $fd_faceclaim_none = "";
    
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
            $fdid = $faceclaim['fdid'];
            $image = "<img src=".$faceclaim['image']." style=\"width: 125px;\" / >";
            $name = $faceclaim['faceclaim'];
            $gender = $faceclaim['gender'];
            $birthday = $faceclaim['birthday'];
            $origin = $faceclaim['origin'];
            $haircolor = $faceclaim['haircolor'];
            $special1 = $faceclaim['special'];
            $mediabase1 = $faceclaim['mediabase'];
            $age = $aktuellesJahr - $birthday;
            $minage = $age - $faceclaims_database_age_limit_number;
            $maxage = $age + $faceclaims_database_age_limit_number;
    
            if ($faceclaims_database_age_limit == 1) {
                $age_limit = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_age_limit, $minage, $maxage);
            } else {
                $age_limit = "";
            }

            if ($faceclaim['special'] != '') {
                $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special, $special1);
            } else {
                $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special_none);
            }
    
            if ($faceclaims_database_mediabase == 1) {

                if ($faceclaim['mediabase'] != '') {
                    $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase, $mediabase1);
                } else {
                    $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase_none);
                }
    
                eval("\$fd_mediabase .= \"".$templates->get("faceclaims_database_faceclaim_bit_mediabase")."\";");
            } else {
                $fd_mediabase = "";
            }

            if ($faceclaims_database_gallery == 1) {

                if ($faceclaim['gallery'] != '') {
                    $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery, $faceclaim['gallery']);
                } else {
                    $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery_none);
                }
    
                eval("\$fd_gallery .= \"".$templates->get("faceclaims_database_faceclaim_bit_gallery")."\";");
            } else {
                $fd_gallery = "";
            }
    
            // Vergeben & Reserviert 
            if ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims == 1) {
                if (strpos($claim_name, $faceclaim['faceclaim']) !== FALSE OR strpos($reserved_name, $faceclaim['faceclaim']) !== FALSE) {
                    $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }
            } 
            // Nur Vergeben
            elseif ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims != 1) {
                if (strpos($claim_name, $faceclaim['faceclaim']) !== FALSE) {
                    $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }
            } 
            // Nur Reserviert
            elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims == 1) {
                if (strpos($reserved_name, $faceclaim['faceclaim']) !== FALSE) {
                    $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }
            } 
            // Gar nicht verblasst
            elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims != 1) {
                $awarded = '';
            } 

            if ($mybb->usergroup['canmodcp'] == '1') {
    
                $edit = "<a href=\"faceclaims_database.php?action=faceclaim_edit&fdid={$fdid}\">{$lang->faceclaims_database_faceclaim_edit}</a>";
                $delete = "<a href=\"{$type_url}&delete={$fdid}\">{$lang->faceclaims_database_faceclaim_delete}</a>";
    
                eval("\$fd_teamoption .= \"".$templates->get("faceclaims_database_faceclaim_bit_teamoption")."\";");
            } else {
                $fd_teamoption = "";
            }
    
            eval("\$fd_faceclaim_bit .= \"".$templates->get("faceclaims_database_faceclaim_bit")."\";");
        }
        
        // AVATARPERSON LÖSCHEN
        $delete = $mybb->input['delete'];
        if($delete) {
            $db->delete_query("faceclaims_database", "fdid = '$delete'");
            redirect("$type_url", $lang->faceclaims_database_redirect_delete);
        }
       
        eval("\$page = \"".$templates->get("faceclaims_database_faceclaim_filters")."\";");
        output_page($page);
        die();          
    }
}

// FILTER - Haarfarbe
foreach ($faceclaims_database_haircolor as $fd_haircolor) {

    $fd_haircolor = strtr($fd_haircolor, $tempstr);
    $fd_haircolor = strtolower($fd_haircolor);
       
    if($mybb->input['filters'] == "$fd_haircolor") {

        $type_url = htmlspecialchars_uni("faceclaims_database.php?filters=$fd_haircolor");

        $fd_haircolor = strtr($fd_haircolor, $tempstr2);
 
        add_breadcrumb("$fd_haircolor Avatarpersonen");  

        $filter_title = $fd_haircolor;

        $countfaceclaims = $db->fetch_field($db->query("SELECT COUNT(fdid) AS faceclaims FROM ".TABLE_PREFIX."faceclaims_database  
        WHERE haircolor LIKE '%$fd_haircolor%' AND accepted != '0'"), "faceclaims");

        if ($countfaceclaims == 1) {
            $lang->faceclaims_database_faceclaim_count = $lang->sprintf($lang->faceclaims_database_faceclaim_count, $countfaceclaims, 'Avatarperson');
        } else {
            $lang->faceclaims_database_faceclaim_count = $lang->sprintf($lang->faceclaims_database_faceclaim_count, $countfaceclaims, 'Avatarpersonen');
        }

        // MULTIPAGE
        $perpage = $faceclaims_database_multipage_show_setting;
        $page = intval($mybb->input['page']);
        if($page) {
            $start = ($page-1) *$perpage;
        }
        else {
            $start = 0;
            $page = 1;
        }

        $end = $start + $perpage;
        $lower = $start+1;
        $upper = $end;
        if($upper > $countfaceclaims) {
            $upper = $countfaceclaims;	
        }

        if ($faceclaims_database_multipage_setting == 1) {
            $multipage = multipage($countfaceclaims, $perpage, $page, $type_url);
        } else {
            $multipage = "";	
        }

        // ABFRAGE ALLER AVATARPERSONEN - MULTIPAGE
        if ($faceclaims_database_multipage_setting == 1) {
            $faceclaims_haircolor =$db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd
            WHERE haircolor LIKE '%$fd_haircolor%'
            AND accepted != '0'
            ORDER BY faceclaim ASC
            LIMIT $start, $perpage
            ");
        } 
        // ABFRAGE ALLER AVATARPERSONEN - OHNE MULTIPAGE
        else {
            $faceclaims_haircolor =$db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd
            WHERE haircolor LIKE '%$fd_haircolor%'
            AND accepted != '0'
            ORDER BY faceclaim ASC       
            ");
        }

        eval("\$fd_faceclaim_none .= \"".$templates->get("faceclaims_database_faceclaim_none")."\";");
         
        while ($faceclaim = $db->fetch_array ($faceclaims_haircolor)) {

           $fd_faceclaim_none = "";
    
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
           $fdid = $faceclaim['fdid'];
            $image = "<img src=".$faceclaim['image']." style=\"width: 125px;\" / >";
            $name = $faceclaim['faceclaim'];
            $gender = $faceclaim['gender'];
            $birthday = $faceclaim['birthday'];
            $origin = $faceclaim['origin'];
            $haircolor = $faceclaim['haircolor'];
            $special1 = $faceclaim['special'];
            $mediabase1 = $faceclaim['mediabase'];
            $age = $aktuellesJahr - $birthday;
            $minage = $age - $faceclaims_database_age_limit_number;
            $maxage = $age + $faceclaims_database_age_limit_number;
    
            if ($faceclaims_database_age_limit == 1) {
                $age_limit = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_age_limit, $minage, $maxage);
            } else {
                $age_limit = "";
            }

            if ($faceclaim['special'] != '') {
                $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special, $special1);
            } else {
                $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special_none);
            }
    
            if ($faceclaims_database_mediabase == 1) {

                if ($faceclaim['mediabase'] != '') {
                    $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase, $mediabase1);
                } else {
                    $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase_none);
                }
    
                eval("\$fd_mediabase .= \"".$templates->get("faceclaims_database_faceclaim_bit_mediabase")."\";");
            } else {
                $fd_mediabase = "";
            }

            if ($faceclaims_database_gallery == 1) {

                if ($faceclaim['gallery'] != '') {
                    $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery, $faceclaim['gallery']);
                } else {
                    $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery_none);
                }
    
                eval("\$fd_gallery .= \"".$templates->get("faceclaims_database_faceclaim_bit_gallery")."\";");
            } else {
                $fd_gallery = "";
            }
    
            // Vergeben & Reserviert 
            if ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims == 1) {
                if (strpos($claim_name, $faceclaim['faceclaim']) !== FALSE OR strpos($reserved_name, $faceclaim['faceclaim']) !== FALSE) {
                    $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }
            } 
            // Nur Vergeben
            elseif ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims != 1) {
                if (strpos($claim_name, $faceclaim['faceclaim']) !== FALSE) {
                    $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }
            } 
            // Nur Reserviert
            elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims == 1) {
                if (strpos($reserved_name, $faceclaim['faceclaim']) !== FALSE) {
                    $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }
            } 
            // Gar nicht verblasst
            elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims != 1) {
                $awarded = '';
            } 

            if ($mybb->usergroup['canmodcp'] == '1') {
    
                $edit = "<a href=\"faceclaims_database.php?action=faceclaim_edit&fdid={$fdid}\">{$lang->faceclaims_database_faceclaim_edit}</a>";
                $delete = "<a href=\"{$type_url}&delete={$fdid}\">{$lang->faceclaims_database_faceclaim_delete}</a>";
    
                eval("\$fd_teamoption .= \"".$templates->get("faceclaims_database_faceclaim_bit_teamoption")."\";");
            } else {
                $fd_teamoption = "";
            }
    
            eval("\$fd_faceclaim_bit .= \"".$templates->get("faceclaims_database_faceclaim_bit")."\";");
        }
        
        // AVATARPERSON LÖSCHEN
        $delete = $mybb->input['delete'];
        if($delete) {
            $db->delete_query("faceclaims_database", "fdid = '$delete'");
            redirect("$type_url", $lang->faceclaims_database_redirect_delete);
        }
       
        eval("\$page = \"".$templates->get("faceclaims_database_faceclaim_filters")."\";");
        output_page($page);
        die();          
    }
}

// FILTER - ALTER
// 1 - 9, 10 - 19, 20 - 29, 30 - 39, 40 - 49, 50 - 59, 60 - 69, 70 - 79, 80 - 89, 90 - 99
foreach ($faceclaims_database_age_string as $fd_age_start) {

    $fd_age_end = $fd_age_start + 9;

    if($mybb->input['filters'] == "$fd_age_start-$fd_age_end") {

        $type_url = htmlspecialchars_uni("faceclaims_database.php?filters=$fd_age_start-$fd_age_end");
 
        add_breadcrumb("$fd_age_start bis $fd_age_end Jahre alte Avatarpersonen");  

        $filter_title = "$fd_age_start bis $fd_age_end Jahre alte";

        if ($faceclaims_database_age_limit == 1) {
            $year_start = $aktuellesJahr - $fd_age_start - $faceclaims_database_age_limit_number; 
            $year_end = $aktuellesJahr - $fd_age_end - $faceclaims_database_age_limit_number;
        } else {
            $year_start = $aktuellesJahr - $fd_age_start; 
            $year_end = $aktuellesJahr - $fd_age_end;
        }

        $countfaceclaims = $db->fetch_field($db->query("SELECT COUNT(fdid) AS faceclaims FROM ".TABLE_PREFIX."faceclaims_database  
        WHERE birthday BETWEEN '$year_end' AND '$year_start' AND accepted != '0'"), "faceclaims");

        if ($countfaceclaims == 1) {
            $lang->faceclaims_database_faceclaim_count = $lang->sprintf($lang->faceclaims_database_faceclaim_count, $countfaceclaims, 'Avatarperson');
        } else {
            $lang->faceclaims_database_faceclaim_count = $lang->sprintf($lang->faceclaims_database_faceclaim_count, $countfaceclaims, 'Avatarpersonen');
        }

        // MULTIPAGE
        $perpage = $faceclaims_database_multipage_show_setting;
        $page = intval($mybb->input['page']);
        if($page) {
            $start = ($page-1) *$perpage;
        }
        else {
            $start = 0;
            $page = 1;
        }

        $end = $start + $perpage;
        $lower = $start+1;
        $upper = $end;
        if($upper > $countfaceclaims) {
            $upper = $countfaceclaims;	
        }

        if ($faceclaims_database_multipage_setting == 1) {
            $multipage = multipage($countfaceclaims, $perpage, $page, $type_url);
        } else {
            $multipage = "";	
        }

        // ABFRAGE ALLER AVATARPERSONEN - MULTIPAGE
        if ($faceclaims_database_multipage_setting == 1) {
            $faceclaims_haircolor =$db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd
            WHERE birthday BETWEEN '$year_end' AND '$year_start'
            AND accepted != '0'
            ORDER BY faceclaim ASC
            LIMIT $start, $perpage
            ");
        } 
        // ABFRAGE ALLER AVATARPERSONEN - OHNE MULTIPAGE
        else {
            $faceclaims_haircolor =$db->query("SELECT * FROM ".TABLE_PREFIX."faceclaims_database fd
            WHERE birthday BETWEEN '$year_end' AND '$year_start'
            AND accepted != '0'
            ORDER BY faceclaim ASC       
            ");
        }

        eval("\$fd_faceclaim_none .= \"".$templates->get("faceclaims_database_faceclaim_none")."\";");
         
        while ($faceclaim = $db->fetch_array ($faceclaims_haircolor)) {

            $fd_faceclaim_none = "";
    
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
            $fdid = $faceclaim['fdid'];
            $image = "<img src=".$faceclaim['image']." style=\"width: 125px;\" / >";
            $name = $faceclaim['faceclaim'];
            $gender = $faceclaim['gender'];
            $birthday = $faceclaim['birthday'];
            $origin = $faceclaim['origin'];
            $haircolor = $faceclaim['haircolor'];
            $special1 = $faceclaim['special'];
            $mediabase1 = $faceclaim['mediabase'];
            $age = $aktuellesJahr - $birthday;
            $minage = $age - $faceclaims_database_age_limit_number;
            $maxage = $age + $faceclaims_database_age_limit_number;
    
            if ($faceclaims_database_age_limit == 1) {
                $age_limit = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_age_limit, $minage, $maxage);
            } else {
                $age_limit = "";
            }

            if ($faceclaim['special'] != '') {
                $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special, $special1);
            } else {
                $special = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_special_none);
            }
    
            if ($faceclaims_database_mediabase == 1) {

                if ($faceclaim['mediabase'] != '') {
                    $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase, $mediabase1);
                } else {
                    $mediabase = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_mediabase_none);
                }
    
                eval("\$fd_mediabase .= \"".$templates->get("faceclaims_database_faceclaim_bit_mediabase")."\";");
            } else {
                $fd_mediabase = "";
            }

            if ($faceclaims_database_gallery == 1) {

                if ($faceclaim['gallery'] != '') {
                    $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery, $faceclaim['gallery']);
                } else {
                    $gallery = $lang->sprintf($lang->faceclaims_database_faceclaim_bit_gallery_none);
                }
    
                eval("\$fd_gallery .= \"".$templates->get("faceclaims_database_faceclaim_bit_gallery")."\";");
            } else {
                $fd_gallery = "";
            }
    
            // Vergeben & Reserviert 
            if ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims == 1) {
                if (strpos($claim_name, $faceclaim['faceclaim']) !== FALSE OR strpos($reserved_name, $faceclaim['faceclaim']) !== FALSE) {
                    $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }
            } 
            // Nur Vergeben
            elseif ($faceclaims_database_awarded_faceclaims == 1 && $faceclaims_database_reserved_faceclaims != 1) {
                if (strpos($claim_name, $faceclaim['faceclaim']) !== FALSE) {
                    $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }
            } 
            // Nur Reserviert
            elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims == 1) {
                if (strpos($reserved_name, $faceclaim['faceclaim']) !== FALSE) {
                    $awarded = 'style="opacity: 0.5;"';
                } else {
                    $awarded = '';
                }
            } 
            // Gar nicht verblasst
            elseif ($faceclaims_database_awarded_faceclaims != 1 && $faceclaims_database_reserved_faceclaims != 1) {
                $awarded = '';
            } 

            if ($mybb->usergroup['canmodcp'] == '1') {
    
                $edit = "<a href=\"faceclaims_database.php?action=faceclaim_edit&fdid={$fdid}\">{$lang->faceclaims_database_faceclaim_edit}</a>";
                $delete = "<a href=\"{$type_url}&delete={$fdid}\">{$lang->faceclaims_database_faceclaim_delete}</a>";
    
                eval("\$fd_teamoption .= \"".$templates->get("faceclaims_database_faceclaim_bit_teamoption")."\";");
            } else {
                $fd_teamoption = "";
            }
    
            eval("\$fd_faceclaim_bit .= \"".$templates->get("faceclaims_database_faceclaim_bit")."\";");
        }

        // AVATARPERSON LÖSCHEN
        $delete = $mybb->input['delete'];
        if($delete) {
            $db->delete_query("faceclaims_database", "fdid = '$delete'");
            redirect("$type_url", $lang->faceclaims_database_redirect_delete);
        }
       
        eval("\$page = \"".$templates->get("faceclaims_database_faceclaim_filters")."\";");
        output_page($page);
        die();   
    }

}

?>
