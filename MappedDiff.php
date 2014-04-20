<?php

/**
 * @todo document, bad name.
 * @private
 * @ingroup DifferenceEngine
 */
class MappedDiff extends Diff {
	/**
	 * Constructor.
	 *
	 * Computes diff between sequences of strings.
	 *
	 * This can be used to compute things like
	 * case-insensitve diffs, or diffs which ignore
	 * changes in white-space.
	 *
	 * @param string[] $from_lines An array of strings.
	 *   Typically these are lines from a file.
	 * @param string[] $to_lines An array of strings.
	 * @param string[] $mapped_from_lines This array should
	 *   have the same size number of elements as $from_lines.
	 *   The elements in $mapped_from_lines and
	 *   $mapped_to_lines are what is actually compared
	 *   when computing the diff.
	 * @param string[] $mapped_to_lines This array should
	 *   have the same number of elements as $to_lines.
	 */
	public function __construct( $from_lines, $to_lines,
		$mapped_from_lines, $mapped_to_lines ) {
		wfProfileIn( __METHOD__ );

		assert( 'count( $from_lines ) == count( $mapped_from_lines )' );
		assert( 'count( $to_lines ) == count( $mapped_to_lines )' );

		parent::__construct( $mapped_from_lines, $mapped_to_lines );

		$xi = $yi = 0;
		$editCount = count( $this->edits );
		for ( $i = 0; $i < $editCount; $i++ ) {
			$orig = &$this->edits[$i]->orig;
			if ( is_array( $orig ) ) {
				$orig = array_slice( $from_lines, $xi, count( $orig ) );
				$xi += count( $orig );
			}

			$closing = &$this->edits[$i]->closing;
			if ( is_array( $closing ) ) {
				$closing = array_slice( $to_lines, $yi, count( $closing ) );
				$yi += count( $closing );
			}
		}
		wfProfileOut( __METHOD__ );
	}
}

