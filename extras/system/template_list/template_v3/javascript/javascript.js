var _auto_focus = [];
$(document).ready(function() {
    if (typeof($().lazy) != "undefined" && $.isFunction($().lazy) == true) {
        $('.lazy').lazy({
            threshold: 0,
            beforeLoad: function(element) {
                $(element).removeClass('lazy_loader');
            }
        });
    }
    $(".alpha").keypress("keyup blur change focus", function(event) {
    	 var inputValue = event.charCode;
        if(!(inputValue >= 65 && inputValue <= 122) && (inputValue != 32 && inputValue != 0)){
            event.preventDefault();
        }
      //  ("" != this.value || null != this.value) && $(this).val($(this).val().replace(/[^a-zA-Z]/, ""))
    });
    $(".company_name").keypress("keyup blur change focus", function(event) {
         var inputValue = event.charCode;
        if(!(inputValue >= 65 && inputValue <= 122) && (inputValue != 32 && inputValue != 0)){
            event.preventDefault();
        }
      //  ("" != this.value || null != this.value) && $(this).val($(this).val().replace(/[^a-zA-Z]/, ""))
    });

    _auto_focus = $('.auto-focus').map(function() {
        return this.id;
    }).get();
});
function alphanumeric(event){
     var keyCode = event.keyCode || event.which
    //console.log(keyCode);
    if (keyCode == 8 || keyCode == 32 || keyCode == 9 || keyCode == 116 || keyCode == 123) { // Backspace, tab
      return;
    }         
    var regex = new RegExp("^[a-zA-Z0-9]+$");
    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);

    if (!regex.test(key)) {
      event.preventDefault();
      return false;
    }
}
function auto_focus_input(cur_ele) {
    if ($('#' + cur_ele).hasClass('auto-focus')) {
        var _tmp_index = _focus_index = _auto_focus.indexOf(cur_ele);
        while (_focus_index = _auto_focus[++_tmp_index]) {
            if ($('#' + _focus_index).is(':visible:not([disabled])')) {
                $('#' + _focus_index).focus();
                break;
            }
        }
    }
}

function highlight_search_text(searched_text, value, label) {
    var auto_suggest_value = '';
    if (searched_text != '') {
        auto_suggest_value = String(value).replace(new RegExp(searched_text, "gi"), "<strong>$&</strong>");
    } else {
        auto_suggest_value = label;
    }
    return auto_suggest_value;
}
$(document).ready(function() {
    $('.numeric').keypress("keyup blur change focus", function(event) {
        if (this.value != '' || this.value != null) {
           // $(this).val($(this).val().replace(/[^-?][^0-9.]/, ''));
           var keycode = event.which;
		    if (!(event.shiftKey == false && (keycode == 46 || keycode == 8 || keycode == 37 || keycode == 39 || (keycode >= 48 && keycode <= 57)))) {
		        event.preventDefault();
		    }
        }
    });
    $('.mobile_number').keypress("keyup blur change focus", function(event) {
        if (this.value != '' || this.value != null) {
           // $(this).val($(this).val().replace(/[^-?][^0-9.]/, ''));
           var keycode = event.which;
            if (!(event.shiftKey == false && (keycode == 46 || keycode == 8 || keycode == 37 || keycode == 39 || (keycode >= 48 && keycode <= 57)))) {
                event.preventDefault();
            }
        }
    });
    $(document).on('click', '.toggle-opitional-view', function() {
        $('.optional-view').toggle();
    });
    $(document).on('click', '.minTable', function() {});
    $(document).ajaxComplete(function() {
        $("body").css({
            "cursor": "default",
            "opacity": "1"
        });
    });
    $(window).load(function() {
        $('.content').css('opacity', '1');
    });
    $(window).unload(function() {
        $('.content').css('opacity', '.1');
    });
    $(document).keydown(function(e) {
        if (e.keyCode == 27) {
            exitFullscreen();
        }
    });
});

function toggle_active_class(thisRef) {
    if ($(thisRef).hasClass('active')) {
        $(thisRef).removeClass('active');
    } else {
        $(thisRef).addClass('active');
    }
}

function launchIntoFullscreen(element) {
    hideFullScreenKey();
    if (element.requestFullscreen) {
        element.requestFullscreen();
    } else if (element.mozRequestFullScreen) {
        element.mozRequestFullScreen();
    } else if (element.webkitRequestFullscreen) {
        element.webkitRequestFullscreen();
    } else if (element.msRequestFullscreen) {
        element.msRequestFullscreen();
    }
}

function showFullScreenKey() {
    $('#nsVisibility').addClass('hide');
    $('#fsVisibility').removeClass('hide');
}

function hideFullScreenKey() {
    $('#fsVisibility').addClass('hide');
    $('#nsVisibility').removeClass('hide');
}

function exitFullscreen() {
    showFullScreenKey();
    if (document.exitFullscreen) {
        document.exitFullscreen();
    } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
    }
}
'use strict';
if (typeof jQuery === "undefined") {
    throw new Error("AdminLTE requires jQuery");
}
$.AdminLTE = {};
$.AdminLTE.options = {
    navbarMenuSlimscroll: true,
    navbarMenuSlimscrollWidth: "3px",
    navbarMenuHeight: "200px",
    sidebarToggleSelector: "[data-toggle='offcanvas']",
    sidebarPushMenu: true,
    sidebarSlimScroll: true,
    sidebarExpandOnHover: false,
    enableBoxRefresh: true,
    enableBSToppltip: true,
    BSTooltipSelector: "[data-toggle='tooltip']",
    enableFastclick: true,
    enableControlSidebar: true,
    controlSidebarOptions: {
        toggleBtnSelector: "[data-toggle='control-sidebar']",
        selector: ".control-sidebar",
        slide: true
    },
    enableBoxWidget: true,
    boxWidgetOptions: {
        boxWidgetIcons: {
            collapse: 'fa-minus',
            open: 'fa-plus',
            remove: 'fa-times'
        },
        boxWidgetSelectors: {
            remove: '[data-widget="remove"]',
            collapse: '[data-widget="collapse"]'
        }
    },
    directChat: {
        enable: true,
        contactToggleSelector: '[data-widget="chat-pane-toggle"]'
    },
    colors: {
        lightBlue: "#3c8dbc",
        red: "#f56954",
        green: "#00a65a",
        aqua: "#00c0ef",
        yellow: "#f39c12",
        blue: "#0073b7",
        navy: "#001F3F",
        teal: "#39CCCC",
        olive: "#3D9970",
        lime: "#01FF70",
        orange: "#FF851B",
        fuchsia: "#F012BE",
        purple: "#8E24AA",
        maroon: "#D81B60",
        black: "#222222",
        gray: "#d2d6de"
    },
    screenSizes: {
        xs: 480,
        sm: 768,
        md: 992,
        lg: 1200
    }
};
$(function() {
    if (typeof AdminLTEOptions !== "undefined") {
        $.extend(true, $.AdminLTE.options, AdminLTEOptions);
    }
    var o = $.AdminLTE.options;
    _init();
    $.AdminLTE.layout.activate();
    $.AdminLTE.tree('.sidebar');
    if (o.enableControlSidebar) {
        $.AdminLTE.controlSidebar.activate();
    }
    if (o.navbarMenuSlimscroll && typeof $.fn.slimscroll != 'undefined') {
        $(".navbar .menu").slimscroll({
            height: o.navbarMenuHeight,
            alwaysVisible: false,
            size: o.navbarMenuSlimscrollWidth
        }).css("width", "100%");
    }
    if (o.sidebarPushMenu) {
        $.AdminLTE.pushMenu.activate(o.sidebarToggleSelector);
    }
    if (o.enableBSToppltip) {
        $('body').tooltip({
            selector: o.BSTooltipSelector
        });
    }
    if (o.enableBoxWidget) {
        $.AdminLTE.boxWidget.activate();
    }
    if (o.enableFastclick && typeof FastClick != 'undefined') {
        FastClick.attach(document.body);
    }
    if (o.directChat.enable) {
        $(o.directChat.contactToggleSelector).on('click', function() {
            var box = $(this).parents('.direct-chat').first();
            box.toggleClass('direct-chat-contacts-open');
        });
    }
    $('.btn-group[data-toggle="btn-toggle"]').each(function() {
        var group = $(this);
        $(this).find(".btn").on('click', function(e) {
            group.find(".btn.active").removeClass("active");
            $(this).addClass("active");
            e.preventDefault();
        });
    });
});

function _init() {
    $.AdminLTE.layout = {
        activate: function() {
            var _this = this;
            _this.fix();
            _this.fixSidebar();
            $(window, ".wrapper").resize(function() {
                _this.fix();
                _this.fixSidebar();
            });
        },
        fix: function() {
            var neg = $('.main-header').outerHeight() + $('.main-footer').outerHeight();
            var window_height = $(window).height();
            var sidebar_height = $(".sidebar").height();
            if ($("body").hasClass("fixed")) {
                $(".content-wrapper, .right-side").css('min-height', window_height - $('.main-footer').outerHeight());
            } else {
                var postSetWidth;
                if (window_height >= sidebar_height) {
                    $(".content-wrapper, .right-side").css('min-height', window_height - neg);
                    postSetWidth = window_height - neg;
                } else {
                    $(".content-wrapper, .right-side").css('min-height', sidebar_height);
                    postSetWidth = sidebar_height;
                }
                var controlSidebar = $($.AdminLTE.options.controlSidebarOptions.selector);
                if (typeof controlSidebar !== "undefined") {
                    if (controlSidebar.height() > postSetWidth) $(".content-wrapper, .right-side").css('min-height', controlSidebar.height());
                }
            }
        },
        fixSidebar: function() {
            if (!$("body").hasClass("fixed")) {
                if (typeof $.fn.slimScroll != 'undefined') {
                    $(".sidebar").slimScroll({
                        destroy: true
                    }).height("auto");
                }
                return;
            } else if (typeof $.fn.slimScroll == 'undefined' && console) {
                console.error("Error: the fixed layout requires the slimscroll plugin!");
            }
            if ($.AdminLTE.options.sidebarSlimScroll) {
                if (typeof $.fn.slimScroll != 'undefined') {
                    $(".sidebar").slimScroll({
                        destroy: true
                    }).height("auto");
                    $(".sidebar").slimscroll({
                        height: ($(window).height() - $(".main-header").height()) + "px",
                        color: "rgba(0,0,0,0.2)",
                        size: "3px"
                    });
                }
            }
        }
    };
    $.AdminLTE.pushMenu = {
        activate: function(toggleBtn) {
            var screenSizes = $.AdminLTE.options.screenSizes;
            $(toggleBtn).on('click', function(e) {
                e.preventDefault();
                if ($(window).width() > (screenSizes.sm - 1)) {
                    $("body").toggleClass('sidebar-collapse');
                } else {
                    if ($("body").hasClass('sidebar-open')) {
                        $("body").removeClass('sidebar-open');
                        $("body").removeClass('sidebar-collapse')
                    } else {
                        $("body").addClass('sidebar-open');
                    }
                }
            });
            $(".content-wrapper").click(function() {
                if ($(window).width() <= (screenSizes.sm - 1) && $("body").hasClass("sidebar-open")) {
                    $("body").removeClass('sidebar-open');
                }
            });
            if ($.AdminLTE.options.sidebarExpandOnHover || ($('body').hasClass('fixed') && $('body').hasClass('sidebar-mini'))) {
                this.expandOnHover();
            }
        },
        expandOnHover: function() {
            var _this = this;
            var screenWidth = $.AdminLTE.options.screenSizes.sm - 1;
            $('.main-sidebar').hover(function() {
                if ($('body').hasClass('sidebar-mini') && $("body").hasClass('sidebar-collapse') && $(window).width() > screenWidth) {
                    _this.expand();
                }
            }, function() {
                if ($('body').hasClass('sidebar-mini') && $('body').hasClass('sidebar-expanded-on-hover') && $(window).width() > screenWidth) {
                    _this.collapse();
                }
            });
        },
        expand: function() {
            $("body").removeClass('sidebar-collapse').addClass('sidebar-expanded-on-hover');
        },
        collapse: function() {
            if ($('body').hasClass('sidebar-expanded-on-hover')) {
                $('body').removeClass('sidebar-expanded-on-hover').addClass('sidebar-collapse');
            }
        }
    };
    $.AdminLTE.tree = function(menu) {
        var _this = this;
        $("li a", $(menu)).on('click', function(e) {
            var $this = $(this);
            var checkElement = $this.next();
            if ((checkElement.is('.treeview-menu')) && (checkElement.is(':visible'))) {
                checkElement.slideUp('normal', function() {
                    checkElement.removeClass('menu-open');
                });
                checkElement.parent("li").removeClass("active");
            } else if ((checkElement.is('.treeview-menu')) && (!checkElement.is(':visible'))) {
                var parent = $this.parents('ul').first();
                var ul = parent.find('ul:visible').slideUp('normal');
                ul.removeClass('menu-open');
                var parent_li = $this.parent("li");
                checkElement.slideDown('normal', function() {
                    checkElement.addClass('menu-open');
                    parent.find('li.active').removeClass('active');
                    parent_li.addClass('active');
                    _this.layout.fix();
                });
            }
            if (checkElement.is('.treeview-menu')) {
                e.preventDefault();
            }
        });
    };
    $.AdminLTE.controlSidebar = {
        activate: function() {
            var _this = this;
            var o = $.AdminLTE.options.controlSidebarOptions;
            var sidebar = $(o.selector);
            var btn = $(o.toggleBtnSelector);
            btn.on('click', function(e) {
                e.preventDefault();
                if (!sidebar.hasClass('control-sidebar-open') && !$('body').hasClass('control-sidebar-open')) {
                    _this.open(sidebar, o.slide);
                } else {
                    _this.close(sidebar, o.slide);
                }
            });
            var bg = $(".control-sidebar-bg");
            _this._fix(bg);
            if ($('body').hasClass('fixed')) {
                _this._fixForFixed(sidebar);
            } else {
                if ($('.content-wrapper, .right-side').height() < sidebar.height()) {
                    _this._fixForContent(sidebar);
                }
            }
        },
        open: function(sidebar, slide) {
            var _this = this;
            if (slide) {
                sidebar.addClass('control-sidebar-open');
            } else {
                $('body').addClass('control-sidebar-open');
            }
        },
        close: function(sidebar, slide) {
            if (slide) {
                sidebar.removeClass('control-sidebar-open');
            } else {
                $('body').removeClass('control-sidebar-open');
            }
        },
        _fix: function(sidebar) {
            var _this = this;
            if ($("body").hasClass('layout-boxed')) {
                sidebar.css('position', 'absolute');
                sidebar.height($(".wrapper").height());
                $(window).resize(function() {
                    _this._fix(sidebar);
                });
            } else {
                sidebar.css({
                    'position': 'fixed',
                    'height': 'auto'
                });
            }
        },
        _fixForFixed: function(sidebar) {
            sidebar.css({
                'position': 'fixed',
                'max-height': '100%',
                'overflow': 'auto',
                'padding-bottom': '50px'
            });
        },
        _fixForContent: function(sidebar) {
            $(".content-wrapper, .right-side").css('min-height', sidebar.height());
        }
    };
    $.AdminLTE.boxWidget = {
        selectors: $.AdminLTE.options.boxWidgetOptions.boxWidgetSelectors,
        icons: $.AdminLTE.options.boxWidgetOptions.boxWidgetIcons,
        activate: function() {
            var _this = this;
            $(_this.selectors.collapse).on('click', function(e) {
                e.preventDefault();
                _this.collapse($(this));
            });
            $(_this.selectors.remove).on('click', function(e) {
                e.preventDefault();
                _this.remove($(this));
            });
        },
        collapse: function(element) {
            var _this = this;
            var box = element.parents(".box").first();
            var box_content = box.find("> .box-body, > .box-footer");
            if (!box.hasClass("collapsed-box")) {
                element.children(":first").removeClass(_this.icons.collapse).addClass(_this.icons.open);
                box_content.slideUp(300, function() {
                    box.addClass("collapsed-box");
                });
            } else {
                element.children(":first").removeClass(_this.icons.open).addClass(_this.icons.collapse);
                box_content.slideDown(300, function() {
                    box.removeClass("collapsed-box");
                });
            }
        },
        remove: function(element) {
            var box = element.parents(".box").first();
            box.slideUp();
        }
    };
}(function($) {
    $.fn.boxRefresh = function(options) {
        var settings = $.extend({
            trigger: ".refresh-btn",
            source: "",
            onLoadStart: function(box) {},
            onLoadDone: function(box) {}
        }, options);
        var overlay = $('<div class="overlay"><div class="fa fa-refresh fa-spin"></div></div>');
        return this.each(function() {
            if (settings.source === "") {
                if (console) {
                    console.log("Please specify a source first - boxRefresh()");
                }
                return;
            }
            var box = $(this);
            var rBtn = box.find(settings.trigger).first();
            rBtn.on('click', function(e) {
                e.preventDefault();
                start(box);
                box.find(".box-body").load(settings.source, function() {
                    done(box);
                });
            });
        });

        function start(box) {
            box.append(overlay);
            settings.onLoadStart.call(box);
        }

        function done(box) {
            box.find(overlay).remove();
            settings.onLoadDone.call(box);
        }
    };
})(jQuery);
(function($) {
    $.fn.todolist = function(options) {
        var settings = $.extend({
            onCheck: function(ele) {},
            onUncheck: function(ele) {}
        }, options);
        return this.each(function() {
            if (typeof $.fn.iCheck != 'undefined') {
                $('input', this).on('ifChecked', function(event) {
                    var ele = $(this).parents("li").first();
                    ele.toggleClass("done");
                    settings.onCheck.call(ele);
                });
                $('input', this).on('ifUnchecked', function(event) {
                    var ele = $(this).parents("li").first();
                    ele.toggleClass("done");
                    settings.onUncheck.call(ele);
                });
            } else {
                $('input', this).on('change', function(event) {
                    var ele = $(this).parents("li").first();
                    ele.toggleClass("done");
                    settings.onCheck.call(ele);
                });
            }
        });
    };
}(jQuery));;
(function($) {
    'use strict';
    $.fn.provabPopup = function(options, callback) {
        if ($.isFunction(options)) {
            callback = options;
            options = null;
        }
        var o = $.extend({}, $.fn.provabPopup.defaults, options);
        if (!o.scrollBar) $('html').css('overflow', 'hidden');
        var $popup = this,
            d = $(document),
            w = window,
            $w = $(w),
            wH = windowHeight(),
            wW = windowWidth(),
            prefix = '__provab-popup',
            isIOS6X = (/OS 6(_\d)+/i).test(navigator.userAgent),
            buffer = 200,
            popups = 0,
            id, inside, fixedVPos, fixedHPos, fixedPosStyle, vPos, hPos, height, width, debounce, autoCloseTO;
        $popup.close = function() {
            close();
        };
        $popup.reposition = function(animateSpeed) {
            reposition(animateSpeed);
        };
        return $popup.each(function() {
            if ($(this).data('provabPopup')) return;
            init();
        });

        function init() {
            triggerCall(o.onOpen);
            popups = ($w.data('provabPopup') || 0) + 1, id = prefix + popups + '__', fixedVPos = o.position[1] !== 'auto', fixedHPos = o.position[0] !== 'auto', fixedPosStyle = o.positionStyle === 'fixed', height = $popup.outerHeight(true), width = $popup.outerWidth(true);
            o.loadUrl ? createContent() : open();
        };

        function createContent() {
            o.contentContainer = $(o.contentContainer || $popup);
            switch (o.content) {
                case ('iframe'):
                    var iframe = $('<iframe class="b-iframe" ' + o.iframeAttr + '></iframe>');
                    iframe.appendTo(o.contentContainer);
                    height = $popup.outerHeight(true);
                    width = $popup.outerWidth(true);
                    open();
                    iframe.attr('src', o.loadUrl);
                    triggerCall(o.loadCallback);
                    break;
                case ('image'):
                    open();
                    $('<img />').load(function() {
                        triggerCall(o.loadCallback);
                        recenter($(this));
                    }).attr('src', o.loadUrl).hide().appendTo(o.contentContainer);
                    break;
                default:
                    open();
                    $('<div class="b-ajax-wrapper"></div>').load(o.loadUrl, o.loadData, function(response, status, xhr) {
                        triggerCall(o.loadCallback, status);
                        recenter($(this));
                    }).hide().appendTo(o.contentContainer);
                    break;
            }
        };

        function open() {
            if (o.modal) {
                $('<div class="pro-modal ' + id + '"></div>').css({
                    backgroundColor: o.modalColor,
                    position: 'fixed',
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 0,
                    opacity: 0,
                    zIndex: o.zIndex + popups
                }).appendTo(o.appendTo).fadeTo(o.speed, o.opacity);
            }
            calcPosition();
            $popup.data('provabPopup', o).data('id', id).css({
                'left': o.transition == 'slideIn' || o.transition == 'slideBack' ? (o.transition == 'slideBack' ? d.scrollLeft() + wW : (hPos + width) * -1) : getLeftPos(!(!o.follow[0] && fixedHPos || fixedPosStyle)),
                'position': o.positionStyle || 'absolute',
                'top': o.transition == 'slideDown' || o.transition == 'slideUp' ? (o.transition == 'slideUp' ? d.scrollTop() + wH : vPos + height * -1) : getTopPos(!(!o.follow[1] && fixedVPos || fixedPosStyle)),
                'z-index': o.zIndex + popups + 1
            }).each(function() {
                if (o.appending) {
                    $(this).appendTo(o.appendTo);
                }
            });
            doTransition(true);
        };

        function close() {
            if (o.modal) {
                $('.pro-modal.' + $popup.data('id')).fadeTo(o.speed, 0, function() {
                    $(this).remove();
                });
            }
            unbindEvents();
            clearTimeout(autoCloseTO);
            doTransition();
            return false;
        };

        function reposition(animateSpeed) {
            wH = windowHeight();
            wW = windowWidth();
            inside = insideWindow();
            if (inside) {
                clearTimeout(debounce);
                debounce = setTimeout(function() {
                    calcPosition();
                    animateSpeed = animateSpeed || o.followSpeed;
                    $popup.dequeue().each(function() {
                        if (fixedPosStyle) {
                            $(this).css({
                                'left': hPos,
                                'top': vPos
                            });
                        } else {
                            $(this).animate({
                                'left': o.follow[0] ? getLeftPos(true) : 'auto',
                                'top': o.follow[1] ? getTopPos(true) : 'auto'
                            }, animateSpeed, o.followEasing);
                        }
                    });
                }, 50);
            }
        };

        function recenter(content) {
            var _width = content.width(),
                _height = content.height(),
                css = {};
            o.contentContainer.css({
                height: _height,
                width: _width
            });
            if (_height >= $popup.height()) {
                css.height = $popup.height();
            }
            if (_width >= $popup.width()) {
                css.width = $popup.width();
            }
            height = $popup.outerHeight(true), width = $popup.outerWidth(true);
            calcPosition();
            o.contentContainer.css({
                height: 'auto',
                width: 'auto'
            });
            css.left = getLeftPos(!(!o.follow[0] && fixedHPos || fixedPosStyle)), css.top = getTopPos(!(!o.follow[1] && fixedVPos || fixedPosStyle));
            $popup.animate(css, 250, function() {
                content.show();
                inside = insideWindow();
            });
        };

        function bindEvents() {
            $w.data('provabPopup', popups);
            $popup.delegate('.proClose, .' + o.closeClass, 'click.' + id, close);
            if (o.modalClose) {
                $('.pro-modal.' + id).css('cursor', 'pointer').bind('click', close);
            }
            if (!isIOS6X && (o.follow[0] || o.follow[1])) {
                $w.bind('scroll.' + id, function() {
                    if (inside) {
                        $popup.dequeue().animate({
                            'left': o.follow[0] ? getLeftPos(!fixedPosStyle) : 'auto',
                            'top': o.follow[1] ? getTopPos(!fixedPosStyle) : 'auto'
                        }, o.followSpeed, o.followEasing);
                    }
                }).bind('resize.' + id, function() {
                    reposition();
                });
            }
            if (o.escClose) {
                d.bind('keydown.' + id, function(e) {
                    if (e.which == 27) {
                        close();
                    }
                });
            }
        };

        function unbindEvents() {
            if (!o.scrollBar) {
                $('html').css('overflow', 'auto');
            }
            $('.pro-modal.' + id).unbind('click');
            d.unbind('keydown.' + id);
            $w.unbind('.' + id).data('provabPopup', ($w.data('provabPopup') - 1 > 0) ? $w.data('provabPopup') - 1 : null);
            $popup.undelegate('.proClose, .' + o.closeClass, 'click.' + id, close).data('provabPopup', null);
        };

        function doTransition(open) {
            switch (open ? o.transition : o.transitionClose || o.transition) {
                case "slideIn":
                    animate({
                        left: open ? getLeftPos(!(!o.follow[0] && fixedHPos || fixedPosStyle)) : d.scrollLeft() - (width || $popup.outerWidth(true)) - buffer
                    });
                    break;
                case "slideBack":
                    animate({
                        left: open ? getLeftPos(!(!o.follow[0] && fixedHPos || fixedPosStyle)) : d.scrollLeft() + wW + buffer
                    });
                    break;
                case "slideDown":
                    animate({
                        top: open ? getTopPos(!(!o.follow[1] && fixedVPos || fixedPosStyle)) : d.scrollTop() - (height || $popup.outerHeight(true)) - buffer
                    });
                    break;
                case "slideUp":
                    animate({
                        top: open ? getTopPos(!(!o.follow[1] && fixedVPos || fixedPosStyle)) : d.scrollTop() + wH + buffer
                    });
                    break;
                default:
                    $popup.stop().fadeTo(o.speed, open ? 1 : 0, function() {
                        onCompleteCallback(open);
                    });
            }

            function animate(css) {
                $popup.css({
                    display: 'block',
                    opacity: 1
                }).animate(css, o.speed, o.easing, function() {
                    onCompleteCallback(open);
                });
            };
        };

        function onCompleteCallback(open) {
            if (open) {
                bindEvents();
                triggerCall(callback);
                if (o.autoClose) {
                    autoCloseTO = setTimeout(close, o.autoClose);
                }
            } else {
                $popup.hide();
                triggerCall(o.onClose);
                if (o.loadUrl) {
                    o.contentContainer.empty();
                    $popup.css({
                        height: 'auto',
                        width: 'auto'
                    });
                }
            }
        };

        function getLeftPos(includeScroll) {
            return includeScroll ? hPos + d.scrollLeft() : hPos;
        };

        function getTopPos(includeScroll) {
            return includeScroll ? vPos + d.scrollTop() : vPos;
        };

        function triggerCall(func, arg) {
            $.isFunction(func) && func.call($popup, arg);
        };

        function calcPosition() {
            vPos = fixedVPos ? o.position[1] : Math.max(0, ((wH - $popup.outerHeight(true)) / 2) - o.amsl), hPos = fixedHPos ? o.position[0] : (wW - $popup.outerWidth(true)) / 2, inside = insideWindow();
        };

        function insideWindow() {
            return wH > $popup.outerHeight(true) && wW > $popup.outerWidth(true);
        };

        function windowHeight() {
            return w.innerHeight || $w.height();
        };

        function windowWidth() {
            return w.innerWidth || $w.width();
        };
    };
    $.fn.provabPopup.defaults = {
        amsl: 50,
        appending: true,
        appendTo: 'body',
        autoClose: false,
        closeClass: 'pop-close',
        content: 'ajax',
        contentContainer: false,
        easing: 'swing',
        escClose: true,
        follow: [true, true],
        followEasing: 'swing',
        followSpeed: 500,
        iframeAttr: 'scrolling="no" frameborder="0"',
        loadCallback: false,
        loadData: false,
        loadUrl: false,
        modal: true,
        modalClose: true,
        modalColor: '#000',
        onClose: false,
        onOpen: false,
        opacity: 0.7,
        position: ['auto', 'auto'],
        positionStyle: 'absolute',
        scrollBar: true,
        speed: 250,
        transition: 'fadeIn',
        transitionClose: false,
        zIndex: 9997
    };
})(jQuery);;
(function($, window) {
    "use strict";
    var $body = null,
        data = {},
        trueMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test((window.navigator.userAgent || window.navigator.vendor || window.opera)),
        transitionEvent, transitionSupported;
    var options = {
        callback: $.noop,
        customClass: "",
        extensions: ["jpg", "sjpg", "jpeg", "png", "gif"],
        fixed: false,
        formatter: $.noop,
        height: 100,
        labels: {
            close: "Close",
            count: "of",
            next: "Next",
            previous: "Previous"
        },
        margin: 50,
        minHeight: 100,
        minWidth: 100,
        mobile: false,
        opacity: 0.75,
        retina: false,
        requestKey: "provabslideshow",
        top: 0,
        videoRatio: 0.5625,
        videoWidth: 600,
        width: 100
    };
    var pub = {
        close: function() {
            if (typeof data.$provabslideshow !== "undefined") {
                data.$provabslideshow.off(".provabslideshow");
                data.$overlay.trigger("click");
            }
        },
        defaults: function(opts) {
            options = $.extend(options, opts || {});
            return $(this);
        },
        destroy: function() {
            return $(this).off(".provabslideshow");
        },
        resize: function(e) {
            if (typeof data.$provabslideshow !== "undefined") {
                if (typeof e !== "object") {
                    data.targetHeight = arguments[0];
                    data.targetWidth = arguments[1];
                }
                if (data.type === "element") {
                    _sizeContent(data.$content.find(">:first-child"));
                } else if (data.type === "image") {
                    _sizeImage();
                } else if (data.type === "video") {
                    _sizeVideo();
                }
                _size();
            }
            return $(this);
        }
    };

    function _init(opts) {
        options.formatter = _formatCaption;
        $body = $("body");
        transitionEvent = _getTransitionEvent();
        transitionSupported = (transitionEvent !== false);
        if (!transitionSupported) {
            transitionEvent = "transitionend.provabslideshow";
        }
        return $(this).on("click.provabslideshow", $.extend({}, options, opts || {}), _build);
    }

    function _build(e) {
        var $target = $(this),
            $object = e.data.$object,
            source = ($target[0].attributes) ? $target.attr("href") || "" : "",
            sourceParts = source.toLowerCase().split(".").pop().split(/\#|\?/),
            extension = sourceParts[0],
            type = '',
            isImage = ((type === "image") || ($.inArray(extension, e.data.extensions) > -1 || source.substr(0, 10) === "data:image")),
            isVideo = (source.indexOf("youtube.com/embed") > -1 || source.indexOf("player.vimeo.com/video") > -1),
            isUrl = ((type === "url") || (!isImage && !isVideo && source.substr(0, 4) === "http")),
            isElement = ((type === "element") || (!isImage && !isVideo && !isUrl && source.substr(0, 1) === "#")),
            isObject = ((typeof $object !== "undefined"));
        if ($("#provabslideshow").length > 1 || !(isImage || isVideo || isUrl || isElement || isObject)) {
            return;
        }
        _killEvent(e);
        data = $.extend({}, {
            $window: $(window),
            $body: $("body"),
            $target: $target,
            $object: $object,
            visible: false,
            resizeTimer: null,
            touchTimer: null,
            gallery: {
                active: false
            },
            isMobile: (trueMobile || e.data.mobile),
            isAnimating: true,
            oldContentHeight: 0,
            oldContentWidth: 0
        }, e.data);
        data.margin *= 2;
        data.containerHeight = data.height;
        data.containerWidth = data.width;
        if (isImage) {
            data.type = "image";
        } else if (isVideo) {
            data.type = "video";
        } else {
            data.type = "element";
        }
        if (isImage || isVideo) {
            var id = data.$target.data("gallery") || data.$target.attr("rel");
            if (typeof id !== "undefined" && id !== false) {
                data.gallery.active = true;
                data.gallery.id = id;
                data.gallery.$items = $("a[data-gallery= " + data.gallery.id + "], a[rel= " + data.gallery.id + "]");
                data.gallery.index = data.gallery.$items.index(data.$target);
                data.gallery.total = data.gallery.$items.length - 1;
            }
        }
        var html = '';
        if (!data.isMobile) {
            html += '<div id="provabslideshow-overlay" class="' + data.customClass + '"></div>';
        }
        html += '<div id="provabslideshow" class="loading animating ' + data.customClass;
        if (data.isMobile) {
            html += ' mobile';
        }
        if (isUrl) {
            html += ' iframe';
        }
        if (isElement || isObject) {
            html += ' inline';
        }
        html += '"';
        if (data.fixed === true) {
            html += ' style="position: fixed;"';
        }
        html += '>';
        html += '<span class="provabslideshow-close">' + data.labels.close + '</span>';
        html += '<div class="provabslideshow-container" style="';
        if (data.isMobile) {
            html += 'height: 100%; width: 100%';
        } else {
            html += 'height: ' + data.height + 'px; width: ' + data.width + 'px';
        }
        html += '">';
        html += '<div class="provabslideshow-content">';
        if (isImage || isVideo) {
            html += '<div class="provabslideshow-meta">';
            if (data.gallery.active) {
                html += '<div class="provabslideshow-control previous">' + data.labels.previous + '</div>';
                html += '<div class="provabslideshow-control next">' + data.labels.next + '</div>';
                html += '<p class="provabslideshow-position"';
                if (data.gallery.total < 1) {
                    html += ' style="display: none;"';
                }
                html += '>';
                html += '<span class="current">' + (data.gallery.index + 1) + '</span> ' + data.labels.count + ' <span class="total">' + (data.gallery.total + 1) + '</span>';
                html += '</p>';
                html += '<div class="provabslideshow-caption gallery">';
            } else {
                html += '<div class="provabslideshow-caption">';
            }
            html += data.formatter.apply(data.$body, [data.$target]);
            html += '</div></div>';
        }
        html += '</div></div></div>';
        data.$body.append(html);
        data.$overlay = $("#provabslideshow-overlay");
        data.$provabslideshow = $("#provabslideshow");
        data.$container = data.$provabslideshow.find(".provabslideshow-container");
        data.$content = data.$provabslideshow.find(".provabslideshow-content");
        data.$meta = data.$provabslideshow.find(".provabslideshow-meta");
        data.$position = data.$provabslideshow.find(".provabslideshow-position");
        data.$caption = data.$provabslideshow.find(".provabslideshow-caption");
        data.$controls = data.$provabslideshow.find(".provabslideshow-control");
        data.paddingVertical = parseInt(data.$provabslideshow.css("paddingTop"), 10) + parseInt(data.$provabslideshow.css("paddingBottom"), 10);
        data.paddingHorizontal = parseInt(data.$provabslideshow.css("paddingLeft"), 10) + parseInt(data.$provabslideshow.css("paddingRight"), 10);
        _center();
        if (data.gallery.active) {
            _updateControls();
        }
        data.$window.on("resize.provabslideshow", pub.resize).on("keydown.provabslideshow", _onKeypress);
        data.$body.on("touchstart.provabslideshow click.provabslideshow", "#provabslideshow-overlay, #provabslideshow .provabslideshow-close", _onClose).on("touchmove.provabslideshow", _killEvent);
        if (data.gallery.active) {
            data.$provabslideshow.on("touchstart.provabslideshow click.provabslideshow", ".provabslideshow-control", _advanceGallery);
        }
        data.$provabslideshow.on(transitionEvent, function(e) {
            _killEvent(e);
            if ($(e.target).is(data.$provabslideshow)) {
                data.$provabslideshow.off(transitionEvent);
                if (isImage) {
                    _loadImage(source);
                } else if (isVideo) {
                    _loadVideo(source);
                } else if (isUrl) {
                    _loadURL(source);
                } else if (isElement) {
                    _cloneElement(source);
                } else if (isObject) {
                    _appendObject(data.$object);
                } else {
                    $.error("BOXER: '" + source + "' is not valid.");
                }
            }
        });
        $body.addClass("provabslideshow-open");
        if (!transitionSupported) {
            data.$provabslideshow.trigger(transitionEvent);
        }
        if (isObject) {
            return data.$provabslideshow;
        }
    }

    function _onClose(e) {
        _killEvent(e);
        if (typeof data.$provabslideshow !== "undefined") {
            data.$provabslideshow.on(transitionEvent, function(e) {
                _killEvent(e);
                if ($(e.target).is(data.$provabslideshow)) {
                    data.$provabslideshow.off(transitionEvent);
                    data.$overlay.remove();
                    data.$provabslideshow.remove();
                    data = {};
                }
            }).addClass("animating");
            $body.removeClass("provabslideshow-open");
            if (!transitionSupported) {
                data.$provabslideshow.trigger(transitionEvent);
            }
            _clearTimer(data.resizeTimer);
            data.$window.off("resize.provabslideshow").off("keydown.provabslideshow");
            data.$body.off(".provabslideshow").removeClass("provabslideshow-open");
            if (data.gallery.active) {
                data.$provabslideshow.off(".provabslideshow");
            }
            if (data.isMobile) {
                if (data.type === "image" && data.gallery.active) {
                    data.$container.off(".provabslideshow");
                }
            }
            data.$window.trigger("close.provabslideshow");
        }
    }

    function _open() {
        var position = _position(),
            controlHeight = 0,
            durration = data.isMobile ? 0 : data.duration;
        if (!data.isMobile) {
            controlHeight = data.$controls.outerHeight();
            data.$controls.css({
                marginTop: ((data.contentHeight - controlHeight) / 2)
            });
        }
        if (!data.visible && data.isMobile && data.gallery.active) {
            data.$content.on("touchstart.provabslideshow", ".provabslideshow-image", _onTouchStart);
        }
        if (data.isMobile || data.fixed) {
            data.$body.addClass("provabslideshow-open");
        }
        data.$provabslideshow.css({
            left: position.left,
            top: position.top
        });
        data.$container.on(transitionEvent, function(e) {
            _killEvent(e);
            if ($(e.target).is(data.$container)) {
                data.$container.off(transitionEvent);
                data.$content.on(transitionEvent, function(e) {
                    _killEvent(e);
                    if ($(e.target).is(data.$content)) {
                        data.$content.off(transitionEvent);
                        data.$provabslideshow.removeClass("animating");
                        data.isAnimating = false;
                    }
                });
                data.$provabslideshow.removeClass("loading");
                if (!transitionSupported) {
                    data.$content.trigger(transitionEvent);
                }
                data.visible = true;
                data.callback.apply(data.$provabslideshow);
                data.$window.trigger("open.provabslideshow");
                if (data.gallery.active) {
                    _preloadGallery();
                }
            }
        }).css({
            height: data.containerHeight,
            width: data.containerWidth
        });
        var contentHasChanged = (data.oldContentHeight !== data.contentHeight || data.oldContentWidth !== data.contentWidth);
        if (data.isMobile || !transitionSupported || !contentHasChanged) {
            data.$container.trigger(transitionEvent);
        }
        data.oldContentHeight = data.contentHeight;
        data.oldContentWidth = data.contentWidth;
    }

    function _size(animate) {
        animate = animate || false;
        if (data.visible) {
            var position = _position(),
                controlHeight = 0;
            if (!data.isMobile) {
                controlHeight = data.$controls.outerHeight();
                data.$controls.css({
                    marginTop: ((data.contentHeight - controlHeight) / 2)
                });
            }
            data.$provabslideshow.css({
                left: position.left,
                top: position.top
            });
            data.$container.css({
                height: data.containerHeight,
                width: data.containerWidth
            });
        }
    }

    function _center() {
        var position = _position();
        data.$provabslideshow.css({
            left: position.left,
            top: position.top
        });
    }

    function _position() {
        if (data.isMobile) {
            return {
                left: 0,
                top: 0
            };
        }
        var pos = {
            left: (data.$window.width() - data.containerWidth - data.paddingHorizontal) / 2,
            top: (data.top <= 0) ? ((data.$window.height() - data.containerHeight - data.paddingVertical) / 2) : data.top
        };
        if (data.fixed !== true) {
            pos.top += data.$window.scrollTop();
        }
        return pos;
    }

    function _formatCaption($target) {
        var title = $target.attr("title");
        return (title !== "" && title !== undefined) ? '<p class="caption">' + title + '</p>' : "";
    }

    function _loadImage(source) {
        data.$image = $("<img />");
        data.$image.one("load.provabslideshow", function() {
            var naturalSize = _naturalSize(data.$image);
            data.naturalHeight = naturalSize.naturalHeight;
            data.naturalWidth = naturalSize.naturalWidth;
            if (data.retina) {
                data.naturalHeight /= 2;
                data.naturalWidth /= 2;
            }
            data.$content.prepend(data.$image);
            if (data.$caption.html() === "") {
                data.$caption.hide();
            } else {
                data.$caption.show();
            }
            _sizeImage();
            _open();
        }).attr("src", source).addClass("provabslideshow-image");
        if (data.$image[0].complete || data.$image[0].readyState === 4) {
            data.$image.trigger("load");
        }
    }

    function _sizeImage() {
        var count = 0;
        data.windowHeight = data.viewportHeight = data.$window.height();
        data.windowWidth = data.viewportWidth = data.$window.width();
        data.containerHeight = Infinity;
        data.contentHeight = 0;
        data.containerWidth = Infinity;
        data.contentWidth = 0;
        data.imageMarginTop = 0;
        data.imageMarginLeft = 0;
        while (data.containerHeight > data.viewportHeight && count < 2) {
            data.imageHeight = (count === 0) ? data.naturalHeight : data.$image.outerHeight();
            data.imageWidth = (count === 0) ? data.naturalWidth : data.$image.outerWidth();
            data.metaHeight = (count === 0) ? 0 : data.metaHeight;
            if (count === 0) {
                data.ratioHorizontal = data.imageHeight / data.imageWidth;
                data.ratioVertical = data.imageWidth / data.imageHeight;
                data.isWide = (data.imageWidth > data.imageHeight);
            }
            if (data.imageHeight < data.minHeight) {
                data.minHeight = data.imageHeight;
            }
            if (data.imageWidth < data.minWidth) {
                data.minWidth = data.imageWidth;
            }
            if (data.isMobile) {
                data.$meta.css({
                    width: data.windowWidth
                });
                data.metaHeight = data.$meta.outerHeight(true);
                data.contentHeight = data.viewportHeight;
                data.contentWidth = data.viewportWidth;
                data.containerHeight = data.viewportHeight - data.paddingVertical;
                data.containerWidth = data.viewportWidth - data.paddingHorizontal;
                _fitImage();
                data.imageMarginTop = (data.containerHeight - data.targetImageHeight - data.metaHeight) / 2;
                data.imageMarginLeft = (data.containerWidth - data.targetImageWidth) / 2;
            } else {
                if (count === 0) {
                    data.viewportHeight -= (data.margin + data.paddingVertical);
                    data.viewportWidth -= (data.margin + data.paddingHorizontal);
                }
                data.viewportHeight -= data.metaHeight;
                _fitImage();
                data.containerHeight = data.contentHeight = data.targetImageHeight;
                data.containerWidth = data.contentWidth = data.targetImageWidth;
            }
            data.$content.css({
                height: (data.isMobile) ? data.contentHeight : "auto",
                width: data.contentWidth
            });
            data.$meta.css({
                width: data.contentWidth
            });
            data.$image.css({
                height: data.targetImageHeight,
                width: data.targetImageWidth,
                marginTop: data.imageMarginTop,
                marginLeft: data.imageMarginLeft
            });
            if (!data.isMobile) {
                data.metaHeight = data.$meta.outerHeight(true);
                data.containerHeight += data.metaHeight;
            }
            count++;
        }
    }

    function _fitImage() {
        var height = (!data.isMobile) ? data.viewportHeight : data.containerHeight - data.metaHeight,
            width = (!data.isMobile) ? data.viewportWidth : data.containerWidth;
        if (data.isWide) {
            data.targetImageWidth = width;
            data.targetImageHeight = data.targetImageWidth * data.ratioHorizontal;
            if (data.targetImageHeight > height) {
                data.targetImageHeight = height;
                data.targetImageWidth = data.targetImageHeight * data.ratioVertical;
            }
        } else {
            data.targetImageHeight = height;
            data.targetImageWidth = data.targetImageHeight * data.ratioVertical;
            if (data.targetImageWidth > width) {
                data.targetImageWidth = width;
                data.targetImageHeight = data.targetImageWidth * data.ratioHorizontal;
            }
        }
        if (data.targetImageWidth > data.imageWidth || data.targetImageHeight > data.imageHeight) {
            data.targetImageHeight = data.imageHeight;
            data.targetImageWidth = data.imageWidth;
        }
        if (data.targetImageWidth < data.minWidth || data.targetImageHeight < data.minHeight) {
            if (data.targetImageWidth < data.minWidth) {
                data.targetImageWidth = data.minWidth;
                data.targetImageHeight = data.targetImageWidth * data.ratioHorizontal;
            } else {
                data.targetImageHeight = data.minHeight;
                data.targetImageWidth = data.targetImageHeight * data.ratioVertical;
            }
        }
    }

    function _loadVideo(source) {
        data.$videoWrapper = $('<div class="provabslideshow-video-wrapper" />');
        data.$video = $('<iframe class="provabslideshow-video" seamless="seamless" />');
        data.$video.attr("src", source).addClass("provabslideshow-video").prependTo(data.$videoWrapper);
        data.$content.prepend(data.$videoWrapper);
        _sizeVideo();
        _open();
    }

    function _sizeVideo() {
        data.windowHeight = data.viewportHeight = data.contentHeight = data.$window.height() - data.paddingVertical;
        data.windowWidth = data.viewportWidth = data.contentWidth = data.$window.width() - data.paddingHorizontal;
        data.videoMarginTop = 0;
        data.videoMarginLeft = 0;
        if (data.isMobile) {
            data.$meta.css({
                width: data.windowWidth
            });
            data.metaHeight = data.$meta.outerHeight(true);
            data.viewportHeight -= data.metaHeight;
            data.targetVideoWidth = data.viewportWidth;
            data.targetVideoHeight = data.targetVideoWidth * data.videoRatio;
            if (data.targetVideoHeight > data.viewportHeight) {
                data.targetVideoHeight = data.viewportHeight;
                data.targetVideoWidth = data.targetVideoHeight / data.videoRatio;
            }
            data.videoMarginTop = (data.viewportHeight - data.targetVideoHeight) / 2;
            data.videoMarginLeft = (data.viewportWidth - data.targetVideoWidth) / 2;
        } else {
            data.viewportHeight = data.windowHeight - data.margin;
            data.viewportWidth = data.windowWidth - data.margin;
            data.targetVideoWidth = (data.videoWidth > data.viewportWidth) ? data.viewportWidth : data.videoWidth;
            if (data.targetVideoWidth < data.minWidth) {
                data.targetVideoWidth = data.minWidth;
            }
            data.targetVideoHeight = data.targetVideoWidth * data.videoRatio;
            data.contentHeight = data.targetVideoHeight;
            data.contentWidth = data.targetVideoWidth;
        }
        data.$content.css({
            height: (data.isMobile) ? data.contentHeight : "auto",
            width: data.contentWidth
        });
        data.$meta.css({
            width: data.contentWidth
        });
        data.$videoWrapper.css({
            height: data.targetVideoHeight,
            width: data.targetVideoWidth,
            marginTop: data.videoMarginTop,
            marginLeft: data.videoMarginLeft
        });
        data.containerHeight = data.contentHeight;
        data.containerWidth = data.contentWidth;
        if (!data.isMobile) {
            data.metaHeight = data.$meta.outerHeight(true);
            data.containerHeight = data.targetVideoHeight + data.metaHeight;
        }
    }

    function _preloadGallery(e) {
        var source = '';
        if (data.gallery.index > 0) {
            source = data.gallery.$items.eq(data.gallery.index - 1).attr("href");
            if (source.indexOf("youtube.com/embed") < 0 && source.indexOf("player.vimeo.com/video") < 0) {
                $('<img src="' + source + '">');
            }
        }
        if (data.gallery.index < data.gallery.total) {
            source = data.gallery.$items.eq(data.gallery.index + 1).attr("href");
            if (source.indexOf("youtube.com/embed") < 0 && source.indexOf("player.vimeo.com/video") < 0) {
                $('<img src="' + source + '">');
            }
        }
    }

    function _advanceGallery(e) {
        _killEvent(e);
        var $control = $(this);
        if (!data.isAnimating && !$control.hasClass("disabled")) {
            data.isAnimating = true;
            data.gallery.index += ($control.hasClass("next")) ? 1 : -1;
            if (data.gallery.index > data.gallery.total) {
                data.gallery.index = data.gallery.total;
            }
            if (data.gallery.index < 0) {
                data.gallery.index = 0;
            }
            data.$content.on(transitionEvent, function(e) {
                _killEvent(e);
                if ($(e.target).is(data.$content)) {
                    data.$content.off(transitionEvent);
                    if (typeof data.$image !== 'undefined') {
                        data.$image.remove();
                    }
                    if (typeof data.$videoWrapper !== 'undefined') {
                        data.$videoWrapper.remove();
                    }
                    data.$target = data.gallery.$items.eq(data.gallery.index);
                    data.$caption.html(data.formatter.apply(data.$body, [data.$target]));
                    data.$position.find(".current").html(data.gallery.index + 1);
                    var source = data.$target.attr("href"),
                        isVideo = (source.indexOf("youtube.com/embed") > -1 || source.indexOf("player.vimeo.com/video") > -1);
                    if (isVideo) {
                        _loadVideo(source);
                    } else {
                        _loadImage(source);
                    }
                    _updateControls();
                }
            });
            data.$provabslideshow.addClass("loading animating");
            if (!transitionSupported) {
                data.$content.trigger(transitionEvent);
            }
        }
    }

    function _updateControls() {
        data.$controls.removeClass("disabled");
        if (data.gallery.index === 0) {
            data.$controls.filter(".previous").addClass("disabled");
        }
        if (data.gallery.index === data.gallery.total) {
            data.$controls.filter(".next").addClass("disabled");
        }
    }

    function _onKeypress(e) {
        if (data.gallery.active && (e.keyCode === 37 || e.keyCode === 39)) {
            _killEvent(e);
            data.$controls.filter((e.keyCode === 37) ? ".previous" : ".next").trigger("click");
        } else if (e.keyCode === 27) {
            data.$provabslideshow.find(".provabslideshow-close").trigger("click");
        }
    }

    function _cloneElement(id) {
        var $clone = $(id).find(">:first-child").clone();
        _appendObject($clone);
    }

    function _loadURL(source) {
        source = source + ((source.indexOf("?") > -1) ? "&" + options.requestKey + "=true" : "?" + options.requestKey + "=true");
        var $iframe = $('<iframe class="provabslideshow-iframe" src="' + source + '" />');
        _appendObject($iframe);
    }

    function _appendObject($object) {
        data.$content.append($object);
        _sizeContent($object);
        _open();
    }

    function _sizeContent($object) {
        data.windowHeight = data.$window.height() - data.paddingVertical;
        data.windowWidth = data.$window.width() - data.paddingHorizontal;
        data.objectHeight = $object.outerHeight(true);
        data.objectWidth = $object.outerWidth(true);
        data.targetHeight = data.targetHeight || data.$target.data("provabslideshow-height");
        data.targetWidth = data.targetWidth || data.$target.data("provabslideshow-width");
        data.maxHeight = (data.windowHeight < 0) ? options.minHeight : data.windowHeight;
        data.isIframe = $object.is("iframe");
        data.objectMarginTop = 0;
        data.objectMarginLeft = 0;
        if (!data.isMobile) {
            data.windowHeight -= data.margin;
            data.windowWidth -= data.margin;
        }
        data.contentHeight = (data.targetHeight !== undefined) ? data.targetHeight : (data.isIframe || data.isMobile) ? data.windowHeight : data.objectHeight;
        data.contentWidth = (data.targetWidth !== undefined) ? data.targetWidth : (data.isIframe || data.isMobile) ? data.windowWidth : data.objectWidth;
        if ((data.isIframe || data.isObject) && data.isMobile) {
            data.contentHeight = data.windowHeight;
            data.contentWidth = data.windowWidth;
        } else if (data.isObject) {
            data.contentHeight = (data.contentHeight > data.windowHeight) ? data.windowHeight : data.contentHeight;
            data.contentWidth = (data.contentWidth > data.windowWidth) ? data.windowWidth : data.contentWidth;
        }
        _setContentSize(data);
    }

    function _setContentSize(data) {
        data.containerHeight = data.contentHeight;
        data.containerWidth = data.contentWidth;
        data.$content.css({
            height: data.contentHeight,
            width: data.contentWidth
        });
    }

    function _onTouchStart(e) {
        _killEvent(e);
        _clearTimer(data.touchTimer);
        if (!data.isAnimating) {
            var touch = (typeof e.originalEvent.targetTouches !== "undefined") ? e.originalEvent.targetTouches[0] : null;
            data.xStart = (touch) ? touch.pageX : e.clientX;
            data.leftPosition = 0;
            data.touchMax = Infinity;
            data.touchMin = -Infinity;
            data.edge = data.contentWidth * 0.25;
            if (data.gallery.index === 0) {
                data.touchMax = 0;
            }
            if (data.gallery.index === data.gallery.total) {
                data.touchMin = 0;
            }
            data.$provabslideshow.on("touchmove.provabslideshow", _onTouchMove).one("touchend.provabslideshow", _onTouchEnd);
        }
    }

    function _onTouchMove(e) {
        var touch = (typeof e.originalEvent.targetTouches !== "undefined") ? e.originalEvent.targetTouches[0] : null;
        data.delta = data.xStart - ((touch) ? touch.pageX : e.clientX);
        if (data.delta > 20) {
            _killEvent(e);
        }
        data.canSwipe = true;
        var newLeft = -data.delta;
        if (newLeft < data.touchMin) {
            newLeft = data.touchMin;
            data.canSwipe = false;
        }
        if (newLeft > data.touchMax) {
            newLeft = data.touchMax;
            data.canSwipe = false;
        }
        data.$image.css({
            transform: "translate3D(" + newLeft + "px,0,0)"
        });
        data.touchTimer = _startTimer(data.touchTimer, 300, function() {
            _onTouchEnd(e);
        });
    }

    function _onTouchEnd(e) {
        _killEvent(e);
        _clearTimer(data.touchTimer);
        data.$provabslideshow.off("touchmove.provabslideshow touchend.provabslideshow");
        if (data.delta) {
            data.$provabslideshow.addClass("animated");
            data.swipe = false;
            if (data.canSwipe && (data.delta > data.edge || data.delta < -data.edge)) {
                data.swipe = true;
                if (data.delta <= data.leftPosition) {
                    data.$image.css({
                        transform: "translate3D(" + (data.contentWidth) + "px,0,0)"
                    });
                } else {
                    data.$image.css({
                        transform: "translate3D(" + (-data.contentWidth) + "px,0,0)"
                    });
                }
            } else {
                data.$image.css({
                    transform: "translate3D(0,0,0)"
                });
            }
            if (data.swipe) {
                data.$controls.filter((data.delta <= data.leftPosition) ? ".previous" : ".next").trigger("click");
            }
            _startTimer(data.resetTimer, data.duration, function() {
                data.$provabslideshow.removeClass("animated");
            });
        }
    }

    function _naturalSize($img) {
        var node = $img[0],
            img = new Image();
        if (typeof node.naturalHeight !== "undefined") {
            return {
                naturalHeight: node.naturalHeight,
                naturalWidth: node.naturalWidth
            };
        } else {
            if (node.tagName.toLowerCase() === 'img') {
                img.src = node.src;
                return {
                    naturalHeight: img.height,
                    naturalWidth: img.width
                };
            }
        }
        return false;
    }

    function _killEvent(e) {
        if (e.preventDefault) {
            e.stopPropagation();
            e.preventDefault();
        }
    }

    function _startTimer(timer, time, callback) {
        _clearTimer(timer);
        return setTimeout(callback, time);
    }

    function _clearTimer(timer) {
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
    }

    function _getTransitionEvent() {
        var transitions = {
                'WebkitTransition': 'webkitTransitionEnd',
                'MozTransition': 'transitionend',
                'OTransition': 'oTransitionEnd',
                'transition': 'transitionend'
            },
            test = document.createElement('div');
        for (var type in transitions) {
            if (transitions.hasOwnProperty(type) && type in test.style) {
                return transitions[type];
            }
        }
        return false;
    }
    $.fn.provabslideshow = function(method) {
        if (pub[method]) {
            return pub[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return _init.apply(this, arguments);
        }
        return this;
    };
    $.provabslideshow = function($target, opts) {
        if (pub[$target]) {
            return pub[$target].apply(window, Array.prototype.slice.call(arguments, 1));
        } else {
            if ($target instanceof $) {
                return _build.apply(window, [{
                    data: $.extend({
                        $object: $target
                    }, options, opts || {})
                }]);
            }
        }
    };
})(jQuery, window);

function isNumber(event, keyCode, ctrlKey, metaKey, shiftKey) {
   // console.log("keyCode"+keyCode);
    if ($.inArray(keyCode, [46, 8, 9, 27, 13, 110]) !== -1 || (keyCode == 65 && (ctrlKey === true || metaKey === true)) || (keyCode >= 35 && keyCode <= 40)) {
        return;
    }
    if ((shiftKey || (keyCode < 48 || keyCode > 57)) && (keyCode < 96 || keyCode > 105) || keyCode == 190) {
        event.preventDefault();
    }
}

function validate_email(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

function validate_date(date) {
    var date = new Date(date);
    if (isNaN(date.getTime()) == false) {
        return true;
    } else {
        return false;
    }
}
$(".alpha_space").keypress("keyup blur change focus", function(event) {
     var inputValue = event.charCode;
     //console.log(inputValue);
        if(!(inputValue >= 65 && inputValue <= 122) && (inputValue != 32 && inputValue != 0)){
            event.preventDefault();
        }
});