<?php defined('ABSPATH') || die(); ?>
<?php /** @var \wpLinksets\Plugin $this */ ?>

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
        <?php echo __('Website') ?>
    </div>

    <input type="text" name="link_url" value="{{ data.url }}" placeholder="https://" />
</script>

<script type="text/html" id="tmpl-wpPostAttachments-item-post">
    <div class="linkset-item-type">
        <i class="dashicons dashicons-admin-post"></i>
        <?php echo __('Post') ?>
    </div>

    <# if (data.post) { #>
    <div class="linkset-item-post-info">
        <a href="{{ data.post.link }}" target="_blank">{{ data.post.link }}</a>
    </div>
    <# } #>

    <input type="hidden" name="link_post_id" value="{{ data.post ? data.post.ID : '' }}" />
    <input type="hidden" name="link_url" value="{{ data.post ? data.post.link : '' }}" />
</script>

<script type="text/html" id="tmpl-wpPostAttachments-item-file">
    <div class="linkset-item-type">
        <i class="dashicons dashicons-media-default"></i>
        <?php echo __('File') ?>
    </div>

    <# if (data.file) { #>
    <div class="linkset-item-file-info">
        <a href="{{ data.file.url }}" target="_blank">{{ data.file.url }}</a>
    </div>
    <# } #>

    <input type="hidden" name="link_file_id" value="{{ data.file ? data.file.id : '' }}" />
    <input type="hidden" name="link_url" value="{{ data.file ? data.file.url : '' }}" />
</script>

<script type="text/html" id="tmpl-wpPostAttachments-item-youtube">
    <div class="linkset-item-type">
        <i class="dashicons dashicons-video-alt3"></i>
        <?php echo __('YouTube Video') ?>
    </div>

    <input type="text" name="video_id" value="{{ data.video_id }}" placeholder="<?php echo __('Video ID or URL at YouTube') ?>"/>
</script>
