<?php
/**
 * This file implements the Goal form.
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 *
 * @copyright (c)2003-2008 by Francois PLANQUE - {@link http://fplanque.com/}
 *
 * @package admin
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author fplanque: Francois PLANQUE.
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * @var Goal
 */
global $edited_Goal;

// Determine if we are creating or updating...
global $action;
$creating = is_create_action( $action );


$Form = & new Form( NULL, 'goal_checkchanges', 'post', 'compact' );

$Form->global_icon( T_('Delete this goal!'), 'delete', regenerate_url( 'action', 'action=delete' ) );
$Form->global_icon( T_('Cancel editing!'), 'close', regenerate_url( 'action' ) );

$Form->begin_form( 'fform', $creating ?  T_('New goal') : T_('Goal') );

	$Form->hidden_ctrl();
	$Form->hidden( 'action', $creating ? 'create' : 'update' );

	if( ! $creating ) $Form->hidden( 'goal_ID', $edited_Goal->ID );

	$Form->text_input( 'goal_name', $edited_Goal->name, 40, T_('Name'), '', array( 'maxlength'=> 50, 'required'=>true ) );

	$Form->text_input( 'goal_key', $edited_Goal->key, 32, T_('Key'), T_('Should be URL friendly'), array( 'required'=>true ) );

	$Form->text_input( 'goal_redir_url', $edited_Goal->redir_url, 60, T_('Redirection URL'), '', array( 'maxlength'=> 255, 'class'=>'large' ) );

	$Form->text_input( 'goal_default_value', $edited_Goal->default_value, 15, T_('Default value'), '' );

if( $creating )
{
	$Form->end_form( array( array( 'submit', 'submit', T_('Record'), 'SaveButton' ),
													array( 'submit', 'submit', T_('Record, then Create New'), 'SaveButton' ),
													array( 'submit', 'submit', T_('Record, then Create Similar'), 'SaveButton' ),
													array( 'reset', '', T_('Reset'), 'ResetButton' ) ) );
}
else
{
	$Form->end_form( array( array( 'submit', 'submit', T_('Update'), 'SaveButton' ),
													array( 'reset', '', T_('Reset'), 'ResetButton' ) ) );
}


/*
 * $Log$
 * Revision 1.1  2008/04/17 11:53:20  fplanque
 * Goal editing
 *
 */
?>