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
            title: options.title || wpLinksets.messages.selectFile,
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
                    const label = dialog.find('label[for="' + input.attr('id') + '"]');
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
    const typeInput = $('input[name="link_type"]');

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
                jQuery: $,
                renderTemplate: renderTemplate
            })
        );
    };

    window.renderTemplate = renderTemplate;

    window.setLinkType = (linkType) => {
        if (linkType === 'file') {
            wpComponents.selectFile((selection) => {
                console.log('selection', selection);
                if (selection) {
                    applySelection('file', { file: selection });
                    const postTitle = $('input[name=post_title]');
                    postTitle.val(selection.filename).focus().blur();
                }
            }, {});
        } else if (linkType === 'post') {
            wpComponents.selectPost((selection) => {
                console.log('selection', selection);
                if (selection) {
                    applySelection('post', { post: selection });
                    const postTitle = $('input[name=post_title]');
                    postTitle.val(selection.title).focus().blur();
                }
            }, {});
        } else {
            applySelection(linkType, {});
        }
    };

    window.unselectLinkType = function () {
        console.log('unselectLinkType', $('#wp_link').html('')[0]);
        $('input[name=link_type]').val('');
        $('#wp_link').html('');

        $('#link-type-select').show();
        $('#link-type-unselect').hide();
    };

    function applySelection(linkType, selection) {
        $('input[name=link_type]').val(linkType);
        $('#link-type-select').hide();
        $('#link-type-unselect').show();
        $('#wp_link').html(getTemplate('wpPostAttachments-item-' + linkType)(selection));
    }

    switch (typeInput.val()) {
        case 'post':
            applySelection('post', {
                post: {
                    ID: $('input[name=link_post_id]').val(),
                    link: $('input[name=link_url]').val()
                }
            });
            break;

        case 'file':
            applySelection('file', {
                file: {
                    id: $('input[name=link_file_id]').val(),
                    url: $('input[name=link_url]').val()
                }
            });
            break;

        case 'youtube':
        case 'url':
            applySelection(typeInput.val(), {
                url: $('input[name=link_url]').val()
            });
            break;
    }
});
