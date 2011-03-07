<?
	require_once( "misc_tools.php" );
	
	$dir = "../interview_files/";

	$interview_list = array( );
	if ( $dh = opendir($dir) ) {
	   while ( ( $file = readdir( $dh ) ) !== false ) {
	   	   //                 0123456789012345 67890123 456 789
	   	   if ( preg_match( "/".$conf->user."_interview_(........)_(..)\.txt/", $file, $matches ) ) {
	   	   		$key = $matches[ 1 ];
	   	   		$idx = $matches[ 2 ];
	   	   		$interview_list[ $key.$idx ] = $dir.$file;
	   	   }
	   }
	   
	   closedir( $dh );
	}

	/*
		MAIN PAGE
	*/
	$output_file = "../pdf/interviews.tex";
	$fp = fopen( $output_file, "w" );

	$head = starttex( "Interviews", "", "\\leftmark", "" );
	$head .= "\\begin{document}\n".title2tex( "Interviews" );
	
	$body = "";
	ksort( $interview_list );
	foreach( $interview_list as $date_key => $file ) {
		$fp_in = fopen( $file, "r" );
		$url_in = rtrim( fgets( $fp_in, 1024 ) );
		$title_in = rtrim(fgets( $fp_in, 1024 ) );
		$synopsis_in = rtrim( fgets( $fp_in, 1024 ) );
		$organization_in = rtrim( fgets( $fp_in, 1024 ) );
		$interviewer_in = rtrim( fgets( $fp_in, 1024 ) );
		$text_in = fread( $fp_in, filesize( $file ) );
		
		/*
			MAIN PAGE
		*/

		$body .= "\chapter{".html2tex($title_in)."}\n";

		$body .= "This interview was conducted by $interviewer_in for $organization_in on ".date_to_string($date_key).".\n\n";
		$body .= "\\url{".$url_in."}\n\n";
		$body .= "\\section*{Interview}\n\n";

		$sections_out = "";
		$sections_cnt = 0;
		$questions_cnt = 0;

		preg_match_all( "/<(.+?)( .+?)?>(.+?)<\/\\1>\r/s", $text_in, $tags_out, PREG_SET_ORDER );
		
		for ( $i = 0; $i < count( $tags_out ); $i++ ) {
			$tag = $tags_out[ $i ][ 1 ];
			$tag_args = ltrim(rtrim( $tags_out[ $i ][ 2 ] ));
			$tag_text = ltrim(rtrim( $tags_out[ $i ][ 3 ] ));
			
			switch ( $tag ) {
				case "desc": 
					$body .= "\\section*{Prologue}\n".html2tex($tag_text)."\n\\section*{Questions}\n";
					break;
					
				case "q":
					if ( $tag_args == "" ) 
						$tag_args = $interviewer_in;
					
					$question_id = "q".str_pad( $questions_cnt++, 6, "0", STR_PAD_LEFT ); 
					$body .= "\\textbf{".$tag_args."}: ".html2tex($tag_text)."\n\n";
					break;
					
				case "a":
					if ( $tag_args == "" ) 
						$tag_args = $conf->fullname;
				
					$body .= "\\textbf{".$tag_args."}: ".html2tex($tag_text)."\n\n";
					break;
					
				case "section":
					$body .= "\section{".$tag_text."}\n";
					break;
			}
		}
	}
	
	$body .= "\end{document}";
	fputs( $fp, $head );
	fputs( $fp, $body );
	fclose( $fp );
	
	// exec( "gzip -f ../$globaluser_????.html" );

?>