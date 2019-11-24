<?php defined('ABSPATH') || die ?>

<script>
    wpLinksets = wpLinksets || {};
    wpLinksets.messages.selectFile = <?php echo wp_json_encode(__('Select file')) ?>;
</script>

<script type="text/html" id="tmpl-wpPostAttachments-find-posts">
    <?php find_posts_div(); ?>
</script>

<script type="text/html" id="tmpl-wpPostAttachments-item-url">
    <div class="linkset-item-type">
        <i class="dashicons dashicons-admin-site"></i>
        <?php echo _e('Website') ?>
    </div>

    <input type="text" name="link_url" value="{{ data.link_url }}" placeholder="https://" />
</script>

<script type="text/html" id="tmpl-wpPostAttachments-item-post">
    <div class="linkset-item-type">
        <i class="dashicons dashicons-admin-post"></i>
        <?php echo _e('Post') ?>
    </div>

    <div class="linkset-item-post-info">
        <a href="{{ data.link_url }}" target="_blank">{{ data.link_url }}</a>
    </div>

    <input type="hidden" name="link_post_id" value="{{ data.link_post_id }}" />
</script>

<script type="text/html" id="tmpl-wpPostAttachments-item-file">
    <div class="linkset-item-type">
        <i class="dashicons dashicons-media-default"></i>
        <?php echo _e('File') ?>
    </div>

    <div class="linkset-item-file-info">
        <a href="{{ data.link_url }}" target="_blank">{{ data.link_url }}</a>
    </div>

    <input type="hidden" name="link_file_id" value="{{ data.link_file_id }}" />
</script>

<script type="text/html" id="tmpl-wpPostAttachments-item-youtube">
    <img class="linkset-video-thumbnail" />
    <div class="linkset-item-type">
        <i class="dashicons dashicons-video-alt3"></i>
        <?php echo _e('YouTube Video') ?>
    </div>

    <input type="text" name="link_youtube_url" value="{{ data.link_youtube_url }}" placeholder="<?php esc_attr_e('Video ID or URL at YouTube') ?>"/>
    <input type="hidden" name="link_youtube_id" value="{{ data.link_youtube_id }}" />
</script>
