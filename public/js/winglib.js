/* Copyright (C) 1999 Masanao Izumo <iz@onicos.co.jp>
 * Version: 1.0
 * LastModified: Dec 25 1999
 * This library is free.  You can redistribute it and/or modify it.
 */

/*
 * Interfaces:
 * b64 = base64encode(data);
 * data = base64decode(b64);
 */

(function() {

var base64EncodeChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
var base64DecodeChars = new Array(
    -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
    -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
    -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63,
    52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1,
    -1,  0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 10, 11, 12, 13, 14,
    15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1,
    -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
    41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1);

function base64encode(str) {
    var out, i, len;
    var c1, c2, c3;

    len = str.length;
    i = 0;
    out = "";
    while(i < len) {
	c1 = str.charCodeAt(i++) & 0xff;
	if(i == len)
	{
	    out += base64EncodeChars.charAt(c1 >> 2);
	    out += base64EncodeChars.charAt((c1 & 0x3) << 4);
	    out += "==";
	    break;
	}
	c2 = str.charCodeAt(i++);
	if(i == len)
	{
	    out += base64EncodeChars.charAt(c1 >> 2);
	    out += base64EncodeChars.charAt(((c1 & 0x3)<< 4) | ((c2 & 0xF0) >> 4));
	    out += base64EncodeChars.charAt((c2 & 0xF) << 2);
	    out += "=";
	    break;
	}
	c3 = str.charCodeAt(i++);
	out += base64EncodeChars.charAt(c1 >> 2);
	out += base64EncodeChars.charAt(((c1 & 0x3)<< 4) | ((c2 & 0xF0) >> 4));
	out += base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >>6));
	out += base64EncodeChars.charAt(c3 & 0x3F);
    }
    return out;
}

function base64decode(str) {
    var c1, c2, c3, c4;
    var i, len, out;

    len = str.length;
    i = 0;
    out = "";
    while(i < len) {
	/* c1 */
	do {
	    c1 = base64DecodeChars[str.charCodeAt(i++) & 0xff];
	} while(i < len && c1 == -1);
	if(c1 == -1)
	    break;

	/* c2 */
	do {
	    c2 = base64DecodeChars[str.charCodeAt(i++) & 0xff];
	} while(i < len && c2 == -1);
	if(c2 == -1)
	    break;

	out += String.fromCharCode((c1 << 2) | ((c2 & 0x30) >> 4));

	/* c3 */
	do {
	    c3 = str.charCodeAt(i++) & 0xff;
	    if(c3 == 61)
		return out;
	    c3 = base64DecodeChars[c3];
	} while(i < len && c3 == -1);
	if(c3 == -1)
	    break;

	out += String.fromCharCode(((c2 & 0XF) << 4) | ((c3 & 0x3C) >> 2));

	/* c4 */
	do {
	    c4 = str.charCodeAt(i++) & 0xff;
	    if(c4 == 61)
		return out;
	    c4 = base64DecodeChars[c4];
	} while(i < len && c4 == -1);
	if(c4 == -1)
	    break;
	out += String.fromCharCode(((c3 & 0x03) << 6) | c4);
    }
    return out;
}

if (!window.btoa) window.btoa = base64encode;
if (!window.atob) window.atob = base64decode;

})();
/**
 * covert canvas to image
 * and save the image file
 */

var Canvas2Image = function () {

	// check if support sth.
	var $support = function () {
		var canvas = document.createElement('canvas'),
			ctx = canvas.getContext('2d');

		return {
			canvas: !!ctx,
			imageData: !!ctx.getImageData,
			dataURL: !!canvas.toDataURL,
			btoa: !!window.btoa
		};
	}();

	var downloadMime = 'image/octet-stream';

	function scaleCanvas (canvas, width, height) {
		var w = canvas.width,
			h = canvas.height;
		if (width == undefined) {
			width = w;
		}
		if (height == undefined) {
			height = h;
		}

		var retCanvas = document.createElement('canvas');
		var retCtx = retCanvas.getContext('2d');
		retCanvas.width = width;
		retCanvas.height = height;
		retCtx.drawImage(canvas, 0, 0, w, h, 0, 0, width, height);
		return retCanvas;
	}

	function getDataURL (canvas, type, width, height) {
		canvas = scaleCanvas(canvas, width, height);
		return canvas.toDataURL(type);
	}

	function saveFile (strData) {
		document.location.href = strData;
	}

	function genImage(strData) {
		var img = document.createElement('img');
		img.src = strData;
		return img;
	}
	function fixType (type) {
		type = type.toLowerCase().replace(/jpg/i, 'jpeg');
		var r = type.match(/png|jpeg|bmp|gif/)[0];
		return 'image/' + r;
	}
	function encodeData (data) {
		if (!window.btoa) { throw 'btoa undefined' }
		var str = '';
		if (typeof data == 'string') {
			str = data;
		} else {
			for (var i = 0; i < data.length; i ++) {
				str += String.fromCharCode(data[i]);
			}
		}

		return btoa(str);
	}
	function getImageData (canvas) {
		var w = canvas.width,
			h = canvas.height;
		return canvas.getContext('2d').getImageData(0, 0, w, h);
	}
	function makeURI (strData, type) {
		return 'data:' + type + ';base64,' + strData;
	}


	/**
	 * create bitmap image
	 * 按照规则生成图片响应头和响应体
	 */
	var genBitmapImage = function (oData) {

		//
		// BITMAPFILEHEADER: http://msdn.microsoft.com/en-us/library/windows/desktop/dd183374(v=vs.85).aspx
		// BITMAPINFOHEADER: http://msdn.microsoft.com/en-us/library/dd183376.aspx
		//

		var biWidth  = oData.width;
		var biHeight	= oData.height;
		var biSizeImage = biWidth * biHeight * 3;
		var bfSize  = biSizeImage + 54; // total header size = 54 bytes

		//
		//  typedef struct tagBITMAPFILEHEADER {
		//  	WORD bfType;
		//  	DWORD bfSize;
		//  	WORD bfReserved1;
		//  	WORD bfReserved2;
		//  	DWORD bfOffBits;
		//  } BITMAPFILEHEADER;
		//
		var BITMAPFILEHEADER = [
			// WORD bfType -- The file type signature; must be "BM"
			0x42, 0x4D,
			// DWORD bfSize -- The size, in bytes, of the bitmap file
			bfSize & 0xff, bfSize >> 8 & 0xff, bfSize >> 16 & 0xff, bfSize >> 24 & 0xff,
			// WORD bfReserved1 -- Reserved; must be zero
			0, 0,
			// WORD bfReserved2 -- Reserved; must be zero
			0, 0,
			// DWORD bfOffBits -- The offset, in bytes, from the beginning of the BITMAPFILEHEADER structure to the bitmap bits.
			54, 0, 0, 0
		];

		//
		//  typedef struct tagBITMAPINFOHEADER {
		//  	DWORD biSize;
		//  	LONG  biWidth;
		//  	LONG  biHeight;
		//  	WORD  biPlanes;
		//  	WORD  biBitCount;
		//  	DWORD biCompression;
		//  	DWORD biSizeImage;
		//  	LONG  biXPelsPerMeter;
		//  	LONG  biYPelsPerMeter;
		//  	DWORD biClrUsed;
		//  	DWORD biClrImportant;
		//  } BITMAPINFOHEADER, *PBITMAPINFOHEADER;
		//
		var BITMAPINFOHEADER = [
			// DWORD biSize -- The number of bytes required by the structure
			40, 0, 0, 0,
			// LONG biWidth -- The width of the bitmap, in pixels
			biWidth & 0xff, biWidth >> 8 & 0xff, biWidth >> 16 & 0xff, biWidth >> 24 & 0xff,
			// LONG biHeight -- The height of the bitmap, in pixels
			biHeight & 0xff, biHeight >> 8  & 0xff, biHeight >> 16 & 0xff, biHeight >> 24 & 0xff,
			// WORD biPlanes -- The number of planes for the target device. This value must be set to 1
			1, 0,
			// WORD biBitCount -- The number of bits-per-pixel, 24 bits-per-pixel -- the bitmap
			// has a maximum of 2^24 colors (16777216, Truecolor)
			24, 0,
			// DWORD biCompression -- The type of compression, BI_RGB (code 0) -- uncompressed
			0, 0, 0, 0,
			// DWORD biSizeImage -- The size, in bytes, of the image. This may be set to zero for BI_RGB bitmaps
			biSizeImage & 0xff, biSizeImage >> 8 & 0xff, biSizeImage >> 16 & 0xff, biSizeImage >> 24 & 0xff,
			// LONG biXPelsPerMeter, unused
			0,0,0,0,
			// LONG biYPelsPerMeter, unused
			0,0,0,0,
			// DWORD biClrUsed, the number of color indexes of palette, unused
			0,0,0,0,
			// DWORD biClrImportant, unused
			0,0,0,0
		];

		var iPadding = (4 - ((biWidth * 3) % 4)) % 4;

		var aImgData = oData.data;

		var strPixelData = '';
		var biWidth4 = biWidth<<2;
		var y = biHeight;
		var fromCharCode = String.fromCharCode;

		do {
			var iOffsetY = biWidth4*(y-1);
			var strPixelRow = '';
			for (var x = 0; x < biWidth; x++) {
				var iOffsetX = x<<2;
				strPixelRow += fromCharCode(aImgData[iOffsetY+iOffsetX+2]) +
							   fromCharCode(aImgData[iOffsetY+iOffsetX+1]) +
							   fromCharCode(aImgData[iOffsetY+iOffsetX]);
			}

			for (var c = 0; c < iPadding; c++) {
				strPixelRow += String.fromCharCode(0);
			}

			strPixelData += strPixelRow;
		} while (--y);

		var strEncoded = encodeData(BITMAPFILEHEADER.concat(BITMAPINFOHEADER)) + encodeData(strPixelData);

		return strEncoded;
	};

	/**
	 * saveAsImage
	 * @param canvasElement
	 * @param {String} image type
	 * @param {Number} [optional] png width
	 * @param {Number} [optional] png height
	 */
	var saveAsImage = function (canvas, width, height, type) {
		if ($support.canvas && $support.dataURL) {
			if (typeof canvas == "string") { canvas = document.getElementById(canvas); }
			if (type == undefined) { type = 'png'; }
			type = fixType(type);
			if (/bmp/.test(type)) {
				var data = getImageData(scaleCanvas(canvas, width, height));
				var strData = genBitmapImage(data);
				saveFile(makeURI(strData, downloadMime));
			} else {
				var strData = getDataURL(canvas, type, width, height);
				saveFile(strData.replace(type, downloadMime));
			}
		}
	};

	var convertToImage = function (canvas, width, height, type) {
		if ($support.canvas && $support.dataURL) {
			if (typeof canvas == "string") { canvas = document.getElementById(canvas); }
			if (type == undefined) { type = 'png'; }
			type = fixType(type);

			if (/bmp/.test(type)) {
				var data = getImageData(scaleCanvas(canvas, width, height));
				var strData = genBitmapImage(data);
				return genImage(makeURI(strData, 'image/bmp'));
			} else {
				var strData = getDataURL(canvas, type, width, height);
				return genImage(strData);
			}
		}
	};



	return {
		saveAsImage: saveAsImage,
		saveAsPNG: function (canvas, width, height) {
			return saveAsImage(canvas, width, height, 'png');
		},
		saveAsJPEG: function (canvas, width, height) {
			return saveAsImage(canvas, width, height, 'jpeg');
		},
		saveAsGIF: function (canvas, width, height) {
			return saveAsImage(canvas, width, height, 'gif');
		},
		saveAsBMP: function (canvas, width, height) {
			return saveAsImage(canvas, width, height, 'bmp');
		},

		convertToImage: convertToImage,
		convertToPNG: function (canvas, width, height) {
			return convertToImage(canvas, width, height, 'png');
		},
		convertToJPEG: function (canvas, width, height) {
			return convertToImage(canvas, width, height, 'jpeg');
		},
		convertToGIF: function (canvas, width, height) {
			return convertToImage(canvas, width, height, 'gif');
		},
		convertToBMP: function (canvas, width, height) {
			return convertToImage(canvas, width, height, 'bmp');
		}
	};

}();

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
    size = 8.625/zoom;
    ctx.font = size.toString()+'pt Sans-Serif';
    ctx.fillText(text,0,0);
    ctx.restore();
}


function draw_line(ctx,x1,y1,x2,y2,pos,text) {
    if (y1==y2) {
        ctx.beginPath();
        ctx.moveTo(x1,y1 + 4/zoom);
        ctx.lineTo(x1,y1 - 4/zoom);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x2,y2 + 4/zoom);
        ctx.lineTo(x2,y2 - 4/zoom);
        ctx.stroke();
        if (text.length > 0) {
            draw_text(ctx,(x1+x2*(pos-1))/pos,y1-4/zoom,0,'center',text);
        }
    } else if (x1 == x2) {
        ctx.beginPath();
        ctx.moveTo(x1 + 4/zoom, y1);
        ctx.lineTo(x1 - 4/zoom, y1);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x2 + 4/zoom, y2);
        ctx.lineTo(x2 - 4/zoom, y2);
        ctx.stroke();
        if (text.length > 0) {
            draw_text(ctx,x1+4/zoom,(y1+(pos-1)*y2)/pos,Math.PI/2,'center',text);
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
        context.lineWidth = 3/zoom; // context.lineWidth = 1.0;
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
        // A share of the canvas rather than a fixed thirty pixels, so an editor canvas that is
        // narrower than 940 keeps the same framing.
        border_size = 30 * canvas_max_width / 940;
        if ((w/h) > (canvas_max_width/canvas_max_height)) {
            zoom = (canvas_w - border_size*2) / w;
        } else {
            zoom = (canvas_h - border_size*2) / h;
        }

        canvas.setAttribute("width", canvas_w);
        canvas.setAttribute("height", canvas_h);
        ctx.strokeStyle="#000";
        ctx.fillStyle="#f4f6ff";
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, canvas_w, canvas_h);
        ctx.save();
        if (p.minX < 0) {
            ctx.translate(canvas_w/2, border_size + zoom * Math.abs(p.minX));
        } else {
            ctx.translate(canvas_w/2, border_size);
        }
        ctx.scale(zoom,zoom);
        ctx.lineWidth = 3/zoom;
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

function unitsystem_setup() {
    var radios = $('#UnitsystemRadios label input');
    if (radios.is(':checked') === false) {
        $("#btn_metric").prop('checked', true);
    }
    // Fix radio label classes
    radios.each(function( index ) {
        if ( $(this).filter(':checked').val() ) {
            $(this).parent().addClass('active');
        }
    });
    // global variables setup
    $("#unitValue").val( $("#btn_metric").filter(':checked').val() ? 'metric' : 'imperial' );
    window.systemunit = $("#unitValue").val();
}

function wingcgcalc_setup() {
    // event linking
    $("#label_btn_metric").click(   function(ev){ systemunits_to_metric();        });
    $("#label_btn_imperial").click( function(ev){ systemunits_to_imperial();      });
    $("#btn_savepng").click(        function(ev){ savepng(); ev.preventDefault(); });
    $("#submit_button").click(      function(ev){ save_image(); });
    $(window).resize(               function(){   draw_wing();  });
    $(".redraw").change(            function(){   draw_wing();  });
    $(".recalcsweep").change(       function(){   angle2sweep($(this).attr('panel')); draw_wing(); });
    $(".recalcangle").change(       function(){   sweep2angle($(this).attr('panel')); draw_wing(); });

    unitsystem_setup();
}




//# sourceMappingURL=winglib.js.map
