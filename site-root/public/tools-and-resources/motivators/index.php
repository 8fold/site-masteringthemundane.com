<?php
$relativeToRoot = '../../';

require_once($relativeToRoot . 'constants.php');

$pageTitle = "The Motivators Exercise: Clarify Your Core Drivers | Tools and Resources | Mastering the Mundane";

$description = 'Discover what truly motivates you. The Motivators exercise clarifies your core drivers across 8 dimensions—for self-discovery, team building, and intentional life design.';

$poster = $url . '/media/poster.png';

require_once($relativeToRoot . 'head.php');
require_once($relativeToRoot . 'header.php');
?>
<article is="article">
    <section>
        <?php require_once('./content.html'); ?>
    </section>
</article>
<?php require_once($relativeToRoot . 'foot.php'); ?>
