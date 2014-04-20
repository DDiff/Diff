<?php

class _HWLDF_WordAccumulator {
    function _HWLDF_WordAccumulator () {
        $this->_lines = array();
        $this->_line = false;
        $this->_group = false;
        $this->_tag = '~begin';
    }

    function _flushGroup ($new_tag) {
        if ($this->_group !== false) {
            if (!$this->_line)
                $this->_line = HTML();
            $this->_line->pushContent($this->_tag
                                      ? new HtmlElement($this->_tag,
                                                        $this->_group)
                                      : $this->_group);
        }
        $this->_group = '';
        $this->_tag = $new_tag;
    }

    function _flushLine ($new_tag) {
        $this->_flushGroup($new_tag);
        if ($this->_line)
            $this->_lines[] = $this->_line;
        $this->_line = HTML();
    }

    function addWords ($words, $tag = '') {
        if ($tag != $this->_tag)
            $this->_flushGroup($tag);

        foreach ($words as $word) {
            // new-line should only come as first char of word.
            if (!$word)
                continue;
            if ($word[0] == "\n") {
                $this->_group .= PrintXML(HTML::raw('&nbsp;'));
                $this->_flushLine($tag);
                $word = substr($word, 1);
            }
            assert(!strstr($word, "\n"));
            $this->_group .= $word;
        }
    }

    function getLines() {
        $this->_flushLine('~done');
        return $this->_lines;
    }
}
