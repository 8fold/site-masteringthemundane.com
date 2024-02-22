<?php
$relativeToRoot = '../';

require_once($relativeToRoot . 'constants.php');

$pageTitle = "Book your free consultation | Mastering the Mundane";

$description = 'Josh Bruce helps individuals make peace with time, space, and money. Whether itʼs physical, digital, or downsizing, Josh can probably help. Josh Bruce has been working as a coach for over a decade and is a self-proclaimed productivity super-freak.';

$poster = $url . '/media/poster.png';

require_once($relativeToRoot . 'head.php');
require_once($relativeToRoot . 'header.php');
?>
<article is="booking">
    <section>
        <h2>Book 30 minute free consultation</h2>
        <?php require_once($relativeToRoot . 'calendar-widget.php'); ?>
    </section>
</article>
<?php require_once($relativeToRoot . 'foot.php'); ?>
