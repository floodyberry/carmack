<?
	function test( $text ) {
		//echo "\nFUCKFUCK:{$text}FUCKFUCK\n";
		if ( 
			preg_match( "!^ </ul>  <ul id=\".*?\"><li id=\".*?\" class=\".*?\"> <div id=\".*?\" class=\".*?\"></div> <div id=\".*?\" class=\".*?\"> <div id=\".*?\" class=\".*?\"> <div class=\"title\"> <h4><a id=\".*?\" name=\".*?\" href=\".*?\" onclick=\".*?\">(.*?)</a>    <span id=\".*?\">.*?</span></h4> </div> <div class=\"details\"> by <a href=\"//slashdot.org/~J!m", $text, $match )
		) {
			$title = $match[ 1 ];
			$date = $match[ 2 ];
			$url = $match[ 4 ];
			$post = $match[ 5 ];
		
			echo "$match[0]\n";
			echo "MATCH: title {$title}, date = {$date}, url = {$url}\n";
		}
	}
	
	
	$fuckyou = "<div class=\"title\"> <h4><a id=\"comment_link_23266718\" name=\"comment_link_23266718\" href=\"//slashdot.org/comments.pl?sid=540256&amp;cid=23266718\" onclick=\"return setFocusComment(23266718)\">Re:Iron Man's Suit Defies Physics -- Mostly</a>    <span id=\"comment_score_23266718\" class=\"score\"> (<a href=\"#\" onclick=\"getModalPrefs('modcommentlog', 'Moderation Comment Log', 23266718); return false\">Score:3</a>, Interesting)</span></h4> </div> <div class=\"details\"> by <a href=\"//slashdot.org/~John+Carmack\">John Carmack (101025)</a>  <span class=\"otherdetails\" id=\"comment_otherdetails_23266718\"> on Thursday May 01, @03:06PM (<a href=\"//slashdot.org/comments.pl?sid=540256&amp;cid=23266718\">#23266718</a>) <small> </small> </span> </div> </div> <div class=\"commentBody\"> <div id=\"comment_body_23266718\">Hydrogen peroxide powered rocket packs fly for around 30 seconds, because they have a specific impulse of around 125, meaning that one pound of propellant can make 125 pound-seconds of thrust, meaning that it takes about two pounds of propellant for every second you are in the air.  Mass ratios are low for anything strapped to a human, so the exponential nature of the rocket equation can be safely ignored.<br><br>A pretty hot (both literally and figuratively) bipropellant rocket could manage about twice the specific impulse, and you could carry somewhat heavier tanks, but two minutes of flight on a rocket pack is probably about the upper limit with conventional propellants.<br><br>However, an actual jet pack that used atmospheric oxygen could have an Isp ten times higher, allowing theoretical flights of fifteen minutes or so.  Here, it really is a matter of technical development, since jet engines have thrust to weight ratios too low to make it practical.  There is movement on this technical front, but it will still take a while.<br><br>John Carmack<br></div> </div>";
	
	$test = file_get_contents( "fuck.txt" );
	test( $test );
	test( "<div class=\"title\"> <h4><a id=\"comment_link_22733726\" name=\"comment_link_22733726\" href=\"//slashdot.org/comments.pl?sid=485272&amp;cid=22733726\" onclick=\"return setFocusComment(22733726)\">Re:Stunning!</a>    <span id=\"comment_score_22733726\" class=\"score\"> (<a href=\"#\" onclick=\"getModalPrefs('modcommentlog', 'Moderation Comment Log', 22733726); return false\">Score:5</a>, Interesting)</span></h4> </div> <div class=\"details\"> by <a href=\"//slashdot.org/~John+Carmack\">John Carmack (101025)</a>  <span class=\"otherdetails\" id=\"comment_otherdetails_22733726\"> on Wednesday March 12, @06:52PM (<a href=\"//slashdot.org/comments.pl?sid=485272&amp;cid=22733726\">#22733726</a>) <small> </small> </span> </div> </div>" );
	test( $fuckyou );
	
	echo "\n\nINFO:".strpos( $test, $fuckyou );

	
	/* 
		John Carmack \(101025\)</a>.*? on (.*?) \(<a href=\"//(.*\.)?slashdot.org/comments.pl?(.*?)\">.*?</a>\) <small> </small> </span>
	*/
?>