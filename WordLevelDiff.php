<?php

class WordLevelDiff extends MappedDiff
{
    function WordLevelDiff ($orig_lines, $final_lines) {
        list ($orig_words, $orig_stripped) = $this->_split($orig_lines);
        list ($final_words, $final_stripped) = $this->_split($final_lines);


        $this->MappedDiff($orig_words, $final_words,
                          $orig_stripped, $final_stripped);
    }

    function _split($lines) {
        // FIXME: fix POSIX char class.
        if (!preg_match_all('/ ( [^\S\n]+ | [[:alnum:]]+ | . ) (?: (?!< \n) [^\S\n])? /xs',
                            implode("\n", $lines),
                            $m)) {
            return array(array(''), array(''));
        }
        return array($m[0], $m[1]);
    }

    function orig () {
        $orig = new _HWLDF_WordAccumulator;

        foreach ($this->edits as $edit) {
            if ($edit->type == 'copy')
                $orig->addWords($edit->orig);
            elseif ($edit->orig)
                $orig->addWords($edit->orig, 'del');
        }
        return $orig->getLines();
    }

    function final () {
        $final = new _HWLDF_WordAccumulator;

        foreach ($this->edits as $edit) {
            if ($edit->type == 'copy')
                $final->addWords($edit->final);
            elseif ($edit->final)
                $final->addWords($edit->final, 'ins');
        }
        return $final->getLines();
    }
}
