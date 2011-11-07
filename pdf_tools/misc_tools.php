<?
	function html2tex( $html ) {
		// characters
		$html = str_replace(
			array(                "\\",   "$",   "|", "&#039;", "&quot;",   "#",         "~", "\\verb1~1", "&nbsp;",  "{",   "}",           "&gt;",        "&lt;",  "&amp;",   "&",                 "^",   "%",   "_", "<span class=\"italic\">", "<span class=\"strong\">", "</span>", "<span class=\"em\">", "[bold]", "[/bold]" ),
			array ( "\\textbackslash ", "\\$", "$|$",      "'",     "''", "\\#", "\\verb1~1",         "/~", "", "\\{", "\\}", "\\textgreater~", "\\textless~",      "&", "\\&", "\\textasciicircum ", "\\%", "\\_",               "\\textit{", "\\textit{", "}", "\\textit{", "\\textbf{", "}" ),
			$html );
		
		// html
		$html = str_replace(
			array( "\n<br />\n", "<br />", "<p>", "</p>", "<ul>", "<ul >", "</ul>", "<li>", "<li >", "</li>", "<blockquote><div>", "</div></blockquote>", "<h4>", "</h4>", "[ul]", "[/ul]", "[li]", "[/li]", "<ol>", "</ol>", "<i>", "</i>", "[blockquote]", "[/blockquote]", "[italic]", "[/italic]" ),
			array ( "", "\\newline\n", "", "\n\n", "\\begin{itemize}", "\\begin{itemize}", "\\end{itemize}", "\\item\n", "\\item\n", "\n", "\\begin{quotation}", "\\end{quotation}", "\\subsection{", "}", "\\begin{itemize}\n", "\\end{itemize}\n", "\\item\n", "\n", "\\begin{enumerate}", "\\end{enumerate}\n", "\\textit{", "}", "\\begin{quotation}\n", "\\end{quotation}\n", "\\textit{", "}" ),
			$html );
		

		$code_formatter = create_function(
			'$matches',
			'$from = array("\\\\verb1~1", "\\\\textgreater~", "\\\\textless~", "\\\\{", "\\\\}", "\\\\textasciicircum ", "\\\\textbackslash ", "\\\\_", "\\\\#", "\\\\&", "\\\\%", "$|$" );'.
			'$to = array("~", ">", "<", "{", "}", "^", "\\\\", "_", "#", "&", "%", "|" );'.
			'if ( !strstr( $matches[1], "\\\\url" ) ) $fixed = "\\begin{Verbatim}[fontsize=\\\\small]\n".str_replace($from,$to,$matches[1])."\n\end{Verbatim}"; else $fixed = str_replace($from,$to,$matches[1]);'.
			'return ( $fixed );'
		);


		$html = ( preg_replace( 
			array( "/<a.+?href=\"(.+?)\">(.+?)<\/a>/s" ), 
			array( "\\url{\\1}" ),
			$html ) );
			
		$html = preg_replace_callback(
			array( '/<pre class="code\\\\_box">(.+?)<\/pre>/s', '/<pre>(.+?)<\/pre>/s', '/\[code\](.+?)\[\/code\]/s', '/(\\\\url\{.+?\})/' ),
			$code_formatter,
			$html );

		return ( $html );
	}
	
	function starttex( $rhead, $rfoot, $cfoot, $lfoot ) {
		global $conf;

		// build header
		$head = "\\documentclass[12pt,oneside]{report}\n".
			"\\usepackage{url,graphicx,textcomp,fancyvrb,fancyhdr,utopia}\n".
			"\\usepackage[colorlinks=true]{hyperref}\n".
			"\\parindent=0mm\n".
			"\\parskip=3mm\n";

		$head .= "% header and footer\n".
			"\\pagestyle{fancy}\n".
			"\\headheight 35pt\n".
			"\\lhead{".$conf->fullname." Archive}\n".
			"\\chead{\\thepage}\n".
			"\\rhead{".$rhead."}\n".
			"\\rfoot{".$rfoot."}\n".
			"\\cfoot{".$cfoot."}\n".
			"\\lfoot{".$lfoot."}\n";
			
		return ( $head );
	}
	
	function title2tex( $doctype ) {
		global $conf;
		
		return (
			"\\title{".$conf->fullname." Archive - $doctype}\n".
			"\\makeatletter\n".
				
			"\\renewenvironment{quotation}\n".
				
			// fix quotations
			"\t\t{\\list{}{\n".
			"\t\t\\itshape\n".
			"\t\t\\listparindent 0em\n".
			"\t\t\\itemindent\\listparindent\\rightmargin\\leftmargin\n".
			"\t\t\\parsep        \\z@ \\@plus\\p@}%\n".
			"\t\t\\item\\relax}\n".
			"\t{\\endlist}\n".

			// create the title
			"\\begin{titlepage}\n\\vskip 60\\p@\n".
  			"\\begin{center}%\n\\begin{figure}\n\\centering\n\\includegraphics{".$conf->logo."}\n\\end{figure}\n".
  			"{\\LARGE \\@title \\par}%\n"."\\vskip 3em%\n".
  			"{\\large\n\\lineskip .75em%\n\\begin{tabular}[t]{c}%\n\\@author\n\\end{tabular}\\par}%".
      		"\\vskip 1.5em%\n\\url{".$conf->url."}\\par\\today\\end{center}\\par\n\\end{titlepage}\n".
      		"\\makeatother\n".
			"\\tableofcontents\n"
		);
	}
		
	function utime( ) {
		return (float )( vsprintf( '%d.%06d', gettimeofday( ) ) );
	}

	function date_to_string( $date ) {
		global $mon;
		
		$yr = substr( $date, 0, 4 );
		$mn = substr( $date, 4, 2 );
		$dy = substr( $date, 6, 2 );
		
		return ( $mon[ $mn ]." $dy, $yr" );
	}
	
	function datetime_to_string( $date ) {
		global $mon;
		
		// 0123456789012
		// YYYYMMDD HHMM
		$yr = substr( $date, 0, 4 );
		$mn = substr( $date, 4, 2 );
		$dy = substr( $date, 6, 2 );
		$hr = substr( $date, 9, 2 );
		$min = substr( $date, 11, 2 );
		
		return ( $mon[ $mn ]." $dy, $yr - $hr:$min" );
	}
	
	function datetime_to_timestring( $date ) {
		$hr = substr( $date, 9, 2 );
		$min = substr( $date, 11, 2 );
		
		return ( "$hr:$min" );
	}
	
	function mon_to_fullstring( $m ) {
		global $mon_full;
		
		if ( strlen( $m ) == 1 )
			$m = "0$m";
			
		return ( $mon_full[ $m ] );
	}
	
	function reset_timeline( ) {
		global $conf, $timeline;
		
		$timeline = Array( );
		unlink( "../timeline/".$conf->user."_timeline.txt" );
	}
	
	function add_to_timeline( $timestamp, $type, $title, $url ) {
		global $conf, $timeline;
		
		$utime = utime( );
		$time_since = ( $utime - $conf->last_utime );
		$conf->last_utime = $utime;
		
		echo "$time_since [$type]: $timestamp - $title<br />\n";
		$timeline[ $timestamp ][] = Array( "type"=>$type, "url"=>$url, "title"=>$title );
	}
	
	function write_timeline( ) {
		global $conf, $timeline;
		$timeline_file = "../timeline/".$conf->user."_timeline.txt";
		$fp = fopen( $timeline_file, "w" );
		
		ksort( $timeline );
		foreach( $timeline as $timestamp => $link_array ) {
			// $date_string = date_to_string( $timestamp );
			
			foreach( $link_array as $idx => $link_info ) {
				fputs( $fp, "$timestamp, $link_info[title] ($link_info[type]), $link_info[url]\n" );
			}
		}
		
		fclose( $fp );
		chmod( $timeline_file, 0777 );
	}
	
	function read_file( $file ) {
		$fp = fopen( $file, "r" );
		$text = fread( $fp, filesize( $file ) );
		fclose( $fp );
		
		return ( $text );
	}
	
	function write_file( $file, $text ) {
		$fp = fopen( $file, "w+" );
		$text = fwrite( $fp, $text );
		fclose( $fp );
		chmod( $file, 0777 );
	}
	
	
	function write_template( $template, $keywords="", $title="", $extra="" ) {
		global $conf;
		
		$page = str_replace( 
			array( "@keywords", "@title" ),
			array( $conf->keywords.$keywords, $conf->title.$title ),
			$conf->header );
		$page .= str_replace(
			array( "@extra" ),
			array( $extra ),
			read_file( "template/".$template ) );
		$page .= $conf->footer;
		write_file( "../".$template, $page );
	}

	
	$mon[ "Jan" ] = "01";
	$mon[ "Feb" ] = "02";
	$mon[ "Mar" ] = "03";
	$mon[ "Apr" ] = "04";
	$mon[ "May" ] = "05";
	$mon[ "Jun" ] = "06";
	$mon[ "Jul" ] = "07";
	$mon[ "Aug" ] = "08";
	$mon[ "Sep" ] = "09";
	$mon[ "Oct" ] = "10";
	$mon[ "Nov" ] = "11";
	$mon[ "Dec" ] = "12";
	
	$mon[ "01" ] = "Jan";
	$mon[ "02" ] = "Feb";
	$mon[ "03" ] = "Mar";
	$mon[ "04" ] = "Apr";
	$mon[ "05" ] = "May";
	$mon[ "06" ] = "Jun";
	$mon[ "07" ] = "Jul";
	$mon[ "08" ] = "Aug";
	$mon[ "09" ] = "Sep";
	$mon[ "10" ] = "Oct";
	$mon[ "11" ] = "Nov";
	$mon[ "12" ] = "Dec";

	$mon_full[ "01" ] = "January";
	$mon_full[ "02" ] = "February";
	$mon_full[ "03" ] = "March";
	$mon_full[ "04" ] = "April";
	$mon_full[ "05" ] = "May";
	$mon_full[ "06" ] = "June";
	$mon_full[ "07" ] = "July";
	$mon_full[ "08" ] = "August";
	$mon_full[ "09" ] = "September";
	$mon_full[ "10" ] = "October";
	$mon_full[ "11" ] = "November";
	$mon_full[ "12" ] = "December";
	
	require_once( "conf.php" );
?>