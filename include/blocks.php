<?php

# IMPORTANT: Do not edit below unless you know what you are doing!
if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

	function render_blocks($blockfile, $blocktitle, $content, $bid, $bposition, $allow_hide) {
	global $showbanners, $allow_block_hide;
	global $foot;
		if ($blockfile != "") {
			if (file_exists ("blocks/".$blockfile."")) {
				if (!defined('BLOCK_FILE'))
					define('BLOCK_FILE', 1);
				require ("blocks/".$blockfile."");
			} else {
				$content = "<center>���������� �������� � ���� ������!</center>";
			}
		}

		if (!((isset ($content) AND !empty ($content)))) {
			$content = "<center>���������� �������� � ���� ������!</center>";
		}

		if ($allow_block_hide && ($allow_hide || get_user_class() >= UC_ADMINISTRATOR)) {
			$hidden_blocks = (isset($_COOKIE['hb']) && !empty($_COOKIE['hb']) ? unserialize($_COOKIE['hb']) : array());
			$display = 'block';
			$picture = 'minus';
			$alt = '������';
			if (in_array($bid, $hidden_blocks)) {
				$display = 'none';
				$picture = 'plus';
				$alt = '��������';
			}
			$blocktitle = $blocktitle . '&nbsp;<span style="cursor: pointer;" onclick="javascript: block_switch(\''.$bid.'\');"><img border="0" src="pic/'.$picture.'.gif" id="picb'.$bid.'" title="'.$alt.'"></span>';
			$content = '<span id="sb'.$bid.'" style="display: '.$display.';">' . $content . '</span>';
		}

		themesidebox($blocktitle, $content, $bposition);
		return null;
}

function themesidebox($title, $content, $pos) {
	global $blockfile, $b_id, $ss_uri;
	static $bl_mass;
	$func = 'echo';
	$func2 = '';
	if ($pos == "s" || $pos == "o") {
		if (empty($blockfile)) {
			$bl_name = "fly-block-".$b_id;
		} else {
			$bl_name = "fly-".str_replace(".php", "", $blockfile);
		}
	} else {
		if (empty($blockfile)) {
			$bl_name = "block-".$b_id;
		} else {
			$bl_name = str_replace(".php", "", $blockfile);
		}
	}
	if (!isset($bl_mass[$bl_name])) {
		if (file_exists("themes/".$ss_uri."/html/".$bl_name.".html")) {
			$bl_mass[$bl_name]['m'] = true;
		} else {
			$bl_mass[$bl_name]['m'] = false;
		}
	}
	if ($bl_mass[$bl_name]['m']) {
		$thefile = addslashes(file_get_contents("themes/".$ss_uri."/html/".$bl_name.".html"));
		$thefile = "\$r_file=\"".$thefile."\";";
		eval($thefile);
		if ($pos == "o") {
			return $r_file;
		} else {
			echo $r_file;
		}
	} else {
		switch($pos) {
			case 'l':
			$bl_name ="block-left";
			break;
			case 'r':
			$bl_name ="block-right";
			break;
			case 'c':
			$bl_name ="block-center";
			break;
			case 'd':
			$bl_name ="block-down";
			break;
			case 's':
			$bl_name ="block-fly";
			break;
			case 'o':
			$func='return(';
			$func2=')';
			$bl_name ="block-fly";
			break;
			default:
			$bl_name ="block-all";
			break;
		}
		if (!isset($bl_mass[$bl_name])) {
			if (file_exists("themes/".$ss_uri."/html/".$bl_name.".html")) {
				$bl_mass[$bl_name]['m'] = true;
				$f_str = file_get_contents("themes/".$ss_uri."/html/".$bl_name.".html");
				$f_str = 'global $ss_uri, $tracker_lang; '.$func.' "'.addslashes($f_str)." \"".$func2.";";
				$bl_mass[$bl_name]['f'] = create_function('$title, $content', $f_str);
			} else {
				$bl_mass[$bl_name]['m'] = false;
			}
		}
		if ($bl_mass[$bl_name]['m']) {
			if ($pos == "o") {
				return $bl_mass[$bl_name]['f']($title, $content);
			} else {
				$bl_mass[$bl_name]['f']($title, $content);
			}
		} else {
			$bl_name = 'block-all';
			if (!isset($bl_mass[$bl_name])) {
				if (file_exists("themes/".$ss_uri."/html/".$bl_name.".html")) {
					$bl_mass[$bl_name]['m'] = true;
					$f_str = file_get_contents("themes/".$ss_uri."/html/".$bl_name.".html");
					$f_str = 'global $ss_uri, $tracker_lang; '.$func.' "'.addslashes($f_str)." \"".$func2.";";
					$bl_mass[$bl_name]['f'] = create_function('$title, $content', $f_str);
				} else {
					$bl_mass[$bl_name]['m'] = false;
				}
			}
			if ($bl_mass[$bl_name]['m']) {
				if ($pos == "o") {
					return $bl_mass[$bl_name]['f']($title, $content);
				} else {
					$bl_mass[$bl_name]['f']($title, $content);
				}
			} else {
				echo "<fieldset><legend>".$title."</legend>".$content."</fieldset>";
			}
		}
	}
}

$orbital_blocks = array();

function show_blocks($position) {
	global $CURUSER, $use_blocks, $already_used, $orbital_blocks;
	static $showed_show_hide;

	if ($use_blocks) {

		if (!$already_used) {
			$blocks_res = sql_query("SELECT * FROM orbital_blocks WHERE active = 1 ORDER BY weight ASC") or sqlerr(__FILE__,__LINE__);
			while ($blocks_row = mysql_fetch_array($blocks_res))
				$orbital_blocks[] = $blocks_row;
			if (!$orbital_blocks)
				$orbital_blocks = array();
			$already_used = true;
		}

		foreach ($orbital_blocks as $block) {
			if (!$showed_show_hide)
				echo '<script language="javascript" type="text/javascript" src="js/show_hide.js"></script>';
			$showed_show_hide = true;
			$bid = $block["bid"];
			$content = $block["content"];
			$title = $block["title"];
			$blockfile = $block["blockfile"];
			$bposition = $block["bposition"];
			$allow_hide = $block["allow_hide"] == 'yes';
			if ($position != $bposition)
				continue;
			$view = $block["view"];
			$which = explode(",", $block["which"]);
			$module_name = str_replace(".php", "", basename($_SERVER["PHP_SELF"]));
			if (!(in_array($module_name, $which) || in_array("all", $which) || (in_array("ihome", $which) && $module_name == "index"))) {
				continue;
			}
			if ($view == 0) {
				render_blocks($blockfile, $title, $content, $bid, $bposition, $allow_hide);
			} elseif ($view == 1 && $CURUSER) {
				render_blocks($blockfile, $title, $content, $bid, $bposition, $allow_hide);
			} elseif ($view == 2 && (get_user_class() >= UC_MODERATOR)) {
				render_blocks($blockfile, $title, $content, $bid, $bposition, $allow_hide);
			} elseif ($view == 3 && (!$CURUSER || get_user_class() >= UC_MODERATOR)) {
				render_blocks($blockfile, $title, $content, $bid, $bposition, $allow_hide);
			}
		}
	}
}

?>