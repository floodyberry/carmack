<?
	require_once( "misc_tools.php" );
	$archive = "files/".$conf->user."_archive.zip";
	
	chdir( ".." );
	exec( "rm created_on*.txt" );
	touch( $conf->created_on.".txt" );
	exec( "rm files/".$conf->user."_archive.zip" );
	exec( "zip -D -X -9 -j $archive *" );
	exec( "zip -9 $archive files/*.txt" );
	chdir( "page_tools" );
?>