<?php
$relativeToRoot = '../../';

require_once($relativeToRoot . 'constants.php');

$pageTitle = "The 5 Ps Framework: Design Your Life (Passions, Pensives, More) | Tools and Resources | Mastering the Mundane";

$description = 'A life design framework to clarify your passions, prohibitions, and career path. Explore the 5 Ps exercise used in team culture and personal coaching.';

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
