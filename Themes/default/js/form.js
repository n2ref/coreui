
var combine = typeof combine !== 'undefined' ? combine : {};

combine.form = {

    theme_src: '',

    /**
     * Отправка формы
     * @param  {object}  form
     * @return {boolean}
     */
    submit : function(form) {

        $(form).find(' > .lock').show();

        var data     = $(form).serializeArray();
        var token    = $(form).data('csrf-token');
        var resource = $(form).data('resource');
        var method   = form.method;
        var action   = form.action || window.location.pathname + window.location.search;

        $.ajax({
            url : action,
            type: method.toUpperCase(),
            dataType : 'json',
            data : data,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-ANT-CSRF-TOKEN', token)
                   .setRequestHeader('X-ANT-RESOURCE',   resource)
                   .setRequestHeader('X-ANT-PROCESS',    'save')
            },
            success : function(result) {
                if (result.status == 'success') {
                    if (result.back_url) {
                        window.location.href = result.back_url;
                    }
                } else {
                    if (result.message) {
                        alert(result.message);
                    }
                }
            },
            complete : function() {
                $(form).find(' > .lock').hide();
            }
        });

        return false;
    },


    /**
     * Заполнение дат в поиске
     * @param $input
     * @param container
     */
    setDate: function ($input, container) {

        var split = $input.val().split(' ');
        var date  = split[0].split('-');

        $('.combine-datepicker-day, .combine-datetimepicker-day', container).val(date[2] ? date[2] : '');
        $('.combine-datepicker-month, .combine-datetimepicker-month', container).val(date[1] ? date[1] : '');
        $('.combine-datepicker-year, .combine-datetimepicker-year', container).val(date[0] ? date[0] : '');


        if (typeof split[1] != 'undefined') {
            var time = split[1].split(':');

            $('.combine-datetimepicker-hour', container).val(time[0] ? time[0] : '00');
            $('.combine-datetimepicker-min', container).val(time[1] ? time[1] : '00');

        } else {
            $('.combine-datetimepicker-hour', container).val('');
            $('.combine-datetimepicker-min', container).val('');
        }
    },


    /**
     * @param {string} id
     */
    dateBlur : function(id) {

        var year  = document.getElementById(id + '-year').value;
        var month = document.getElementById(id + '-month').value;
        var day   = document.getElementById(id + '-day').value;

        year  = year  ? '0000'.substring(0, 4 - year.length) + year : '';
        month = month ? '00'.substring(0, 2 - month.length) + month : '';
        day   = day   ? '00'.substring(0, 2 - day.length) + day     : '';

        if (year === '' || month === '' || day === '') {
            document.getElementById(id).value = '';
        } else {
            document.getElementById(id).value = year + '-' + month + '-' + day;
        }


        if (document.getElementById(id + '-hour')) {
            var h = document.getElementById(id + '-hour').value;
            var m = document.getElementById(id + '-min').value;

            h = h ? '00'.substring(0, 2 - h.length) + h : '00';
            m = m ? '00'.substring(0, 2 - m.length) + m : '00';

            if (document.getElementById(id).value !== '') {
                document.getElementById(id).value += ' ' + h + ':' + m;
            }
        }
    },


    /**
     * Валидация даты
     * @param e
     */
    dateKeyPress : function (e) {
        var keyCode;
        if (e.keyCode) keyCode = e.keyCode;
        else if(e.which) keyCode = e.which;
        var av = new Array(8, 9, 35, 36, 37, 38, 40, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57);
        for (var i = 0; i < av.length; i++) {
            if (av[i] == keyCode) {
                return;
            }
        }
        e.preventDefault();
    },


    /**
     * Валидация даты
     * @param {string} id
     * @param          obj
     */
    dateKeyUp : function (id, obj) {

        if (obj.id == id + '-day') {
            if (Number(obj.value) > 31) {
                obj.value = 31;
            } else if (obj.value === '') {
                obj.value = '';
            } else if (Number(obj.value) < 1) {
                obj.value = 1;
            }
        } else if (obj.id == id + '-month') {
            if (Number(obj.value) > 12) {
                obj.value = 12;
            } else if (obj.value === '') {
                obj.value = '';
            } else if (Number(obj.value) < 1) {
                obj.value = 1;
            }
        } else if (obj.id == id + '-year') {
            if (obj.value === '') {
                obj.value = '';
            } else if (Number(obj.value) > 9999) {
                obj.value = 9999;
            }
        }
        obj.focus();
        this.dateBlur(id);
    },

    /**
     * Валидация время
     * @param {string} id
     * @param          obj
     */
    timeKeyUp : function (id, obj) {

        if (obj.id == id + '-hour') {
            if (Number(obj.value) > 23) {
                obj.value = 23;
            } else if (obj.value === '') {
                obj.value = '';
            } else if (Number(obj.value) < 0) {
                obj.value = 0;
            }
        } else if (obj.id == id + '-min') {
            if (Number(obj.value) > 59) {
                obj.value = 59;
            } else if (obj.value === '') {
                obj.value = '';
            } else if (Number(obj.value) < 0) {
                obj.value = 0;
            }
        }
        obj.focus();
        this.dateBlur(id);
    },


    /**
     * Создание календаря
     * @param container
     */
    createCalendar : function(container) {
        var $input = $('.value input', container);
        var dateFormat = 'yy-mm-dd';

        this.setDate($input, container);

        // Показ/скрытие календаря
        $('.combine-datepicker-trigger, .combine-datetimepicker-trigger', container).click(function(){
            $('.datepicker-container, .datetimepicker-container').hide('fast');
            if ( ! $('.datepicker-container, .datetimepicker-container', container).is(':visible')) {
                $('.datepicker-container, .datetimepicker-container', container).show('fast');
            }
            return false;
        });


        // Создание календаря
        $('.datepicker-container, .datetimepicker-container', container).datepicker({
            firstDay: 1,
            dateFormat: dateFormat,
            onSelect: function(dateText, inst) {
                $input.val(dateText);
                $(this).datepicker();

                combine.form.setDate($input, container);
                $(this).hide('fast');
            }
        });
    },


    /**
     * Переключатель кнопки
     * @param {object} button
     */
    changeButtonSwitch: function(button) {
        var $button        = $(button);
        var $control       = $button.find('input');
        var $active        = $button.find('.active-button');
        var $inactive      = $button.find('.inactive-button');
        var active_value   = $active.data('value');

        if ($control.val() == active_value) {
            $control.val($inactive.data('value'));
            $active.hide();
            $inactive.show();
        } else {
            $control.val(active_value);
            $active.show();
            $inactive.hide();
        }
    },

    modal: {
        key: '',

        load: function(key, url) {
            this.key = key;

            var $body_container = $('#' + this.key + '-modal>.combine-modal-dialog>.combine-modal-content>.combine-modal-body');
            $body_container.html(
                '<div style="text-align:center">' +
                    '<img src="' + combine.form.theme_src + '/img/preloader_circle.gif" alt="loading">' +
                    ' Загрузка' +
                '</div>'
            );

            $body_container.load(url);


            $('#' + this.key + '-modal').modal('show');
        },

        clear: function(key) {
            $('#' + key).val('');
            $('#' + key + '-title').val('');
        },

        hide: function() {
            $('#' + this.key + '-modal').modal('hide');
        },

        choose: function(value, title) {
            $('#' + this.key).val(value);
            $('#' + this.key + '-title').val(title);
            this.hide();
        }
    }
};

(function(){
    var myTags = document.getElementsByTagName("script");
    var src = myTags[myTags.length-1].src;
    if (src.indexOf('theme_src=') != -1) {
        combine.form.theme_src = encodeURI(src).split("theme_src=")[1].split("&")[0];
    }
}());


$(document).ready(function(){
    /**
     * Сткрытие открытых календарей
     */
    $(document).click(function(e) {
        var target = $(e.target);
        if ($(target).parents('.datepicker-container, .datetimepicker-container, .ui-datepicker-group, .ui-datepicker-header').length) {
            return false;

        } else {
            $('.datepicker-container, .datetimepicker-container').hide('fast');
        }
    });


    /**
     * Очистка даты в календаре
     */
    $('.combine-datepicker-clear, .combine-datetimepicker-clear').click(function() {
        var container = $(this).parent();
        var $input = $('.value input', container);
        $input.val('');

        combine.form.setDate($input, container);
    });
});