<?php
/**
 * This file implements the Filemanager class.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2004 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2004 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 * {@internal
 * b2evolution is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * b2evolution is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with b2evolution; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * }}
 *
 * {@internal
 * Daniel HAHLER grants Fran�ois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package admin
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author blueyed: Daniel HAHLER.
 *
 * @version $Id$
 *
 * @todo Permissions!
 * @todo Performance
 * @todo favorite folders/bookmarks
 */
if( !defined('DB_USER') ) die( 'Please, do not access this page directly.' );

/**
 * Includes
 */
require_once dirname(__FILE__).'/_filelist.class.php';


/**
 * Extends {@link Filelist} and provides file management functionality.
 *
 * @package admin
 */
class FileManager extends Filelist
{
	// {{{ class variables
	/**
	 * root ('user', 'user_X' or 'blog_X' - X is id)
	 * @param string
	 */
	var $root;

	/**
	 * root directory
	 * @param string
	 */
	var $root_dir;

	/**
	 * root URL
	 * @param string
	 */
	var $root_url;

	/**
	 * current working directory
	 * @param string
	 */
	var $cwd;


	/**
	 * User preference: sort dirs not at top
	 * @var boolean
	 */
	var $dirsnotattop = false;

	/**
	 * User preference: show hidden files?
	 * @var boolean
	 */
	var $showhidden = true;

	/**
	 * User preference: show permissions like "ls -l" (true) or octal (false)?
	 * @var boolean
	 */
	var $permlikelsl = true;

	// --- going to user options ---
	var $default_chmod_file = 664;
	var $default_chmod_dir = 664;


	/* ----- PRIVATE ----- */
	/**
	 * order files by what? (name/path/type/size/lastmod/perms)
	 * 'name' as default.
	 * @var string
	 * @access protected
	 */
	var $order = NULL;

	/**
	 * files ordered ascending?
	 * NULL is default and means ascending for 'name', descending for the rest
	 * @var boolean
	 * @access protected
	 */
	var $orderasc = NULL;

	/**
	 * relative path
	 * @var string
	 * @access protected
	 */
	var $path = '';

	/**
	 * Remember the mode we're in ('file_cmr')
	 * @var string
	 * @access protected
	 */
	var $mode = NULL;


	/**
	 * Flat mode? (all files recursive without dirs)
	 * @var boolean
	 * @access protected
	 */
	var $flatmode = NULL;


	/**
	 * These are variables that get considered when regenerating a URL
	 * @var array
	 * @access private
	 */
	var $_internalGlobals = array(
			'root', 'path', 'filterString', 'filterIsRegexp', 'order', 'orderasc',
			'mode', 'source', 'keepsource', 'flatmode',
		);

	// }}}


	/**
	 * Constructor
	 *
	 * @param User the current User {@link User}}
	 * @param string the URL where the object is included (for generating links)
	 * @param string the root directory ('user', 'user_X', 'blog_X')
	 * @param string the dir of the Filemanager object (relative to root)
	 * @param string order files by what? (NULL means 'name')
	 * @param boolean order ascending or descending? NULL means ascending for 'name', descending for other
	 * @param string filter files by what?
	 * @param boolean is the filter a regular expression (default is glob pattern)
	 * @param boolean filter in subdirs (search)
	 */
	function FileManager( &$cUser, $url, $root, $path = '', $order = NULL, $asc = NULL,
												$filterString = NULL, $filterIsRegexp = NULL, $flatmode = NULL )
	{
		global $basepath, $baseurl, $media_subdir, $admin_subdir, $admin_url;
		global $mode;
		global $BlogCache, $UserCache;
		global $Debuglog;

		$this->User =& $cUser;
		$this->Messages =& new Log( 'error' );


		// {{{ -- get/translate root directory ----
		$this->root = $root;

		$root_A = explode( '_', $this->root );

		if( count($root_A) == 2 && $root_A[1] !== '' )
		{
			switch( $root_A[0] )
			{
				case 'blog':
					$tBlog = $BlogCache->get_by_ID( $root_A[1] );
					$this->root_dir = $tBlog->get( 'mediadir' );
					$this->root_url = $tBlog->get( 'mediaurl' );
					break;

				case 'user':
					$tUser = & $UserCache->get_by_ID($root_A[1]);
					$this->root_dir = $tUser->getMediaDir();
					$this->root_url = $tUser->getMediaUrl();
					break;
			}
		}
		else switch( $root_A[0] )
		{
			case NULL:
			case 'user':
				$this->root_dir = $this->User->getMediaDir();
				$this->root_url = $this->User->getMediaUrl();
				break;
		}

		list( $real_root_dir, $real_root_dir_exists ) = str2path( $this->root_dir );
		$Debuglog->add( 'real_root_dir: '.var_export( $real_root_dir, true ), 'filemanager' );

		if( !$this->root_dir )
		{
			$this->Messages->add( T_('No access to root directory.'), 'error' );
			$this->cwd = NULL;
		}
		elseif( !$real_root_dir_exists )
		{
			$this->Messages->add( sprintf( T_('The root directory [%s] does not exist.'), $this->root_dir ), 'error' );
			$this->cwd = NULL;
		}
		else
		{
			$this->cwd = trailing_slash( $this->root_dir.$path );
			// get real cwd

			list( $realpath, $realpath_exists ) = str2path( $this->cwd );


			if( !preg_match( '#^'.$this->root_dir.'#', $realpath ) )
			{ // cwd is not below root!
				$this->Messages->add( T_( 'You are not allowed to go outside your root directory!' ) );
				$this->cwd = $this->root_dir;
			}
			else
			{ // allowed
				if( !$realpath_exists )
				{ // does not exist
					$this->Messages->add( sprintf( T_('The directory [%s] does not exist.'), $this->cwd ) );
					$this->cwd = NULL;
				}
				else
				{
					$this->cwd = $realpath;
				}
			}
		}

		// get the subpath relative to root
		$this->path = preg_replace( '#^'.$this->root_dir.'#', '', $this->cwd );
		// }}}


		/**
		 * base URL, used for created links
		 * @var string
		 */
		$this->url = $url;
		/**
		 * Remember mode from passed global.
		 * @var string
		 */
		$this->mode = empty($mode) ? NULL : $mode;

		if( $this->mode
				&& $this->source = urldecode( param( 'source', 'string', '' ) ) )
		{
			$sourceName = basename( $this->source );
			$sourcePath = dirname( $this->source ).'/';
			if( $this->SourceList =& new Filelist( $sourcePath ) )
			{ // TODO: should fail for non-existant sources, or sources where no read-perm
				$this->SourceList->addFileByPath( $sourceName );
				$this->keepsource = param( 'keepsource', 'integer', 0 );
			}
			else
			{
				$this->mode = false;
			}
		}
		else
		{
			$this->SourceList = false;
			$this->source = NULL;
			$this->keepsource = NULL;
		}


		$this->filterString = $filterString;
		$this->filterIsRegexp = $filterIsRegexp;

		if( $this->filterIsRegexp && !is_regexp( $this->filterString ) )
		{
			$this->Messages->add( sprintf( T_('The filter [%s] is not a regular expression.'), $this->filterString ) );
			$this->filterString = '.*';
		}
		$this->order = ( in_array( $order, array( 'name', 'path', 'type', 'size', 'lastmod', 'perms' ) ) ? $order : NULL );
		$this->orderasc = ( $asc === NULL  ? NULL : (bool)$asc );

		$this->loadSettings();


		$Debuglog->add( 'root: '.var_export( $this->root, true ), 'filemanager' );
		$Debuglog->add( 'root_dir: '.var_export( $this->root_dir, true ), 'filemanager' );
		$Debuglog->add( 'root_url: '.var_export( $this->root_url, true ), 'filemanager' );
		$Debuglog->add( 'cwd: '.var_export( $this->cwd, true ), 'filemanager' );
		$Debuglog->add( 'path: '.var_export( $this->path, true ), 'filemanager' );

		$this->flatmode = $flatmode;


		$this->load();
	}


	/**
	 * Calls the parent constructor, loads and rewinds the filelist.
	 */
	function load()
	{
		parent::Filelist( $this->cwd );
		parent::load( $this->flatmode );
		parent::restart();

		#debug: pre_dump( $this->entries );
	}


	/**
	 * Sort the Filelist entries.
	 */
	function sort()
	{
		parent::sort( $this->translate_order( $this->order ),
									$this->translate_asc( $this->orderasc, $this->translate_order( $this->order ) ),
									!$this->dirsnotattop );
	}


	/**
	 * Display button (icon) to change to the parent directory.
	 */
	function dispButtonParent()
	{
		if( $link = $this->getLinkParent() )
		{
			echo '<a title="'.T_('Go to parent folder').'" href="'.$link.'">'
						.getIcon( 'folder_parent' ).'</a>';
		}
	}


	/**
	 * Display a button to edit a file.
	 *
	 * @param File the File, defaults to current.
	 */
	function dispButtonFileEdit( $File = NULL )
	{
		if( $File === NULL )
		{
			$File = $this->curFile;
		}
		if( $link = $this->getLinkFileEdit() )
		{
			echo '<a title="'.T_('Edit the file').'" href="'.$link.'">'.getIcon( 'file_edit' ).'</a>';
		}
	}


	/**
	 * Display a button to edit the permissions of the current file.
	 */
	function dispButtonFileEditPerms()
	{
		if( $link = $this->getLinkFileEditPerms() )
		{
			echo '<a title="'.T_('Edit permissions').'" href="'.$link.'">'
						.$this->curFile->getPerms( $this->permlikelsl ? 'lsl' : '' ).'</a>';
		}
	}


	/**
	 * Displays a button to enter upload mode.
	 *
	 * @param string title for the button
	 * @param string optional HTML attribs for the input button
	 */
	function dispButtonUploadMode( $title = NULL, $attribs = '' )
	{
		if( $title === NULL )
		{
			$title = T_('Upload a file/image');
		}

		$url = $this->getCurUrl( array( 'mode' => 'file_upload' ) );

		echo '<input class="ActionButton" type="button" value="'.format_to_output( $title, 'formvalue' )
					.'" onclick="'.$this->getJsPopupCode( $url, 'fileman_upload' )
					.'" '.$attribs.' />';
	}


	/**
	 * Stub for dispButtonUploadMode() until re-designed
	 */
	function dispButtonUpload( $title = NULL, $attribs = '' )
	{
		return $this->dispButtonUploadMode( $title, $attribs );
	}


	/**
	 * Display a button to a mode where we choose the destination (name and/or
	 * folder) of the file or folder.
	 *
	 * @param string 'copy', 'move' or 'rename'
	 */
	function dispButtonFileCopyMoveRename( $mode )
	{
		if( $mode != 'copy' && $mode != 'move' && $mode != 'rename' )
		{
			return false;
		}
		$url = $this->getCurUrl( array( 'mode' => 'file_cmr',
																		'source' => false,
																		'keepsource' => (int)($mode == 'copy') ) );
		$url .= '&amp;source='.urlencode( $this->curFile->getPath( true ) );

		echo '<a href="'.$url.'" target="fileman_copymoverename" onclick="'
					.$this->getJsPopupCode( $url, 'fileman_copymoverename' )
					.'" title="';
		switch( $mode )
		{
			case 'copy': echo T_('Copy'); break;
			case 'move': echo T_('Move'); break;
			case 'rename': echo T_('Rename'); break;
		}

		echo '">'.getIcon( 'file_'.$mode ).'</a>';
	}


	/**
	 * Display a button to copy the current File.
	 */
	function dispButtonFileCopy()
	{
		$this->dispButtonFileCopyMoveRename( 'copy' );
	}


	/**
	 * Display a button to move the current File.
	 */
	function dispButtonFileMove()
	{
		$this->dispButtonFileCopyMoveRename( 'move' );
	}


	/**
	 * Display a button to rename the current File.
	 */
	function dispButtonFileRename()
	{
		$this->dispButtonFileCopyMoveRename( 'rename' );
	}


	/**
	 * Display a button to delete a File. When the action is confirmed using
	 * Javascript the GET param confirmed gets appended and set to 1.
	 *
	 * @param File|NULL the File to delete
	 */
	function dispButtonFileDelete( $File = NULL )
	{
		if( $File === NULL )
		{
			$File = $this->curFile;
		}

		if( $url = $this->getLinkFileDelete( $File ) )
		{
			echo '<a title="'.T_('Delete').'" href="'.$url.'" onclick="if( confirm(\''
				.sprintf( /* TRANS: Warning this is a javascript string */ T_('Do you really want to delete [%s]?'),
				format_to_output( $File->getName(), 'formvalue' ) ).'\') )
				{
					this.href += \'&amp;confirmed=1\';
					return true;
				}
				else return false;">'.getIcon( 'file_delete' ).'</a>';
		}
	}


	/**
	 * Get current working directory.
	 *
	 * @return string the current working directory
	 */
	function getCwd()
	{
		return $this->cwd;
	}


	/**
	 * Get the path of the Filemanager, relative to root.
	 *
	 * @return string the path
	 */
	function getPath()
	{
		return $this->path;
	}


	/**
	 * Get current mode.
	 *
	 * @return string|false 'file_copy', 'file_move', 'file_rename'
	 */
	function getMode()
	{
		return $this->mode;
	}


	/**
	 * Get the current url, with all relevant GET params (root, path, filterString,
	 * filterIsRegexp, order, orderasc).
	 * Params can be overridden or be forced to
	 *
	 * @uses $_internalGlobals
	 * @param array override internal globals {@link $_internalGlobals}
	 * @return string the resulting URL
	 */
	function getCurUrl( $override = array() )
	{
		global $cache_fmCurUrls;

		$r = $this->url;

		$toAppend = array();
		foreach( $this->_internalGlobals as $check )
		{
			if( isset( $override[$check] ) )
			{
				if( $override[$check] === false )
				{ // don't include
					continue;
				}

				$toAppend[] = $check.'='.$override[$check];
			}
			elseif( $this->$check !== NULL )
			{
				$toAppend[] = $check.'='.$this->$check;
			}
		}
		$r = url_add_param( $r, implode( '&amp;', $toAppend ) );

		@$cache_fmCurUrls[$r]++;
		return $r;
	}


	/**
	 * Generates hidden input fields for forms, based on {@link getCurUrll()}
	 */
	function getFormHiddenInputs( $override = array() )
	{
		// get current Url, remove leading URL and '?'
		$params = preg_split( '/&amp;/',
														substr( $this->getCurUrl( $override ), strlen( $this->url )+1 ) );

		$r = '';
		foreach( $params as $lparam )
		{
			if( $pos = strpos($lparam, '=') )
			{
				$r .= '<input type="hidden" name="'.substr( $lparam, 0, $pos ).'" value="'.format_to_output( substr( $lparam, $pos+1 ), 'formvalue' ).'" />';
			}
		}

		return $r;
	}


	/**
	 * Get an array of available root directories.
	 *
	 * @return array of arrays for each root: array( type [blog/user], id, name )
	 */
	function getRootList()
	{
		global $BlogCache;

		$bloglist = $BlogCache->load_user_blogs( 'browse', $this->User->ID );

		$r = array();

		foreach( $bloglist as $blog_ID )
		{
			$Blog = & $BlogCache->get_by_ID( $blog_ID );

			$r[] = array( 'type' => 'blog',
											'id' => $blog_ID,
											'name' => $Blog->get( 'shortname' ) );
		}

		$r[] = array( 'type' => 'user',
										'name' => T_('My media folder') );

		return $r;
	}


	/**
	 *
	 */
	function getLinkSort( $type, $atext )
	{
		$r = '<a href="'
					.$this->getCurUrl( array( 'order' => $type,
																		'asc' => false ) );

		if( $this->order == $type )
		{ // change asc
			$r .= '&amp;asc='.(1 - $this->isSortingAsc());
		}

		$r .= '" title="'
					.( ($this->order == $type && !$this->isSortingAsc($type))
						|| ( $this->order != $type && $this->isSortingAsc($type) )
							? T_('Sort ascending by this column') : T_('Sort descending by this column')
					).'">'.$atext;

		if( $this->order == $type )
		{ // add asc/desc image
			$r .= ' '.( $this->isSortingAsc() ?
										getIcon( 'ascending' ) :
										getIcon( 'descending' ) );
		}

		return $r.'</a>';
	}


	/**
	 * get the next File in the Filelist
	 *
	 * @param string can be used to query only 'file's or 'dir's.
	 * @return boolean File object on success, false on end of list
	 */
	function &getNextFile( $type = '' )
	{
		$this->curFile = parent::getNextFile( $type );

		return $this->curFile;
	}


	/**
	 * Get the path (and name) of a {@link File} relative to the filelists
	 * root path.
	 *
	 * @param File a File object or NULL for current File
	 * @param boolean appended with name?
	 * @return string path (and optionally name)
	 */
	function getFileSubpath( &$File, $withname = true )
	{
		if( $File === NULL )
		{
			$File = $this->curFile;
		}

		$r = $this->path;

		if( $this->flatmode )
		{
			$r .= substr( $File->getPath($withname), strlen( $this->cwd ) );
		}
		else
		{
			$r .= $withname ? $File->getName() : '';
		}

		return $r;
	}


	/**
	 * Get the URL to access a file.
	 *
	 * @param File the File object
	 */
	function getFileUrl( $File = NULL )
	{
		if( $File === NULL )
		{
			$File = $this->curFile;
		}
		if( method_exists( $File, 'getName' ) )
		{
			return $this->root_url.$this->getFileSubpath($File);
		}
		else
		{
			return false;
		}
	}


	function getFileImageSize( $param = 'widthxheight', $File = NULL )
	{
		if( !$this->getImageSizes )
		{
			return false;
		}

		if( $File === NULL )
		{
			$File =& $this->curFile;
		}

		return $File->getImageSize( $param );

	}


	/**
	 * Get the link to access a file or folder.
	 *
	 * @param File file object
	 * @param boolean force link to a folder (default is to change into that folder).
	 */
	function getLinkFile( &$File, $folderAsParam = false )
	{
		if( $File->isDir() && !$folderAsParam )
		{
			if( !isset( $File->cache['linkFile_1'] ) )
			{
				$File->cache['linkFile_1'] = $this->getCurUrl( array( 'path' => $this->getFileSubpath($File) ) );
			}

			return $File->cache['linkFile_1'];
		}
		else
		{
			if( !isset( $File->cache['linkFile_2'] ) )
			{
				$File->cache['linkFile_2'] = $this->getCurUrl( array( 'path' => $this->getFileSubpath($File, false) ) )
							.'&amp;file='.urlencode( $File->getName() );
			}
			return $File->cache['linkFile_2'];
		}
	}


	function getLinkFileEditPerms()
	{
		return $this->getLinkFile( $this->curFile, true ).'&amp;action=editperm';
	}


	function getLinkFileEdit()
	{
		if( $this->curFile->isDir() )
		{
			return false;
		}
		return $this->getLinkFile( $this->curFile ).'&amp;action=edit';
	}


	/**
	 * Get link to rename a file or folder.
	 *
	 * @return string the URL
	 */
	function getLinkFileRename()
	{
		return $this->getLinkFile( NULL, true ).'&amp;action=rename';
	}


	/**
	 * Get link to delete a file or folder.
	 *
	 * @return string the URL
	 */
	function getLinkFileDelete( $File = NULL )
	{
		if( $File === NULL )
		{
			$File = $this->curFile;
		}
		return $this->getLinkFile( $File, true ).'&amp;action=delete';
	}


	/**
	 * Get the link to the parent folder
	 *
	 * @return mixed URL or false if in root
	 */
	function getLinkParent()
	{
		if( empty($this->path) )
		{ // cannot go higher
			return false;
		}
		return $this->getCurUrl( array( 'path' => $this->path.'..' ) );
	}


	/**
	 * Get the link to current root's home directory
	 */
	function getLinkHome()
	{
		return $this->getCurUrl( array( 'root' => 'user',
																		'path' => false ) );
	}


	/**
	 * do actions to a file/dir
	 *
	 * @param string filename (in cwd)
	 * @param string the action (chmod)
	 * @param string parameter for action
	 */
	function obsolete__cdo_file( $filename, $what, $param = '' )
	{
		if( $this->loadc( $filename ) )
		{
			$path = $this->cget( 'path' );
			switch( $what )
			{
				case 'send':
					if( is_dir($path) )
					{ // we cannot send directories!
						return false;
					}
					else
					{
						header('Content-type: application/octet-stream');
						//force download dialog
						header('Content-disposition: attachment; filename="' . $filename . '"');

						header('Content-transfer-encoding: binary');
						header('Content-length: ' . filesize($path));

						//send file contents
						readfile($path);
						exit;
					}
			}
		}
		else
		{
			$this->Messages->add( sprintf( T_('File [%s] not found.'), $filename ) );
			return false;
		}

		$this->restorec();
		return $r;
	}


	/**
	 * Remove a file or directory.
	 *
	 * @param string filename, defaults to current loop entry
	 * @param boolean delete subdirs of a dir?
	 * @return boolean true on success, false on failure
	 * @deprecated not used anymore
	 */
	function delete( $file = NULL, $delsubdirs = false )
	{
		// TODO: permission check

		if( $file == NULL )
		{ // use current entry
			if( isset($this->current_entry) )
			{
				$entry = $this->current_entry;
			}
			else
			{
				$this->Messages->add('delete: no current file!');
				return false;
			}
		}
		else
		{ // use a specific entry
			if( ($key = $this->getKeyByName( $file )) !== false )
			{
				$entry = $this->entries[$key];
			}
			else
			{
				$this->Messages->add( sprintf(T_('The file &laquo;%s&raquo; could not be found.'), $file) );
				return false;
			}
		}

		if( $entry['type'] == 'dir' )
		{
			if( $delsubdirs )
			{
				if( deldir_recursive( $this->cwd.'/'.$entry['name'] ) )
				{
					$this->Messages->add( sprintf( T_('The directory &laquo;%s&raquo; and its subdirectories have been deleted.'), $entry['name'] ), 'note' );
					return true;
				}
				else
				{
					$this->Messages->add( sprintf( T_('The directory &laquo;%s&raquo; could not be deleted.'), $entry['name'] ) );
					return false;
				}
			}
			elseif( @rmdir( $this->cwd.$entry['name'] ) )
			{
				$this->Messages->add( sprintf( T_('The directory &laquo;%s&raquo; has been deleted.'), $entry['name'] ), 'note' );
				return true;
			}
			else
			{
				$this->Messages->add( sprintf( T_('The directory &laquo;%s&raquo; could not be deleted (probably not empty).'), $entry['name'] ) );
				return false;
			}
		}
		else
		{
			if( unlink( $this->cwd.$entry['name'] ) )
			{
				$this->Messages->add( sprintf( T_('The file &laquo;%s&raquo; has been deleted.'), $entry['name'] ), 'note' );
				return true;
			}
			else
			{
				$this->Messages->add( sprintf( T_('The file &laquo;%s&raquo; could not be deleted.'), $entry['name'] ) );
				return false;
			}
		}
	}


	/**
	 * Creates a directory or file.
	 *
	 * @param string type; 'dir' or 'file'
	 * @param string name of the directory or file
	 * @param string path of the directory or file, defaults to cwd
	 * @param integer permissions for the new directory/file (octal format)
	 * @return false|File false on failure, File on success
	 */
	function createDirOrFile( $type, $name, $path = NULL, $chmod = NULL )
	{
		if( $type != 'dir' && $type != 'file' )
		{
			return false;
		}
		$path = $path === NULL ? $this->cwd : trailing_slash( $path );

		if( $chmod == NULL )
		{
			$chmod = $type == 'dir' ?
								$this->default_chmod_dir :
								$this->default_chmod_file;
		}

		if( empty($name) )
		{
			$this->Messages->add( $type == 'dir' ?
														T_('Cannot create empty directory.') :
														T_('Cannot create empty file.') );
			return false;
		}
		elseif( !isFilename($name) )
		{
			$this->Messages->add( sprintf( ($type == 'dir' ?
																			T_('[%s] is not a valid directory.') :
																			T_('[%s] is not a valid filename.') ), $name) );
			return false;
		}


		$newFile =& getFile( $name, $path );

		if( $newFile->exists() )
		{
			$this->Messages->add( sprintf( T_('The file [%s] already exists.'), $name ) );
			return false;
		}

		if( $newFile->create() )
		{
			if( $type == 'file' )
			{
				$this->Messages->add( sprintf( T_('File [%s] has been created.'), $name ), 'note' );
			}
			else
			{
				$this->Messages->add( sprintf( T_('Directory [%s] has been created.'), $name ), 'note' );
			}

			$this->addFile( $newFile );
		}
		else
		{
			if( $type == 'file' )
			{
				$this->Messages->add( sprintf( T_('Could not create file [%s] in [%s].'), $name, $path ) );
			}
			else
			{
				$this->Messages->add( sprintf( T_('Could not create directory [%s] in [%s].'), $name, $path ) );
			}
		}

		return $newFile;
	}


	/**
	 * Returns cwd, where the accessible directories (below root) are clickable
	 *
	 * @return string cwd as clickable html
	 */
	function getCwdClickable()
	{
		if( !$this->cwd )
		{
			return ' -- '.T_('No directory.').' -- ';
		}
		// not clickable
		$r = substr( $this->root_dir, 0, strrpos( substr($this->root_dir, 0, -1), '/' )+1 );

		// get the part that is clickable
		$clickabledirs = explode( '/', substr( $this->cwd, strlen($r) ) );

		$cd = '';
		foreach( $clickabledirs as $nr => $dir )
		{
			if( empty($dir) )
			{
				break;
			}
			if( $nr )
			{
				$cd .= $dir.'/';
			}
			$r .= '<a href="'.$this->getCurUrl( array( 'path' => $cd ) )
					.'" title="'.T_('Change to this directory').'">'.$dir.'/</a>';
		}

		return $r;
	}


	function getJsPopupCode( $href = NULL, $target = 'fileman_default',
														$width = NULL, $height = NULL )
	{
		if( $href === NULL )
		{
			$href = $this->getLinkFile( $this->curFile ).'&amp;mode=browse';
		}
		if( $width === NULL )
		{
			$width = 800;
		}
		if( $height === NULL )
		{
			$height = 800;
		}

		$r = "opened = window.open('$href','$target','scrollbars=yes,"  // ."status=yes,toolbar=1,location=yes,"
					.( $width ?
							"width=$width," :
							'' )
					.( $height ?
							"height=$height," :
							'' )
					.'resizable=yes'
					."'); opened.focus();"
					."if( typeof(openedWindows) == 'undefined' )"
					."{ openedWindows = new Array(opened); }"
					."else"
					."{ openedWindows.push(opened); }"
					."return false;";

		return $r;
	}


	/**
	 * Load user's preferences from {@link $UserSettings}
	 *
	 * @return void
	 */
	function loadSettings()
	{
		global $UserSettings;

		$UserSettings->get_cond( $this->dirsnotattop,     'fm_dirsnotattop',     $this->User->ID );
		$UserSettings->get_cond( $this->permlikelsl,      'fm_permlikelsl',      $this->User->ID );
		$UserSettings->get_cond( $this->getImageSizes,    'fm_getimagesizes',    $this->User->ID );
		$UserSettings->get_cond( $this->recursivedirsize, 'fm_recursivedirsize', $this->User->ID ); // TODO: check for permission (Server load)
		$UserSettings->get_cond( $this->showhidden,       'fm_showhidden',       $this->User->ID );
	}


	/**
	 * Check permissions
	 *
	 * @param string for what? (upload)
	 * @return true if permission granted, false if not
	 */
	function perm( $for )
	{
		global $Debuglog;

		switch( $for )
		{
			case 'upload':
				return $this->User->check_perm( 'upload', 'any', false );

			default:  // return false if not defined
				$Debuglog->add( 'Filemanager: permission check for ['.$for.'] not defined!' );
				return false;
		}
	}


	/**
	 * translates $asc parameter, if it's NULL
	 * @param boolean sort ascending?
	 * @return integer 1 for ascending, 0 for descending
	 */
	function translate_asc( $asc, $order )
	{
		if( $asc !== NULL )
		{
			return $asc;
		}
		elseif( $this->orderasc !== NULL )
		{
			return $this->orderasc;
		}
		else
		{
			return ($order == 'name') ? 1 : 0;
		}
	}


	/**
	 * translates $order parameter, if it's NULL
	 * @param string order by?
	 * @return string order by what?
	 */
	function translate_order( $order )
	{
		if( $order !== NULL )
		{
			return $order;
		}
		elseif( $this->order !== NULL )
		{
			return $this->order;
		}
		else
		{
			return 'name';
		}
	}


	/**
	 * Copies a File object physically to another File object
	 *
	 * @param File the source file (expected to exist)
	 * @param File the target file (expected to not exist)
	 * @return boolean true on success, false on failure
	 */
	function copyFileToFile( $SourceFile, &$TargetFile )
	{
		if( !$SourceFile->exists() || $TargetFile->exists() )
		{
			return false;
		}
		else
		{
			if( $r = copy( $SourceFile->getPath(true), $TargetFile->getPath(true) ) )
			{
				$TargetFile->refresh();
				if( $this->getKeyByName( $TargetFile->getName() ) === false )
				{ // File not in filelist (expected)
					$this->addFile( $TargetFile );
				}
			}
			return $r;
		}
	}

}

/*
 * $Log$
 * Revision 1.12  2004/12/29 04:32:10  blueyed
 * no message
 *
 * Revision 1.10  2004/11/10 22:44:26  blueyed
 * small fix
 *
 * Revision 1.9  2004/11/09 00:25:12  blueyed
 * minor translation changes (+MySQL spelling :/)
 *
 * Revision 1.8  2004/11/05 15:44:31  blueyed
 * no message
 *
 * Revision 1.7  2004/11/05 00:36:43  blueyed
 * no message
 *
 * Revision 1.6  2004/11/03 00:58:02  blueyed
 * update
 *
 * Revision 1.5  2004/10/24 22:55:12  blueyed
 * upload, fixes, ..
 *
 * Revision 1.4  2004/10/21 00:41:20  blueyed
 * made JsPopup nice again
 *
 * Revision 1.3  2004/10/21 00:14:44  blueyed
 * moved
 *
 * Revision 1.2  2004/10/16 01:31:22  blueyed
 * documentation changes
 *
 * Revision 1.1  2004/10/13 22:46:32  fplanque
 * renamed [b2]evocore/*
 *
 * Revision 1.37  2004/10/12 22:32:49  blueyed
 * started 'copymove' mode
 *
 * Revision 1.36  2004/10/12 10:27:18  fplanque
 * Edited code documentation.
 *
 */
?>