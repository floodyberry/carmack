<?
	require_once( "misc_tools.php" );
	
	exec( "rm ../".$conf->user."_interview_*.html" );

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
	$output_file = "../interviews.html";
	$fp = fopen( $output_file, "w" );

	// build header
	$head = preg_replace( 
		array( "/@keywords/", "/@title/" ),
		array( $conf->keywords.", interview", "$conf->title - Interview archive" ),
		$conf->header );
	$head .= read_file( "template/interview.html" );
	
	$titles = "<h2>Interview List</h2>\n<ul>\n";
	
	$box_alternate = 0;
	ksort( $interview_list );
	foreach( $interview_list as $date_key => $file ) {
		$fp_in = fopen( $file, "r" );
		$url_in = rtrim( fgets( $fp_in, 1024 ) );
		$title_in = rtrim(fgets( $fp_in, 1024 ) );
		$synopsis_in = rtrim( fgets( $fp_in, 1024 ) );
		$organization_in = rtrim( fgets( $fp_in, 1024 ) );
		$interviewer_in = rtrim( fgets( $fp_in, 1024 ) );
		$text_in = fread( $fp_in, filesize( $file ) );
		
		// clean the title for a filename 
		$clean_in = array( "/&amp;/", "/@/", "/[-()'.]/", "/[^a-zA-Z0-9]/" );
		$clean_out = array( "and", "at", "", "_" );
		$clean_title_in = preg_replace( $clean_in, $clean_out, $title_in );
		fclose( $fp_in );
	
		/*
			MAIN PAGE
		*/

		/*
		if ( $box_alternate == 0 ) {
			$cl = " class=\"li_alternate\"";
		} else {
			$cl = "";
		}
		*/
		
		$cl = "";
		$box_alternate ^= 1;
		$output_file = $conf->user."_interview_".substr( $date_key, 0, 4 )."_".$clean_title_in.".html";
		$output_path = "../$output_file";
		
		// add_to_timeline( $timestamp, $type, $title, $url )
		add_to_timeline( substr( $date_key, 0, 8 ), "Interview", $title_in, $output_file );
		
		$titles .= "<li$cl><span class=\"strong\">".date_to_string( $date_key )."</span> - $organization_in - <a href=\"$output_file\"> $title_in </a> - \n$synopsis_in</li> \n";

		/*
			SUB PAGE
		*/	
		$fp_out = fopen( $output_path, "w" );
		
		$head_out = preg_replace( 
			array( "/@keywords/", "/@title/" ),
			array( $conf->keywords.", interview", "$conf->title - $title_in (".date_to_string( $date_key ).")" ),
			$conf->header );
		
		$head_out .= "<h1>$title_in</h1><p></p>
<h2>Overview</h2>
<p>
	<span class=\"strong\">Date: </span> ".date_to_string( $date_key )." <br />
	<span class=\"strong\">Original URL: </span> <a href=\"$url_in\">$url_in</a> <br />
	<span class=\"strong\">Synopsis: </span> $synopsis_in <br />
</p>";
		$body_out = "<h2>Questions</h2>";
		$sections_out = "";
		$sections_cnt = 0;
		$questions_cnt = 0;

		preg_match_all( "/<(.+?)( .+?)?>(.+?)<\/\\1>\r/s", $text_in, $tags_out, PREG_SET_ORDER );
		
		for ( $i = 0; $i < count( $tags_out ); $i++ ) {
			$tag = $tags_out[ $i ][ 1 ];
			$tag_args = $tags_out[ $i ][ 2 ];
			$tag_text =  trim( $tags_out[ $i ][ 3 ] );
			$tag_text = preg_replace( "/\n/", "<br />\n", $tag_text );
			//$tag_text = "<p>{$tag_text}</p>";
			//$tag_text = preg_replace( "/\n/", "</p><p>\n", $tag_text );
			$tag_text = wordwrap( $tag_text, 80, "\n" );
			
			switch ( $tag ) {
				case "desc": 
					$head_out .= "\n\n<p>\n$tag_text\n</p> ";
					break;
					
				case "q":
					if ( $tag_args == "" ) 
						$tag_args = $interviewer_in;
					
					$question_id = "q".str_pad( $questions_cnt++, 6, "0", STR_PAD_LEFT ); 
					$body_out .= "\n\n<p class=\"question\"><a id=\"$question_id\"></a><a href=\"#$question_id\"><img src=\"dot.png\" alt=\"\" /></a> <span class=\"strong\">$tag_args</span>:\n $tag_text</p>";
					break;
					
				case "a":
					if ( $tag_args == "" ) 
						$tag_args = $conf->fullname;
				
					$body_out .= "\n\n<p class=\"answer\"><span class=\"strong\">$tag_args</span>:\n $tag_text</p>";
					break;
					
				case "section":
					$tag_clean = preg_replace( $clean_in, $clean_out, $tag_text );
					if ( preg_match( "/^\d/", $tag_clean ) ) // can't have a number as the first char of an id
						$tag_clean = "s$tag_clean";
					$body_out .= "<h3 class=\"section\"><a id=\"$tag_clean\"></a>$tag_text</h3>\n\n";
					$sections_out .= "\t<li><a href=\"#$tag_clean\">$tag_text</a></li>\n";
					$sections_cnt++;
					break;
			}
		}
		
		fputs( $fp_out, $head_out );
		
		if ( $sections_cnt > 0 ) {
			$sections_out = "<h2>Sections</h2>\n<ul>\n$sections_out</ul>";
			fputs( $fp_out, $sections_out );
		}
		
		fputs( $fp_out, $body_out );
		fputs( $fp_out, $conf->footer );
		fclose( $fp_out );		
		chmod( $output_path, 0666 );
	}
	
	$titles .= "\n</ul>\n";
	
	fputs( $fp, $head );
	fputs( $fp, $titles );
	fputs( $fp, $conf->footer );
	fclose( $fp );
	
	// exec( "gzip -f ../$globaluser_????.html" );

?>