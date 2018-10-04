function handlers() {
	$('.js-parser-btn').click(function(){
		parser_action($(this));
		return false;
	});
	$('.js-cmd-save').click(function(){
		cmd_save();
	});
	$('.js-cmd-newname input').click(function(){
		cmd_new_name_selector(this);
	});
	$('.js-var-radio').click(function(){
		cmd_radio_selector(this);
	});
	$('.js-ctr-cmd-list').click(function(){
		cmd_select_selector(this);
	});
	$('button.js-team-del').click(function(){
		team_delete(this);
	});
	$('#js-search-teams').click(function(){
		SearchTeams(this);
	});
	$('#js-select-teams').click(function(){
		SelectTeams(this);
	});
	$('input[name="game-exclude"]').click(function(){
		SelectExcludeTeams(this);
	});
}

function cmd_new_name_selector(el) {
	cmd_code = $(el).attr('data-cmd_code');
	$('input.js-var-radio[data-cmd_code="'+cmd_code+'"]').prop('checked',false);
	$('select.js-ctr-cmd-list[data-cmd_code="'+cmd_code+'"] option[value=0]').prop('selected','selected');
	$('select.js-ctr-cmd-list[data-cmd_code="'+cmd_code+'"]').val(0);
}
function cmd_radio_selector(el) {
	cmd_code = $(el).attr('data-cmd_code');
	$('.js-cmd-newname input[data-cmd_code="'+cmd_code+'"]').val('');
	$('select.js-ctr-cmd-list[data-cmd_code="'+cmd_code+'"] option[value=0]').prop('selected','selected');
	$('select.js-ctr-cmd-list[data-cmd_code="'+cmd_code+'"]').val(0);
}
function cmd_select_selector(el) {
	cmd_code = $(el).attr('data-cmd_code');
	$('input.js-var-radio[data-cmd_code="'+cmd_code+'"]').prop('checked',false);
	$('.js-cmd-newname input[data-cmd_code="'+cmd_code+'"]').val('');
}

function SelectExcludeTeams(el) {
	var left_cmd,right_cmd,exclude_games = [];
	var left = $('.js-cmd-left');
	var right = $('.js-cmd-right');
	for (var i = 0; i < left.length; i++) {
		if ($(left[i]).prop('checked')) left_cmd = $(left[i]).attr('value');
	}
	for (var i = 0; i < right.length; i++) {
		if ($(right[i]).prop('checked')) right_cmd = $(right[i]).attr('value');
	}
	var exclude = $('input[name="game-exclude"]');
	for (var i = 0; i < exclude.length; i++) {
		if ($(exclude[i]).prop('checked')) exclude_games.push($(exclude[i]).attr('value'));
	}
	var data = {
		action: 'select_teams',
		champ: $('#js-champ-selector').val(),
		cmd1: left_cmd,
		cmd2: right_cmd,
		exclude: exclude_games
	};
	$.ajax({
		url: "/ajax/analizer/",
		method: 'POST',
		data: data,
		dataType: 'json',
		success: function(response) {
			renderPairList(response);
		},
		error: function() {
			console.log('error');
		}
	});
}
function SelectTeams(el) {
	var left_cmd,right_cmd;
	var left = $('.js-cmd-left');
	var right = $('.js-cmd-right');
	for (var i = 0; i < left.length; i++) {
		if ($(left[i]).prop('checked')) left_cmd = $(left[i]).attr('value');
	}
	for (var i = 0; i < right.length; i++) {
		if ($(right[i]).prop('checked')) right_cmd = $(right[i]).attr('value');
	}
	var data = {
		action: 'select_teams',
		champ: $('#js-champ-selector').val(),
		cmd1: left_cmd,
		cmd2: right_cmd,
		exclude: []
	};
	$.ajax({
		url: "/ajax/analizer/",
		method: 'POST',
		data: data,
		dataType: 'json',
		success: function(response) {
			renderPairList(response);
		},
		error: function() {
			console.log('error');
		}
	});
}

function renderPairList(responce) {
	//pair-list
	var table = $('#pair-list');
	remove_lines(table,'tr');
	var tpl_js = table.find('tr.js_tpl');
	for (var i in responce.result.pair) {
		var line = responce.result.pair[i];
		var tpl = $(tpl_js).clone(true);
		tpl.removeClass('js_tpl');
		if (line.EX == '1') {
			tpl.addClass('exclude');
			tpl.find('input').prop('checked',true);
		}
		tpl.find('.js-gid').text(line.GAME_ID);
		tpl.find('input').val(line.GAME_ID);
		tpl.find('.js-gname').text(line.NAME);
		tpl.find('.js-gdate').text(line.GAME_DATE);
		if (line.HOME=='1') {
			tpl.find('.js-gscore').text(line.SCORE1 + ' : '+line.SCORE2);
			if (line.EX != 1)
				tpl.find('.js-gb').text(line.B1SCORE + ' : '+line.B2SCORE);
		} else {
			tpl.find('.js-gscore').text(line.SCORE2 + ' : '+line.SCORE1);
			if (line.EX != 1)
				tpl.find('.js-gb').text(line.B2SCORE + ' : '+line.B1SCORE);
		}
		table.append(tpl);
	}
}

function SearchTeams(el) {
	var data = {
		action: 'search_teams',
		champ: $('#js-champ-selector').val(),
		cmd1: $('#cmd1').val(),
		cmd2: $('#cmd2').val()
	};
	$.ajax({
		url: "/ajax/analizer/",
		method: 'POST',
		data: data,
		dataType: 'json',
		success: function(response) {
			if (response.error) {
				//$(modalWin).find('.modal-body').text(response.error_msg);
			} else {
				$('#js-left-list').empty();
				$('#js-right-list').empty();
				var tpl_node = $('p.js_tpl');
				for (var i = 0; i < response.result.team1.length; i++) {
					var tpl = $(tpl_node).clone();
					tpl.removeClass('js_tpl');
					tpl.find('label').text(response.result.team1[i].NAME);
					tpl.find('label').attr('for','cmd-left-'+response.result.team1[i].ID);
					tpl.find('input').attr('id','cmd-left-'+response.result.team1[i].ID);
					tpl.find('input').attr('name','cmd-left');
					tpl.find('input').addClass('js-cmd-left');
					tpl.find('input').val(response.result.team1[i].ID);
					$('#js-left-list').append(tpl);
				}
				for (var i = 0; i < response.result.team2.length; i++) {
					var tpl = $(tpl_node).clone();
					tpl.removeClass('js_tpl');
					tpl.find('label').text(response.result.team2[i].NAME);
					tpl.find('label').attr('for','cmd-right-'+response.result.team2[i].ID);
					tpl.find('input').attr('id','cmd-right-'+response.result.team2[i].ID);
					tpl.find('input').attr('name','cmd-right');
					tpl.find('input').addClass('js-cmd-right');
					tpl.find('input').val(response.result.team2[i].ID);
					$('#js-right-list').append(tpl);
				}
				$('#js-search-result').show();
			}
		},
		error: function() {
			console.log('error');
		}
	});
}

function parser_action(el) {
	var data = {
		parser: $(el).data('parser'),
		action: $(el).data('action')
	}
	if ($(el).data('chy')) {
		var chy_id = $(el).data('chy');
	}
	if ($(el).data('code')) {
		data.code = $(el).data('code');
	}
	if ($(el).data('country')) {
		data.country = $(el).data('country');
	}
	var modalWin;
	if (data.action == 'cmd')
		modalWin = '#parserResponse';
	else if (data.action == 'game')
		modalWin = '#parserGameResponse';
	else if (data.action == 'season')
		modalWin = '#parserResponse';
	$.ajax({
		url: "/ajax/parser/",
		method: 'POST',
		data: data,
		dataType: 'json',
		success: function(response) {
			if (response.error) {
				$(modalWin).find('.modal-body').text(response.error_msg);
			} else {
				response.chy_id = chy_id;
				response.parser = data.parser;
				if (data.action == 'cmd') {
					cmd_popup(response);
				}
				if (data.action == 'game') {
					games_popup(response);
				}
				$(modalWin).find('.modal-body').text(response.msg);
			}
			$(modalWin).modal('show');
		},
		error: function() {
			console.log('error');
			$(modalWin).find('.modal-body').text('Ошибка сервера');
			$(modalWin).modal('show');
		}
	});
}

function cmd_popup(response){
	$('#cmd_check_list').data('chy',response.chy_id);
	$('#cmd_check_list').attr('data-chy',response.chy_id);
	$('#cmd_check_list').data('parser',response.parser);
	$('#cmd_check_list').attr('data-parser',response.parser);
	var table = $('.js-cmd-check tbody');
	remove_lines(table,'tr');
	var tpl_js = table.find('tr.js_tpl');
	for (var i in response.result.code) {
		var line = response.result.code[i];
		var tpl = $(tpl_js).clone(true);
		tpl.removeClass('js_tpl');
		tpl.find('.js-cmd-code').text(i);
		if (typeof response.result.all[i] != 'undefined') {
			tpl.find('.js-cmd-name').html(line.PARSER_NAME+' <i class="fas fa-angle-down"></i>');
			tpl.find('.js-cmd-number input').val(response.result.all[i].NUMBER);
		} else {
			tpl.find('.js-cmd-name').html(line.PARSER_NAME);
		}
		tpl.find('.js-cmd-number input').attr('data-cmd_code',i);
		if (line.VARIANTS.length > 0) {
			tpl.find('.js-var-choice').hide();
			var li_js_tpl = tpl.find('.js-var-list li.js_tpl');
			remove_lines(tpl.find('.js-var-list'),'li');
			var iCnt = line.VARIANTS.length;
			for (var v = 0; v < iCnt; v++) {
				var li = $(li_js_tpl).clone(true);
				li.removeClass('js_tpl');
				li.find('.js-var-radio').attr('value',line.VARIANTS[v].ID);
				li.find('.js-var-radio').attr('data-cmd_code',i);
				if (iCnt == 1) {
					li.find('.js-var-radio').prop('checked',true);
				} else if (line.VARIANTS[v].CODE == i ) {
					li.find('.js-var-radio').prop('checked',true);
				}
				li.find('.js-var-name').text(line.VARIANTS[v].NAME);
				tpl.find('.js-var-list ul').append(li);
			}
			tpl.find('.js-var-list').show();
		} else {
			tpl.find('.js-var-list').hide();
		}
			var select = tpl.find('.js-ctr-cmd-list');
			select.attr('data-cmd_code',i);
			for (var c = 0; c < CountryTeams.length; c++) {
				if (typeof response.result.all[i] != 'undefined' && CountryTeams[c].id == response.result.all[i].ID && line.VARIANTS.length == 0) {
					select.append('<option value="'+CountryTeams[c].id+'" selected>'+CountryTeams[c].name+'</option>');
				} else {
					select.append('<option value="'+CountryTeams[c].id+'">'+CountryTeams[c].name+'</option>');
				}
			}
			tpl.find('.js-var-choice').show();
		//}
		tpl.find('.js-cmd-newname input').attr('data-cmd_code',i);
		table.append(tpl);
	}
}

function cmd_save() {
	var chy_id = $('#cmd_check_list').data('chy');
	var parser = $('#cmd_check_list').data('parser');
	//var table = $('.js-cmd-check tbody');
	var save_list = {};
	var radio = $('.js-var-radio');
	for (var i = 0; i < radio.length; i++) {
		if ($(radio[i]).prop('checked')) {
			var code = $(radio[i]).attr('data-cmd_code');
			save_list[code] = {
				cmd_id: $(radio[i]).val(),
				parse_code: code,
				new_name: 0,
				number: ''
			};
		}
	}
	var text = $('.js-cmd-newname input');
	for (var i = 0; i < text.length; i++) {
		if ($(text[i]).val() != '') {
			var code = $(text[i]).attr('data-cmd_code');
			if (typeof save_list[code] == 'undefined')
				save_list[code] = {
					cmd_id: 0,
					parse_code: code,
					new_name: $(text[i]).val(),
					number: ''
				};
		}
	}
	var list = $('.js-ctr-cmd-list');
	for (var i = 0; i < list.length; i++) {
		var selected = $(list[i]).val();
		if (selected) {
			var code = $(list[i]).attr('data-cmd_code');
			if (typeof code != 'undefined' && typeof save_list[code] == 'undefined')
				save_list[code] = {
					cmd_id: selected,
					parse_code: code,
					new_name: 0,
					number: ''
				};
		}
	}
	var number = $('.js-cmd-number input');
	for (var i = 0; i < number.length; i++) {
		if ($(number[i]).val() != '') {
			var code = $(number[i]).attr('data-cmd_code');
			
			save_list[code].number = $(number[i]).val();
		}
	}
	var data = {
		action: 'save_cmd',
		parser: parser,
		chy_id: chy_id,
		save_list: save_list
	}
	$.ajax({
		url: "/ajax/parser/",
		method: 'POST',
		data: data,
		dataType: 'json',
		success: function(response) {
			console.log('save response');
			console.log(response);
			$('.modal-footer .response').text('');
			if (response.error) {
				$('.modal-footer .response').text(response.error_msg);
			} else {
				$('.modal-footer .response').text(response.msg);
			}
		}
	});
	//$('#parserResponse').modal('hide');
}

function games_popup(response){
	var table = $('.js-game-check tbody');
	remove_lines(table,'tr');
	var tpl_js = table.find('tr.js_tpl');
	for (var i in response.result) {
		var line = response.result[i];
		var tpl = $(tpl_js).clone(true);
		tpl.removeClass('js_tpl');
		tpl.find('.js-gm-num').text(line.ID);
		tpl.find('.js-gm-date').text(line.GAME_DATE);
		tpl.find('.js-gm-teams').text(line.NAME);
		tpl.find('.js-gm-score').text(line.SCORES);
		table.append(tpl);
	}
}

function remove_lines(list,css_line) {
	var oldTr = list.find(css_line+':not(".js_tpl")');
	for (var i = 0; i < oldTr.length; i++) {
		$(oldTr[i]).empty().remove();
	}
}

function team_delete(el) {
	var team_id = $(el).attr('data-id');
	var data = {
		team_id: team_id,
		action: 'delete'
	}

	$.ajax({
		url: "/ajax/team/",
		method: 'POST',
		data: data,
		dataType: 'json',
		success: function(response) {
			if (response.error) {
				alert(response.error_msg);
			} else {
				$("tr.game-"+team_id).empty().remove();
			}
		},
		error: function() {
			alert('Ошибка сервера');
		}
	});
}


(function ($) {
    $.urlManager = function (method) {
        var self = this;

        self.get = function () {
            return self._getCurrentUrl();
        };

        self.set = function (params, path, replace) {
            self._updateUrl(params, path, replace);
        };

        /**
         * Get params from current url
         * @returns {Object} currentParams - current parameters in url
         */
        self._getCurrentUrl = function () {

            var currentParams = {};
            if (document.location.search != '') {
                var tmpParams = document.location.search.substr(1).split('&');
                var tmpArray = [];

                $.each(tmpParams, function (index, value) {
                    tmpArray = value.split('=');
                    currentParams[tmpArray[0]] = tmpArray[1];
                });
            }

            return currentParams;
        };

        self.getParamsString = function (values, exclude) {
            var newParams = [];

            $.each(values, function (index, value) {
                if (exclude && exclude.indexOf(value) > -1) return true;

                if (value != 'N' && value != '' && index.indexOf('ajax') == -1)
                    newParams.push([index, value].join('='));
            });

            return (newParams.length ? newParams.join('&') : '');
        };

        /**
         * Combine current url params with new params from filter and get final result
         * @param {Object} values - filter params
         * @returns {string} pathname - pathname with new parameters
         */
        self._getNewUrl = function (values, path) {
            var pathname = (path ? path : document.location.pathname);
            var newParamsStr = self.getParamsString(values, false);

            if (newParamsStr != '')
                return pathname + '?' + newParamsStr;
            else
                return pathname;
        };

        /**
         * Change page url, if history api is available
         * @param {Object} params - filter parameters.
         */
        self._updateUrl = function (params, path, replace) {
			console.log('_updateUrl');
			console.log(params);
			console.log(path);
			console.log(replace);
            var newUrl = self._getNewUrl(params, path);
			console.log('newUrl: '+newUrl);
            if (!replace && history.pushState) {
                history.pushState(null, null, newUrl);
            }
            else if (replace && history.replaceState) {
                history.replaceState(null, null, newUrl);
            }
            else
                document.location.href = newUrl;
        };

        if (typeof method == 'string' && method[0] != '_' && typeof self[method] == 'function')
            return self[method].apply(this, Array.prototype.slice.call(arguments, 1));

    }
}(jQuery));

$(document).ready(function() {
	handlers();
});


