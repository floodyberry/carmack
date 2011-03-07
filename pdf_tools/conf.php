<?
	class conf {
		public $header, $footer;
		public $user, $fullname;
		public $title, $keywords;
		public $created_on, $compiled_on;
		public $now, $last_utime;
		
		function conf( ) {
			$this->now = date("YmdHi");
			$this->last_utime = utime( );
			$this->created_on = "created_on_$this->now";
			$this->compiled_on = date("l dS \of F Y h:i:s A");
			$this->user = "johnc";
			$this->keywords = "John, Carmack, id, archive, compilation, doom, quake";
			$this->title = "The John Carmack Archive";
			$this->fullname = "John Carmack";
			$this->logo = "id";
			$this->url = "http://www.team5150.com/~andrew/carmack";
			
			$this->header = read_file( "template/header.html" );
			$this->footer = read_file( "template/footer.html" );
		}
	}
	
	$conf = new conf( );
?>