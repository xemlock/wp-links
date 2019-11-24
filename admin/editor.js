window.wpComponents = {};

(function () {
    let frame;
    wpComponents.selectFile = function (onSelect, options) {
        if (frame) {
            frame.open();
            return;
        }

        options = options || {};
        frame = wp.media({
            title: options.title || 'Select file',
            button: { text: 'Select' },
            library: { type: options.type },
            multiple : options.multiple
        });

        frame.on('open', function () {
            if (options.selected) {
                const selection = frame.state().get('selection');
                const file = wp.media.attachment(options.selected);

                file.fetch();
                console.log('fetched: ', file);

                if (file) {
                    selection.add([file])
                }
            }
        });

        // Runs when an image is selected
        frame.on('select', function () {
            const selected = frame.state().get('selection').map((model) => model.toJSON());

            if (typeof onSelect == 'function') {
                onSelect(options.multiple ? selected : selected[0]);
            }
        });

        frame.on('close', function () {
            console.log('close frame!');
        });

        frame.open();
    };
})();

// depends on findPosts from admin/js/media.js
(function ($) {
    wpComponents.selectPost = function (onSelect, options) {
        if ($('#find-posts').length === 0) {
            $('body').append($(renderTemplate('find-posts')));
        }

        const dialog = $('#find-posts');
        dialog.find('input, button').prop('disabled', false);

        dialog.find('#find-posts-submit')
            .unbind('click')
            .bind('click', function (e) {
                e.preventDefault();

                const input = dialog.find('[name="found_post_id"]:checked');
                if (input.length) {
                    const post_id = parseInt(input.val(), 10);

                    dialog.find('input, button').prop('disabled', true);
                    $(this).unbind('click');

                    $.get(wp.ajax.settings.url + '?action=wp-links-get-post&post_id=' + post_id).done(function (response) {
                        findPosts.close();
                        if (typeof onSelect == 'function') {
                            onSelect(response);
                        }
                    });
                }
            });

        // findPosts.open() does not setup event handlers, so we have to do it manually
        // (handlers are done in $.ready which will not work well with templated dialog)

        dialog.find('#find-posts-close')
            .unbind('click')
            .bind('click', function (e) {
                e.preventDefault();
                findPosts.close();
            });

        $('#find-posts-search').click(findPosts.send);
        $('#find-posts .find-box-search :input').keypress(function (e) {
            if (e.which === 13) {
                findPosts.send();
                return false;
            }
        });
        $('#find-posts-close').click(function () {
            findPosts.close();
        });

        findPosts.open();
    };
})(window.jQuery);

window.jQuery(function ($) {
    window.getTemplate = (name) => {
        if ($('#tmpl-' + name).size()) {
            const template = wp.template(name);
            return (data) => template(data || {});
        }
        throw new ReferenceError('Template ' + name + ' was not found');
    };

    const renderTemplate = (name, data) => {
        const template = getTemplate('wpPostAttachments-' + name);
        return template(
            $.extend(typeof data === 'object' && data || {}, {
                $: $,
                jQuery: $
            })
        );
    };

    window.renderTemplate = renderTemplate;

    window.setLinkType = (linkType) => {
        if (linkType === 'file') {
            wpComponents.selectFile((file) => {
                applySelection('file', {
                    link_url: file.url,
                    link_file_id: file.id
                });
                $('input[name=post_title]').val(file.filename).focus().blur();
            }, {});
        } else if (linkType === 'post') {
            wpComponents.selectPost((post) => {
                console.log('selection', post);
                applySelection('post', {
                    link_url: post.link,
                    link_post_id: post.ID
                });
                $('input[name=post_title]').val(post.title).focus().blur();
            });
        } else {
            applySelection(linkType, {});
        }
    };

    window.unselectLinkType = function () {
        console.log('unselectLinkType', $('#wp_link').html('')[0]);
        $('input[name=link_type]').val('');
        $('input[name=link_url]').val('');
        $('#wp_link').html('');

        $('#link-type-select').show();
        $('#link-type-unselect').hide();
    };

    function applySelection(linkType, selection) {
        $('input[name=link_type]').val(linkType);
        $('input[name=link_url]').val(selection.link_url || '');

        $('#link-type-select').hide();
        $('#link-type-unselect').show();

        $('#wp_link').html(getTemplate('wpPostAttachments-item-' + linkType)(selection));

        if (linkType === 'youtube') {
            function setYoutubeId(url) {
                const videoId = extractYoutubeId(url);
                $('input[name=link_youtube_id]').val(videoId || '');

                if (videoId) {
                    $('input[name=link_url]').val('https://www.youtube.com/watch?v=' + videoId);
                } else {
                    $('input[name=link_url]').val('');
                }

                $('#wp_link').find('img.linkset-video-thumbnail').attr(
                    'src',
                    'http://img.youtube.com/vi/' + videoId + '/hqdefault.jpg'
                );
            }

            $('#wp_link').find('input[name=link_youtube_url]').on('keyup change', function () {
                setYoutubeId(this.value);
            });

            setYoutubeId(selection.link_url);
        }
    }

    if (window.WP_Links_initialValues) {
        const linkType = WP_Links_initialValues.link_type;
        console.log('WP_Links_initialValues', window.WP_Links_initialValues);

        switch (linkType) {
            case 'post':
            case 'file':
            case 'youtube':
            case 'url':
                applySelection(linkType, WP_Links_initialValues);
                break;
        }
    }

    function extractYoutubeId(val) {
        val = String(val);

        const VIDEO_ID_REGEX = '[a-zA-Z0-9_-]{11}';
        const patterns = [
            new RegExp('^' + VIDEO_ID_REGEX + '$'), // Video ID only
            new RegExp('[?&]v=(' + VIDEO_ID_REGEX + ')'),     // regular
            new RegExp('\/v\/(' + VIDEO_ID_REGEX + ')'),      // embed
            new RegExp('\/embed\/(' + VIDEO_ID_REGEX + ')'),  // embed
            new RegExp('youtu\.be\/('+ VIDEO_ID_REGEX + ')'), // shortened
        ];
        for (let pattern of patterns) {
            const match = val.match(pattern);
            if (match && match[1]) {
                return match[1];
            }
        }
        return null;
    }

});
