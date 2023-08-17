<?php


class crawler {

	private $start_url;

	public function __construct( $start_url ) {
		$this->start_url = $start_url;
	}

	public function run() {
		$htmlInput = @file_get_contents( $this->start_url );

		$doc = new \DOMDocument();
		@$doc->loadHTML( mb_convert_encoding( $htmlInput, 'HTML-ENTITIES', 'UTF-8' ) );

		$data = [];

		$container = $doc->getElementById( "member_current" );
		$arr       = $container->getElementsByTagName( "a" );

		foreach ( $arr as $item ) {
			$photo = $item->getElementsByTagName( "img" )[0]->getAttribute( "src" );

			$photo = explode( '/', $photo );
			array_pop( $photo );
			$photo    = implode( '/', $photo );
			$file     = sprintf( "5th/%s", sprintf( "%s.%s", md5( $photo ), pathinfo( basename( $photo ), PATHINFO_EXTENSION ) ) );
			$filePath = sprintf( "img/pol/%s", $file );

			$name    = trim( $item->getElementsByTagName( "h6" )[0]->nodeValue );
			$details = $item->getElementsByTagName( "p" );

			if ( ! file_exists( $filePath ) ) {
				file_put_contents( $filePath, file_get_contents( $photo ) );
			}

			print $name . "\n";
			$data[] = [
				'name'         => $name,
				'gender'       => '',
				'photo'        => $file,
				'party'        => trim( $details[0]->nodeValue ),
				'region'       => trim( $details[1]->nodeValue ),
				'constituency' => trim( $details[2]->nodeValue ),
				'contact'      => [
					"telephone" => "",
					"mobile"    => "",
					"email"     => "",
					"social"    => [
						"facebook" => "",
						"twitter"  => "",
						"youtube"  => ""
					]
				]
			];
		}

		return $data;
	}

}


// USAGE


foreach ( [ 'am', 'en' ] as $lang ) {
	$result = [];

	foreach ( range( 1, 23 ) as $page ) {
		$data     = [];
		$startURL = 'https://www.hopr.gov.et/' . $lang . '/web/guest/members?_com_liferay_asset_publisher_web_portlet_AssetPublisherPortlet_INSTANCE_dyyw_cur=' . $page;
		$crawler  = new crawler( $startURL );
		$data     = $crawler->run();

		$result = array_merge( $result, $data );
	}

	$to_writes = json_encode( $result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );

	print count( $result ) . " ==> $lang\n";
	file_put_contents( sprintf( 'data/mps_5th_%s.json', $lang ), $to_writes );

}


