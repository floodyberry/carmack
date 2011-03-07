<?
	require_once( "misc_tools.php" );

	$replace_anchors = array( );
	function replacement_anchor( $in_text, $anchor_text, $replace_with ) {
		global $replace_anchors;

		$anchor_idx = "ANCHOR".str_pad( count( $replace_anchors ), 5, "0", STR_PAD_LEFT );
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
		$output_file = "../pdf/plan_".$key.".tex";
		$fp = fopen( $output_file, "w" );
		
		$head = starttex( ".plan $key", "", "\\rightmark", "" );
		$head .= "\\begin{document}\n".title2tex( ".plan ($key)" );
		$body = "";

		$last_month = "";
		$titles_by_month = array( );

		// Sort by date
		asort( $list );
		foreach( $list as $file ) {
			// Read the file in, extract date
			$fp_plan = fopen( $dir.$file, "r" );
			$data_plan = ltrim( fread( $fp_plan, filesize( $dir.$file ) ) );
			fclose( $fp_plan );
			$date_from_file = substr( $file, 11, 8 );
			$month = substr( $date_from_file, 4, 2 );
			$date = date_to_string( $date_from_file );

			if ( $date_from_file < "19960701" )
				continue;
			
			if ( $month != $last_month ) {
				$body .= "\\chapter{".$mon_full[ $month ]."}\n";
				$last_month = $month;
			}

			// Pull out a title if we can
			$title = "";
			
			// A string of text followed by 2 line breaks
			if ( preg_match( "/^(.*?)(\n\n|\xd\xa\xd\xa)/", $data_plan, $matches ) ) {
				$tmp = $matches[ 1 ];
				
				// Has to be smaller than 90 for a title, and not start with * or +
				if ( strlen( $tmp ) < 90 && !preg_match( "/^(\*|\+)/", $tmp ) ) {
					$data_plan = ltrim( substr( $data_plan, strlen( $tmp ) + 2, strlen( $data_plan ) ) );
					$title = preg_replace( "/:$/", "", $tmp );
					$title = str_replace( ">", "&gt;", $title ); // d:>mkdir
				}
			}
			
			// Build a display title
			if ( $title == "" )
				$title = $date;
			else
				$title = "$title ($date)";

			// Pre-parse the code out
			$match_cnt = preg_match_all( "/\[code\](.*?)\[\/code\]/s", $data_plan, $tags_out, PREG_SET_ORDER );
			for ( $i = 0; $i < $match_cnt; $i++ ) {
				$tag_data = ltrim( $tags_out[ $i ][ 1 ] );
				$replace = "\\begin{Verbatim}[fontsize=\\small]\n$tag_data\n\end{Verbatim}\n";
				$data_plan = replacement_anchor( $data_plan, $tags_out[ $i ][ 0 ], $replace );
			}

			// Problem characters
			$data_plan = str_replace( 
				array( "&", "<", ">" ),
				array( "&amp;", "&lt;", "&gt;" ),
				$data_plan
			);

			// url
			$data_plan = preg_replace( 
				array (
					"/((http|ftp):\/\/[^ \n,]*)/S", // links
					"/([^<>\"\/])((www|ftp)\.[a-zA-Z]*\....?)([^<>\"\/])/S", // websites
					"/\xd+\xa?@(.*?)@\xd+\xa?/", // m=multi-line, subheading
	 			),
				
				array (
					"<a href=\"\\1\">\\1</a>",
					"\\1<a href=\"http://\\2\">\\2</a>\\4",
					"\n[bold]\\1[/bold]\n",
				),
				$data_plan
			);

			$data_plan = html2tex( $data_plan );

			$data_plan = preg_replace( 
				array (	"/(\n\n|\xd\xa\xd\xa)(\n|\xd\xa)*/", "/(\n|\xd\xa)/" ),
				array (	"\\par ", "\\newline\n" ),
				$data_plan
			);

			
			// Put the code tags back in
			for ( $i = 0; $i < count( $replace_anchors ); $i++ ) {
				$data_plan = str_replace( $replace_anchors[ $i ][ "anchor_index" ], $replace_anchors[ $i ][ "replacement" ], $data_plan );
			}

			$body .= "\\section{".html2tex($title)."}\n";
			$body .= $data_plan."\n\n";
		}
		
		$body .= "\end{document}";
		
		fputs( $fp, $head );
		fputs( $fp, $body );
		fclose( $fp );
		chmod( $output_file, 0666 );
	}
	
	// exec( "gzip -f ../$conf->user_????.html" );

?>