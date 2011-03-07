<?
	require_once( "misc_tools.php" );

	function sidkey_to_date( $sid ) {
		global $mon;
		
		$yr = substr( $sid, 0, 4 );
		$mn = substr( $sid, 4, 2 );
		$dy = substr( $sid, 6, 2 );
		
		return ( $mon[ $mn ]." $dy, $yr" );
	}

	function fgetschop( $fp ) {
		return ( chop( fgets( $fp ) ) );
	}
	
	function get_article( $fp ) {
		return ( array( "sid"=>fgetschop( $fp ), "url"=>fgetschop( $fp ), "title"=>fgetschop( $fp ), "posts"=>fgetschop( $fp ) ) );
	}

	function get_post( $fp ) {
		return ( array( "date"=>fgetschop( $fp ), "url"=>fgetschop( $fp ), "title"=>fgetschop( $fp ), "post"=>fgetschop( $fp ) ) );
	}
	
	
	$sids = array( );
	$posts = array( );
	$post_count = 0;
	
	$cur_sid = "";
	$cur_title = "";
	
	$fp = fopen( "../slash_files/slash_parsed2.txt", "r" );
	if ( !$fp ) 
		exit;
	
	$articles = fgetschop( $fp );
	while ( $articles-- ) {
		$article = get_article( $fp );

		$cur_sid = ( $article["sid"] );
		$cur_title = ( $article["title"] );
		$sids[ $cur_sid ] = array( "title"=>$cur_title, "url"=>$article["url"] );
		
		while ( $article["posts"]-- ) {
			$post = get_post( $fp );
			$posts[ $cur_sid ][ $post["date"] ][] = array( "url"=>$post["url"], "title"=>$post["title"], "article"=>$cur_title, "post"=>$post["post"] );
		}
	}
	fclose( $fp );

	$output_file = "../slashdot.html";
	$fp = fopen( $output_file, "w" );
		
	$head = preg_replace( 
		array( "/@keywords/", "/@title/" ),
		array( $conf->keywords.", slashdot", "$conf->title - Slashdot archive" ),
		$conf->header );
	$head .= read_file( "template/slashdot.html" );
	
	$titles = "<h2>Article List</h2>\n<ul>\n";
	ksort( $sids );
	foreach( $sids as $sid_key => $sid_info ) {
		$titles .= "\t<li>".sidkey_to_date( $sid_key )." - <a href=\"#s$sid_key\">$sid_info[title]</a></li>\n";
	}
	$titles .= "</ul>\n\n";

	$body = "<h2>Comments</h2>\n";
	ksort( $sids );
	foreach( $sids as $sid_key => $sid_info ) {
		$body .= "\t<h3><a href=\"#s$sid_key\"><img src=\"dot.png\" alt=\"\" /></a><a id=\"s$sid_key\"></a> <a href=\"$sid_info[url]\">$sid_info[title]</a></h3>\n\n\t<div class=\"highlight_block\">\n";

		ksort( $posts[ $sid_key ] );
		foreach( $posts[ $sid_key ] as $date => $post_list ) {
			foreach( $post_list as $idx => $post_info ) {
				add_to_timeline( substr( $date, 0, 8 ), "Slashdot", "$post_info[article] - $post_info[title]", "slashdot.html#s$sid_key" );
				$body .= "\t\t<h4>".date_to_string( $date )." - <a href=\"$post_info[url]\">$post_info[title]</a></h4><div>$post_info[post]</div>\n";
			}
		}
		
		$body .= "\t</div>\n";
	}
	
	$body .= "<p></p>";
	
	fputs( $fp, $head );
	fputs( $fp, $titles );
	fputs( $fp, $body );
	fputs( $fp, $conf->footer );
	fclose( $fp );		
	chmod( $output_file, 0666 );
?>