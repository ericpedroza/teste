<?php

class ZnHgFw_ScriptsManager{

	var $inline_js = array();
	var $inline_css = '';

	function __construct(){
		add_action( 'wp_footer', array( $this, 'output_inline_js' ), 25 );
		add_action( 'wp_head', array( $this, 'output_inline_css' ), 25 );
	}

	/**
	 * @param string $code The code that you want to add to inline js
	 * @param bool|false $echo should we echo or return the code ?
	 */
	public function add_inline_js( $code, $echo = false ) {

		if ( $echo ) {

			$code = $code[ key( $code ) ];

			echo '<!-- Generated inline javascript -->';
			echo '<script type="text/javascript">';
				echo '(function($){';
					echo $code;
				echo '})(jQuery);';
			echo '</script>';

			return;
		}

		$this->inline_js[ key( $code ) ] = "\n" . $code[ key( $code ) ] . "\n";
	}


	/**
	 * @param string $code
	 * @param bool|false $echo
	 */
	public function add_inline_css( $code, $echo = false ) {

		if ( $echo ) {

			echo '<!-- Generated inline styles -->';
			echo '<style type="text/css">';
				echo $code;
			echo '</style>';

			return;
		}

		$this->inline_css .= $code;

	}

	/**
	 * Output the inline js
	 */
	public function output_inline_js() {

		if ( ! empty( $this->inline_js ) && is_array( $this->inline_js ) ) {

			echo '<!-- Zn Framework inline JavaScript-->';
			echo '<script type="text/javascript">';
				echo 'jQuery(document).ready(function($) {';
				foreach ( $this->inline_js as $key => $code ) {
					echo $code;
				}
				echo '});';
			echo '</script>';

		}
	}

	/**
	 * Output the inline css
	 */
	public function output_inline_css() {
		if ( $this->inline_css ) {
			echo '<!-- Generated inline styles -->';
			echo "<style type='text/css' id='zn-inline-styles'>";
				echo $this->inline_css;
			echo '</style>';
		}
	}
}

return new ZnHgFw_ScriptsManager();
