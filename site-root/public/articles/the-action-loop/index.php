<?php
$relativeToRoot = '../../';

require_once($relativeToRoot . 'constants.php');

$pageTitle = "The Action Loop: Work Sustainably and Get Things Done | Articles and Thoughts | Mastering the Mundane";

$description = 'Master the 5-phase action loop to sustain energy, prevent burnout, and maintain peak productivity. Learn the framework for intentional, balanced progress.';

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
