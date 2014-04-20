<?php

/**
 * HTML unified diff formatter.
 *
 * This class formats a diff into a CSS-based
 * unified diff format.
 *
 * Within groups of changed lines, diffs are highlit
 * at the character-diff level.
 */
class HtmlUnifiedDiffFormatter extends UnifiedDiffFormatter {

  function HtmlUnifiedDiffFormatter($context_lines = 4) {
    $this->UnifiedDiffFormatter($context_lines);
  }

  function _start_diff() {
    $this->_top = HTML::div(array('class' => 'diff'));
  }

  function _end_diff() {
    $val = $this->_top;
    unset($this->_top);
    return $val;
  }

  function _start_block($header) {
    $this->_block = HTML::div(array('class' => 'block'), HTML::tt($header));
  }

  function _end_block() {
    $this->_top->pushContent($this->_block);
    unset($this->_block);
  }

  function _lines($lines, $class, $prefix = FALSE, $elem = FALSE) {
    if (!$prefix) {
      $prefix = HTML::raw('&nbsp;');
    }
    $div = HTML::div(array('class' => 'difftext'));
    foreach ($lines as $line) {
      if ($elem) {
        $line = new HtmlElement($elem, $line);
      }
      $div->pushContent(HTML::div(array('class' => $class),
        HTML::tt(array('class' => 'prefix'), $prefix),
        $line,
        HTML::raw('&nbsp;')
      ));
    }
    $this->_block->pushContent($div);
  }

  function _context($lines) {
    $this->_lines($lines, 'context');
  }

  function _deleted($lines) {
    $this->_lines($lines, 'deleted', '-', 'del');
  }

  function _added($lines) {
    $this->_lines($lines, 'added', '+', 'ins');
  }

  function _changed($orig, $final) {
    $diff = new WordLevelDiff($orig, $final);
    $this->_lines($diff->orig(), 'original', '-');
    $this->_lines($diff->final(), 'final', '+');
  }

}

