<?php

function PageInfoRow ($label, $rev, &$request)
{
    global $Theme, $WikiNameRegexp;

    $row = HTML::tr(HTML::td(array('align' => 'right'), $label));
    if ($rev) {
        $author = $rev->get('author');
        $dbi = $request->getDbh();

        $iswikipage = (preg_match("/^$WikiNameRegexp\$/", $author)
                       && $dbi->isWikiPage($author));
        $authorlink = $iswikipage ? WikiLink($author) : $author;

        $linked_version = WikiLink($rev, 'existing', $rev->getVersion());
        $row->pushContent(HTML::td(fmt("version %s", $linked_version)),
                          HTML::td($Theme->getLastModifiedMessage($rev,
                                                                  false)),
                          HTML::td(fmt("by %s", $authorlink)));
    } else {
        $row->pushContent(HTML::td(array('colspan' => '3'), _("None")));
    }
    return $row;
}

function showDiff (&$request) {
    $pagename = $request->getArg('pagename');
    if (is_array($versions = $request->getArg('versions'))) {
        // Version selection from pageinfo.php display:
        rsort($versions);
        list ($version, $previous) = $versions;
    }
    else {
        $version = $request->getArg('version');
        $previous = $request->getArg('previous');
    }

    // abort if page doesn't exist
    $dbi = $request->getDbh();
    $page = $request->getPage();
    $current = $page->getCurrentRevision();
    if ($current->getVersion() < 1) {
        $html = HTML(HTML::p(fmt("I'm sorry, there is no such page as %s.",
                                 WikiLink($pagename, 'unknown'))));
        include_once('lib/Template.php');
        GeneratePage($html, sprintf(_("Diff: %s"), $pagename), false);
        return; //early return
    }

    if ($version) {
        if (!($new = $page->getRevision($version)))
            NoSuchRevision($request, $page, $version);
        $new_version = fmt("version %d", $version);
    }
    else {
        $new = $current;
        $new_version = _("current version");
    }

    if (preg_match('/^\d+$/', $previous)) {
        if ( !($old = $page->getRevision($previous)) )
            NoSuchRevision($request, $page, $previous);
        $old_version = fmt("version %d", $previous);
        $others = array('major', 'minor', 'author');
    }
    else {
        switch ($previous) {
        case 'author':
            $old = $new;
            while ($old = $page->getRevisionBefore($old)) {
                if ($old->get('author') != $new->get('author'))
                    break;
            }
            $old_version = _("revision by previous author");
            $others = array('major', 'minor');
            break;
        case 'minor':
            $previous='minor';
            $old = $page->getRevisionBefore($new);
            $old_version = _("previous revision");
            $others = array('major', 'author');
            break;
        case 'major':
        default:
            $old = $new;
            while ($old && $old->get('is_minor_edit'))
                $old = $page->getRevisionBefore($old);
            if ($old)
                $old = $page->getRevisionBefore($old);
            $old_version = _("predecessor to the previous major change");
            $others = array('minor', 'author');
            break;
        }
    }

    $new_link = WikiLink($new, '', $new_version);
    $old_link = $old ? WikiLink($old, '', $old_version) : $old_version;
    $page_link = WikiLink($page);

    $html = HTML(HTML::p(fmt("Differences between %s and %s of %s.",
                             $new_link, $old_link, $page_link)));

    $otherdiffs = HTML::p(_("Other diffs:"));
    $label = array('major' => _("Previous Major Revision"),
                   'minor' => _("Previous Revision"),
                   'author'=> _("Previous Author"));
    foreach ($others as $other) {
        $args = array('action' => 'diff', 'previous' => $other);
        if ($version)
            $args['version'] = $version;
        if (count($otherdiffs->getContent()) > 1)
            $otherdiffs->pushContent(", ");
        else
            $otherdiffs->pushContent(" ");
        $otherdiffs->pushContent(Button($args, $label[$other]));
    }
    $html->pushContent($otherdiffs);


    if ($old and $old->getVersion() == 0)
        $old = false;

    $html->pushContent(HTML::Table(PageInfoRow(_("Newer page:"), $new,
                                               $request),
                                   PageInfoRow(_("Older page:"), $old,
                                               $request)));

    if ($new && $old) {
        $diff = new Diff($old->getContent(), $new->getContent());

        if ($diff->isEmpty()) {
            $html->pushContent(HTML::hr(),
                               HTML::p('[', _("Versions are identical"),
                                       ']'));
        }
        else {
            // New CSS formatted unified diffs (ugly in NS4).
            $fmt = new HtmlUnifiedDiffFormatter;

            // Use this for old table-formatted diffs.
            //$fmt = new TableUnifiedDiffFormatter;
            $html->pushContent($fmt->format($diff));
        }
    }

    include_once('lib/Template.php');
    GeneratePage($html, sprintf(_("Diff: %s"), $pagename), $new);
}

