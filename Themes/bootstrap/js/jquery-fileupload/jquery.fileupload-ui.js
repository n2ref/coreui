/*
 * jQuery File Upload User Interface Plugin 9.6.1
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
(function ($) {
    'use strict';
    $.widget('blueimp.fileupload', $.blueimp.fileupload, {

        options: {
            add: function (e, data) {
                var $this = $(this);
                data.context = $('<div/>').addClass('file').appendTo('.files', this);
                $('.button-bar', this).show();

                $.each(data.files, function (index, file) {
                    var preview  = $('<div/>').addClass('file-preview');
                    var fileName = $('<span/>').addClass('file-name').text(file.name);
                    var messages = $('<div/>').addClass('messages');
                    var uploadButton = $('<input/>')
                        .attr('type', 'button')
                        .val('Загрузка')
                        .addClass('btn btn-primary upload-button')
                        .on('click', function () {

                            data.submit().always(function () {
                                //$this.remove();
                            });

                            //var $this = $(this),
                            //    data = $this.data();
                            //
                            //$this.off('click')
                            //    .val('Abort')
                            //    .on('click', function () {
                            //        $this.remove();
                            //        data.abort();
                            //    });
                            //
                            //data.submit().always(function () {
                            //    $this.remove();
                            //});
                        }).data(data);

                    preview.appendTo(data.context);
                    fileName.appendTo(data.context);
                    messages.appendTo(data.context);
                    $(
                        '<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" '+
                        'aria-valuemax="100" aria-valuenow="0">' +
                        '<div class="progress-bar progress-bar-success" style="width:0"></div>' +
                        '</div>'
                    ).appendTo(data.context);
                    uploadButton.appendTo(data.context);
                });

                data.process(function () {
                    return $this.fileupload('process', data);

                }).always(function () {
                    data.context.find('.file-preview').each(function (index, elm) {
                        if (data.files[index].preview) {
                            $(elm).append(data.files[index].preview);
                        } else {
                            // TODO add icon
                        }

                        if (data.files[index].error) {
                            var error = data.files[index].error;
                            data.context.find('.messages').append($('<span class="text-danger"/>').text(error));
                            data.context.find('.upload-button').attr('disabled', 'disabled');
                        }
                    });

                }).done(function () {
                    //$.each(data.files, function (index, file) {
                    //    if (file.url) {
                    //        var link = $('<a>').attr('target', '_blank').prop('href', file.url);
                    //        data.context.find('.file-preview').wrap(link);
                    //    } else if (file.error) {
                    //        var error = $('<span class="text-danger"/>').text(file.error);
                    //        data.context.find('.messages').append(error);
                    //    }
                    //});
                });
            },
            // Callback for the start of each file upload request:
            send: function (e, data) {
                if (e.isDefaultPrevented()) {
                    return false;
                }
                var that = $(this).data('blueimp-fileupload') ||
                        $(this).data('fileupload');
                if (data.context && data.dataType &&
                        data.dataType.substr(0, 6) === 'iframe') {
                    // Iframe Transport does not support progress events.
                    // In lack of an indeterminate progress bar, we set
                    // the progress to 100%, showing the full animated bar:
                    data.context
                        .find('.progress').addClass(
                            !$.support.transition && 'progress-animated'
                        )
                        .attr('aria-valuenow', 100)
                        .children().first().css(
                            'width',
                            '100%'
                        );
                }
                return that._trigger('sent', e, data);
            },
            // Callback for upload progress events:
            progress: function (e, data) {
                if (e.isDefaultPrevented()) {
                    return false;
                }
                var progress = Math.floor(data.loaded / data.total * 100);
                if (data.context) {
                    data.context.each(function () {
                        $(this).find('.progress')
                            .attr('aria-valuenow', progress)
                            .children().first().css(
                                'width',
                                progress + '%'
                            );
                    });
                }
            }
        },

        _formatFileSize: function (bytes) {
            if (typeof bytes !== 'number') {
                return '';
            }
            if (bytes >= 1000000000) {
                return (bytes / 1000000000).toFixed(2) + ' GB';
            }
            if (bytes >= 1000000) {
                return (bytes / 1000000).toFixed(2) + ' MB';
            }
            return (bytes / 1000).toFixed(2) + ' KB';
        },

        _formatBitrate: function (bits) {
            if (typeof bits !== 'number') {
                return '';
            }
            if (bits >= 1000000000) {
                return (bits / 1000000000).toFixed(2) + ' Gbit/s';
            }
            if (bits >= 1000000) {
                return (bits / 1000000).toFixed(2) + ' Mbit/s';
            }
            if (bits >= 1000) {
                return (bits / 1000).toFixed(2) + ' kbit/s';
            }
            return bits.toFixed(2) + ' bit/s';
        },

        _formatTime: function (seconds) {
            var date = new Date(seconds * 1000),
                days = Math.floor(seconds / 86400);
            days = days ? days + 'd ' : '';
            return days +
                ('0' + date.getUTCHours()).slice(-2) + ':' +
                ('0' + date.getUTCMinutes()).slice(-2) + ':' +
                ('0' + date.getUTCSeconds()).slice(-2);
        },

        _formatPercentage: function (floatValue) {
            return (floatValue * 100).toFixed(2) + ' %';
        },

        _renderExtendedProgress: function (data) {
            return this._formatBitrate(data.bitrate) + ' | ' +
                this._formatTime(
                    (data.total - data.loaded) * 8 / data.bitrate
                ) + ' | ' +
                this._formatPercentage(
                    data.loaded / data.total
                ) + ' | ' +
                this._formatFileSize(data.loaded) + ' / ' +
                this._formatFileSize(data.total);
        }
    });
})($);
