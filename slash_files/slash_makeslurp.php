<?php
	$text = file_get_contents( "slasharchive.txt" );
	$urls = array();

	$n = preg_match_all( "/(slashdot\.org\/comments\.pl\?.*?)\"/m", $text, $match, PREG_SET_ORDER );
	for ( $i = 0; $i < $n; $i++ )
		$urls[] = str_replace( "&amp;", "&", $match[$i][ 1 ] );
	$urls = array_reverse( $urls );


	$fp = fopen( "slash_slurp.php", "w+" );
	fputs( $fp, "<?php\n\n" );
	foreach( $urls as $url )
		fputs( $fp, "\t$"."urls[] = \"http://".$url."\";\n" );

	fputs( $fp,
	'
	foreach( $urls as $url ) {
		$data = file_get_contents( $url );
		$url = preg_replace( 
			array( "!^.*comments.pl\?!", "!&amp;!", "!&!", "!=!" ),
			array( "", "_", "_", "_" ),
			$url );
		$fp = fopen( "$url", "w+" );
		fwrite( $fp, $data, strlen( $data ) );
		fclose( $fp );
	}
?>');

	fclose( $fp );
?>