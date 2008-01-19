<?php
/**
 * This file implements the UI view for the user group properties.
 *
 * Called by {@link b2users.php}
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2007 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2004-2006 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * {@internal License choice
 * - If you have received this file as part of a package, please find the license.txt file in
 *   the same folder or the closest folder above for complete license terms.
 * - If you have received this file individually (e-g: from http://evocms.cvs.sourceforge.net/)
 *   then you must choose one of the following licenses before using the file:
 *   - GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *   - Mozilla Public License 1.1 (MPL) - http://www.opensource.org/licenses/mozilla1.1.php
 * }}
 *
 * {@internal Open Source relicensing agreement:
 * Daniel HAHLER grants Francois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package admin
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author fplanque: Francois PLANQUE
 * @author blueyed: Daniel HAHLER
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


/**
 * @var Group
 */
global $edited_Group;

global $action;

// Begin payload block:
$this->disp_payload_begin();


$Form = & new Form( NULL, 'group_checkchanges' );

$Form->global_icon( T_('Cancel editing!'), 'close', regenerate_url( 'grp_ID,action' ) );

if( $edited_Group->ID == 0 )
{
	$Form->begin_form( 'fform', T_('Creating new group') );
}
else
{
	$title = ( $action == 'edit_user' ? T_('Editing group:') : T_('Viewing group:') )
						.' '.
						( isset($edited_grp_oldname) ? $edited_grp_oldname : $edited_Group->dget('name') )
						.' ('.T_('ID').' '.$edited_Group->ID.')';
	$Form->begin_form( 'fform', $title );
}

$Form->hidden_ctrl();
$Form->hidden( 'action', 'groupupdate' );
$Form->hidden( 'grp_ID', $edited_Group->ID );

$perm_none_option = array( 'none', T_('No Access') );
$perm_view_option = array( 'view', T_('View details') );
$perm_edit_option = array( 'edit', T_('Edit/delete all') );


$Form->begin_fieldset( T_('General') );

	$Form->text( 'edited_grp_name', $edited_Group->name, 50, T_('Name'), '', 50, 'large' );

 	if( $edited_Group->ID != 1 )
	{	// Groups others than #1 can be prevented from editing users
		$Form->radio( 'edited_grp_perm_admin', $edited_Group->get('perm_admin'),
				array(  $perm_none_option,
								array( 'hidden', T_('Hidden') ),
								array( 'visible', T_('Visible link') ) // TODO: this may be obsolete, especially now we have to evobar
							), T_('Access to Admin area'), false );
	}
	else
	{	// Group #1 always has user management right:
		$Form->info( T_('Access to Admin area'), T_('Visible link') );
	}

$Form->end_fieldset();

$Form->begin_fieldset( T_('Blogging permissions') );

	$Form->radio( 'edited_grp_perm_blogs', $edited_Group->get('perm_blogs'),
			array(  array( 'user', T_('User permissions') ),
							array( 'viewall', T_('View all') ),
							array( 'editall', T_('Full Access') )
						), T_('Blogs'), false );

	$Form->radio( 'perm_xhtmlvalidation', $edited_Group->get('perm_xhtmlvalidation'),
			array(  array( 'always', T_('Force valid XHTML'), T_('This option will allow for the most effective security checking.') ),
							array( 'never', T_('Disabled'), T_('Will only perform basic security checking. Only give this permission to trusted users.') )
						), T_('XHTML validation'), true );

	$Form->radio( 'perm_xhtmlvalidation_xmlrpc', $edited_Group->get('perm_xhtmlvalidation_xmlrpc'),
			array(  array( 'always', T_('Force valid XHTML'), T_('This option will allow for the most effective security checking.') ),
							array( 'never', T_('Disabled'), T_('Will only perform basic security checking. Only give this permission to trusted users.') )
						), T_('XHTML validation on XML-RPC calls'), true );

$Form->end_fieldset();

$Form->begin_fieldset( T_('Additional permissions') );

	$Form->radio( 'edited_grp_perm_stats', $edited_Group->get('perm_stats'),
			array(  $perm_none_option,
							array( 'user', T_('View stats for specific blogs'), T_('Based on each blog\'s edit permissions') ), // fp> dirty hack, I'll tie this to blog edit perm for now
							array( 'view', T_('View stats for all blogs') ),
							array( 'edit', T_('Full Access'), T_('Includes deleting/reassigning of stats') )
						), T_('Stats'), true );

	// fp> todo perm check
	$filetypes_linkstart = '<a href="?ctrl=filetypes" title="'.T_('Edit locked file types...').'">';
	$filetypes_linkend = '</a>';
	$Form->radio( 'edited_grp_perm_files', $edited_Group->get('perm_files'),
			array(	$perm_none_option,
							array( 'view', T_('View files for all allowed roots') ),
							array( 'add', T_('Add/Upload files to allowed roots') ),
							array( 'edit', sprintf( T_('Edit %sunlocked files'), $filetypes_linkstart.get_icon('file_allowed').$filetypes_linkend ) ),
							array( 'all', sprintf( T_('Edit all files, including %slocked ones'), $filetypes_linkstart.get_icon('file_not_allowed').$filetypes_linkend ), T_('Needed for editing PHP files in skins.') ),
						), T_('Files'), true, T_('This setting will further restrict any media file permissions on specific blogs.') );

$Form->end_fieldset();

$Form->begin_fieldset( T_('System admin permissions') );

	$Form->radio( 'edited_grp_perm_spamblacklist', $edited_Group->get('perm_spamblacklist'),
			array(  $perm_none_option,
							array( 'view', T_('View only') ),
							array( 'edit', T_('Full Access') )
						), T_('Antispam'), false );

	$Form->checkbox( 'edited_grp_perm_templates', $edited_Group->get('perm_templates'), T_('Skins'), T_('Check to allow access to skin files.') );

	if( $edited_Group->ID != 1 )
	{	// Groups others than #1 can be prevented from editing users
		$Form->radio( 'edited_grp_perm_users', $edited_Group->get('perm_users'),
				array(	$perm_none_option,
								$perm_view_option,
								$perm_edit_option
							), T_('Users & Groups') );
	}
	else
	{	// Group #1 always has user management right:
		$Form->info( T_('Users & Groups'), T_('Full Access') );
	}
	$Form->radio( 'edited_grp_perm_options', $edited_Group->get('perm_options'),
			array(	$perm_none_option,
							$perm_view_option,
							$perm_edit_option
						), T_('Settings') );

$Form->end_fieldset();

if( $action != 'view_group' )
{
	$Form->buttons( array(
		array( '', '', T_('Save !'), 'SaveButton' ),
		array( 'reset', '', T_('Reset'), 'ResetButton' ) ) );
}

$Form->end_form();

// End payload block:
$this->disp_payload_end();

/*
 * $Log$
 * Revision 1.2  2008/01/19 10:57:10  fplanque
 * Splitting XHTML checking by group and interface
 *
 * Revision 1.1  2007/06/25 11:01:50  fplanque
 * MODULES (refactored MVC)
 *
 * Revision 1.11  2007/04/26 00:11:13  fplanque
 * (c) 2007
 *
 * Revision 1.10  2007/03/20 09:53:26  fplanque
 * Letting boggers view their own stats.
 * + Letthing admins view the aggregate by default.
 *
 * Revision 1.9  2007/01/23 04:20:31  fplanque
 * wording
 *
 * Revision 1.8  2006/12/17 23:42:39  fplanque
 * Removed special behavior of blog #1. Any blog can now aggregate any other combination of blogs.
 * Look into Advanced Settings for the aggregating blog.
 * There may be side effects and new bugs created by this. Please report them :]
 *
 * Revision 1.7  2006/12/07 16:06:24  fplanque
 * prepared new file editing permission
 *
 * Revision 1.6  2006/11/24 18:27:26  blueyed
 * Fixed link to b2evo CVS browsing interface in file docblocks
 */
?>