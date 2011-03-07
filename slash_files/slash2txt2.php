<?
	chdir( "../page_tools/" );
	include( "misc_tools.php" );
	chdir( "../slash_files/" );

	function fixtext( $text ) {
		$text = str_replace( "<br>", "\n<br>", $text );
		$text = str_replace( "John Carmack.", "", $text );
		$text = str_replace( "John Carmack", "", $text );
		$lines = preg_split( "!\n!m", $text );
		if ( preg_match( "!^&gt;!", $lines[0] ) ) {
			$out = "<p><span class=\"italic\">";
			$quote = true;
			$lines[0] = str_replace( "&gt;", "", $lines[0] );
		} else {
			$out = "<p>";
			$quote = false;
		}
		
		
		$out .= chop($lines[0])." ";
		for ( $i = 1; $i < count($lines); $i++ ) {
			if ( preg_match( "!<br>&gt;!", $lines[$i] ) ) {
				if ( !$quote ) {
					$out .= "</p><p><span class=\"italic\">";
					$quote = true;
				}
				$lines[$i] = str_replace( "<br>&gt;", "", $lines[$i] );
				$out .= chop($lines[$i])." ";
			} else if ( chop($lines[$i]) == "<br>" ) {
				if ( $quote )
					$out .= "</span></p><p>";
				else
					$out .= "</p><p>";
				$quote = false;
			} else {
				$lines[$i] = str_replace( "<br>", "", $lines[$i] );
				$out .= chop($lines[$i]);
			}
		}
		
		if ( $quote )
			$out .= "</span></p>";
		else
			$out .= "</p>";

		return ( $out );
	}
	
	function make_url( $sub, $path ) {
		if ( preg_match( "/sid=(\d*)/", $path, $sid_m ) ) {
			if ( preg_match( "/cid=(\d*)/", $path, $cid_m ) || preg_match( "/pid=\d*#(\d*)/", $path, $cid_m ) ) {
				return ( "http://".$sub."slashdot.org/comments.pl?sid=$sid_m[1]&amp;cid=$cid_m[1]" );
			}
		}
		
		return ( "" );
	}
	
	function split_sid( $sid ) {
		$yr = substr( $sid, 0, 2 );
		$mo = substr( $sid, 3, 2 );
		$dy = substr( $sid, 6, 2 );
		$rest = "x".substr( $sid, 9, 6 );
			
		if ( $yr < 10 ) 
			$yr = "20$yr";
		else
			$yr = "19$yr";
		
		return ( "$yr$mo$dy$rest" );
	}
	
	function crap_to_date( $sid_key, $date ) {
		global $mon;
		
		preg_match( "/\w*? (\w*?) (\d\d), @(\d\d)\:(\d\d)(.)/", $date, $date_m );
		$m = $date_m[ 1 ];
		$d = $date_m[ 2 ];
		$hr = $date_m[ 3 ];
		$mn = $date_m[ 4 ];
		$ampm = $date_m[ 5 ];
		
		if ( $ampm == "P" )
			$hr += 12;

		$out = ( substr( $sid_key, 0, 4 ).$mon[ substr( $m, 0, 3 ) ].$d." $hr$mn" );
		
		// echo "crap to date $out <br />\n";
		return ( $out );
	}
	
	
	function sidkey_to_date( $sid ) {
		global $mon;
		
		$yr = substr( $sid, 0, 4 );
		$mn = substr( $sid, 4, 2 );
		$dy = substr( $sid, 6, 2 );
		
		return ( $mon[ $mn ]." $dy, $yr" );
	}
	
	function slash_info( $name ) {
		global $sids, $posts, $post_count;
		
		echo "chunking $name.. <br />\n";
		
		$text = file_get_contents( $name );	
		$text = str_replace( "\n", " ", $text );
		$text = str_replace( "\t", "", $text );
		$text = str_replace( "<div class=\"commentTop\">", "\n<div class=\"commentTop\">", $text );
		$text = str_replace( "</li>", "</li>\n", $text );
		$text = str_replace( "<li>", "<li>\n", $text );
		$text = str_replace( "<ul>", "<ul>\n", $text );
		$text = str_replace( "<img", "\n<img", $text );
		$text = str_replace( " & ", "&amp;", $text );

		$sub = "";
		if ( preg_match( "/slashdot\_article\_url = \"http\:\/\/([a-z]*\.)?slashdot.org/", $text, $match ) ) {
			$sub = $match[ 1 ];
		}


		if (
			preg_match( "/  <a href=\"\/\/([a-z]*\.)?slashdot.org\/article.pl\?sid=(.*?)\">(.*?)<\/a>/", $text, $match ) ||
	   		preg_match( "/<span class=\"h-inline\"><a href=\"\/\/([a-z]*\.)?slashdot.org\/article.pl\?sid=(.*?)\".*?>(.*?)<\/a><\/span>/", $text, $match ) ||
			preg_match( "/<span class=\"escape-link\"><a href=\"\/\/([a-z]*\.)?slashdot.org\/article.pl\?sid=(.*?)\".*?>(.*?)<\/a><\/span>/", $text, $match )
		) {
			$sid = $match[ 2 ];
			$sid_key = split_sid( $sid );
			$title = $match[ 3 ];
			if ( $sub ) {
				$type = ucwords( str_replace( ".", "", $sub ) );
				$title = "$type - $title";
			}
			
			echo "$sub - $sid - $title </br> \n ";
			
			$sids[ $sid_key ] = array( "title"=>$title, "sid"=>$sid, "url"=>"http://".$sub."slashdot.org/article.pl?sid=$sid" );
			/*
			if ( $sid_key == "20080306x204233" )
				echo $text;
			*/
		} else {
			echo "CAQNT FIND SID $name\n";
			return;
		}
		
		if ( 
			preg_match( "/<h4><a .*?>(.*?)<\/a>.*?<\/div> <div class=\"details\"> by <a.*?>John Carmack \(101025\)<\/a>.*? on (.*?) \(<a href=\"\/\/(.*\.)?slashdot.org\/comments.pl\?(.*?)\">.*?<\/a>\) <small> <\/small> <\/span> <\/div>.*?<\/div> <div class=\"commentBody\"> <div id=.*?>(.*?)<\/div> <\/div>/", $text, $match ) ||
			preg_match( "!^ </ul>  <ul id=\".*?\"><li id=\".*?\" class=\".*?\"> <div id=\".*?\" class=\".*?\"></div> <div id=\".*?\" class=\".*?\"> <div id=\".*?\" class=\".*?\"> <div class=\"title\"> <h4><a id=\".*?\">(.*?)</a>.*?</h4> </div> <div class=\"details\"> by <a href=\".*?\">John Carmack \(101025\)</a>.*? on (.*?) \(<a href=\"//(.*\.)?slashdot.org/comments.pl?(.*?)\">.*?</a>\) <small> </small> </span> </div>.*?</div> <div class=\"commentBody\"> <div id=.*?>(.*?)</div> </div>!m", $text, $match )
		) {
			$title = $match[ 1 ];
			$date = $match[ 2 ];
			$fulldate = crap_to_date( $sid_key, $date );
			$url = $match[ 4 ];
			$post = fixtext($match[ 5 ]);
			
			echo "MATCH: title {$title}, date = {$date}, url = {$url}<br />\n";

			$posts[ $sid_key ][ $fulldate ][] = array( "url"=>make_url( $sub, $url ), "title"=>$title, "post"=>$post );
			$post_count++;
			//echo make_url( $sub, $url )." $fulldate - $title - $sub<br />$post<br /><br />\n";
		} else {
			echo $text;
		}
	}
	
	$dir = "./";
	
	$sids = array( );
	$posts = array( );
	$post_count = 0;
	
	if ( $dh = opendir($dir) ) {
	   while ( ( $file = readdir( $dh ) ) ) {
	   	   if ( preg_match( "!^sid\_!", $file ) ) {
	   	   	   slash_info( $dir.$file );
	   	   }
	   }
	   
	   closedir( $dh );
	}
	
	$output_file = "slash_parsed2.txt";
	$fp = fopen( $output_file, "w+" );
		
	fputs( $fp, count($sids)."\n" );
	ksort( $sids );
	foreach( $sids as $sid_key => $sid_info ) {
		if ( !isset( $posts[ $sid_key ] ) ) {
			echo "EMPTY SID $sid_key <br />\n";
			continue;
		}
		
		fputs( $fp, "$sid_key\n$sid_info[url]\n$sid_info[title]\n".count($posts[$sid_key])."\n" );
		
		ksort( $posts[ $sid_key ] );
		foreach( $posts[ $sid_key ] as $date => $post_list ) {
			foreach( $post_list as $idx => $post_info ) {
				fputs( $fp, "$date\n$post_info[url]\n$post_info[title]\n$post_info[post]\n" );
			}
		}
	}

	fclose( $fp );
	chmod( $output_file, 0666 );
?>