<?php
/**
 *
 *
 */
if( !defined('EVO_CONFIG_LOADED') ) die( 'Please, do not access this page directly.' );


/**
 * Map of filenames for icons and their respective alt tag.
 *
 * @global array icon name => array( 'file', 'alt', 'size', 'class', 'rollover' )
 */
$map_iconfiles = array(
	'folder' => array(        // icon for folders
		'file' => $rsc_subdir.'icons/fileicons/folder.gif',
		'alt' => T_('Folder'),
		'size' => array( 16, 15 ),
	),
	'file_unknown' => array(  // icon for unknown files
		'file' => $rsc_subdir.'icons/fileicons/default.png',
		'alt' => T_('Unknown file'),
		'size' => array( 16, 16 ),
	),
	'file_empty' => array(    // empty file
		'file' => $rsc_subdir.'icons/fileicons/empty.png',
		'alt' => T_('Empty file'),
		'size' => array( 16, 16 ),
	),

	'folder_parent' => array( // go to parent directory
		'file' => $rsc_subdir.'icons/up.gif',
		'alt' => T_('Parent folder'),
		'size' => array( 16, 15 ),
	),
	'folder_home' => array(   // home folder
		'file' => $rsc_subdir.'icons/folder_home2.png',
		'alt' => T_('Home folder'),
		'size' => array( 16, 16 ),
	),

	'file_edit' => array(     // edit a file
		'file' => $rsc_subdir.'icons/edit.png',
		'alt' => T_('Edit'),
		'size' => array( 16, 16 ),
	),
	'file_copy' => array(     // copy a file/folder
		'file' => $rsc_subdir.'icons/filecopy.png',
		'alt' => T_('Copy'),
		'size' => array( 16, 16 ),
	),
	'file_move' => array(     // move a file/folder
		'file' => $rsc_subdir.'icons/filemove.png',
		'alt' => T_('Move'),
		'size' => array( 16, 16 ),
	),
	'file_rename' => array(   // rename a file/folder
		'file' => $rsc_subdir.'icons/filerename.png',
		'alt' => T_('Rename'),
		'size' => array( 16, 16 ),
	),
	'file_delete' => array(   // delete a file/folder
		'file' => $rsc_subdir.'icons/filedelete.png',
		'alt' => T_('Del'),
		'legend'=>T_('Delete'),
		'size' => array( 16, 16 ),
	),
	'file_perms' => array(    // edit permissions of a file
		'file' => $rsc_subdir.'icons/fileperms.gif',
		'alt' => T_('Permissions'),
		'size' => array( 16, 16 ),
	),


	'ascending' => array(     // sort ascending
		'file' => $rsc_subdir.'icons/ascending.png',
		'alt' => T_('ascending'),
		'size' => array( 16, 16 ),
	),
	'descending' => array(    // sort descending
		'file' => $rsc_subdir.'icons/descending.png',
		'alt' => T_('descending'),
		'size' => array( 16, 16 ),
	),
	'window_new' => array(    // open in a new window
		'file' => $rsc_subdir.'icons/window_new.png',
		'alt' => T_('New window'),
		'size' => array( 15, 13 ),
	),


	'file_word' => array(
		'ext' => '\.(s[txd]w|doc|rtf)',
		'file' => $rsc_subdir.'icons/fileicons/wordprocessing.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_image' => array(
		'ext' => '\.(gif|png|jpe?g)',
		'file' => $rsc_subdir.'icons/fileicons/image2.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_www' => array(
		'ext' => '\.html?',
		'file' => $rsc_subdir.'icons/fileicons/www.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_log' => array(
		'ext' => '\.log',
		'file' => $rsc_subdir.'icons/fileicons/log.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_sound' => array(
		'ext' => '\.(mp3|ogg|wav)',
		'file' => $rsc_subdir.'icons/fileicons/sound.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_video' => array(
		'ext' => '\.(mpe?g|avi)',
		'file' => $rsc_subdir.'icons/fileicons/video.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_message' => array(
		'ext' => '\.msg',
		'file' => $rsc_subdir.'icons/fileicons/message.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_document' => array(
		'ext' => '\.pdf',
		'file' => $rsc_subdir.'icons/fileicons/pdf-document.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_php' => array(
		'ext' => '\.php[34]?',
		'file' => $rsc_subdir.'icons/fileicons/php.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_encrypted' => array(
		'ext' => '\.(pgp|gpg)',
		'file' => $rsc_subdir.'icons/fileicons/encrypted.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_tar' => array(
		'ext' => '\.tar',
		'file' => $rsc_subdir.'icons/fileicons/tar.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_tgz' => array(
		'ext' => '\.tgz',
		'file' => $rsc_subdir.'icons/fileicons/tgz.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_document' => array(
		'ext' => '\.te?xt',
		'file' => $rsc_subdir.'icons/fileicons/document.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),
	'file_pk' => array(
		'ext' => '\.(zip|rar)',
		'file' => $rsc_subdir.'icons/fileicons/pk.png',
		'alt' => '',
		'size' => array( 16, 16 ),
	),


	'collapse' => array(
		'file' => $img_subdir.'collapse.gif',
		'alt' => T_('Close'),
		'size' => array( 16, 16 ),
	),
	'expand' => array(
		'file' => $img_subdir.'expand.gif',
		'alt' => T_('Open'),
		'size' => array( 16, 16 ),
	),
	'reload' => array(
		'file' => $img_subdir.'reload.png',
		'alt' => T_('Reload'),
		'size' => array( 16, 16 ),
	),
	'download' => array(
		'file' => $img_subdir.'download_manager.png',
		'alt' => T_('Download'),
		'size' => array( 16, 16 ),
	),


	'warning' => array(
		'file' => $rsc_subdir.'icons/warning.png',
		'alt' => T_('Warning'),
		'size' => array( 16, 16 ),
	),

	'email' => array(
		'file' => $rsc_subdir.'icons/envelope.gif',
		'alt' => T_('Email'),
		'size' => array( 13, 10 ),
	),
	'www' => array(   /* user's web site, plugin's help url */
		'file' => $rsc_subdir.'icons/url.gif',
		'alt' => T_('WWW'),
		'size' => array( 34, 17 ),
	),

	'new' => array(
		'file' => $rsc_subdir.'icons/new.gif',
		'alt' => T_('New'),
		'size' => array( 16, 15 ),
	),
	'copy' => array(
		'file' => $rsc_subdir.'icons/copy.gif',
		'alt' => T_('Copy'),
		'size' => array( 14, 15 ),
	),
	'edit' => array(
		'file' => $rsc_subdir.'icons/edit.gif',
		'alt' => T_('Edit'),
		'size' => array( 16, 15 ),
	),
	'properties' => array(
		'file' => $admin_subdir.'img/properties.png',
		'alt' => T_('Properties'),
		'size' => array( 18, 13 ),
	),
	'publish' => array(
		'file' => $rsc_subdir.'icons/publish.gif',
		'alt' => T_('Publish'),
		'size' => array( 12, 15 ),
	),
	'deprecate' => array(
		'file' => $rsc_subdir.'icons/deprecate.gif',
		'alt' => T_('Deprecate'),
		'size' => array( 12, 15 ),
	),
	'locate' => array(
		'file' => $rsc_subdir.'icons/target.gif',
		'alt' => T_('Locate'),
		'size' => array( 15, 15 ),
	),
	'delete' => array(
		'file' => $rsc_subdir.'icons/delete.gif',
		'alt' => T_('Del'),
		'legend' => T_('Delete'),
		'size' => array( 15, 15 ),
	),
	'close' => array(
		'file' => $admin_subdir.'img/close.gif',
		'rollover' => true,
		'alt' => T_('Close'),
		'size' => array( 14, 14 ),
	),

	'assign' => array(
		'file' => $rsc_subdir.'icons/handpoint13.gif',
		'alt' => T_('Assigned to'),
		'size' => array( 27, 13 ),
	),

 	'arrow_up' => array(
		'file' => $admin_subdir.'img/arrowup.png',
		'alt' => T_('+'),
		'size' => array( 14, 14 ),
	),
 	'arrow_down' => array(
		'file' => $admin_subdir.'img/arrowdown.png',
		'alt' => T_('-'),
		'size' => array( 14, 14 ),
	),

	'activate' => array(
		'file' => $admin_subdir.'img/bullet_activate.png',
		'alt' => T_('Act.'),
		'legend' => T_('Activate'),
		'size' => array( 17, 17 ),
	),
 	'deactivate' => array(
		'file' => $admin_subdir.'img/bullet_deactivate.png',
		'alt' => T_('Deact.'),
		'legend' => T_('Deactivate'),
		'size' => array( 17, 17 ),
	),

	'link' => array(
		'file' => $admin_subdir.'img/chain_link.gif',
		'alt' => T_('Link'),
		'size' => array( 14, 14 ),
	),
 	'unlink' => array(
		'file' => $rsc_subdir.'icons/chain_unlink.gif',
		'alt' => T_('Unlink'),
		'size' => array( 14, 14 ),
	),

	'calendar' => array(
		'file' => $rsc_subdir.'icons/calendar.gif',
		'alt' => T_('Calendar'),
		'size' => array( 16, 15 ),
	),

	'parent_childto_arrow' => array(
		'file' => $rsc_subdir.'icons/parent_childto_arrow.png',
		'alt' => T_('+'),
		'size' => array( 14, 17 ),
	),

	'help' => array(
		'file' => $img_subdir.'smilies/icon_question.gif',
		'alt' => T_('Help'),
		'size' => array( 15, 15 ),
	),
	'webhelp' => array(
		'file' => $img_subdir.'smilies/icon_help.gif',
		'alt' => T_('Help'),
		'size' => array( 15, 15 ),
	),
	'permalink' => array(
		'file' => $rsc_subdir.'icons/minipost.gif',
		'alt' => T_('Permalink'),
		'size' => array( 12, 9 ),
	),
	'history' => array(
		'file' => $rsc_subdir.'icons/clock.png',
		'alt' => T_('History'),
		'size' => array( 15, 15 ),
	),

	'file_allowed' => array(
		'file' => $rsc_subdir.'icons/unlocked.gif',
		'alt' => T_( 'Allowed' ),
		'size' => array( 16, 14 ),
	),
 	'file_not_allowed' => array(
		'file' => $rsc_subdir.'icons/locked.gif',
		'alt' => T_( 'Blocked' ),
		'size' => array( 16, 14 ),
	),

	'comments' => array(
		'file' => $rsc_subdir.'icons/comments.gif',
		'alt' => T_('Comments'),
		'size' => array( 15, 16 ),
	),
	'nocomment' => array(
		'file' => $rsc_subdir.'icons/nocomment.gif',
		'alt' => T_('Comments'),
		'size' => array( 15, 16 ),
	),

	'move_up' => array(
		'file' => $rsc_subdir.'icons/move_up.gif',
		'rollover' => true,
		'alt' => T_( 'Up' ),
		'size' => array( 12, 13 ),
	),
 	'move_down' => array(
		'file' => $rsc_subdir.'icons/move_down.gif',
		'rollover' => true,
		'alt' => T_( 'Down'),
		'size' => array( 12, 13 ),
	),
	'nomove_up' => array(
		'file' => $rsc_subdir.'icons/nomove_up.gif',
		'alt' => T_( 'Sort by order' ),
		'size' => array( 12, 13 ),
	),
	'nomove_down' => array(
		'file' => $rsc_subdir.'icons/nomove_down.gif',
		'alt' => T_( 'Sort by order' ),
		'size' => array( 12, 13 ),
	),
	 	'nomove' => array(
		'file' => $rsc_subdir.'icons/nomove.gif',
		'size' => array( 12, 13 ),
	),
		
	'check_all' => array(
		'file' => $rsc_subdir.'icons/check_all.gif',
		'alt'	 => T_('Check all'),
		'size' => array( 17, 17 ),
	),
	'uncheck_all' => array(
		'file' => $rsc_subdir.'icons/uncheck_all.gif',
		'alt'	 => T_('Uncheck all'),
		'size' => array( 17, 17 ),
	),
	
	'reset_filters' => array(
		'file' => $rsc_subdir.'icons/reset_filter.gif',
		'alt'	 => T_('Reset all filters'),
		'size' => array( 16, 16 ),
	),
);

?>