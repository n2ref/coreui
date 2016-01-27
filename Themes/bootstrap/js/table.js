
var combine = typeof combine !== 'undefined' ? combine : {};

combine.table = {

    preloader : {
        show : function(resource) {
            var wrapper = document.getElementById('table-' + resource + '-wrapper');
            var nodes   = wrapper.childNodes;
            for (var i = 0; i < nodes.length; i++) {
                if (/(\\s|^)preloader(\\s|$)/.test(nodes[i].className)) {
                    nodes[i].style.display = 'block';
                    break;
                }
            }
        },

        hide : function(resource) {
            var wrapper = document.getElementById('table-' + resource + '-wrapper');
            var nodes   = wrapper.childNodes;
            for (var i = 0; i < nodes.length; i++) {
                if (/(\\s|^)preloader(\\s|$)/.test(nodes[i].className)) {
                    nodes[i].style.display = 'none';
                    break;
                }
            }
        }
    },


    /**
     * Заполнение дат в поиске
     * @param $from
     * @param $to
     * @param container
     */
    setDate: function ($from, $to, container) {

        var from_split = $from.val().split(' ');
        var to_split   = $to.val().split(' ');

        var from_date = from_split[0].split('-');
        var to_date   = to_split[0].split('-');

        $('.table-datepicker-from-day, .table-datetimepicker-from-day', container).val(from_date[2] ? from_date[2] : '');
        $('.table-datepicker-from-month, .table-datetimepicker-from-month', container).val(from_date[1] ? from_date[1] : '');
        $('.table-datepicker-from-year, .table-datetimepicker-from-year', container).val(from_date[0] ? from_date[0] : '');

        $('.table-datepicker-to-day, .table-datetimepicker-to-day', container).val(to_date[2] ? to_date[2] : '');
        $('.table-datepicker-to-month, .table-datetimepicker-to-month', container).val(to_date[1] ? to_date[1] : '');
        $('.table-datepicker-to-year, .table-datetimepicker-to-year', container).val(to_date[0] ? to_date[0] : '');


        if (typeof from_split[1] != 'undefined') {
            var from_time = from_split[1].split(':');

            $('.table-datetimepicker-from-hour', container).val(from_time[0]);
            $('.table-datetimepicker-from-min', container).val(from_time[1]);

        } else {
            $('.table-datetimepicker-from-hour', container).val('00');
            $('.table-datetimepicker-from-min', container).val('00');
        }


        if (typeof to_split[1] != 'undefined') {
            var to_time = to_split[1].split(':');

            $('.table-datetimepicker-to-hour', container).val(to_time[0]);
            $('.table-datetimepicker-to-min', container).val(to_time[1]);

        } else {
            $('.table-datetimepicker-to-hour', container).val('00');
            $('.table-datetimepicker-to-min', container).val('00');
        }
    },


    /**
     * Создание календаря
     * @param container
     */
    createCalendar : function(container) {
        var $from_input = $('.table-datepicker-from-value, .table-datetimepicker-from-value', container);
        var $to_input   = $('.table-datepicker-to-value, .table-datetimepicker-to-value', container);

        var dateFormat = 'yy-mm-dd';

        combine.table.setDate($from_input, $to_input, container);

        // Показ/скрытие календаря
        $('.table-datepicker-trigger, .table-datetimepicker-trigger', container).click(function(){
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
            numberOfMonths: 2,
            beforeShowDay: function(date) {
                var date1   = $.datepicker.parseDate(dateFormat, $from_input.val());
                var date2   = $.datepicker.parseDate(dateFormat, $to_input.val());
                var classes = date1 && ((date.getTime() == date1.getTime()) || (date2 && date >= date1 && date <= date2))
                    ? 'dp-highlight'
                    : '';
                return [true, classes];
            },
            onSelect: function(dateText, inst) {
                var date_from = $.datepicker.parseDate(dateFormat, $from_input.val());
                var date_to   = $.datepicker.parseDate(dateFormat, $to_input.val());
                var selectedDate = $.datepicker.parseDate(dateFormat, dateText);


                if ( ! date_from || date_to) {
                    $from_input.val(dateText);
                    $to_input.val("");
                    $(this).datepicker();
                } else if( selectedDate < date_from ) {
                    $to_input.val( $from_input.val() );
                    $from_input.val( dateText );
                    $(this).datepicker();
                } else {
                    $to_input.val(dateText);
                    $(this).datepicker();
                }


                combine.table.setDate($from_input, $to_input, container);
            }
        });
    },


    /**
     * переформирование календаря
     * @param {string} id
     */
    rebuildCalendar : function(id) {
        var from_input = document.getElementById(id + '-from-value');
        var to_input   = document.getElementById(id + '-to-value');

        $(from_input)
            .parent().parent()
            .find('.datepicker-container, .datetimepicker-container')
            .datepicker( "option", "beforeShowDay", function(date) {

                var dateFormat = 'yy-mm-dd';
                var date1 = $.datepicker.parseDate(dateFormat, from_input.value);
                var date2 = $.datepicker.parseDate(dateFormat, to_input.value);
                var classes = date1 && ((date.getTime() == date1.getTime()) || (date2 && date >= date1 && date <= date2))
                    ? 'dp-highlight'
                    : '';
                return [true, classes];
        });
    },


    /**
     * @param {string} id
     */
    dateBlur : function(id) {

        var from_year  = document.getElementById(id + '-from-year').value;
        var from_month = document.getElementById(id + '-from-month').value;
        var from_day   = document.getElementById(id + '-from-day').value;

        var to_year  = document.getElementById(id + '-to-year').value;
        var to_month = document.getElementById(id + '-to-month').value;
        var to_day   = document.getElementById(id + '-to-day').value;


        from_year  = from_year  ? '0000'.substring(0, 4 - from_year.length) + from_year : '';
        from_month = from_month ? '00'.substring(0, 2 - from_month.length) + from_month : '';
        from_day   = from_day   ? '00'.substring(0, 2 - from_day.length) + from_day     : '';

        to_year  = to_year  ? '0000'.substring(0, 4 - to_year.length) + to_year : '';
        to_month = to_month ? '00'.substring(0, 2 - to_month.length) + to_month : '';
        to_day   = to_day   ? '00'.substring(0, 2 - to_day.length) + to_day     : '';


        if (from_year === '' || from_month === '' || from_day === '') {
            document.getElementById(id + '-from-value').value = '';
        } else {
            document.getElementById(id + '-from-value').value = from_year + '-' + from_month + '-' + from_day;
        }

        if (to_year === '' || to_month === '' || to_day === '') {
            document.getElementById(id + '-to-value').value = '';
        } else {
            document.getElementById(id + '-to-value').value = to_year + '-' + to_month + '-' + to_day;
        }

        if (document.getElementById(id + '-from-hour')) {
            var from_h = document.getElementById(id + '-from-hour').value;
            var from_m = document.getElementById(id + '-from-min').value;
            var to_h   = document.getElementById(id + '-to-hour').value;
            var to_m   = document.getElementById(id + '-to-min').value;

            from_h = from_h ? '00'.substring(0, 2 - from_h.length) + from_h : '';
            from_m = from_m ? '00'.substring(0, 2 - from_m.length) + from_m : '';
            to_h   = to_h   ? '00'.substring(0, 2 - to_h.length) + to_h     : '';
            to_m   = to_m   ? '00'.substring(0, 2 - to_m.length) + to_m     : '';

            if (document.getElementById(id + '-from-value').value !== '') {
                document.getElementById(id + '-from-value').value += ' ' + from_h + ':' + from_m;
            }
            if (document.getElementById(id + '-to-value').value !== '') {
                document.getElementById(id + '-to-value').value += ' ' + to_h + ':' + to_m;
            }
        }

        this.rebuildCalendar(id);
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

        if ((obj.id == id + '-from-day' || obj.id == id + '-to-day')) {
            if (Number(obj.value) > 31) {
                obj.value = 31;
            } else if (Number(obj.value) < 1) {
                obj.value = 1;
            }
        } else if ((obj.id == id + '-from-month' || obj.id == id + '-to-month')) {
            if (Number(obj.value) > 12) {
                obj.value = 12;
            } else if (Number(obj.value) < 1) {
                obj.value = 1;
            }
        } else if ((obj.id == id + '-from-year' || obj.id == id + '-to-year') && Number(obj.value) > 9999) {
            obj.value = 9999;
        }
        obj.focus();
        this.dateBlur(id);
    },


    /**
     * Событие изменения времени
     * @param {string} id
     * @param          select
     */
    changeTime : function(id, select) {
        this.dateBlur(id);
    },


	pageSw: function(obj, resource) {
		var page = obj.getAttribute('title');
		var p   = '_page_' + resource + '=' + page;
        var uri = window.location.search + window.location.hash;
        window.location.href = uri
            ? ((new RegExp('_page_' + resource + '=')).test(uri)
                ? uri.replace(
                    new RegExp('(_page_' + resource + '=)([^&]*)'),
                    '$1' + page)
                : window.location.href + '&' + p)
            : window.location.href + '?' + p;
	},


	goToPage: function(obj, resource) {
		var page = $('#table-' + resource + '-gotopage').val();
        if (page > 0) {
            var p   = '_page_' + resource + '=' + page;
            var uri = window.location.search + window.location.hash;
            window.location.href = uri
                ? ((new RegExp('_page_' + resource + '=')).test(uri)
                    ? uri.replace(
                        new RegExp('(_page_' + resource + '=)([^&]*)'),
                        '$1' + page)
                    : window.location.href + '&' + p)
                : window.location.href + '?' + p;
        }
	},


    order : function(resource, column_number) {
        this.preloader.show(resource);

        var token = $('#table-' + resource).data('csrf-token');
        $.ajax({
            url: window.location.pathname + window.location.search,
            type: 'POST',
            dataType : 'json',
            data : {
                column_number : column_number
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CMB-CSRF-TOKEN', token)
                   .setRequestHeader('X-CMB-RESOURCE',   resource)
                   .setRequestHeader('X-CMB-PROCESS',    'order')
            },
            success: function(result) {
                if (result.status == 'success') {
                    window.location.reload();
                } else {
                    combine.table.preloader.hide(resource);
                    if (result.message) {
                        alert(result.message);
                    }
                }
            },
            error: function(xhr, textStatus) {
                if (xhr.status == 0) {
                    alert('You are offline!\nCheck you network.');
                } else if (xhr.status == 404) {
                    alert('404 - page not found');
                } else if (xhr.status == 500) {
                    alert('500 - server error');
                } else if (textStatus == 'parsererror') {
                    alert('parse error');
                } else if (textStatus == 'timeout') {
                    alert('timeout');
                } else {
                    alert(xhr.status + ' - ' + xhr.responseText);
                }
                combine.table.preloader.hide(resource);
            }
        });
    },


	switchActive: function(img, resource, rec_id) {
		var src   = $(img).attr('src');
		var value = $(img).data('value');

		if (value == 'Y' || value == '1') {
            var new_value = value === 'Y' ? 'N' : 0;
            var new_src   = src.replace("on.png", "off.png");
			var msg       = "Деактивировать запись?";
		} else {
            var new_value = value === 'N' ? 'Y' : 1;
            var new_src   = src.replace("off.png", "on.png");
			var msg       = "Активировать запись?";
		}						
		if (confirm(msg)) {
            var preloader_src = src.substr(0, src.lastIndexOf('/')+1) + 'preloader_circle.gif';
            $(img).attr('src', preloader_src);

            var token = $('#table-' + resource).data('csrf-token');
            var url   = window.location.pathname + window.location.search;

            $.ajax({
                url: url,
                type: 'POST',
                dataType : 'json',
                data : {
                    rec_id: rec_id,
                    new_value: new_value
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-CMB-CSRF-TOKEN', token)
                       .setRequestHeader('X-CMB-RESOURCE',   resource)
                       .setRequestHeader('X-CMB-PROCESS',    'status')
                },
                success: function(data, textStatus) {
					if (data.status == "success") {
						$(img).attr('src', new_src);
						$(img).data('value', new_value);
					} else {
                        $(img).attr('src', src);
						if (data.message) {
                            alert(data.message);
                        }
					}
				},
			    error : function(xhr, textStatus) {
                    if (xhr.status == 0) {
                        alert('You are offline!\nCheck you network.');
                    } else if (xhr.status == 404) {
                        alert('404 - page not found');
                    } else if (xhr.status == 500) {
                        alert('500 - server error');
                    } else if (textStatus == 'parsererror') {
                        alert('parse error');
                    } else if (textStatus == 'timeout') {
                        alert('timeout');
                    } else {
                        alert(xhr.status + ' - ' + xhr.responseText);
                    }
                    $(img).attr('src', src);
                }
            });
		}		
	},


	getChecked : function (resource, returnArray) {
		var j = 1;
		if (returnArray === true) {
			var val = [];
		} else {
			var val = "";
		}

		for (var i = 0; i < j; i++) {
			if (document.getElementById("check-" + resource + '-' + j)) {
				if (document.getElementById("check-" + resource + '-' + j).checked) {
					if (returnArray === true) {
						val.push(document.getElementById("check-" + resource + '-' + j).value);
					} else {
						val += val === ''
                            ? document.getElementById("check-" + resource + '-' + j).value
                            : ',' + document.getElementById("check-" + resource + '-' + j).value;
					}
				}
				j++;
			}
		}

		return val;
	},


	del: function (resource, confirm_msg, no_select_msg, url) {
		var checked_rows = this.getChecked(resource, true);
		if (checked_rows) {
			if (checked_rows.length) {
				if (confirm(confirm_msg)) {
                    this.preloader.show(resource);
                    if ( ! url) {
                        var url = window.location.pathname + window.location.search;
                    }
                    var token = $('#table-' + resource).data('csrf-token');
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        dataType : 'json',
                        data : {
                            id_rows : checked_rows
                        },
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-CMB-CSRF-TOKEN', token)
                               .setRequestHeader('X-CMB-RESOURCE',   resource)
                               .setRequestHeader('X-CMB-PROCESS',    'delete')
                        },
                        success: function(result) {
                            if (result.status == 'success') {
                                window.location.reload();
                            } else {
                                combine.table.preloader.hide(resource);
                                if (result.message) {
                                    alert(result.message);
                                }
                            }
                        },
                        error: function(xhr, textStatus) {
                            if (xhr.status == 0) {
                                alert('You are offline!\nCheck you network.');
                            } else if (xhr.status == 404) {
                                alert('404 - page not found');
                            } else if (xhr.status == 500) {
                                alert('500 - server error');
                            } else if (textStatus == 'parsererror') {
                                alert('parse error');
                            } else if (textStatus == 'timeout') {
                                alert('timeout');
                            } else {
                                alert(xhr.status + ' - ' + xhr.responseText);
                            }
                            combine.table.preloader.hide(resource);
                        }
                    });
				}
			} else {
				alert(no_select_msg);
			}
		}
	},


	checkAll : function (obj, resource) {
		var j = 1;
        var check = !! obj.checked;
		for (var i = 0; i < j; i++) {
			if (document.getElementById("check-" + resource + '-' + j)) {
				document.getElementById("check-" + resource + '-' + j).checked = check;
				j++;
			}
		}
		return;
	},


	showSearch : function(resource) {
        var $search_container = $("#search-" + resource);
        $search_container.toggle('fast');
        var f = $search_container.find("form");
		f[0].elements[0].focus();
		return;
	},


    clearSearch : function(resource) {
        this.preloader.show(resource);

        var token = $('#table-' + resource).data('csrf-token');
        var url   = window.location.pathname + window.location.search;

        $.ajax({
            url: url,
            type: 'POST',
            dataType : 'json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CMB-CSRF-TOKEN', token)
                   .setRequestHeader('X-CMB-RESOURCE',   resource)
                   .setRequestHeader('X-CMB-PROCESS',    'clear_search')
            },
            success: function(result) {
                if (result.status == 'success') {
                    window.location.reload();
                } else {
                    combine.table.preloader.hide(resource);
                    if (result.message) {
                        alert(result.message);
                    }
                }
            },
            error: function(xhr, textStatus) {
                if (xhr.status == 0) {
                    alert('You are offline!\nCheck you network.');
                } else if (xhr.status == 404) {
                    alert('404 - page not found');
                } else if (xhr.status == 500) {
                    alert('500 - server error');
                } else if (textStatus == 'parsererror') {
                    alert('parse error');
                } else if (textStatus == 'timeout') {
                    alert('timeout');
                } else {
                    alert(xhr.status + ' - ' + xhr.responseText);
                }
                combine.table.preloader.hide(resource);
            }
        });
	},


    searchData : function(resource, form) {
        this.preloader.show(resource);

        var table_search = $(form).serializeArray();
        var token        = $('#table-' + resource).data('csrf-token');
        var url          = window.location.pathname + window.location.search;

        $.ajax({
            url: url,
            type: 'POST',
            dataType : 'json',
            data : table_search,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CMB-CSRF-TOKEN', token)
                   .setRequestHeader('X-CMB-RESOURCE',   resource)
                   .setRequestHeader('X-CMB-PROCESS',    'search')
            },
            success: function(result) {
                if (result.status == 'success') {
                    window.location.reload();
                } else {
                    combine.table.preloader.hide(resource);
                    if (result.message) {
                        alert(result.message);
                    }
                }
            },
            error: function(xhr, textStatus) {
                if (xhr.status == 0) {
                    alert('You are offline!\nCheck you network.');
                } else if (xhr.status == 404) {
                    alert('404 - page not found');
                } else if (xhr.status == 500) {
                    alert('500 - server error');
                } else if (textStatus == 'parsererror') {
                    alert('parse error');
                } else if (textStatus == 'timeout') {
                    alert('timeout');
                } else {
                    alert(xhr.status + ' - ' + xhr.responseText);
                }
                combine.table.preloader.hide(resource);
            }
        });
	},


    recordsPerPage : function(resource, select) {
        this.preloader.show(resource);

        var token = $('#table-' + resource).data('csrf-token');
        var url   = window.location.pathname + window.location.search;

        $.ajax({
            url: url,
            type: 'POST',
            dataType : 'json',
            data : {
                records_per_page : select.value
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CMB-CSRF-TOKEN', token)
                   .setRequestHeader('X-CMB-RESOURCE',   resource)
                   .setRequestHeader('X-CMB-PROCESS',    'records_per_page')
            },
            success: function(result, status) {
                if (result.status == 'success') {
                    window.location.reload();
                } else {
                    combine.table.preloader.hide(resource);
                    if (result.message) {
                        alert(result.message);
                    }
                }
            },
            error: function(xhr, textStatus) {
                if (xhr.status == 0) {
                    alert('You are offline!\nCheck you network.');
                } else if (xhr.status == 404) {
                    alert('404 - page not found');
                } else if (xhr.status == 500) {
                    alert('500 - server error');
                } else if (textStatus == 'parsererror') {
                    alert('parse error');
                } else if (textStatus == 'timeout') {
                    alert('timeout');
                } else {
                    alert(xhr.status + ' - ' + xhr.responseText);
                }
                combine.table.preloader.hide(resource);
            }
        });
	},


    load : function(url) {
        window.location.href = url
    },


	showFilter : function(id) {
		$("#filterColumn" + id).toggle('fast');
	},


	columnFilterStart : function(id, isAjax) {
		var o = $('#filterColumn' + id + ' form').find(':checkbox:checked');
		var l = o.length;
		var post = {};
		var t = [];

		for (var i = 0; i < l; i++) {
			t.push(o[i].value);
		}
		post['column_' + id] = t;
		var container = '';

		if (combine.table.loc[id]) {
			if (isAjax) {
				container = document.getElementById("list" + id).parentNode;
				load(combine.table.loc[id] + '&__filter=1', post, container);
			} else {
				load(combine.table.loc[id], post, container);
			}
		}
	}
};


$(document).ready(function(){
    /**
     * Очистка даты в календаре
     */
    $('.table-datepicker-clear, .table-datetimepicker-clear').click(function() {
        var container = $(this).parent();
        var $from_input = $('.table-datepicker-from-value, .table-datetimepicker-from-value', container);
        var $to_input   = $('.table-datepicker-to-value, .table-datetimepicker-to-value', container);

        $from_input.val('');
        $to_input.val('');

        combine.table.setDate($from_input, $to_input, container);

        $('.datepicker-container, .datetimepicker-container', $(container).parent()).datepicker('refresh');
    });


    /**
     * Сткрытие открытых календарей
     */
    $(document).click(function(e) {
        var target = $(e.target);
        if ($(target).parents('.datepicker-container, .datetimepicker-container, .ui-datepicker-group').length) {
            return false;

        } else {
            $('.datepicker-container, .datetimepicker-container').hide('fast');
        }
    });


    /**
     * Создание календарей
     */
    $('.table-datepicker, .table-datetimepicker').each(function() {
        combine.table.createCalendar(this);
    });
});