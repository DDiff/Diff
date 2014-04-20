<?php

/**
 * HTML table-based unified diff formatter.
 *
 * This class formats a diff into a table-based
 * unified diff format.  (Similar to what was produced
 * by previous versions of PhpWiki.)
 *
 * Within groups of changed lines, diffs are highlit
 * at the character-diff level.
 */
class TableUnifiedDiffFormatter extends HtmlUnifiedDiffFormatter
{
    function TableUnifiedDiffFormatter($context_lines = 4) {
        $this->HtmlUnifiedDiffFormatter($context_lines);
    }

    function _start_diff() {
        $this->_top = HTML::table(array('width' => '100%',
                                        'class' => 'diff',
                                        'cellspacing' => 1,
                                        'cellpadding' => 1,
                                        'border' => 1));
    }

    function _start_block($header) {
        $this->_block = HTML::table(array('width' => '100%',
                                          'class' => 'block',
                                          'cellspacing' => 0,
                                          'cellpadding' => 1,
                                          'border' => 0),
                                    HTML::tr(HTML::td(array('colspan' => 2),
                                                      HTML::tt($header))));
    }

    function _end_block() {
        $this->_top->pushContent(HTML::tr(HTML::td($this->_block)));
        unset($this->_block);
    }

    function _lines($lines, $class, $prefix = false, $elem = false) {
        if (!$prefix)
            $prefix = HTML::raw('&nbsp;');
        $prefix = HTML::td(array('class' => 'prefix',
                                 'width' => "1%"), $prefix);
        foreach ($lines as $line) {
            if (! trim($line))
                $line = HTML::raw('&nbsp;');
            elseif ($elem)
                $line = new HtmlElement($elem, $line);
            $this->_block->pushContent(HTML::tr(array('valign' => 'top'),
                                                $prefix,
                                                HTML::td(array('class' => $class),
                                                         $line)));
        }
    }
}
