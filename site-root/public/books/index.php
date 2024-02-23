<?php
$relativeToRoot = '../';

require_once($relativeToRoot . 'constants.php');

$pageTitle = "Books | Mastering the Mundane";

$description = 'Josh Bruce has spent his life trying to make peace with time, space, and money while also helping others do the same. Heʼs worked with individuals, The United States Federal Government Agencies, micro-businesses, Fortune 500 companies in the for-profit and not-for-profit categories.';

$poster = $url . '/media/poster.png';

require_once($relativeToRoot . 'head.php');
require_once($relativeToRoot . 'header.php');
?>
<article is="book-details">
    <section>
        <figure>
            <img src="../../media/time-cover.png" alt="Book cover: black on white background. Time centered at top. Mastering the Mundane small in bottom right corner. Josh Bruce small in bottom left. Banner across top right corner reads: Explicit language. Styles watch face set to 3 oʼclock." width="200" height="auto">
            <figcaption>Cover designed by Josh Bruce</figcaption>
        </figure>
    </section>
    <section>
        <h2>Time<br><small>Mastering the Mundane</small></h2>
        <p>Part of the Mastering the Mundane set of self-management cooks.</p>
        <p>The questions and exercises will help you create a personal productivity system while also giving you the values, principles, practices, and tools to act as guidelines and guardrails as you use, improve, and customize the system for your preferred ways of working and thinking.</p>
        <p>Available primarily at:</p>
        <ul>
            <li><a href="https://opencollective.com/mastering-the-mundane/projects/book-mastering-the-mundane">Crowdfunding with rewards</a> (Open Collective)</li>
            <li><a href="https://leanpub.com/master-the-mundane">Leanpub</a> (<abbr title="Digital Rights Management">DRM</abbr> free)</li>
        </ul>
    </section>
</article>
<?php require_once($relativeToRoot . 'foot.php'); ?>
