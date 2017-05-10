/**
 * This file is used for widgets designer mode
 */

jQuery( document ).on( 'mouseover', '.evo_widget', function()
{	// Initialize and Show widget designer block:

	// To be sure all previous designer blocks are hidden before show new one except of processing widgets:
	jQuery( '.evo_container__designer_block, .evo_widget__designer_block:not(.wdb_process):not(.wdb_failed)' ).hide();

	var widget = jQuery( this );
	var designer_block_selector = evo_widget_designer_block_selector( widget );
	var container_block_selector = evo_widget_container_block_selector( widget );
	if( jQuery( designer_block_selector ).length )
	{	// Just display a designer block if it already has been initialized on previous time:
		evo_widget_update_designer_position( widget );
		return;
	}

	if( jQuery( '.evo_widget__designer_blocks' ).length == 0 )
	{	// Create wrapper for all widget designer blocks:
		jQuery( 'body' ).append( '<div class="evo_widget__designer_blocks"></div>' );
	}

	// Initialize a container designer block only first time:
	if( ! jQuery( container_block_selector ).length )
	{	// Only if it was not create for another widget from the same container:
		jQuery( '.evo_widget__designer_blocks' ).append( '<div class="evo_container__designer_block" data-name="' + widget.data( 'container' ) + '">' +
				'<div><div class="evo_container__designer_name">' + widget.data( 'container' ) + '</div></div>' +
			'</div>' );
	}

	// Initialize a widget designer block only first time:
	jQuery( '.evo_widget__designer_blocks' ).append( '<div class="evo_widget__designer_block" data-id="' + widget.data( 'id' ) + '" data-container="' + widget.data( 'container' ) + '">' +
			'<div><div class="evo_widget__designer_type">' + widget.data( 'type' ) + '</div></div>' +
		'</div>' );
	evo_widget_update_designer_position( widget );
	if( widget.data( 'can-edit' ) == '1' &&
	    ( widget.next( '.evo_widget' ).length || widget.prev( '.evo_widget' ).length ) )
	{	// Display a panel with actions if current user has a permission to edit widget:
		jQuery( '>div', designer_block_selector ).append( '<div class="evo_widget__designer_actions">' +
				b2evo_widget_icon_up +
				b2evo_widget_icon_down +
			'</div>' );
		if( widget.next( '.evo_widget' ).length == 0 )
		{	// Hide action icon to move widget down if it is the last widget in container:
			jQuery( designer_block_selector ).find( '.evo_widget__designer_move_down' ).hide();
		}
		if( widget.prev( '.evo_widget' ).length == 0 )
		{	// Hide action icon to move widget up if it is the first widget in container:
			jQuery( designer_block_selector ).find( '.evo_widget__designer_move_up' ).hide();
		}
	}
} );

jQuery( document ).on( 'mouseover', '.evo_widget__designer_block', function()
{	// Show container designer block:
	var widget = jQuery( evo_widget_selector( jQuery( this ) ) );
	var container_block = jQuery( evo_widget_container_block_selector( widget ) );
	if( ! container_block.is( ':visible' ) )
	{
		container_block.show();
	}
} );

jQuery( document ).on( 'click', '.evo_widget__designer_block', function( e )
{	// Link to edit widget:
	if( jQuery( e.target ).hasClass( 'evo_widget__designer_move_up' ) ||
	    jQuery( e.target ).hasClass( 'evo_widget__designer_move_down' ) )
	{	// Ignore if click is on order action icons:
		return;
	}
	if( typeof( b2evo_widget_edit_url ) != 'undefined' )
	{	// If global widget edit form url is defined:
		var widget_ID = jQuery( this ).data( 'id' );
		var widget = jQuery( evo_widget_selector( jQuery( this ) ) );
		if( widget.length && widget.data( 'can-edit' ) == '1' )
		{	// Redirect to widget edit form only if it is allowed for current user:
			location.href = b2evo_widget_edit_url.replace( '$wi_ID$', widget_ID );
		}
	}
} );

jQuery( document ).on( 'mouseout', '.evo_widget__designer_block', function( e )
{	// Hide widget designer block:
	if( ! jQuery( e.toElement ).closest( '.evo_widget__designer_block' ).length )
	{	// Hide it only when cursor is really out of designer block:
		evo_widget_hide_designer_block( jQuery( this ) );
	}
} );

jQuery( document ).on( 'click', '.evo_widget__designer_move_up, .evo_widget__designer_move_down', function()
{	// Change an order of widget:
	var designer_block = jQuery( this ).closest( '.evo_widget__designer_block' );
	var widget = jQuery( evo_widget_selector( designer_block ) );
	var order_type = jQuery( this ).hasClass( 'evo_widget__designer_move_up' ) ? 'up' : 'down';

	// Mark current widget with process class:
	designer_block.removeClass( 'wdb_failed' ).addClass( 'wdb_process' );

	// Change an order of the widget with near widget:
	evo_widget_reorder( widget, order_type );

	var widgets_ids = [];
	widget.parent().find( '.evo_widget[data-id]' ).each( function()
	{
		widgets_ids.push( jQuery( this ).data( 'id' ) );
	} );

	jQuery.ajax(
	{
		type: 'POST',
		url: htsrv_url + 'async.php',
		data: {
			'blog': b2evo_widget_blog,
			'crumb_widget': b2evo_widget_crumb,
			'action': 'reorder_widgets',
			'container': widget.data( 'container' ),
			'widgets': widgets_ids,
		},
		success: function( result )
		{	// If order has been updated successfully:
			result = ajax_debug_clear( result );
			if( result != '' )
			{	// Error:
				evo_widget_display_order_error( result, widget, order_type );
			}
			else
			{	// Success:
				designer_block.removeClass( 'wdb_process wdb_failed' ).addClass( 'wdb_success' );
				evo_widget_update_designer_position( widget );
				setTimeout( function()
				{
					evo_widget_hide_designer_block( designer_block );
					designer_block.removeClass( 'wdb_success' );
				}, 500 );
			}
		},
		error: function( jqXHR, textStatus, errorThrown )
		{	// Display error text on error request:
			evo_widget_display_order_error( 'There was an error communicating with the server. Please reload the page to be in sync with the server. (' + textStatus + ': ' + errorThrown + ')', widget, order_type );
		}
	} );
} );


/*jQuery( window ).scroll( function()
{	// Update position of designer block
	jQuery( '.evo_widget__designer_block[data-id]:visible' ).each( function()
	{
		evo_widget_update_designer_position( jQuery( evo_widget_selector( jQuery( this ) ) ) );
	} );
} );*/


/**
 * Get jQuery selector for widget by designer block
 *
 * @param object Designer block
 * @returns string
 */
function evo_widget_selector( designer_block )
{
	return '.evo_widget[data-id=' + designer_block.data( 'id' ) + ']';
}


/**
 * Get jQuery selector for designer block by widget
 *
 * @param object Widget
 * @returns string
 */
function evo_widget_designer_block_selector( widget )
{
	return '.evo_widget__designer_block[data-id=' + widget.data( 'id' ) + ']';
}


/**
 * Get jQuery selector for container designer block by widget
 *
 * @param object Widget
 * @returns string
 */
function evo_widget_container_block_selector( widget )
{
	return '.evo_container__designer_block[data-name="' + widget.data( 'container' ) + '"]';
}


/**
 * Display an error after failed widget reorder action
 * 
 * @param string Error message
 * @param object Widget
 * @param string Order direction: 'up', 'down'
 */
function evo_widget_display_order_error( error, widget, order_type )
{
	jQuery( evo_widget_designer_block_selector( widget ) ).removeClass( 'wdb_process' ).addClass( 'wdb_failed' );
	alert( error );
	// Revert widget order back:
	evo_widget_reorder( widget, order_type == 'up' ? 'down' : 'up' );
}


/**
 * Change an order of the widget with near widget in same container
 *
 * @param object Widget
 * @param string Order direction: 'up', 'down'
 */
function evo_widget_reorder( widget, direction )
{
	if( direction == 'up' )
	{	// Move HTML of the widget before previous widget:
		var near_widget = widget.prev();
		near_widget.before( widget );
	}
	else
	{	// Move HTML of the widget after next widget:
		var near_widget = widget.next();
		near_widget.after( widget );
	}

	// Update visibility of up/down action icons of first/last widgets:
	var container_widgets = widget.parent().find( '.evo_widget' );
	var widget_num = 1;
	container_widgets.each( function()
	{
		var designer_block = jQuery( '.evo_widget__designer_block[data-id=' + jQuery( this ).data( 'id' ) + ']' );
		if( designer_block.length )
		{	// If designer block is initialized:
			designer_block.find( '.evo_widget__designer_move_up, .evo_widget__designer_move_down' ).show();
			if( widget_num == 1 )
			{	// Hide action icon to move widget up for the first widget in container:
				designer_block.find( '.evo_widget__designer_move_up' ).hide();
			}
			else if( widget_num == container_widgets.length )
			{	// Hide action icon to move widget up for the last widget in container:
				designer_block.find( '.evo_widget__designer_move_down' ).hide();
			}
		}
		widget_num++;
	} );

	evo_widget_update_designer_position( widget );
	evo_widget_update_designer_position( near_widget );
}


/**
 * Update position of widget designer block
 *
 * @param object Widget
 */
function evo_widget_update_designer_position( widget )
{
	jQuery( evo_widget_designer_block_selector( widget ) )
		.css( {
			'top': widget.offset().top - 3,
			'left': widget.offset().left - 3,
			'width': widget.width() + 6,
			'height': widget.height() + 6,
		} )
		.show();

	// Also update container position:
	var container = widget.closest( '.evo_container' );
	jQuery( evo_widget_container_block_selector( widget ) )
		.css( {
			'top': container.offset().top - 6,
			'left': container.offset().left - 6,
			'width': container.width() + 13,
			'height': container.height() + 13,
		} )
		.show();
}


/**
 * Hide widget designer block
 *
 * @param object Designer block
 */
function evo_widget_hide_designer_block( designer_block )
{
	if( ! designer_block.hasClass( 'wdb_process' ) )
	{	// Hide only when widget is not in process:
		if( ! designer_block.hasClass( 'wdb_failed' ) )
		{
			designer_block.hide();
		}
		// Also hide container designer block:
		if( ! jQuery( '.evo_widget__designer_block[data-container="' + designer_block.data( 'container' ) + '"]:not(.wdb_failed):visible' ).length )
		{	// If it has no visible other designer blocks:
			var widget = jQuery( evo_widget_selector( designer_block ) );
			var container_block = jQuery( evo_widget_container_block_selector( widget ) );
			container_block.hide();
		}
	}
}