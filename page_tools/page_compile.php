<?
	ini_set( "max_execution_time", 60 * 60 * 60 );

	include( "misc_tools.php" );

	$static_dir = "../static_files/";
	$root_dir = "../";

	// clean root
	if ( $dh = opendir( $root_dir ) ) {
	   while ( ( $file = readdir( $dh ) ) !== false ) {
	   	   if ( strlen( $file ) > 2 ) {
	   	   	   if ( is_file( $root_dir.$file ) ) 
	   	   	   	   unlink( $root_dir.$file );
	   	   }
	   }
	   closedir( $dh );
	}

	reset_timeline( );
	include( "slash2html.php" );
	include( "interview2html.php" );
	include( "plan2html.php" );

	// add_to_timeline( $timestamp, $type, $title, $url )
	// artificial for audio
	add_to_timeline( "19980723", "Audio", "John Carmack at CPL 1998", "http://www.archive.org/details/john_carmack_cpl_1998" );
	add_to_timeline( "20020815", "Audio", "John Carmack at QuakeCon 2002", "http://www.archive.org/details/john_carmack_quakecon_2002" );
	add_to_timeline( "20040812", "Audio", "John Carmack at QuakeCon 2004", "http://www.archive.org/details/john_carmack_quakecon_2004" );
	add_to_timeline( "20050811", "Audio", "John Carmack at QuakeCon 2005", "http://www.archive.org/details/john_carmack_quakecon_2005" );
	add_to_timeline( "20060804", "Audio", "John Carmack at QuakeCon 2006", "http://www.archive.org/details/john_carmack_quakecon_2006" );
	add_to_timeline( "20070803", "Audio", "John Carmack at QuakeCon 2007", "http://www.archive.org/details/JohnCarmackAtQuakecon2007" );
	add_to_timeline( "20080801", "Audio", "John Carmack at QuakeCon 2008 (Keynote Up!)", "http://www.archive.org/details/JohnCarmackAtQuakecon2008" );
	add_to_timeline( "20090813", "Audio", "John Carmack at QuakeCon 2009", "http://www.archive.org/details/JohnCarmackAtQuakecon2009" );

	write_template( "audio.html", ", audio, mp3, speech, keynote", " - Audio files" );
	write_template( "plan.html", ", blog, log, plan", " - plan Archive" );
	write_template( "pdfs.html", ", pdf", " - .pdf Archive" );

	// build latest postings for index
	krsort( $timeline );
	$text = "<h2>Recent Items</h2>\n<ul>";
	$items = 0;
	foreach( $timeline as $timestamp => $link_array ) {
		$cnt = count( $link_array );
		for ( $i = $cnt - 1; $i >= 0; $i-- ) {
			if ( $items++ > 20 )
				break;
			$link_info = $link_array[$i];
			$text .= "<li><span class=\"strong\">".date_to_string( $timestamp )."</span> - ($link_info[type]) <a href=\"$link_info[url]\">$link_info[title]</a></li>\n";
		}
	}
	$text .= "</ul>\n";
	$text .= "<p><span class=\"italic\">Compiled on $conf->compiled_on.</span></p>\n";
	write_template( "index.html", "", "", $text );
	
	write_timeline( );


	// copy static in
	if ( $dh = opendir( $static_dir ) ) {
	   while ( ( $file = readdir( $dh ) ) !== false ) {
	   	   if ( strlen( $file ) > 2 ) {
	   	   	   copy( $static_dir.$file, $root_dir.$file );
	   	   	   chmod( $root_dir.$file, 0777 ); // :|
	   	   }
	   }
	   closedir( $dh );
	}
	
	include( "page_compress.php" );
?>
