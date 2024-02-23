<?php
$relativeToRoot = '../';

require_once($relativeToRoot . 'constants.php');

$pageTitle = "Books | Mastering the Mundane";

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
    <section>
        <h2>How it works</h2>
        <ul>
            <li>A book idea is added to <a href="#the-hopper">the hopper</a> and a crowdfunding project is created on <a href="https://opencollective.com/mastering-the-mundane/projects" rel="noopener noreferrer" target="_blank">Open Collective</a>.<br><small>This helps prioritize the books.</small></li>
            <li>A certain amount of time each day is dedicated to working on the primary book.<br><small>Less time will often be spent on a secondary and tertiary book as well.</small></li>
            <li>Once enough of the primary book is considered stable, it will be made available as a free sample and Community Edition on <a href="https://leanpub.com/u/itsjoshbruce" rel="noopener noreferrer" target="_blank">Leanpub</a>.</li>
            <li>Once the first few Parts of the book are considered stable, new Parts will be added every one or two weeks for purchase on Leanpub.</li>
            <li>Once the complete book is available for purchase on Leanpub, we will use <a href="https://www.scribl.com" rel="noopener noreferrer" target="_blank">Scribl</a> to mass-distribute the electronic text and audio versions of the book.</li>
            <li>Within a week or two of the mass-distribution through Scribl being completed, weʼll make a print edition of the book available, and that edition (iteration) of the book will be considered done.</li>
        </ul>
    </section>
    <section>
        <h2>Wishful thinking</h2>
        <p>Our hope is to earn enough in book sales to keep pricing of our <a href="/coaching-and-mentoring/">coaching and mentoring</a> practice and <a href="/services/">other services</a> affordable. While also helping to offset (subsidize) providing those services to those who could not otherwise afford them.</p>
    </section>
    <section id="the-hopper">
        <h2>The hopper</h2>
        <p>We usually spend 2 hours per day on things related to the primary book, 1 hour per day on the secondary book, and 30 minutes on one of the others.</p>
        <p>You can help guide our writing schedule and focus by contributing to the crowdfunding campaigns.</p>
        <ol>
            <li><a href="#time-mastering-the-mundane">Time: Mastering the Mundane</a></li>
            <li>Money: Mastering the Mundane (<a href="https://opencollective.com/mastering-the-mundane/projects/book-mastering-the-mundane-mon" rel="noopener noreferrer" target="_blank">Open Collective</a>)</li>
            <li>Others:
                <ul>
                    <li>Space: Mastering the Mundane (<a href="https://opencollective.com/mastering-the-mundane/projects/book-mastering-the-mundane-spa" rel="noopener noreferrer" target="_blank">Open Collective</a>)</li>
                    <li>Homeless not Hopeless (<a href="https://opencollective.com/mastering-the-mundane/projects/book-homeless-not-hopeless" rel="noopener noreferrer" target="_blank">Open Collective</a>)</li>
                    <li>Throwing Copper (<a href="https://opencollective.com/mastering-the-mundane/projects/book-throwing-copper" rel="noopener noreferrer" target="_blank">Open Collective</a>)</li>
                    <li>Triumph over Time: Second Edition (<a href="https://opencollective.com/mastering-the-mundane/projects/book-triumph-over-time-2nd-edi" rel="noopener noreferrer" target="_blank">Open Collective</a>)</li>
                    <li>Character Sheet (<a href="https://opencollective.com/mastering-the-mundane/projects/book-character-sheet" rel="noopener noreferrer" target="_blank">Open Collective</a>)</li>
                    <li>Software: Mastering the Mundane (<a href="https://opencollective.com/mastering-the-mundane/projects/book-mastering-the-mundane-sof" rel="noopener noreferrer" target="_blank">Open Collective</a>)</li>
                </ul>
            </li>
        </ol>
    </section>
</article>
<?php require_once($relativeToRoot . 'foot.php'); ?>
