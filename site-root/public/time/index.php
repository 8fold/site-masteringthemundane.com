<?php
$relativeToRoot = '../';

require_once($relativeToRoot . 'constants.php');

$pageTitle = "Time | Mastering the Mundane";

$description = 'Josh Bruce is a self-proclaimed productivity super-freak and understands what itʼs like to feel like time is actively working against you. Over the years he has worked with individuals and organizations in making work suck less and increase joy in how folks spend their time.';

$poster = $url . '/media/poster.png';

require_once($relativeToRoot . 'head.php');
require_once($relativeToRoot . 'header.php');
?>
<article is="booking">
    <section>
        <h2>Time</h2>
        <p>Letʼs keep it simple and see how we can get the most of our time together.</p>
        <h3>Step one</h3>
        <p>I highly recommend reading the <i>Time: Mastering the Mundane</i> book before moving to <a href="#step-two">step two</a>.</p>
        <!-- Mini-marketing page for the book -->
        <h3 id="step-two">Step two</h3>
        <p>Youʼve read the book and taken your system as far as you can on your own, and want to direct feedback and attention.</p>
        <?php require_once($relativeToRoot . 'calendly-widget.php'); ?>
    </section>
</article>
<?php require_once($relativeToRoot . 'foot.php'); ?>
