function addPanel() {
    if (! $("#addPanelBtn").hasClass("disabled")) {
        var qty = $("#panelsqty");
        var newqty = strtoint(qty.val()) + 1;
        if (newqty <= 6) {
            var panel = "#panel" + newqty.toString();
            $(panel).show("fast");
            qty.val(newqty);
            if (newqty == 6) {
                $("#addPanelBtn").addClass("disabled");
            }
            $("#removePanelBtn").removeClass("disabled");
        } else {
            alert("6 panels max");
        }
    }
}

function removePanel() {
    if (! $("#removePanelBtn").hasClass("disabled")) {
        var qty = $("#panelsqty");
        var newqty = strtoint(qty.val()) - 1;
        if (newqty >= 1) {
            var panel = "#panel" + qty.val();
            $(panel).hide("fast");
            qty.val(newqty);
            if (newqty == 1) {
                $("#removePanelBtn").addClass("disabled");
            }
            $("#addPanelBtn").removeClass("disabled");
        } else {
            alert("Minimim 1 panel");
        }
    }
}

function intRandom(max) {
    return Math.floor(Math.random()*(max+1));
}



function draw_text(ctx,x,y,rotate,textAlign,text) {
    ctx.save();
    ctx.translate(x,y);
    if (rotate != 0) {
        ctx.rotate(rotate);
    }
    ctx.textAlign = textAlign;
    ctx.fillStyle='#000';
    size = 7/zoom;
    ctx.font = size.toString()+'pt Sans-Serif';
    ctx.fillText(text,0,0);
    ctx.restore();
}


function draw_line(ctx,x1,y1,x2,y2,pos,text) {
    if (y1==y2) {
        ctx.beginPath();
        ctx.moveTo(x1,y1 + 3/zoom);
        ctx.lineTo(x1,y1 - 3/zoom);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x2,y2 + 3/zoom);
        ctx.lineTo(x2,y2 - 3/zoom);
        ctx.stroke();
        if (text.length > 0) {
            draw_text(ctx,(x1+x2*(pos-1))/pos,y1-3/zoom,0,'center',text);
        }
    } else if (x1 == x2) {
        ctx.beginPath();
        ctx.moveTo(x1 + 3/zoom, y1);
        ctx.lineTo(x1 - 3/zoom, y1);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x2 + 3/zoom, y2);
        ctx.lineTo(x2 - 3/zoom, y2);
        ctx.stroke();
        if (text.length > 0) {
            draw_text(ctx,x1+3/zoom,(y1+(pos-1)*y2)/pos,Math.PI/2,'center',text);
        }
    }
    ctx.beginPath();
    ctx.moveTo(x1,y1);
    ctx.lineTo(x2,y2);
    ctx.stroke();
}


function draw_panel(ctx,pspan,sweep,rootc,tipc,angle,drawmeasures,drawtip) {
    if (window.systemunit == "metric") {
        msr = ' mm';
    } else {
        msr = ' in';
    }
    ctx.beginPath();
    ctx.moveTo(0,0);
    ctx.lineTo(pspan,sweep);
    ctx.lineTo(pspan,sweep+tipc);
    ctx.lineTo(0,rootc);
    ctx.lineTo(0,0);
    ctx.closePath();
    ctx.stroke();
    ctx.fill();
    if (drawmeasures) {
        if (Math.abs(angle) < 15) {
            r = 0.7;
        } else {
            r = 0.4;
        }
        if (sweep >=0) {
            draw_line(ctx, 0, -5/zoom, pspan, -5/zoom, 2, pspan.toString()+msr); // span
            draw_line(ctx, pspan, 0, pspan, sweep, 2, sweep.toString()+msr); // sweep;
            ctx.beginPath();
            ctx.arc(0,0,pspan*r,0,angle*Math.PI/180,false);
            ctx.stroke();
            draw_text(ctx, pspan*(r+0.03), (0.7*r) * sweep, 0,'left', angle.toString()+'º');
        } else {
            draw_line(ctx, 0, sweep-5/zoom, pspan, sweep-5/zoom, 2, pspan.toString()+msr); // span
            draw_line(ctx, 0, 0, 0, sweep, 2, sweep.toString()+msr); // sweep;
            ctx.beginPath();
            ctx.arc(0,0,pspan*r,angle*Math.PI/180,0,false);
            ctx.stroke();
            draw_text(ctx, pspan*(r+0.03), (0.7*r) * sweep, 0,'left', angle.toString()+'º');
        }
        draw_line(ctx, 0 + 7/zoom, 0, 0 + 7/zoom, rootc, 4, rootc.toString()+msr); // root chord
        if (drawtip) {
            draw_line(ctx, pspan + 7/zoom, sweep, pspan + 7/zoom, sweep + tipc, 2, tipc.toString()+msr); // tip chord
        }
    }
}


function draw_cg(context,zoom,p,drawmeasures,wholewing) {
    context.save();

    context.beginPath();
    if (wholewing) {
        context.strokeStyle="#3636b2";
        context.fillStyle="#3636b2";
        context.lineWidth = 2/zoom; // context.lineWidth = 1.0;
    } else {
        context.strokeStyle="#7070ff";
        context.fillStyle="#7070ff";
        context.lineWidth = 1/zoom; // context.lineWidth = 1.0;
    }
    // draw the MAC
    context.moveTo( p.mac_x, p.le_mac_y);
    context.lineTo( p.mac_x, p.te_mac_y);
    context.stroke();

    context.beginPath();
    // draw the MAC distance to the root chord
    context.moveTo(0, p.cg_dist);
    context.lineTo(p.mac_x, p.cg_dist);
    context.stroke();

    var cg_radius;
    if (window.systemunit == "metric") {
        cg_radius = Math.max(1.5*panels[0].span/100, 5);
    } else {
        cg_radius = Math.max(1.5*panels[0].span/100, 0.2);
    }

    context.beginPath();
    // draw the CG ball
    context.arc(0, p.cg_dist, cg_radius, 0, Math.PI * 2.0, false);
    context.stroke();

    context.beginPath();
    context.arc(0, p.cg_dist, cg_radius, 0, Math.PI * 0.5, false);
    context.lineTo(0, p.cg_dist);
    context.fill();
    context.beginPath();
    context.arc(0, p.cg_dist, cg_radius, Math.PI * 1.0, Math.PI * 1.5, false);
    context.lineTo(0, p.cg_dist);
    context.fill();

    if (drawmeasures) {
        cg_pos = Math.round( strtofloat($("#cgpos").val()) * 100) / 100;
        cg_dst = Math.round( p.cg_dist * 100) / 100;
        if (window.systemunit == "metric") {
            msr = ' mm';
        } else {
            msr = ' in';
        }
        draw_line(context, 0 + 14/zoom, 0, 0 + 14/zoom, p.cg_dist,
            2, 'CG@'+cg_pos.toString()+'% '+cg_dst.toString()+msr); // cg
    }

    context.restore();
}


function strtofloat(str) {
    var f = parseFloat(str);
    if (isNaN(f)) {
        f = 0;
    }
    return f;
}


function fieldtofloat(field) {
    return strtofloat(field.val());
}


function strtoint(str) {
    var i = parseInt(str);
    if (isNaN(i)) {
        i = 0;
    }
    return i;
}


function load_panels(qty) {
    var i,j,lastpanel;
    if (!window.panels) {
        window.panels = new Array();
    }
    window.panels[0] = new Array(); // panel 0 is the whole wing, we will concatenate span and sweep
    window.panels[0].span  = 0;
    window.panels[0].root  = 0;
    window.panels[0].tipc  = 0;
    window.panels[0].sweep = 0;
    window.panels[0].maxX  = 0;
    window.panels[0].minX  = 0;
    window.panels[0].angle = 0;
    window.panels[0].qty   = 0;
    lastpanel = 0;
    var preconcsweep;
    for (i=1;i<=qty;i++) {
        j = i-1;
        if (j==0) {
            $root_id = '#rootchord'
        } else {
            $root_id = '#panel'+j.toString()+'chord';
        }
        window.panels[i] = new Array();
        preconcsweep = window.panels[0].sweep; // previous concatenated sweep;
        window.panels[i].span  =  strtofloat($("#panel"+i.toString()+"span").val());
        if (window.panels[i].span > 0) {
            lastpanel = i;
        }
        window.panels[0].span  += panels[i].span; // concatenate span
        window.panels[i].root  =  strtofloat($($root_id).val()); // previous panel
        window.panels[i].tipc  =  strtofloat($("#panel"+i.toString()+"chord").val());
        window.panels[i].sweep =  strtofloat($("#panel"+i.toString()+"sweep").val());
        window.panels[i].angle =  strtofloat($("#panel"+i.toString()+"angle").val());
        window.panels[0].sweep += panels[i].sweep; // concatenate sweep

        // mantain max and min X coordinates
        // we use the concatenated sweep.
        window.panels[0].maxX = Math.max(window.panels[0].maxX, panels[i].root, panels[i].tipc+window.panels[0].sweep);
        window.panels[0].minX = Math.min(window.panels[0].minX, preconcsweep, panels[0].sweep);

    }

    qty = lastpanel;
    window.panels[0].qty  = lastpanel;


    window.panels[0].root = panels[1].root;   // first chord
    window.panels[0].tipc = panels[qty].tipc; // last chord

    debug = "";
    var cg_pos = strtofloat($("#cgpos").val());
    for(i=1;i<=qty;i++) {
        p = window.panels[i];
        if (p.span>0) {
            window.panels[i].wingarea = ((p.root + p.tipc)/2) * p.span;

            // Find LE and TE line formula
            var le_b = 0;
            var le_a = (p.sweep - le_b) / p.span;
            var te_b = p.root;
            var te_a = ((p.sweep + p.tipc) - te_b) / p.span;

            // Find helper line formula
            var mac_b0 = -p.tipc;
            var mac_a0 = ((p.sweep + p.tipc + p.root) - mac_b0) / p.span;
            var mac_b1 = p.root + p.tipc;
            var mac_a1 = ((p.sweep - p.root) - mac_b1) / p.span;

            // Determine MAC using intersection of helper lines
            window.panels[i].mac_x = (mac_b1 - mac_b0) / (mac_a0 - mac_a1);

            // Compute MAC intersection with LE and TE
            window.panels[i].le_mac_y = le_a * p.mac_x + le_b;
            window.panels[i].te_mac_y = te_a * p.mac_x + te_b;

            // Compute CG
            window.panels[i].cg_dist = p.le_mac_y + (p.te_mac_y - p.le_mac_y) * cg_pos / 100;

            // MAC length & distance
            window.panels[i].maclen = p.te_mac_y - p.le_mac_y;
            window.panels[i].macdist = p.mac_x;

            debug += "Panel "+i.toString()+"\n";
            debug += "\tArea "+p.wingarea.toString()+"\n";
            debug += "\tX "+   p.mac_x.toString()+"\n";
            debug += "\tle_y "+p.le_mac_y.toString()+"\n";
            debug += "\tte_y "+p.te_mac_y.toString()+"\n";
            debug += "\n";
        }
    }

    // Here is the black magic: all the individuals panels are counted on based on their area
    window.panels[0].wingarea = 0;
    window.panels[0].mac_x = 0;
    window.panels[0].le_mac_y = 0;
    window.panels[0].te_mac_y = 0;
    rootx = 0;
    rooty = 0;
    for(i=1;i<=qty;i++) {
        p = window.panels[i];
        if (p.wingarea>0) {
            window.panels[0].wingarea += p.wingarea;
            window.panels[0].mac_x    += (rootx + p.mac_x) * p.wingarea;
            window.panels[0].le_mac_y += (rooty + p.le_mac_y) * p.wingarea;
            window.panels[0].te_mac_y += (rooty + p.te_mac_y) * p.wingarea;
            rootx += p.span;
            rooty += p.sweep;
        }
    }
    p = window.panels[0];
    window.panels[0].mac_x    = p.mac_x    / p.wingarea;
    window.panels[0].le_mac_y = p.le_mac_y / p.wingarea;
    window.panels[0].te_mac_y = p.te_mac_y / p.wingarea;
    // Compute CG
    window.panels[0].cg_dist = p.le_mac_y + (p.te_mac_y - p.le_mac_y) * cg_pos / 100;
    // MAC length & distance
    window.panels[0].maclen = p.te_mac_y - p.le_mac_y;
    window.panels[0].macdist = p.mac_x;

    debug += "Panel 0"+"\n";
    debug += "\tArea "+p.wingarea.toString()+"\n";
    debug += "\tX "+   p.mac_x.toString()+"\n";
    debug += "\tle_y "+p.le_mac_y.toString()+"\n";
    debug += "\tte_y "+p.te_mac_y.toString()+"\n";
    debug += "\n";

    // Update UI
    if (window.systemunit == "metric") {
        $('#wingarea').val(    Math.round( (2*window.panels[0].wingarea/10000) * 100) / 100);
        loadarea = $('#wingarea').val();
    } else {
        $('#wingarea').val(    Math.round( (2*window.panels[0].wingarea)       * 100) / 100);
        loadarea = $('#wingarea').val()/144;
    }
    $('#macdist').val( Math.round( window.panels[0].macdist * 100) / 100);
    $('#maclength').val(  Math.round( window.panels[0].maclen * 100) / 100);
    $('#cg').val(  Math.round( window.panels[0].cg_dist * 100) / 100);

    w = $('#weight').val();

    if (loadarea>0) {
        $('#wingload').val(Math.round( (w/loadarea) * 100) / 100);
    }
    //$('#debug').val( debug );

    return panels;
}

function mkURIcomponent(id) {
    value = $('#'+id).val();
    if (value.length > 0) {
        ret = id+'=' + encodeURIComponent(value) + '&';
    } else {
        ret = '';
    }
    return ret;
}

function makePARAMS() {
    var url = "?";
    url = url + mkURIcomponent('unitvalue');
    url = url + mkURIcomponent('panelsqty');
    url = url + mkURIcomponent('cgpos');
    url = url + mkURIcomponent('weight');
    url = url + mkURIcomponent('drawmeasurement');
    url = url + mkURIcomponent('chord0');
    for(i=1;i<=6;i++) {
        p = i.toString();
        url = url + mkURIcomponent('panelspan'+p);
        url = url + mkURIcomponent('chord'+p);
        url = url + mkURIcomponent('sweep'+p);
        url = url + mkURIcomponent('angle'+p);
    }
    return url;
}

function makeURL() {
    url = makePARAMS();
    box = window.document.location;
    site = box.protocol + "//" + box.hostname + box.pathname;
    site = box.protocol + "//" + box.hostname + "/";
    return site+url;
}


function fix_language_link_params(a) {
    a.href = a.href + makePARAMS();
}


function shortURL(field, url_to_be_shorted) {
    $.ajax({
      url: "/php/short.php?addr="+encodeURIComponent(url_to_be_shorted),
      timeout: 2000,
      success: function(data) {
          if (data.substr(0, 7) == 'http://') {
              field.val(data);
          } else {
              field.val(url_to_be_shorted);
          }
      },
      error: function(x, t, m) {
          field.val(url_to_be_shorted);
      }
    });
}


function angle2sweep(panelnumber) {
    // sweep  = math.tan( angle * math.PI / 180 ) * span
    angle = fieldtofloat($('#panel'+panelnumber+'angle'));
    span  = fieldtofloat($('#panel'+panelnumber+'span'));
    sw = Math.tan( angle * Math.PI / 180 ) * span;
    $('#panel'+panelnumber+'sweep').val( Math.round( 100 * sw ) / 100);
}


function sweep2angle(panelnumber) {
    // angle = math.atan(sweep/span) * (180/math.PI)
    sweep = fieldtofloat($('#panel'+panelnumber+'sweep'));
    span  = fieldtofloat($('#panel'+panelnumber+'span'));
    an = Math.atan( sweep/span ) * (180 / Math.PI);
    val =  Math.round( 100 * an ) / 100;
    $('#panel'+panelnumber+'angle').val( (isNaN(val) ? null : val) );
}


function draw_wing(forcesize) {
    var panels = load_panels(6);
    var panel_qty = window.panels[0].qty;
    var canvas_max_width;
    var canvas_max_height;
    var border_size = 30;
    var canvas = document.getElementById('wingcanvas');
    var canvasdiv = $('#canvasdiv');
    var drawmeasurement = $('#drawvalues').prop('checked');

    if (forcesize) {
            canvas_max_width  = 940;
            canvas_max_height = 400;
            drawmeasurement = false;
    } else {
        if (canvasdiv.width() == 945) {
            canvas_max_width  = 940;
            canvas_max_height = 400;
        } else if (Math.floor(canvasdiv.width()) == 778) {
            canvas_max_width  = 770;
            canvas_max_height = 327;
        } else  {
            canvas_max_width  = canvasdiv.width()-5;
            canvas_max_height = Math.ceil(((canvasdiv.width()-5)/940)*400);
        }
    }

    if (canvas.getContext){
        var ctx = canvas.getContext('2d');

        var i;
        var p = panels[0];
        var w = p.span * 2;
        var h = Math.max(p.root, p.sweep+p.tipc, p.maxX-p.minX);

        canvas_w = canvas_max_width;
        canvas_h = canvas_max_height;
        if ((w/h) > (canvas_max_width/canvas_max_height)) {
            zoom = (canvas_w - border_size*2) / w;
        } else {
            zoom = (canvas_h - border_size*2) / h;
        }

        canvas.setAttribute("width", canvas_w);
        canvas.setAttribute("height", canvas_h);
        ctx.strokeStyle="#000";
        ctx.fillStyle="#fff";
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, canvas_w, canvas_h);
        ctx.save();
        if (p.minX < 0) {
            ctx.translate(canvas_w/2, border_size + zoom * Math.abs(p.minX));
        } else {
            ctx.translate(canvas_w/2, border_size);
        }
        ctx.scale(zoom,zoom);
        ctx.lineWidth = 2/zoom;
        ctx.strokeStyle = "#000";
        trw = Math.max(p.span,(canvas_w/zoom)/2);
        trh = -p.minX;
        ctx.save();

        // right side
        for(i=1;i<=panel_qty;i++) {
            var p = panels[i];
            draw_panel(ctx,p.span,p.sweep,p.root,p.tipc,p.angle,drawmeasurement,(i==panel_qty));
            /*if ((p.span > 0) && (p.cg_dist != 0) && (panel_qty >1)) {
                draw_cg(ctx,zoom,p,false,false);
            }*/
            ctx.translate(p.span,p.sweep);
        }
        ctx.restore();
        ctx.save();
        // left side
        for(i=1;i<=panel_qty;i++) {
            var p = panels[i];
            draw_panel(ctx,-p.span,p.sweep,p.root,p.tipc,p.angle,false,false);
            ctx.translate(-p.span,p.sweep);
        }
        ctx.restore();
        draw_cg(ctx,zoom,panels[0],drawmeasurement,true);
        ctx.restore();
    }
    // $('#deeplinkurl').val( makeURL() );
    // $('#publicurl').val("");
    // $('#btn_shortit').removeClass('disabled');
}


function shortit() {
    shortURL($('#publicurl'),$('#deeplinkurl').val());
    $('#btn_shortit').addClass('disabled');
}

function roundit(value) {
    var strval;
    var round_factor = 10000;
    value = Math.round(round_factor * value) / round_factor;
    strval = value.toString();
    if ( (strval.search('.99') != -1) || (strval.search('.00') != -1) ) {
        value = Math.round(value);
    }
    return value;
}

function systemunits_to_metric(recalc) {
    var inch_value = 25.4;
    var ounce_value = 28.349523;
    var unitSys = $("#unitValue");
    recalc = typeof recalc !== 'undefined' ? recalc : true;

    if (window.systemunit != "metric") {
        if (recalc) {
            $("#weight").val(    roundit( strtofloat($("#weight").val())    * ounce_value) );
            $("#rootchord").val( roundit( strtofloat($("#rootchord").val()) * inch_value ) );
            for(i=1;i<=6;i++) {
                p = i.toString();
                $("#panel"+p+"span").val( roundit( strtofloat($("#panel"+p+"span").val()) * inch_value ) );
                $("#panel"+p+"chord").val(roundit( strtofloat($("#panel"+p+"chord").val())* inch_value ) );
                $("#panel"+p+"sweep").val(roundit( strtofloat($("#panel"+p+"sweep").val())* inch_value ) );
                if ( ($("#panel"+p+"span").val() == 0) && ($("#panel"+p+"chord").val() == 0) ) {
                    $("#panel"+p+"span").val('');
                    $("#panel"+p+"chord").val('');
                    $("#panel"+p+"sweep").val('');
                }
            }
        }
        $(".input-group-addon:not(.nommin-addon)").html('mm');
        $(".data-inmm").attr('data-content', 'mm');
        $("#weight-addon").html('g');
        $("#wingarea").attr('data-content', 'dm²');
        $("#wingload").attr('data-content', 'g/dm²');

        window.systemunit = "metric";
        unitSys.val(window.systemunit);
        draw_wing();
    } else {
        console.log('is already metric!');
    }
}

function systemunits_to_imperial(recalc) {
    var inch_value = 25.4;
    var ounce_value = 28.349523;
    var unitSys = $("#unitValue");
    recalc = typeof recalc !== 'undefined' ? recalc : true;

    if (window.systemunit != "imperial") {
        if (recalc) {
            $("#weight").val(   roundit( strtofloat($("#weight").val())    / ounce_value) );
            $("#rootchord").val(roundit( strtofloat($("#rootchord").val()) / inch_value ) );
            for(i=1;i<=6;i++) {
                p = i.toString();
                $("#panel"+p+"span").val( roundit( strtofloat($("#panel"+p+"span").val())  / inch_value ) );
                $("#panel"+p+"chord").val(roundit( strtofloat($("#panel"+p+"chord").val()) / inch_value ) );
                $("#panel"+p+"sweep").val(roundit( strtofloat($("#panel"+p+"sweep").val()) / inch_value ) );
                if ( ($("#panel"+p+"span").val() == 0) && ($("#panel"+p+"chord").val() == 0) ) {
                    $("#panel"+p+"span").val('');
                    $("#panel"+p+"chord").val('');
                    $("#panel"+p+"sweep").val('');
                }
            }
        }
        $(".input-group-addon:not(.nommin-addon)").html('in');
        $(".data-inmm").attr('data-content', 'in');
        $("#weight-addon").html('oz');
        $("#wingarea").attr('data-content', 'in²');
        $("#wingload").attr('data-content', 'oz/ft²');

        window.systemunit = "imperial";
        unitSys.val(window.systemunit);
        draw_wing();
    } else {
        console.log('is already imperial!');
    }
}


function URLSelectAll() {
    url = $('#publicurl');
    url.focus();
    url.select();
}


function savepng() {
    var canvas = document.getElementById('wingcanvas');
    Canvas2Image.saveAsPNG(canvas);
}

function save_image() {
    var canvas = document.getElementById('wingcanvas');
    draw_wing("FORCE_940x400");
    $("#image_base64").val(canvas.toDataURL())
    draw_wing();
    return true;
}

function wingcgcalc_setup() {
    // event linking
    $("#label_btn_metric").click(function(event){     systemunits_to_metric();   });
    $("#label_btn_imperial").click(function(event){   systemunits_to_imperial(); });
    $("#btn_savepng").click(function(event){    savepng();                  event.preventDefault();});
    $(window).resize(function(){                draw_wing(); });
    $(".redraw").change(function(){             draw_wing(); });
    $(".recalcsweep").change(function(){        angle2sweep($(this).attr('panel')); draw_wing(); });
    $(".recalcangle").change(function(){        sweep2angle($(this).attr('panel')); draw_wing(); });
    $("#submit_button").click(function(event){  save_image(); })

    // global variables setup
    if ($("#btn_metric").filter(':checked').val()) {
        $("#unitValue").val('metric');
    } else {
        $("#unitValue").val('imperial');
    }
    var unitSys = $("#unitValue");
    window.systemunit = unitSys.val();
}



