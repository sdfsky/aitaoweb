var selLocationObj = {
    settings: {},
    data: {},
    containerNormal: null,
    containerFloat: null,
    normalIndex: 0,
    floatIndex: 0,
    currentNormal: [],
    currentFloat: [],
    callElement: null,
    space: []
};
$.fn.connectSelectLocation = function(c) {
    selLocationObj.settings = $.extend({
        type: "normal",
        data: null,
        display: "horizontal",
        select1: null,
        select2: null,
        select3: null,
        current: null,
        callelement: null,
        onComplete: function() {},
        space: [7, 6, 6]
    },
    c || {});
    var b = this;
    var a = selLocationObj.settings.select1;
    var e = selLocationObj.settings.select2;
    var d = selLocationObj.settings.select3;
    $.getJSON(selLocationObj.settings.data,
    function(f) {
        selLocationObj.data = f[0];
        if (selLocationObj.settings.type == "normal") {
            selLocationObj.containerNormal = b;
            selLocationObj.currentNormal = selLocationObj.settings.current;
            loadSelectNormalLocationData(a, e, d)
        } else {
            selLocationObj.containerFloat = b;
            selLocationObj.currentFloat = selLocationObj.settings.current;
            selLocationObj.callElement = selLocationObj.settings.callelement;
            selLocationObj.space = selLocationObj.settings.space;
            loadSelectFloatLocationData(a, e, d)
        }
        selLocationObj.settings.onComplete();
        
    });
    return this
};
var loadSelectFloatLocationData = function(a, f, d) {
    var b = selLocationObj.containerFloat;
    if (selLocationObj.settings.display == "horizontal") {
        b.append('<table class="v-t" border="0" cellspacing="0" cellpadding="0"><tr><td class="v-d"></td><td class="v-d"></td><td class="v-d"></td></tr></table>')
    }
    $("select", b).each(function(h, j) {
        if (selLocationObj.settings.display == "horizontal") {
            $(".v-d", b).eq(h).append($(this))
        }
        var g = $("option", $(this)).html();
        $(this).after("<div class='wrap w" + h + "'><div class='sel'><div class='l'></div><div class='c'>" + g + "</div><div class='r'></div></div><div class='panel'>请选择</div></div>")
    });
    $(".wrap .sel", b).mouseenter(function() {
        $(this).next().show()
    });
    $(".wrap", b).mouseleave(function() {
        $(".panel", $(this)).hide()
    });
    var e = "";
    $.each(selLocationObj.data.province,
    function(g, h) {
        e += getHtmlLocationSel(0, g, h.value, h.label);
        if ((g + 1) % selLocationObj.space[0] == 0 && g > 0) {
            e += "<br/>"
        }
    });
    $(".panel", b)[0].innerHTML = e;
    if (selLocationObj.currentFloat) {
        for (var c = 0; c <= 2; c++) {
            $(".panel:eq(" + c + ") a[value=" + selLocationObj.currentFloat[c] + "]", b).trigger("click")
        }
    }
};
var getFloatLocationSel = function(f, e, d, c, b) {
    var a = selLocationObj.containerFloat;
    $(".panel", a).eq(e).hide();
    $(".c", a).eq(e).html("<a href='javascript:void(0);'>" + b + "</a>");
    $("select:eq(" + e + ") option", a).eq(0).val(c).html(b);
    $(".checked", $(f).parent()).removeClass("checked");
    $(f).addClass("checked");
    if (e == 0) {
        $(".c:eq(1),.c:eq(2)", a).html("请选择");
        $(".panel", a).eq(1).html("");
        $(".panel", a).eq(2).html("请选择");
        $("select:eq(1) option", a).eq(0).val(0).html("");
        $("select:eq(2) option", a).eq(0).val(0).html("");
        var g = "";
        $.each(selLocationObj.data.city[d],
        function(h, j) {
            g += getHtmlLocationSel(1, h, j.value, j.label);
            if ((h + 1) % selLocationObj.space[1] == 0 && h > 0) {
                g += "<br/>"
            }
        });
        $(".panel", a)[1].innerHTML = g;
        selLocationObj.floatIndex = d;
        $(".sel", a).eq(1).trigger("mouseenter");
        if (c <= 4) {
            $(".panel:eq(1) a:first", a).trigger("click")
        }
        selLocationObj.settings.fun1(c)
    } else {
        if (e == 1) {
            $(".c:last", a).html("请选择");
            $(".panel:last", a).html("");
            $("select:eq(2) option", a).eq(0).val(0).html("");
            var g = "";
            $.each(selLocationObj.data.district[selLocationObj.floatIndex][d],
            function(h, j) {
                g += getHtmlLocationSel(2, h, j.value, j.label);
                if ((h + 1) % selLocationObj.space[2] == 0 && h > 0) {
                    g += "<br/>"
                }
            });
            $(".panel", a)[2].innerHTML = g;
            $(".sel", a).eq(2).trigger("mouseenter");
            selLocationObj.settings.fun2(c)
        } else {
            if (e == 2) {
                selLocationObj.settings.fun3(c)
            }
        }
    }
    if (selLocationObj.callElement) {
        selLocationObj.callElement[0].value = "";
        $("select", a).each(function(h, j) {
            selLocationObj.callElement[0].value += $("option", $(j)).eq(0).val() + ","
        })
    }
    return false
};
var getHtmlLocationSel = function(d, b, c, a) {
    return "<a value=" + c + " onclick=getFloatLocationSel(this," + d + "," + b + "," + c + ",'" + a + "') href='javascript:void(0);'>" + a + "</a><span>|</span>"
};
var loadSelectNormalLocationData = function(a, e, d) {
    var b = selLocationObj.containerNormal;
    $.each(selLocationObj.data.province,
    function(f, g) {
        a.append("<option value=" + g.value + ">" + g.label + "</option>")
    });
    a.change(function() {
        selLocationObj.normalIndex = this.selectedIndex;
        e.empty().append("<option>请选择</option>");
        d.empty().append("<option value=0>请选择</option>");
        if (selLocationObj.normalIndex != 0) {
            $.each(selLocationObj.data.city[selLocationObj.normalIndex - 1],
            function(f, g) {
                e.append("<option value=" + g.value + ">" + g.label + "</option>")
            })
        }
    });
    e.change(function() {
        var f = this.selectedIndex;
        d.empty().append("<option value=0>请选择</option>");
        if (f != 0) {
            $.each(selLocationObj.data.district[selLocationObj.normalIndex - 1][f - 1],
            function(g, h) {
                d.append("<option value=" + h.value + ">" + h.label + "</option>")
            })
        }
    });
    if (selLocationObj.currentNormal) {
        for (var c = 0; c <= 2; c++) {
            $("select:eq(" + c + ") option[value=" + selLocationObj.currentNormal[c] + "]", b)[0].selected = true;
            $("select:eq(" + c + ")", b).trigger("change")
        }
    }
};