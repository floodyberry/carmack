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

	function sidkey_to_date( $sid ) {
		global $mon;
		
		$yr = substr( $sid, 0, 4 );
		$mn = substr( $sid, 4, 2 );
		
		return ( $mon[ $mn ]." $yr" );
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



	$output_file = "../pdf/slashdot.tex";
	$fp = fopen( $output_file, "w+" );
	
	$head = starttex( "Slashdot Archive", "", "\\rightmark", "" );
	$head .= "\\begin{document}\n".title2tex( "Slashdot Archive" );
	$body = "\chapter{Posts}\n";

	ksort( $sids );
	foreach( $sids as $sid_key => $sid_info ) {
		$body .= "\section{".html2tex($sid_info[title])." - ".sidkey_to_date($sid_key)."}\n";

		ksort( $posts[ $sid_key ] );
		foreach( $posts[ $sid_key ] as $date => $post_list ) {
			foreach( $post_list as $idx => $post_info ) {
				$body .= "\subsection{".html2tex($post_info[title])."}\n";
				$body .= html2tex($post_info[post]);
			}
		}
		
		$body .= "\n";
	}

	$body .= "\end{document}";
		
	fputs( $fp, $head );
	fputs( $fp, $body );
	fclose( $fp );
	chmod( $output_file, 0666 );
?>