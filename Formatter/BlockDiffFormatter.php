<?php

/**
 * block conflict diff formatter.
 *
 * This class will format a diff identical to Diff3 (i.e. editpage
 * conflicts), but when there are only two source files. To be used by
 * future enhancements to reloading / upgrading pgsrc.
 *
 * Functional but not finished yet, need to eliminate redundant block
 * suffixes (i.e. "=======" immediately followed by another prefix)
 * see class LoadFileConflictPageEditor
 */
class BlockDiffFormatter extends DiffFormatter {

  function BlockDiffFormatter($context_lines = 4) {
    $this->leading_context_lines = $context_lines;
    $this->trailing_context_lines = $context_lines;
  }

  function _lines($lines, $prefix = '') {
    if (!$prefix == '') {
      echo "$prefix\n";
    }
    foreach ($lines as $line) {
      echo "$line\n";
    }
    if (!$prefix == '') {
      echo "$prefix\n";
    }
  }

  function _added($lines) {
    $this->_lines($lines, ">>>>>>>");
  }

  function _deleted($lines) {
    $this->_lines($lines, "<<<<<<<");
  }

  function _block_header($xbeg, $xlen, $ybeg, $ylen) {
    return "";
  }

  function _changed($orig, $final) {
    $this->_deleted($orig);
    $this->_added($final);
  }

}

