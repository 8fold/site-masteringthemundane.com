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

        <noscript>
            <p>Please follow this link to our <a href="https://calendly.com/mastering-the-mundane/30min?month=2024-02">scheduling provider</a> (Calendly) and be sure JavaScript is enabled for that site.</p>
            <p><small>The booking widget requires JavaScript. If you donʼt want to enable JavaScript for our site, we understand.</small></p>
        </noscript>
        <p>The scheduling view may take a moment to appear.</p>
        <!-- Calendly inline widget begin -->
        <div class="calendly-inline-widget" data-url="https://calendly.com/mastering-the-mundane/30min?primary_color=831601" style="min-width:320px;height:700px;"></div>
        <script type="text/javascript" src="https://assets.calendly.com/assets/external/widget.js" async></script>
        <!-- Calendly inline widget end -->
    </section>
</article>
<?php require_once($relativeToRoot . 'foot.php'); ?>
