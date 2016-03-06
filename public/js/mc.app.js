
var typundef = typeof undefined,
    typstr = typeof '',
    typobj = typeof {},
    typnull = typeof null,
    typfunc = typeof

function() {},
factor = {
        'gs': (1 / ((1 + Math.sqrt(5)) / 2))
    },
    $pselements;

(function($) {
    'use strict';

    $.ajaxSetup({
        cache: false
    });

})(jQuery);


function postDispatch() {
        'use strict';

        // MASTER SLIDER START
        var slider = new MasterSlider();
        slider.setup('masterslider', {
            width: 1140, // slider standard width
            height: 854, // slider standard height
            space: 0,
            speed: 10,
            layout: "fullwidth",
            centerControls: false,
            loop: true,
          autoplay: true
                    // more slider options goes here...
                    // check slider options section in documentation for more options.
        });
        // adds Arrows navigation control to the slider.
        slider.control('arrows');
        
        // $('#services-carousel').owlCarousel({
        //     items: 3,
        //     loop: true,
        //     margin: 30,
        //     responsiveClass: true,
        //     mouseDrag: true,
        //     dots: true,
        //     responsive: {
        //         0: {
        //             items: 1,
        //             nav: false,
        //             loop: true,
        //             autoplay: true,
        //             autoplayTimeout: 3000,
        //             autoplayHoverPause: true,
        //             responsiveClass: true,
        //             autoHeight: true
        //         },
        //         600: {
        //             items: 2,
        //             nav: false,
        //             loop: true,
        //             autoplay: true,
        //             autoplayTimeout: 3000,
        //             autoplayHoverPause: true,
        //             responsiveClass: true,
        //             autoHeight: true
        //         },
        //         1000: {
        //             items: 3,
        //             nav: false,
        //             loop: true,
        //             autoplay: true,
        //             autoplayTimeout: 3000,
        //             autoplayHoverPause: true,
        //             responsiveClass: true,
        //             mouseDrag: true,
        //             autoHeight: true
        //         }
        //     }
        // });

        // CLIENTS CAROUSEL START
        // $('#client-carousel').owlCarousel({
        //     items: 5,
        //     loop: true,
        //     margin: 30,
        //     responsiveClass: true,
        //     mouseDrag: true,
        //     dots: false,
        //     responsive: {
        //         0: {
        //             items: 2,
        //             nav: true,
        //             loop: true,
        //             autoplay: true,
        //             autoplayTimeout: 3000,
        //             autoplayHoverPause: true,
        //             responsiveClass: true
        //         },
        //         600: {
        //             items: 3,
        //             nav: true,
        //             loop: true,
        //             autoplay: true,
        //             autoplayTimeout: 3000,
        //             autoplayHoverPause: true,
        //             responsiveClass: true
        //         },
        //         1000: {
        //             items: 5,
        //             nav: true,
        //             loop: true,
        //             autoplay: true,
        //             autoplayTimeout: 3000,
        //             autoplayHoverPause: true,
        //             responsiveClass: true,
        //             mouseDrag: true
        //         }
        //     }
        // });
}


(function(jQuery) {
    jQuery.fn.equalHeight = function() {
        var heights = [];
        jQuery.each(this, function(i, element) {
            var jQueryelement = jQuery(element);
            var element_height;
            var includePadding = (jQueryelement.css('box-sizing') == 'border-box') || (jQueryelement.css('-moz-box-sizing') == 'border-box');
            if (includePadding) {
                element_height = jQueryelement.innerHeight();
            } else {
                element_height = jQueryelement.height();
            }
            heights.push(element_height);
        });
        this.height(Math.max.apply(window, heights));
        return this;
    };
    jQuery.fn.equalHeightGrid = function(columns) {
        var jQuerytiles = this;
        jQuerytiles.css('height', 'auto');
        for (var i = 0; i < jQuerytiles.length; i++) {
            if (i % columns === 0) {
                var row = jQuery(jQuerytiles[i]);
                for (var n = 1; n < columns; n++) {
                    row = row.add(jQuerytiles[i + n]);
                }
                row.equalHeight();
            }
        }
        return this;
    };
    jQuery.fn.detectGridColumns = function() {
        var offset = 0,
            cols = 0;
        this.each(function(i, elem) {
            var elem_offset = jQuery(elem).offset().top;
            if (offset === 0 || elem_offset == offset) {
                cols++;
                offset = elem_offset;
            } else {
                return false;
            }
        });
        return cols;
    };
    jQuery.fn.responsiveEqualHeightGrid = function() {
        var _this = this;

        function syncHeights() {
            var cols = _this.detectGridColumns();
            _this.equalHeightGrid(cols);
        }
        jQuery(window).bind('resize load', syncHeights);
        syncHeights();
        return this;
    };
    $('.equalcol').equalHeight();
})(jQuery);


// Simple JavaScript Templating
// John Resig - http://ejohn.org/ - MIT Licensed


(function(w) {
    var cache = {};

    w.tmpl = function tmpl(str, data) {
        // Figure out if we're getting a template, or if we need to
        // load the template - and be sure to cache the result.
        var fn = !/\W/.test(str) ?
            cache[str] = cache[str] ||
            tmpl(document.getElementById(str).innerHTML) :

            // Generate a reusable function that will serve as a template
            // generator (and which will be cached).
            new Function("obj",
                "var p=[],print=function(){p.push.apply(p,arguments);};" +

                // Introduce the data as local variables using with(){}
                "with(obj){p.push('" +

                // Convert the template into pure JavaScript
                str
                .replace(/[\r\t\n]/g, " ")
                .split("<%").join("\t")
                .replace(/((^|%>)[^\t]*)'/g, "$1\r")
                .replace(/\t=(.*?)%>/g, "',$1,'")
                .split("\t").join("');")
                .split("%>").join("p.push('")
                .split("\r").join("\\'") + "');}return p.join('');");

        // Provide some basic currying to the user
        return data ? fn(data) : fn;
    };
})(window);



var App, EventManager;

App = {
    isInView: function(el) {
        var eT = el.getBoundingClientRect().top,
            eH = 0;
        return ((eT + eH) > 0);
    },

    urlDeParam: function(a, n) {
        var c, b, d, e = {};
        a = (a || window.location.href);
        a = a.slice(a.indexOf('?') + 1);
        if ("" !== a)
            for (a = a.split("&"), c = 0; c < a.length; c += 1)
                b = a[c].split("="), d = decodeURIComponent(b[0]), b = 1 < b.length ? decodeURIComponent(b[1]) : void 0, e[d] = b;
        return (n != undefined && e[n]) ? e[n] : e
    },

    getRandomNumber: function(range) {
        return Math.floor(Math.random() * range);
    },
    getRandomChar: function() {
        var chars = "0123456789abcdefghijklmnopqurstuvwxyzABCDEFGHIJKLMNOPQURSTUVWXYZ";
        return chars.substr(this.getRandomNumber(62), 1);
    },
    getId: function(size) {
        var i = 0,
            str = '';
        for (i = 0; i < (size || 10); i += 1) {
            str += this.getRandomChar();
        };
        return str;
    },
    isUsrInGrp: function(usergroup, needle) {
        if (typeof usergroup == undef) usergroup = Data.SessionUser.role;
        usergroup = _.isNumber(usergroup) ? usergroup : Data.Roles['USR_' + usergroup.toUpperCase()];
        needle = _.isNumber(needle) ? needle : Data.Roles['GRP_' + needle.toUpperCase()];
        return ((parseInt(usergroup) & parseInt(needle)) !== 0);
    },
    isNullOrWhitespace: function(text) {
        if (text == null) {
            return true;
        }
        return text.replace(/\s/gi, '').length < 1;
    },
    trim: function(text) {
        return (text || '').replace(/\s/gi, '');
    },
    stripTags: function(a, b) {
        b = (((b || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join("");
        return a.replace(/\x3c!--[\s\S]*?--\x3e|<\?(?:php)?[\s\S]*?\?>/gi, "").replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/gi, function(a, c) {
            return -1 < b.indexOf("<" + c.toLowerCase() + ">") ? a : ""
        })
    },
    spinner: function() {
        var self = this;
        if (typeof fn === typundef) {
            var fn = {
                'start': new Function('_args',
                    "var options=_.extend(_args || {});" +
                    "$(options[0]).parent().prepend($spinner.removeClass('fa-3x').addClass('inline relative').attr('style',''));" +
                    "$(options[0]).hide();return _args;"
                ),
                'done': new Function('_args',
                    "var options=_.extend(_args || {});" +
                    "$(options[0]).parent().find('[data-spinner]').show();" +
                    "$(options[0]).remove();return null;"
                ).bind(arguments[0])
            };
        }
        fn.p = fn[self](arguments || fn.p);
    }

};


var EvM = EventManager = (function() {

    var defaults = {
        'debug': false,
        'listener': []
    };

    function notify(event, data) {
        data = data ? [data] : [];
        if (defaults.debug) console.log("app-notify: " + event, data);
        var n = 0;

        try {
            var _keys = Array.prototype.slice.call(Object.keys(defaults.listener));
        } catch (_ex) {
            var _keys = Array.prototype.slice.call([]);
        }

        if (jQuery.inArray(event, _keys)) {
            for (var id in defaults.listener[event]) {
                var l = defaults.listener[event][id];
                try {
                    if (defaults.debug) console.log(l);

                    setTimeout((function(l, data) {
                        l.func.apply(l.context, data);
                    })(l, data), 100);

                    n++;
                } catch (e) {
                    if ("console" in window && "error" in console) {}
                }
            }
        }
        return n;
    }

    function addListener(event, func, context, id) {

        context = context || window;
        id = id || "listener_" + App.getId(16);
        if (defaults.debug) console.log("app-event-id: " + id);

        if (event && context && func) {
            if (!defaults.listener[event]) {
                defaults.listener[event] = {};
            }
            defaults.listener[event][id] = {
                context: context,
                func: func
            };
            if (defaults.debug) {
                console.log("app-add: " + event, context, func, id);
                console.info(defaults.listener);
            }

            return this;
        }
        return false;
    }

    function remListener(event, id) {
        if (event in defaults.listener) {
            delete defaults.listener[event][id];
            return true;
        }
        return false;
    }

    function selection(o) {
        var selector = o.selector != undefined ? o.selector : document;
        jQuery(selector).disableSelection(o.attribute);
    }

    return {
        "notify": notify,
        "addListener": addListener,
        "remListener": remListener,
        "selection": selection,
        "defaults": defaults
    };

})(jQuery);

EvM.addListener('mc.jsonresponse', function(r) {

    if(!_.isUndefined(r.hasShowModal)) {
        $(r.hasShowModal.selector+'Label').empty().append(r.hasShowModal.label);
        $(r.hasShowModal.selector+'Content').empty().append(r.hasShowModal.content);
        showModal(r.hasShowModal.selector, {'listener':{
            'hidden.bs.modal': function(){ window.location.reload() }
        }});
        return;
    }
    for (var a in r) {

        console.log(r[a])

        if (r[a].type == undefined || r[a].content == undefined) continue;
        switch (r[a].type) {
            case 'replace':
                jQuery(a).replaceWith(r[a].content);
                break;
            case 'update':
                jQuery(a).empty().append(r[a].content);
                break;
            case 'append':
                jQuery(a).append(r[a].content);
                break;
            case 'prepend':
                jQuery(a).prepend(r[a].content);
                break;
            default:
                jQuery(a).empty().append(r[a].content);
                break;
        }
    }
});
EvM.addListener('mc.selection', EventManager.selection, EventManager);



EventManager.addListener('mc.searchfield', function(options) {
    switch (options.type) {
        case 'image':
            $(options.target).wrap($('<form />', {
                'class': 'jqform',
                'method': 'post',
                'action': '/page/ajax/' + options.type + '/id'
            }));
            break;
        case 'document':
            $(options.target).wrap($('<form />', {
                'class': 'jqform',
                'method': 'post',
                'action': '/page/ajax/' + options.type + '/id'
            }));
            break;
        default:
            null;
    }
});

EvM.addListener('mc.xeditable', function(options) {

    options.selector = options.selector || '[data-toggle="xeditable"]';

    $.fn.editable.defaults.mode = 'popup';
    $(options.selector).each(function() {
        var _op = $(this).data('xeditable-options') || {};
        $(this).editable(_op);
    });
});

var $pselements;
EvM.addListener('mc.scroll', function(options) {

    if (typeof options == typundef || typeof options.selector == typundef) options.selector = '.oyscroll';
    // options.selector += ':not(:has(.ps-container))';

    $(options.selector).each(function(i, elem) {
        if (typeof $(elem).attr('id') != typundef && App.trim($(elem).attr('id')) != '') $(elem).attr('id', App.getId(10));
    });
    $pselements = $(options.selector).perfectScrollbar({
            wheelSpeed: 2,
            suppressScrollX: true,
            scrollYMarginOffset: 10,
            wheelPropagation: false,
            swipePropagation: false,
            includePadding: true
        })
        .on('change', function(e) {
            e.preventDefault();
            $(this).perfectScrollbar('update');
        })
        .on('load', function(e) {
            e.preventDefault();
            $(this).perfectScrollbar('update');
        });
    $(options.selector).perfectScrollbar('update');


});
EvM.addListener('mc.jqform', function(options) {

    var _options = options;

    if (typeof options != typundef && typeof options.selector != typundef) {

        $(options.selector).each(function() {

            if (!_.isUndefined($(this).data('jqformAttach'))) return;
            $(this).attr('data-jqformAttach', true);

            $(this).unbind();
            $(this).on('submit', function(e) {
                $(this).validationEngine('attach', {
                    promptPosition: "centerRight",
                    scroll: false
                });
                options.event = e;
                e.preventDefault();

                var _params = '';
                if ($(this).attr('action').indexOf('_=') === -1) $(this).attr('action', $(this).attr('action') + '?_=' + Date.now());
                else if ($(this).attr('action').indexOf('_=') !== -1) $(this).attr('action', $(this).attr('action').replace(/_=[0-9]+/, '_=' + Date.now()));


                for (var i in $(this).data()) {
                    if (typeof $(this).data(i) == typstr) {
                        _params += '&' + i + '=' + $(this).data(i);
                    }
                }
                $(this).attr('action', $(this).attr('action') + _params);

                var precallReturn = true;
                if (typeof options.precall !== typundef) precallReturn = options.precall(this, options);
                if ($(this).find('.formError').length > 0 || precallReturn !== true) return false;

                $(this).ajaxSubmit({
                    "dataType": 'json',
                    "success": function(data) {
                        if (options.callback != undefined) {
                            options.callback(data);
                        }
                    }
                });
                return false;
            });
        });
    }

});


var _editors = [];
EvM.addListener('mc.wysihtml', function(options) {
    var selector = typeof options.selector == typundef ? ".wysihtml5, .whtml" : options.selector;
    if (typeof options.cleanWysihtml5 != typundef) {
        $('iframe.wysihtml5-sandbox, input[name="_wysihtml5_mode"], .wysihtml5-toolbar').remove();
        $("body").removeClass("wysihtml5-supported");
    }
    $(selector).each(function() {
        var _id = typeof this.id == typundef || this.id.length <= 0 ? App.getId() : this.id;
        $(this).attr('data-seeker-target', _id);
        $(this).attr('id', _id);
    });

    var _ed = $(selector).wysihtml5({
        "html": true,
        "formatBlock": false,
        "parserRules": (typeof options.wysihtml5ParserRules != typundef ? options.wysihtml5ParserRules : wysihtml5ParserRules),
        "parser": function(html) {
            return html;
        }
    });

    _editors.push(_ed);

});
EvM.addListener('mc.postDispatch', function(options) {

    $('a').each(function() {
        if (this.hostname && this.hostname !== location.hostname) {
            jQuery(this).attr({
                'target': '_blank'
            });
            if (!$(this).find('img'))
                jQuery(this).addClass('external-link');
        }
    });

    $('a.jsonload').each(function(){
        var $a=$(this);
        if('javascript:;' !== $a.attr('href')) {
            $a.attr('data-href', $a.attr('href'));
            $a.attr('href', 'javascript:;');
        }
        $a.on("click", function(e){
            e=e.originalEvent;
            e.preventDefault();
            $.get($(this).attr('data-href'),{},function(r){
                EvM.notify("mc.jsonresponse",r);
            },'json');
        })
    });

});

function showModal(a) {
    a = $(a);
    var b = $(".modal.in");
    0 < b.length ? b.one("hidden", function() {
        a.modal("show");
        a.one("hidden", function() {
            b.modal("show");
        });
    }).modal("hide") : a.modal("show");
};

(function(a) {
    var e = a.fn.attr;
    a.fn.attr = function() {
        var b, a, c, d;
        if (this[0] && 0 === arguments.length) {
            d = {};
            c = this[0].attributes;
            a = c.length;
            for (b = 0; b < a; b++) d[c[b].name.toLowerCase()] = c[b].value;
            return d
        }
        return e.apply(this, arguments)
    }
})(jQuery);

function strip_tags(a, b) {
    b = (((b || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join("");
    return a.replace(/\x3c!--[\s\S]*?--\x3e|<\?(?:php)?[\s\S]*?\?>/gi, "").replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/gi, function(a, c) {
        return -1 < b.indexOf("<" + c.toLowerCase() + ">") ? a : ""
    })
};
$.urlDeParam = function(n, a) {
    var c, b, d, e = {};
    a = (a || window.location.href);
    a = a.slice(a.indexOf('?') + 1);
    if ("" !== a)
        for (a = a.split("&"), c = 0; c < a.length; c += 1) b = a[c].split("="), d = decodeURIComponent(b[0]), b = 1 < b.length ? decodeURIComponent(b[1]) : void 0, e[d] = b;
    return (n != undefined && e[n]) ? e[n] : e
};
