<?php
	/**
	 * This is the main template. It displays the blog.
	 *
	 * However this file is not meant to be called directly.
	 * It is meant to be called automagically by b2evolution.
	 * To display a blog, you should call a stub file instead, for example:
	 * /blogs/index.php or /blogs/blog_b.php
	 *
	 * b2evolution - {@link http://b2evolution.net/}
	 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
	 * @copyright (c)2003-2004 by Francois PLANQUE - {@link http://fplanque.net/}
	 *
	 * @package evoskins
	 * @subpackage custom
	 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

skin_content_header();	// Sets charset!
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php locale_lang() ?>" lang="<?php locale_lang() ?>">
<head>
	<?php skin_content_meta(); /* Charset for static pages */ ?>
	<?php $Plugins->trigger_event( 'SkinBeginHtmlHead' ); ?>
	<title><?php
		$Blog->disp('name', 'htmlhead');
		request_title( ' - ', '', ' - ', 'htmlhead' );
	?>
	</title>
	<?php skin_base_tag(); /* Base URL for this skin. You need this to fix relative links! */ ?>
	<meta name="description" content="<?php $Blog->disp( 'shortdesc', 'htmlattr' ); ?>" />
	<meta name="keywords" content="<?php $Blog->disp( 'keywords', 'htmlattr' ); ?>" />
	<meta name="generator" content="b2evolution <?php echo $app_version ?>" /> <!-- Please leave this for stats -->
	<link rel="alternate" type="text/xml" title="RSS 2.0" href="<?php $Blog->disp( 'rss2_url', 'raw' ) ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom" href="<?php $Blog->disp( 'atom_url', 'raw' ) ?>" />
	<link rel="stylesheet" href="rsc/styles.css" type="text/css" />
</head>
<body>
<div id="wrapper">

<?php
	/**
	 * --------------------------- BLOG LIST INCLUDED HERE -----------------------------
	 */
	require( dirname(__FILE__).'/_bloglist.php' );
	// ----------------------------- END OF BLOG LIST ---------------------------- ?>

<div class="pageHeader">

<h1 id="pageTitle"><?php $Blog->disp( 'name', 'htmlbody' ) ?></h1>

<div class="pageSubTitle"><?php $Blog->disp( 'tagline', 'htmlbody' ) ?></div>

</div>

<div class="bPosts">
<?php request_title( '<h2>', '</h2>' ) ?>

<!-- =================================== START OF MAIN AREA =================================== -->

<?php // ------------------------------------ START OF POSTS ----------------------------------------
	if( isset($MainList) ) $MainList->display_if_empty(); // Display message if no post

	if( isset($MainList) ) while( $Item = & $MainList->get_item() )
	{
		$MainList->date_if_changed();
	?>
	<div class="bPost" lang="<?php $Item->lang() ?>">
		<?php
			locale_temp_switch( $Item->locale ); // Temporarily switch to post locale
			$Item->anchor(); // Anchor for permalinks to refer to
		?>
		<div class="bSmallHead">
		<?php
			$Item->permanent_link( '#icon#' );
			echo ' ';
			$Item->issue_time();
			echo ', ', T_('Categories'), ': ';
			$Item->categories();
			echo ' &nbsp; ';
		?>
		</div>
		<h3 class="bTitle"><?php $Item->title(); ?></h3>
		<div class="bText">
			<?php $Item->content(); ?>
			<?php
				// Links to post pages (for multipage posts):
				$Item->page_links( '<p class="right">'.T_('Pages:').' ', '</p>', ' &middot; ' );
			?>
		</div>
		<div class="bSmallPrint">
			<?php $Item->permanent_link(); ?>
			<?php $Item->feedback_link( 'comments', ' &bull; ' ) // Link to comments ?>
			<?php $Item->feedback_link( 'trackbacks', ' &bull; ' ) // Link to trackbacks ?>

			<?php $Item->edit_link( ' &bull; ' ) // Link to backoffice for editing ?>

			<?php $Item->trackback_rdf() // trackback autodiscovery information ?>
		</div>
			<?php // ------------- START OF INCLUDE FOR COMMENTS, TRACKBACK, PINGBACK, ETC. -------------
			$disp_comments = 1;					// Display the comments if requested
			$disp_comment_form = 1;			// Display the comments form if comments requested
			$disp_trackbacks = 1;				// Display the trackbacks if requested

			$disp_trackback_url = 1;		// Display the trackbal URL if trackbacks requested
			$disp_pingbacks = 0;        // Don't display the pingbacks (deprecated)
			require( dirname(__FILE__).'/_feedback.php' );
			// ---------------- END OF INCLUDE FOR COMMENTS, TRACKBACK, PINGBACK, ETC. ----------------

			locale_restore_previous();	// Restore previous locale (Blog locale)
			?>
	</div>
	<div class="separator" ><img src="rsc/img/separator.gif" width="265" height="14" alt="" /></div>
<?php } // ---------------------------------- END OF POSTS ------------------------------------ ?>

	<p class="center"><strong>
		<?php
			// Links to list pages:
			if( isset($MainList) ) $MainList->page_links( '<p class="center"><strong>', '</strong></p>', '$prev$ :: $next$', array(
   				'prev_text' => '&lt;&lt; '.T_('Previous'),
   				'next_text' => T_('Next').' &gt;&gt;',
				) );
		?>
		<?php
			// previous_post( '<p class="center">%</p>' );
			// next_post( '<p class="center">%</p>' );
		?>
	</strong></p>

	<?php
		// -------------- START OF INCLUDES FOR LAST COMMENTS, MY PROFILE, ETC. --------------
		// Note: you can customize any of the sub templates included here by
		// copying the matching php file into your skin directory.
		$current_skin_includes_path = dirname(__FILE__).'/';
		// Call the dispatcher:
		require $skins_path.'_dispatch.inc.php';
		// --------------- END OF INCLUDES FOR LAST COMMENTS, MY PROFILE, ETC. ---------------
	?>

</div>
<!-- =================================== START OF SIDEBAR =================================== -->

<div class="bSideBar">

	<?php 
		// -------------------------- CALENDAR INCLUDED HERE -----------------------------
		// Call the Calendar plugin:
		$Plugins->call_by_code( 'evo_Calr', array(	// Params follow:
				'title'=>'',			// No title.
			) );
		// ----------------------------- END OF CALENDAR --------------------------------- 
	?>

	<div class="bSideItem">
		<h3><?php $Blog->disp( 'name', 'htmlbody' ) ?></h3>
		<p><?php $Blog->disp( 'longdesc', 'htmlbody' ); ?></p>
		<ul>
			<li><a href="<?php $Blog->disp( 'dynurl', 'raw' ) ?>"><strong><?php echo T_('Recently') ?></strong></a></li>
			<li><a href="<?php $Blog->disp( 'arcdirurl', 'raw' ) ?>"><strong><?php echo T_('Archives') ?></strong></a></li>
			<li><a href="<?php $Blog->disp( 'lastcommentsurl', 'raw' ) ?>"><strong><?php echo T_('Last comments') ?></strong></a></li>
		</ul>
	</div>

	<div class="bSideItem">
		<h3 class="sideItemTitle"><?php echo T_('Search') ?></h3>
		<?php form_formstart( $Blog->dget( 'blogurl', 'raw' ), 'search', 'SearchForm' ) ?>
			<p><input type="text" name="s" size="30" value="<?php echo htmlspecialchars($s) ?>" class="SearchField" /><br />
			<input type="radio" name="sentence" value="AND" id="sentAND" <?php if( $sentence=='AND' ) echo 'checked="checked" ' ?>/><label for="sentAND"><?php echo T_('All Words') ?></label><br />
			<input type="radio" name="sentence" value="OR" id="sentOR" <?php if( $sentence=='OR' ) echo 'checked="checked" ' ?>/><label for="sentOR"><?php echo T_('Some Word') ?></label><br />
			<input type="radio" name="sentence" value="sentence" id="sentence" <?php if( $sentence=='sentence' ) echo 'checked="checked" ' ?>/><label for="sentence"><?php echo T_('Entire phrase') ?></label></p>
			<input type="submit" name="submit" class="submit" value="<?php echo T_('Search') ?>" />
		</form>
	</div>


	<?php // -------------------------- CATEGORIES INCLUDED HERE -----------------------------
		// Call the Categories plugin:
		$Plugins->call_by_code( 'evo_Cats', array(	// Add parameters below:
			) );
		// -------------------------------- END OF CATEGORIES ---------------------------------- ?>


	<?php // -------------------------- ARCHIVES INCLUDED HERE -----------------------------
		// Call the Archives plugin:
		$Plugins->call_by_code( 'evo_Arch', array(	// Add parameters below:
			) );
		// -------------------------------- END OF ARCHIVES ---------------------------------- ?>


	<?php if( ! $Blog->get('force_skin') )
	{	// Skin switching is allowed for this blog: ?>
		<div class="bSideItem">
			<h3><?php echo T_('Choose skin') ?></h3>
			<ul>
				<?php // ------------------------------- START OF SKIN LIST -------------------------------
				for( skin_list_start(); skin_list_next(); ) { ?>
					<li><a href="<?php skin_change_url() ?>"><?php skin_list_iteminfo( 'name', 'htmlbody' ) ?></a></li>
				<?php } // ------------------------------ END OF SKIN LIST ------------------------------ ?>
			</ul>
		</div>
	<?php } ?>

	<div class="bSideItem">
		<h3><?php echo T_('Misc') ?></h3>
		<ul>
			<?php
				user_login_link( '<li>', '</li>' );
				user_register_link( '<li>', '</li>' );
				user_admin_link( '<li>', '</li>' );
				user_profile_link( '<li>', '</li>' );
				user_subs_link( '<li>', '</li>' );
				user_logout_link( '<li>', '</li>' );
			?>
		</ul>
	</div>

<div class="bSideItem">
		<h3><img src="<?php echo $rsc_url ?>icons/feed-icon-16x16.gif" width="16" height="16" class="top" alt="" /> <?php echo T_('XML Feeds') ?></h3>
			<ul>
				<li>
					RSS 0.92:
					<a href="<?php $Blog->disp( 'rss_url', 'raw' ) ?>"><?php echo T_('Posts') ?></a>,
					<a href="<?php $Blog->disp( 'comments_rss_url', 'raw' ) ?>"><?php echo T_('Comments') ?></a>
				</li>
				<li>
					RSS 1.0:
					<a href="<?php $Blog->disp( 'rdf_url', 'raw' ) ?>"><?php echo T_('Posts') ?></a>,
					<a href="<?php $Blog->disp( 'comments_rdf_url', 'raw' ) ?>"><?php echo T_('Comments') ?></a>
				</li>
				<li>
					RSS 2.0:
					<a href="<?php $Blog->disp( 'rss2_url', 'raw' ) ?>"><?php echo T_('Posts') ?></a>,
					<a href="<?php $Blog->disp( 'comments_rss2_url', 'raw' ) ?>"><?php echo T_('Comments') ?></a>
				</li>
				<li>
					Atom:
					<a href="<?php $Blog->disp( 'atom_url', 'raw' ) ?>"><?php echo T_('Posts') ?></a>,
					<a href="<?php $Blog->disp( 'comments_atom_url', 'raw' ) ?>"><?php echo T_('Comments') ?></a>
				</li>
			</ul>

		<a href="http://webreference.fr/2006/08/30/rss_atom_xml" title="External - English">What is this?</a>

</div>
<p class="center">powered by<br />
<a href="http://b2evolution.net/" title="b2evolution home"><img src="<?php echo $rsc_url; ?>img/b2evolution_logo_80.gif" alt="b2evolution" width="80" height="17" border="0" class="middle" /></a></p>

</div>

<div class="clear"><img src="<?php echo $rsc_url; ?>img/blank.gif" width="1" height="1" alt="" /></div>

<div id="pageFooter">
	<p class="baseline">
		Original <a href="http://b2evolution.net/">b2evolution</a> template design by <a href="http://severinelandrieu.com/">S&eacute;verine LANDRIEU</a> &amp; <a href="http://fplanque.net/">Fran&ccedil;ois PLANQUE</a>.
	</p>
  <p class="baseline">
    Credits: <a href="http://skinfaktory.com/">skin makers</a>
	<?php
		// Display additional credits (see /conf/_advanced.php):
 		// If you can add your own credits without removing the defaults, you'll be very cool :))
		// Please leave this at the bottom of the page to make sure your blog gets listed on b2evolution.net
		display_list( $credit_links, '|', '', '|', ' ', ' ' );
  ?>
  </p>
  <?php
		$Hit->log();	// log the hit on this page
		debug_info(); // output debug info if requested
	?>
</div>
</div>
</body>
</html>