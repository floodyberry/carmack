<?
	require_once( "misc_tools.php" );
	include( "plan_compile.php" );

	function replacement_anchor( $in_text, $anchor_text, $replace_with ) {
		global $replace_anchors;

		$anchor_idx = "%anchor".str_pad( count( $replace_anchors ), 5, "0", STR_PAD_LEFT )."%";
		$dummy = str_replace( $anchor_text, $anchor_idx, $in_text );
		$replace_anchors[] = array( "anchor_index"=>$anchor_idx, "replacement"=>$replace_with );
		return ( $dummy );
	}

	$dir = "../plan_files/";
	
	// Compile a list of plans so we can sort them by date
	$plans_mon = array( );
	$plans_yr = array( );
	if ( $dh = opendir($dir) ) {
		while ( ( $file = readdir( $dh ) ) !== false ) {
			if ( preg_match( "/".$conf->user."_plan_(....)(..)(..)\.txt/", $file, $matches ) ) {
				$year = $matches[ 1 ];
				$month = $matches[ 2 ];
				$day = $matches[ 3 ];

				$plans_yr[ $year ][] = $file;
			}
		}

		closedir( $dh );
	}

	// List by year
	foreach( $plans_yr as $key => $list ) {
		$output_file = "../".$conf->user."_plan_".$key.".html";
		$fp = fopen( $output_file, "w" );
		
		
		/*
			HTML header
		*/
		$head = preg_replace( 
			array( "/@keywords/", "/@title/" ),
			array( $conf->keywords.", interview", "$conf->title - plan archive" ),
			$conf->header );
		$head .= "<h1>".$conf->fullname." .plan entries for $key</h1>";

		$body = "<h2>Entries</h2>";
		$titles_by_month = array( );
		$replace_anchors = array( );
		$box_highlight = 1;
		
		// Sort by date
		asort( $list );
		foreach( $list as $file ) {
			// Read the file in, extract date
			$fp_plan = fopen( $dir.$file, "r" );
			$data_plan = ltrim( fread( $fp_plan, filesize( $dir.$file ) ) );
			fclose( $fp_plan );
			$date_from_file = substr( $file, 11, 8 );
			$hash = "d".$date_from_file;
			$date = date_to_string( $date_from_file );

			// Problem characters
			$data_plan = preg_replace( "/&/", "&amp;", $data_plan );
			$data_plan = preg_replace( "/</", "&lt;", $data_plan );
			$data_plan = preg_replace( "/>/", "&gt;", $data_plan );

			// Pull out a title if we can
			$title = "";
			
			// A string of text followed by 2 line breaks
			if ( preg_match( "/^(.*?)(\n\n|\xd\xa\xd\xa)/", $data_plan, $matches ) ) {
				$tmp = $matches[ 1 ];
				
				// Has to be smaller than 90 for a title, and not start with * or +
				if ( strlen( $tmp ) < 90 && !preg_match( "/^(\*|\+)/", $tmp ) ) {
					$data_plan = ltrim( substr( $data_plan, strlen( $tmp ) + 2, strlen( $data_plan ) ) );
					$title = preg_replace( "/:$/", "", $tmp );
				}
			}
			
			// Build a display title
			$timeline_title = $date;
			$entry_title = $date;
			if ( $title != "" ) {
				$entry_title .= " - $title";
				$timeline_title = $title;
			}

			// Pre-parse the code tags out so they don't get <br />'d
			$match_cnt = preg_match_all( "/\[code\](.*?)\[\/code\]/s", $data_plan, $tags_out, PREG_SET_ORDER );
			for ( $i = 0; $i < $match_cnt; $i++ ) {
				$tag_data = $tags_out[ $i ][ 1 ];
				$replace = "<pre class=\"code_box\">$tag_data</pre>";
				$data_plan = replacement_anchor( $data_plan, $tags_out[ $i ][ 0 ], $replace );
			}
			
			// Text formatting, this really kills us
			$reformat_find = array (
				"/((http|ftp):\/\/[^ \n,]*)/S", // links
				"/([^<>\"\/])((www|ftp)\.[a-zA-Z]*\....?)([^<>\"\/])/S", // websites
				"/^- /m", // decided against
				"/\n\s*?[-=]+\s*\n/", // only dash seperators on a line, turn into <hr />
				"/^\*/m", // m=multi-line
				"/^\+/m", // m=multi-line
				"/\xd+\xa?@(.*?)@\xd+\xa?\xd?\xa?/", // m=multi-line, subheading
				"/\n/", // patch in <br />
				"/(\\$\d+([,.]\d+)?k?)/S", // money
				"/(f40|f50|ferrari|ferarri)/i",
 			);
			
			$reformat_replace = array (
				"<a href=\"\\1\">\\1</a>",
				"\\1<a href=\"http://\\2\">\\2</a>\\4",
				"<span class=\"against\">-</span> ",
				"<hr />",
				"<span class=\"done\">*</span>",
				"<span class=\"fix\">+</span>",
				"<h4>\\1</h4>",
				"<br />\n", // new-lines
				"<span class=\"money\">\\1</span>",
				"<span class=\"ferrari\">\\1</span>",
			);
			
			
			// FORMAT
			$data_plan = preg_replace( $reformat_find, $reformat_replace, $data_plan );

			// Put the code tags back in
			for ( $i = 0; $i < count( $replace_anchors ); $i++ ) {
				$data_plan = str_replace( $replace_anchors[ $i ][ "anchor_index" ], $replace_anchors[ $i ][ "replacement" ], $data_plan );
			}
			
			if ( $box_highlight ) {
				$h = " class=\"plan_alternate\"";
			} else {
				$h = " class=\"plan\"";
			}
			$box_highlight ^= 1;

			// add_to_timeline( $timestamp, $type, $title, $url )
			add_to_timeline( $date_from_file, "Plan", $timeline_title, $conf->user."_plan_$key.html#$hash" );

			$body .= "<h3><a href=\"#$hash\"><img src=\"dot.png\" alt=\"\" /></a><a id=\"$hash\"></a>$entry_title</h3>\n<div$h>\n".$data_plan."</div>\n";

			// Make a list of titles by month
			$month = substr( $hash, 5, 2 );
			$titles_by_month[ $month ][] = array( "hash"=>$hash, "title"=>$title );
		}
		
		$body .= "<p style=\"padding: 5px\"></p>";
		
		// Build title list
		$titles = "<h2>Topics</h2>";
		ksort( $titles_by_month );
		foreach( $titles_by_month as $month => $title_list ) {
			$entries = count( $title_list );
			$entry_txt = "$entries entr";
			if ( $entries == 1 )
				$entry_txt .= "y";
			else
				$entry_txt .= "ies";
			
			$titles .= "<h3>".mon_to_fullstring( $month )." ($entry_txt)</h3><ul>\n";
			$titles_empty = "";
			
			foreach ( $title_list as $idx => $title_pair ) {
				$hash = $title_pair[ "hash" ];
				$title = $title_pair[ "title" ];
				
				if ( $title ) {
					// spew out the list of empty titles if we have any
					if ( $titles_empty != "" ) {
						$titles .= "\t<li>$titles_empty</li>\n";
						$titles_empty = "";
					}
					
					$titles .= "\t<li>".date_to_string( substr( $hash, 1, 20 ) )." - <a href=\"#$hash\">$title</a></li>\n";
				} else {
					// add this entry to the list of empty titles
					$m = substr( $hash, 5, 2 );
					$d = substr( $hash, 7, 2 );
					
					$titles_empty .= "<a href=\"#$hash\">$d</a> ";
				}
			}

			if ( $titles_empty != "" )
				$titles .= "\t<li>$titles_empty</li>\n";

			$titles .= "</ul>\n\n";
		}
		$titles .= "\n\n";
		
		fputs( $fp, $head );
		fputs( $fp, $titles );
		fputs( $fp, $body );
		fputs( $fp, '</div></body></html>' );
		fclose( $fp );		
		chmod( $output_file, 0666 );
	}
	
	// exec( "gzip -f ../$conf->user_????.html" );

?>