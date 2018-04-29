/*
 * jquery.flot.tooltip
 * 
 * description: easy-to-use tooltips for Flot charts
 * version: 0.6.6
 * author: Krzysztof Urbas @krzysu [myviews.pl]
 * website: https://github.com/krzysu/flot.tooltip
 * 
 * build on 2014-02-10
 * released under MIT License, 2012
*/ 
!function(a){var b={tooltip:!1,tooltipOpts:{content:"%s | X: %x | Y: %y",xDateFormat:null,yDateFormat:null,monthNames:null,dayNames:null,shifts:{x:10,y:20},defaultTheme:!0,onHover:function(){}}},c=function(a){this.tipPosition={x:0,y:0},this.init(a)};c.prototype.init=function(b){function c(a){var b={};b.x=a.pageX,b.y=a.pageY,e.updateTooltipPosition(b)}function d(a,b,c){var d=e.getDomElement();if(c){var f;f=e.stringFormat(e.tooltipOptions.content,c),d.html(f),e.updateTooltipPosition({x:b.pageX,y:b.pageY}),d.css({left:e.tipPosition.x+e.tooltipOptions.shifts.x,top:e.tipPosition.y+e.tooltipOptions.shifts.y}).show(),"function"==typeof e.tooltipOptions.onHover&&e.tooltipOptions.onHover(c,d)}else d.hide().html("")}var e=this;b.hooks.bindEvents.push(function(b,f){if(e.plotOptions=b.getOptions(),e.plotOptions.tooltip!==!1&&"undefined"!=typeof e.plotOptions.tooltip){e.tooltipOptions=e.plotOptions.tooltipOpts;{e.getDomElement()}a(b.getPlaceholder()).bind("plothover",d),a(f).bind("mousemove",c)}}),b.hooks.shutdown.push(function(b,e){a(b.getPlaceholder()).unbind("plothover",d),a(e).unbind("mousemove",c)})},c.prototype.getDomElement=function(){var b;return a("#flotTip").length>0?b=a("#flotTip"):(b=a("<div />").attr("id","flotTip"),b.appendTo("body").hide().css({position:"absolute"}),this.tooltipOptions.defaultTheme&&b.css({background:"#fff","z-index":"1040",padding:"0.4em 0.6em","border-radius":"0.5em","font-size":"0.8em",border:"1px solid #111",display:"none","white-space":"nowrap"})),b},c.prototype.updateTooltipPosition=function(b){var c=a("#flotTip").outerWidth()+this.tooltipOptions.shifts.x,d=a("#flotTip").outerHeight()+this.tooltipOptions.shifts.y;b.x-a(window).scrollLeft()>a(window).innerWidth()-c&&(b.x-=c),b.y-a(window).scrollTop()>a(window).innerHeight()-d&&(b.y-=d),this.tipPosition.x=b.x,this.tipPosition.y=b.y},c.prototype.stringFormat=function(a,b){var c,d,e=/%p\.{0,1}(\d{0,})/,f=/%s/,g=/%x\.{0,1}(\d{0,})/,h=/%y\.{0,1}(\d{0,})/,i="%x",j="%y";if("undefined"!=typeof b.series.threshold?(c=b.datapoint[0],d=b.datapoint[1]):(c=b.series.data[b.dataIndex][0],d=b.series.data[b.dataIndex][1]),null===b.series.label&&b.series.originSeries&&(b.series.label=b.series.originSeries.label),"function"==typeof a&&(a=a(b.series.label,c,d,b)),"undefined"!=typeof b.series.percent&&(a=this.adjustValPrecision(e,a,b.series.percent)),a="undefined"!=typeof b.series.label?a.replace(f,b.series.label):a.replace(f,""),this.isTimeMode("xaxis",b)&&this.isXDateFormat(b)&&(a=a.replace(g,this.timestampToDate(c,this.tooltipOptions.xDateFormat))),this.isTimeMode("yaxis",b)&&this.isYDateFormat(b)&&(a=a.replace(h,this.timestampToDate(d,this.tooltipOptions.yDateFormat))),"number"==typeof c&&(a=this.adjustValPrecision(g,a,c)),"number"==typeof d&&(a=this.adjustValPrecision(h,a,d)),"undefined"!=typeof b.series.xaxis.ticks&&b.series.xaxis.ticks.length>b.dataIndex&&!this.isTimeMode("xaxis",b)&&(a=a.replace(g,b.series.xaxis.ticks[b.dataIndex].label)),"undefined"!=typeof b.series.yaxis.ticks)for(var k in b.series.yaxis.ticks)if(b.series.yaxis.ticks.hasOwnProperty(k)){var l=this.isCategoriesMode("yaxis",b)?b.series.yaxis.ticks[k].label:b.series.yaxis.ticks[k].v;l===d&&(a=a.replace(h,b.series.yaxis.ticks[k].label))}return"undefined"!=typeof b.series.xaxis.tickFormatter&&(a=a.replace(i,b.series.xaxis.tickFormatter(c,b.series.xaxis).replace(/\$/g,"$$"))),"undefined"!=typeof b.series.yaxis.tickFormatter&&(a=a.replace(j,b.series.yaxis.tickFormatter(d,b.series.yaxis).replace(/\$/g,"$$"))),a},c.prototype.isTimeMode=function(a,b){return"undefined"!=typeof b.series[a].options.mode&&"time"===b.series[a].options.mode},c.prototype.isXDateFormat=function(){return"undefined"!=typeof this.tooltipOptions.xDateFormat&&null!==this.tooltipOptions.xDateFormat},c.prototype.isYDateFormat=function(){return"undefined"!=typeof this.tooltipOptions.yDateFormat&&null!==this.tooltipOptions.yDateFormat},c.prototype.isCategoriesMode=function(a,b){return"undefined"!=typeof b.series[a].options.mode&&"categories"===b.series[a].options.mode},c.prototype.timestampToDate=function(b,c){var d=new Date(1*b);return a.plot.formatDate(d,c,this.tooltipOptions.monthNames,this.tooltipOptions.dayNames)},c.prototype.adjustValPrecision=function(a,b,c){var d,e=b.match(a);return null!==e&&""!==RegExp.$1&&(d=RegExp.$1,c=c.toFixed(d),b=b.replace(a,c)),b};var d=function(a){new c(a)};a.plot.plugins.push({init:d,options:b,name:"tooltip",version:"0.6.6"})}(jQuery);