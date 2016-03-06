"use strict";


var typundef = typeof undefined,
    typstr = typeof '',
    typobj = typeof {},
    typnull = typeof null,
    typfunc = typeof

function() {},
$pselements;

(function($) {
    $.ajaxSetup({
        cache: false
    });

})(jQuery);


var _cacheTpls = [];
var Editor = (function($) {

    var defaults = {}

    var init = function(options) {
        defaults = $.extend(defaults, options || {});
        prepare();
    }

    var prepare = function() {
        if (!jQuery('body').hasClass('editor')) jQuery('body').addClass('editor');

        var self = this;
        prepareLeader();
        $('.widgetHolder').each(function() {
            var $holder = $(this);
            prepareGroup($holder);

            $holder.find('.widget').each(function() {
                prepareWidget($holder, $(this));
            });
        });


        _.each(_cacheTpls, function(e) {
            // console.log(e)
        });

    }

    var prepareGroup = function($holder) {
        var entries = defaults.buttons.placement,
            i;

        for (i in defaults.buttons.placement) {
            var $attribs = $holder.attr('data-valid').split('|') || [];
            if (_.findWhere($attribs, i)) {
                entries[i].attributes = $holder.attr();
                entries[i].attributes['class'] = '';
                entries[i].attributes['data-type'] = i;
                entries[i].attributes['data-document'] = $holder.data('document');
                entries[i].attributes['data-action'] = 'create';
                entries[i].attributes['data-notify'] = 'mc.edit.createWidget';
            }
        }

        EvM.notify('mc.edit.getEditorGroupButton', {
            '$holder': $holder,
            'entries': entries,
            'attributes': $holder.attr(),
            'btnclass': 'btn btn-xs btn-primary',
            'notify': 'mc.edit.createWidget',
            'callback': function($btn) {
                $btn.addClass('absolute pull-right rt-40 zi1045');
                $btn.data('document', $holder.data('document'));
                $holder.append($btn);
            }
        });
    }

    var prepareWidget = function($holder, $widget) {
        var i, $attr = $widget[0].attributes,
            that = this;
        $attr['class'] = '';


        var entries = defaults.buttons.widget;
        for (i in defaults.buttons.widget) {

            if (i != 'divider') {
                entries[i].attributes = $widget.attr();
                entries[i].attributes['data-document'] = $holder.data('document');
                entries[i].attributes['data-notify'] = 'mc.edit.widgetAction';
                delete entries[i].attributes.id;
            }
        }

        EvM.notify('mc.edit.getEditorGroupButton', {
            '$holder': $holder,
            'entries': entries,
            'attributes': $attr,
            'btnclass': 'btn btn-xs btn-warning',
            'notify': 'mc.edit.widgetAction',
            'callback': function($btn) {
                $btn.addClass('absolute pull-right rb-30 zi1045');
                $widget.append($btn);
            }
        });
    }

    var prepareLeader = function() {

        EvM.notify('mc.edit.getEditorGroupButton', {
            'entries': defaults.buttons.leader,
            'attributes': {},
            'btnclass': 'btn btn-xs btn-success',
            'notify': 'mc.edit.rootAction',
            'callback': function($btn) {
                $btn.addClass('absolute pull-right r-30 t-10 zi1045');
                $('body').append($btn);
            }
        })
    }

    return {
        'defaults': defaults,
        'init': init
    }
})(jQuery);

EvM.addListener('mc.edit.createWidget', function(options) {
    var id = $(options).data('path').replace(/\/.*$/, '');
    var u = '/mc/widget/set/id/' + $(options).data('document') + '/token/' + $(options).data('token') + '/type/' + $(options).data('type') + '?path=' + $(options).data('path');
    $btn = $('<a />')
        .attr('class', 'fancybox fancybox.ajax')
        .attr('href', u).fancybox();
    $('body').append($btn.trigger('click').remove());
});
EvM.addListener('mc.edit.rootAction', function(options) {
    var $href, $tmp;
    switch ($(options).data('action')) {
        case 'document':
            $href = '/mc/' + $(options).data('action');
            break;
        case 'user':
            $href = '/mc/' + $(options).data('action');
            break;
        case 'images':
            $href = '/mc/' + $(options).data('action');
            break;
        case 'client':
            $href = '/mc/' + $(options).data('action');
            break;
        case 'logout':
            $href = '/' + $(options).data('action');
            window.location.href = $href;
            return;
            break;
        default:
            $href = '/mc/' + $(options).data('action');
            break;
    }
    $href += '/index';
    var $tmp = $('<a />')
        .attr('class', 'fancybox fancybox.ajax')
        .attr('href', $href);

    $('body').append($tmp);
    $tmp.fancybox().trigger('click').remove();

});
EvM.addListener('mc.edit.widgetAction', function(options) {
    var $target = $(options);

    switch ($target.data('action')) {
        case 'edit':
            var documentid = $target.data('document');
            var u = '/mc/widget/set/id/' + documentid + '/token/' + $target.data('token');
            var _fb = $('<a fancybox />').attr('class', 'fancybox.ajax').attr('href', u);
            $('body').append(_fb);
            _fb.fancybox().trigger('click').remove();

            break;
        case 'remove':
            if (confirm('wirklich?')) {
                $.get('/mc/widget/remove/id/' + $target.attr('data-id'), {
                    'id': $target.attr('data-id')
                }, function(data) {
                    if (!_.isUndefined(data.hasShowModal)) {
                        $(data.hasShowModal.selector + 'Label').empty().append(data.hasShowModal.label);
                        $(data.hasShowModal.selector + 'Content').empty().append(data.hasShowModal.content);
                        showModal(data.hasShowModal.selector, {
                            'listener': {
                                'hidden.bs.modal': function() {
                                    window.location.reload()
                                }
                            }
                        });
                    }

                }, 'json');
            }
            break;
        case 'create':
            var documentid = $target.data('document'); //$target.data('path').replace(/\/.*$/, '');
            var u = '/type/' + $target.data('type');

            $a = $('<a/>').attr('href', u + '?path=' + $target.data('path')).attr('class', 'fancybox.ajax');
            $('body').append($a);
            $a.fancybox().trigger('click');
            break;
        default:
            alert('no action found ... something went wrong');
            break;

    }
});
EvM.addListener('mc.edit.widgetHolderReload', function(options) {
    var _uri = window.location.href + ' div[data-path="' + options.parent + '"]';
    $('div[data-path="' + options.parent + '"]').load(_uri);
});
EvM.addListener('mc.edit.getEditorGroupButton', function(options) {

    if (typeof options == typfunc) return;
    if (typeof options.$holder !== typundef) var $holder = options.$holder;

    var $ul = $('<ul/>', {
            'class': 'dropdown-menu absolute zi1045'
        }),
        $li = $('<li/>'),
        $lis = [],
        $a, $_dummmy, $btn;

    for (var i in options.entries) {
        if ((typeof i == typstr) && i != 'divider') {
            var $a = $('<a/>')
                .attr('id', App.getId(24))
                .attr('href', 'javascript:;')
                .attr('data-action', i)
                .attr('data-type', options.entries[i].action)
                .attr('class', 'pull-right margin-right-10')
                .attr('data-notify', options.entries[i].notify).text(options.entries[i].title);

            var _tempAttribs = typeof options.entries[i].attributes != typundef ? options.entries[i].attributes : options.attributes;

            $a.attr(_.extend(_tempAttribs, $a.attr()));

            $a.removeAttr('data-effect-delay')
                .removeAttr('data-animation-effect')
                .removeAttr('style')
                .removeAttr('data-valid');

            $a.attr('id', App.getId(24));
            if (/createWidget/.test(options.notify)) {
                $a.attr('data-action', 'set');
                $a.attr('data-create', '1');
                var __o = $a.data();
                var __path = $a.data('path');
                delete __o['id'];
                delete __o['path'];
                var __p = $.map(Object.getOwnPropertyNames(__o), function(k) {
                    return [k, __o[k]].join('/')
                }).join('/');
                __p = '/mc/widget/' + __o['action'] + '/' + __p + (typeof __path !== 'undefined' ? '?path=' + __path : '');
                $a.attr('class', 'fancybox fancybox.ajax').attr('href', __p.replace(/document/, 'id')).fancybox();
            }
            if (/widgetAction/i.test(options.notify)) {
                if ($a.data('action') == 'edit') $a.data('action', 'set');
                var __o = $a.data();
                var __path = $a.data('path');
                delete __o['id'];
                delete __o['path'];
                var __p = $.map(Object.getOwnPropertyNames(__o), function(k) {
                    return [k, __o[k]].join('/')
                }).join('/');
                __p = '/mc/widget/' + __o['action'] + '/' + __p + (typeof __path !== 'undefined' ? '?path=' + __path : '');
                var __tclass = __o['action'] == 'remove' ? 'remove' : 'fancybox fancybox.ajax';
                $a.attr('class', __tclass).attr('href', __p.replace(/document/, 'id'));
                if (__o['action'] != 'remove') $a.fancybox();
            }
            if (/rootAction/.test(options.notify)) {
                var u = ['/mc', $a.data('action'), 'index'].join('/');
                $a.attr('class', 'fancybox fancybox.ajax').attr('href', u).fancybox();
            }

            $a.on('click', function(e) {
                var self = $(this);
                if ($(this).hasClass('remove')) {
                    e.preventDefault();
                    if (confirm('wirklich?')) {
                        $.get('/mc/widget/remove/id/' + self.attr('data-id'), {
                            'id': self.attr('data-id')
                        }, function(data) {
                            if (!_.isUndefined(data.hasShowModal)) {
                                $(data.hasShowModal.selector + 'Label').empty().append(data.hasShowModal.label);
                                $(data.hasShowModal.selector + 'Content').empty().append(data.hasShowModal.content);
                                showModal(data.hasShowModal.selector, {
                                    'listener': {
                                        'hidden.bs.modal': function() {
                                            window.location.reload()
                                        }
                                    }
                                });
                            }

                        }, 'json');
                    }
                    return false;
                }
                if (!$(this).attr('href')) {
                    e.preventDefault();
                    if ($(this).data('notify')) {
                        EvM.notify($(this).data('notify'), this);
                    } else {
                        EvM.notify(options.notify, this);
                    }
                }

            }).prepend($('<i/>', {
                'class': 'fa fa-info-circle fa-fw margin-right-5',
                'data-placement': 'left',
                'data-toggle': 'popover',
                'data-content': options.entries[i].tooltip,
                'title': 'Hilfe - ' + options.entries[i].title
            }));


        };

        var _temp = i == 'divider' ? $li.clone().addClass('divider') : $li.clone().append($a);
        $lis.push($ul.append(_temp));
    }

    $btn = $('<div/>').attr('class', 'inlineAction btn-group').attr('id', App.getId(24))
        .append($('<a/>').attr('class', options.btnclass + ' dropdown-toggle buttons-radio absolute zi1024')
            .attr('id', App.getId(24))
            .attr('data-toggle', 'dropdown').append($('<span>', {
                'class': 'caret'
            })))
        .append($lis);

    if (typeof options.callback != typundef) options.callback($btn);
});
