<?
	require_once( "misc_tools.php" );
	
	$dir = "../plan_files/";

	$plans_mon = array( );
	$plans_yr = array( );
	if ( $dh = opendir($dir) ) {
		while ( ( $file = readdir( $dh ) ) !== false ) {
			if ( preg_match( "/".$conf->user."_plan_(....)(..)(..)\.txt/", $file, $matches ) ) {
					$year = $matches[ 1 ];
					$month = $matches[ 2 ];
					$day = $matches[ 3 ];

					$plans_mon[ "$year$month" ][] = $file;
					$plans_yr[ "$year" ][] = $file;
				}
			}

		closedir( $dh );
	}
		
	foreach( $plans_yr as $key => $list ) {
		$output_file = $conf->user."_plan_".$key.".txt";
		$fp = fopen( $output_file, "w" );

		asort( $list );
		foreach( $list as $file ) {
			$fp_plan = fopen( $dir.$file, "r" );
			$data_plan = fread( $fp_plan, filesize( $dir.$file ) );
			fclose( $fp_plan );
			$date = date_to_string( substr( $file, 11, 8 ) );
			
			fputs( $fp, "-----------------------------------------\n".$conf->fullname."'s .plan for $date\n-----------------------------------------\n\n" );
			fwrite( $fp, $data_plan, strlen( $data_plan ) );
			fputs( $fp, "\n\n\n" );
		}
		
		fclose( $fp );
		chmod( $output_file, 0777 );
	}
	
	exec( "zip -9 ../files/".$conf->user."_plan_by_year.zip ".$conf->user."_plan_????.txt" );
	exec( "zip -9 -j ../files/".$conf->user."_plan_by_day.zip ".$dir.$conf->user."_plan_????????.txt" );
	chmod( "../files/".$conf->user."_plan_by_year.zip", 0777 );
	chmod( "../files/".$conf->user."_plan_by_day.zip", 0777 );
	exec( "rm ".$conf->user."_plan_????.txt" );
?>