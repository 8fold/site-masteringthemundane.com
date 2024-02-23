<?php
$relativeToRoot = '../../';

require_once($relativeToRoot . 'constants.php');

$pageTitle = "Time: Mastering the Mundane | Books | Mastering the Mundane";

$description = 'Josh Bruce has spent his life making peace with time, space, and money while also helping others do the same. Heʼs worked with individuals, The United States Federal Government Agencies, micro-businesses, Fortune 500 companies in the for-profit and not-for-profit categories.';

$poster = $url . '/media/poster.png';

require_once($relativeToRoot . 'head.php');
require_once($relativeToRoot . 'header.php');

// stage = [lean-publishing, mass-distribution ebook, mass-distribution audiobook, mass-distribution print]
// crowdfunding = link to project
// purchase
?>
<article is="book-details">
    <section id="time-mastering-the-mundane">
        <h2>Time<br><small>Mastering the Mundane</small></h2>
        <figure>
            <img src="../../media/time-cover.png" alt="Book cover: black on white background. Time centered at top. Mastering the Mundane small in bottom right corner. Josh Bruce small in bottom left. Banner across top right corner reads: Explicit language. Styles watch face set to 3 oʼclock." width="200" height="auto">
            <figcaption>Cover designed by Josh Bruce</figcaption>
        </figure>
        <div>
            <p>Part of the Mastering the Mundane set of self-management books.</p>
            <p>The questions and exercises help you create a personal productivity system while identifying values, principles, practices, and tools to act as guidelines and guardrails to use, improve, and customize your system.</p>
            <p>Available for:</p>
            <ul>
                <li>Purchase, Community Edition (email required), and free sample on <a href="https://leanpub.com/master-the-mundane" rel="noopener noreferrer" target="_blank">Leanpub</a> (<abbr title="Digital Rights Management">DRM</abbr> free)</li>
                <li>Crowdfunding on <a href="https://opencollective.com/mastering-the-mundane/projects/book-mastering-the-mundane" rel="noopener noreferrer" target="_blank">Open Collective</a></li>
            </ul>
        </div>
    </section>
</article>
<?php require_once($relativeToRoot . 'foot.php'); ?>
