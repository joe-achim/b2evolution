/**
 *	Server communication functions
 *
 * Ajax without the pain
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
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
 * @package main
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author yabs {@link http://innervisions.org.uk/ }
 *
 * @version $Id$
 */


/**
 * Init : adds required elements to the document tree
 *
 */
jQuery(document).ready(function()
{
	jQuery( '<div id="server_messages"></div>' ).prependTo( '.pblock' );// placeholder for error/success messages
	jQuery( '<iframe id="server_postback" name="server_postback"></iframe>' ).appendTo( 'body' ); // used for POST requests
	jQuery( '#server_postback' ).css( { position:'absolute',left:"-1000em",top:"-1000em" } );
});


/**
 * Sends a javascript request to admin
 *
 * @param string ctrl Admin control to send request to
 * @param string action Action to take
 * @param string query_string Any extra data
 */
function SendAdminRequest( ctrl, action, query_string )
{
	SendServerRequest( b2evo_dispatcher_url + '?ctrl='+ctrl+'&action='+action+( query_string ? '&'+query_string : '' ) );
}


/**
 * Sends a javascript request to the server
 *
 * @param string the url to request
 */
function SendServerRequest( url )
{
	// add a & to the URL if we already have a query string, otherwise add a ?
	url += ( url.indexOf( '?' ) != -1 ) ? '&' : '?';

	var the_call = document.createElement( 'script' ); // create script element
	the_call.src = url+'display_mode=js'; // add flag for js display mode
	the_call.type = 'text/javascript'; // to be sure to be sure
	document.body.appendChild( the_call ); // add script to body and let browser do the rest
}


/**
 * Send a forms request as javascript request
 *
 * @param string DOM ID of form to attach to
 */
function AttachServerRequest( whichForm )
{
	jQuery( '<input type="hidden" name="display_mode" value="js" /><input type="hidden" name="js_target" value="window.parent." />' ).appendTo( '#' + whichForm );	// add our inputs
	jQuery( '#'+whichForm ).attr( 'target', 'server_postback' ); // redirect form via hidden iframe
}


/**
 * Displays Messages ( @see Log::display() )
 *
 * @param string message The html to display
 */
function DisplayServerMessages( messages )
{	// display any server messages and highlight them
	jQuery( '#server_messages' ).html( messages );
	// highlight success message
	jQuery( '#server_messages .log_success' ).animate({
			backgroundColor: "#88ff88"
		},"fast" ).animate({
			backgroundColor: "#ffffff"
		},"fast", "", function(){jQuery( this ).removeAttr( "style" );
	});
	// highlight error message
	jQuery( '#server_messages > .log_error' ).animate({
			backgroundColor: "#ff8888"
		},"fast" ).animate({
			backgroundColor: "#ffffff"
		},"fast", "", function(){jQuery( this ).removeAttr( "style" );
	});
}


/**
 * Potential replacement code ( once finished )
 *
 */

var _b2evoCommunications = function()
{
	var me; // reference to self

	var _delay_default = 2500; // default buffer delay in milli seconds

	var _decrement = 250; // delay buffer decrement

	return {
		/**
		 * Initialise the helper object
		 * Adds any translation strings found in html
		 *
		 * @param delay (int) buffered server call delay in milliseconds
		 */
		Init:function()
		{
			// set available params to defaults
			var params = jQuery.fn.extend({
				// no comma after final entry or IE barfs
				delay:_delay_default
				}, ( arguments.length ? arguments[0] : '' ) );

		_delay_default = params.delay; // change default delay if required
			me = this; // set reference to self

			b2evoHelper.info( 'Communications object ready' );
		},


		/**
		 * Enables server calls to be buffered
		 *
		 * @param ticker_callback (function) Called each time ticker occurs
		 * @param send_callback (function) Called when send event occurs
		 * @param buffer_delay (int) initial delay for buffer : defaults to _delay_default
		 * @param buffer_name (string) name for the buffer
		 */
		BufferedServerCall:function()
		{
			// set available params to defaults
			var params = jQuery.fn.extend({
					// no comma after final entry or IE barfs
					ticker_callback: function(){ return true; }, // callback for ticker checks
					send_callback: function(){}, // callback for sending
					buffer_delay: _delay_default, // time to buffer call for
					buffer_name:'' // name for the buffer
					}, ( arguments.length ? arguments[0] : '' ) );

			if( ticker_status = params.ticker_callback( params.buffer_delay ) )
			{
				if( ticker_status !== true )
				{
					b2evoHelper.log( 'Ticker status : '+ticker_status );
				}
				switch( ticker_status )
				{
					case 'cancel' : // cancel the server call
						b2evoHelper.DisplayMessage( '<div class="log_message">'+b2evoHelper.T_( 'Update canceled' )+'</div>' );
						return;

					case 'pause' : // pause the server call
						b2evoHelper.DisplayMessage( '<div class="log_message">'+b2evoHelper.T_( 'Update Paused' )+' : '+b2evoHelper.str_repeat( '.', params.buffer_delay / _decrement )+'</div>' );
						me.BufferedServerLoop( params );
						return;

					default :
						params.buffer_delay -= _decrement;
						if( params.buffer_delay > 0 )
						{ // still buffered
							b2evoHelper.DisplayMessage( '<div class="log_message">'+b2evoHelper.T_( 'Changes pending' )+' : '+b2evoHelper.str_repeat( '.', params.buffer_delay / _decrement )+'</div>' );
							me.BufferedServerLoop(params);
							return;
						}
						// send the call;
						b2evoHelper.DisplayMessage( '<div class="log_message">'+b2evoHelper.T_( 'Saving changes' )+'</div>' );
						params.send_callback();
				}
			}
		},


		/**
		 * Callback to add params to relevant buffer so we can use timeout
		 *
		 * @param params (mixed) parameters from @func BufferedServerCall
		 */
		BufferedServerLoop:function( params )
		{
			var current_buffers = jQuery( me ).data( 'buffers' );
			if( typeof( current_buffers ) == 'undefined' )
			{
				current_buffers = Array();
			}
			current_buffers[ params.buffer_name ] = params; // store params
			jQuery( me ).data( 'buffers', current_buffers );
			window.setTimeout( 'b2evoCommunications.BufferedServerCallback( "'+params.buffer_name+'" )', _decrement );
		},


		/**
		 * Callback for timeout, calls @func BufferedServerCall with relevant params
		 *
		 * @param buffer_key (string) name of the buffer
		 */
		BufferedServerCallback:function( buffer_key )
		{
			var current_buffers = jQuery( me ).data( 'buffers' );
			me.BufferedServerCall( current_buffers[ buffer_key ] );
		}
	}
} // _b2evoCommunications

// create instance of the communications object
var b2evoCommunications = new _b2evoCommunications();


/*
 * $Log$
 * Revision 1.3  2009/02/18 09:57:51  yabs
 * Updating drag n drop
 *
 */
