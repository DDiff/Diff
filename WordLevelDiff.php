<?php

/**
 * @todo document
 * @private
 * @ingroup DifferenceEngine
 */
class WordLevelDiff extends MappedDiff {
	const MAX_LINE_LENGTH = 10000;

	/**
	 * @param string[] $orig_lines
	 * @param string[] $closing_lines
	 */
	public function __construct( $orig_lines, $closing_lines ) {
		wfProfileIn( __METHOD__ );

		list( $orig_words, $orig_stripped ) = $this->split( $orig_lines );
		list( $closing_words, $closing_stripped ) = $this->split( $closing_lines );

		parent::__construct( $orig_words, $closing_words,
			$orig_stripped, $closing_stripped );
		wfProfileOut( __METHOD__ );
	}

	/**
	 * @param string[] $lines
	 *
	 * @return array[]
	 */
	private function split( $lines ) {
		wfProfileIn( __METHOD__ );

		$words = array();
		$stripped = array();
		$first = true;
		foreach ( $lines as $line ) {
			# If the line is too long, just pretend the entire line is one big word
			# This prevents resource exhaustion problems
			if ( $first ) {
				$first = false;
			} else {
				$words[] = "\n";
				$stripped[] = "\n";
			}
			if ( strlen( $line ) > self::MAX_LINE_LENGTH ) {
				$words[] = $line;
				$stripped[] = $line;
			} else {
				$m = array();
				if ( preg_match_all( '/ ( [^\S\n]+ | [0-9_A-Za-z\x80-\xff]+ | . ) (?: (?!< \n) [^\S\n])? /xs',
					$line, $m )
				) {
					foreach ( $m[0] as $word ) {
						$words[] = $word;
					}
					foreach ( $m[1] as $stripped_word ) {
						$stripped[] = $stripped_word;
					}
				}
			}
		}
		wfProfileOut( __METHOD__ );

		return array( $words, $stripped );
	}

	/**
	 * @return string[]
	 */
	public function orig() {
		wfProfileIn( __METHOD__ );
		$orig = new HWLDFWordAccumulator;

		foreach ( $this->edits as $edit ) {
			if ( $edit->type == 'copy' ) {
				$orig->addWords( $edit->orig );
			} elseif ( $edit->orig ) {
				$orig->addWords( $edit->orig, 'del' );
			}
		}
		$lines = $orig->getLines();
		wfProfileOut( __METHOD__ );

		return $lines;
	}

	/**
	 * @return string[]
	 */
	public function closing() {
		wfProfileIn( __METHOD__ );
		$closing = new HWLDFWordAccumulator;

		foreach ( $this->edits as $edit ) {
			if ( $edit->type == 'copy' ) {
				$closing->addWords( $edit->closing );
			} elseif ( $edit->closing ) {
				$closing->addWords( $edit->closing, 'ins' );
			}
		}
		$lines = $closing->getLines();
		wfProfileOut( __METHOD__ );

		return $lines;
	}

}
