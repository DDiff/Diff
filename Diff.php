<?php

/**
 * Class representing a 'diff' between two sequences of strings.
 * @todo document
 * @private
 * @ingroup DifferenceEngine
 */
class Diff {

	/**
	 * @var DiffOp[]
	 */
	public $edits;

	/**
	 * Constructor.
	 * Computes diff between sequences of strings.
	 *
	 * @param string[] $from_lines An array of strings.
	 *   Typically these are lines from a file.
	 * @param string[] $to_lines An array of strings.
	 */
	public function __construct( $from_lines, $to_lines ) {
		$eng = new DiffEngine;
		$this->edits = $eng->diff( $from_lines, $to_lines );
	}

	/**
	 * @return DiffOp[]
	 */
	public function getEdits() {
		return $this->edits;
	}

	/**
	 * Compute reversed Diff.
	 *
	 * SYNOPSIS:
	 *
	 *    $diff = new Diff($lines1, $lines2);
	 *    $rev = $diff->reverse();
	 *
	 * @return Object A Diff object representing the inverse of the
	 *   original diff.
	 */
	public function reverse() {
		$rev = $this;
		$rev->edits = array();
		/** @var DiffOp $edit */
		foreach ( $this->edits as $edit ) {
			$rev->edits[] = $edit->reverse();
		}

		return $rev;
	}

	/**
	 * Check for empty diff.
	 *
	 * @return bool True if two sequences were identical.
	 */
	public function isEmpty() {
		foreach ( $this->edits as $edit ) {
			if ( $edit->type != 'copy' ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Compute the length of the Longest Common Subsequence (LCS).
	 *
	 * This is mostly for diagnostic purposed.
	 *
	 * @return int The length of the LCS.
	 */
	public function lcs() {
		$lcs = 0;
		foreach ( $this->edits as $edit ) {
			if ( $edit->type == 'copy' ) {
				$lcs += count( $edit->orig );
			}
		}

		return $lcs;
	}

	/**
	 * Get the original set of lines.
	 *
	 * This reconstructs the $from_lines parameter passed to the
	 * constructor.
	 *
	 * @return string[] The original sequence of strings.
	 */
	public function orig() {
		$lines = array();

		foreach ( $this->edits as $edit ) {
			if ( $edit->orig ) {
				array_splice( $lines, count( $lines ), 0, $edit->orig );
			}
		}

		return $lines;
	}

	/**
	 * Get the closing set of lines.
	 *
	 * This reconstructs the $to_lines parameter passed to the
	 * constructor.
	 *
	 * @return string[] The sequence of strings.
	 */
	public function closing() {
		$lines = array();

		foreach ( $this->edits as $edit ) {
			if ( $edit->closing ) {
				array_splice( $lines, count( $lines ), 0, $edit->closing );
			}
		}

		return $lines;
	}
}

