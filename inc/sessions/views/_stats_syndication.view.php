<?php
/**
 * This file implements the UI view for the syndication stats.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2016 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package admin
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

global $blog, $cgrp_ID, $admin_url, $rsc_url, $AdminUI, $agent_type_color;

echo '<h2 class="page-title">'.T_('Hits from RSS/Atom feed readers - Summary').get_manual_link( 'feed-hits-summary' ).'</h2>';

echo '<p class="notes">'.T_('Any user agent accessing the XML feeds will be flagged as an XML reader.').'</p>';

$SQL = new SQL( 'Get RSS/Atom feed readers hits summary' );
$SQL->SELECT( 'SQL_NO_CACHE COUNT(*) AS hits, EXTRACT(YEAR FROM hit_datetime) AS year,
	EXTRACT(MONTH FROM hit_datetime) AS month, EXTRACT(DAY FROM hit_datetime) AS day' );
$SQL->FROM( 'T_hitlog' );
$SQL->WHERE( 'hit_type = "rss"' );
if( ! empty( $cgrp_ID ) )
{	// Filter by collection group:
	$SQL->FROM_add( 'LEFT JOIN T_blogs ON hit_coll_ID = blog_ID' );
	$SQL->WHERE_and( 'blog_cgrp_ID = '.$cgrp_ID );
}
if( $blog > 0 )
{	// Filter by collection:
	$SQL->WHERE_and( 'hit_coll_ID = ' . $blog );
}
$SQL->GROUP_BY( 'year, month, day' );
$SQL->ORDER_BY( 'year DESC, month DESC, day DESC' );
$res_hits = $DB->get_results( $SQL->get(), ARRAY_A, $SQL->title );


/*
 * Chart
 */
if( count($res_hits) )
{
	// Initialize params to filter by selected collection and/or group:
	$coll_group_params = empty( $blog ) ? '' : '&blog='.$blog;
	$coll_group_params .= empty( $cgrp_ID ) ? '' : '&cgrp_ID='.$cgrp_ID;

	$last_date = 0;

	$chart[ 'chart_data' ][ 0 ] = array();
	$chart[ 'chart_data' ][ 1 ] = array();

	$chart['dates'] = array();

	// Initialize the data to open an url by click on bar item
	$chart['link_data'] = array();
	$chart['link_data']['url'] = $admin_url.'?ctrl=stats&tab=hits&datestartinput=$date$&datestopinput=$date$'.$coll_group_params.'&hit_type=$param1$';
	$chart['link_data']['params'] = array(
			array( 'rss' )
		);

	$count = 0;
	foreach( $res_hits as $row_stats )
	{
		$this_date = mktime( 0, 0, 0, $row_stats['month'], $row_stats['day'], $row_stats['year'] );
		if( $last_date != $this_date )
		{ // We just hit a new day, let's display the previous one:
			$last_date = $this_date;	// that'll be the next one
			$count ++;
			array_unshift( $chart[ 'chart_data' ][ 0 ], date( 'D '.locale_datefmt(), $last_date ) );
			array_unshift( $chart[ 'chart_data' ][ 1 ], 0 );

			array_unshift( $chart['dates'], $last_date );
		}
		$chart [ 'chart_data' ][1][0] = $row_stats['hits'];
	}

	array_unshift( $chart[ 'chart_data' ][ 0 ], '' );
	array_unshift( $chart[ 'chart_data' ][ 1 ], T_('XML (RSS/Atom) hits') );	// Translations need to be UTF-8

	$chart[ 'series_color' ] = array (
			$agent_type_color['rss'],
		);

	$chart[ 'canvas_bg' ] = array( 'width'  => 780, 'height' => 355 );

	echo '<div class="center">';
	load_funcs('_ext/_canvascharts.php');
	CanvasBarsChart( $chart );
	echo '</div>';

}

?>