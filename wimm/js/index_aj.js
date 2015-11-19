/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var bCtrlHit = false;   // ctrl held down
var aParts = [[0,4,-1,0], [5,2,12,1], [8,2,31,1], [11,2,23,0], [14,2,59,0], [17,2,59,0]];   // text parts
var aDays = [0,31,28,31,30,31,30,31,31,30,31,30,31];    // month's days
var jqxr = null;    // jQuery ajax request
// type - value type "s" - text, "n" - numeric, "d" - date
// tbl_type - table disp type "t" - text, "v" - value, i - title
var a_fields = [
    {id:"t_user",len:0,type:"n",val:0,in_tbl:"#L_USR_",tbl_type:"t"},
    {id:"t_user",len:0,type:"n",val:0,in_tbl:"#T_USR_",tbl_type:"v"},
    {id:"t_name",len:0,type:"s",val:0,in_tbl:"#TNAME_",tbl_type:"t"},
    {id:"t_type",len:0,type:"n",val:0,in_tbl:"#T_TYPE_",tbl_type:"v"},
    {id:"t_curr",len:0,type:"n",val:0,in_tbl:"#T_CURR_",tbl_type:"v"},
    {id:"t_sum",len:0,type:"n",val:0,in_tbl:"#T_SUMM_",tbl_type:"t"},
    {id:"t_date",len:0,type:"d",val:0,in_tbl:"#T_DATE_",tbl_type:"t"},//2014-05-05 05:05:05
    {id:"t_date",len:18,type:"d",val:0,in_tbl:"#T_DATE_",tbl_type:"i"},//2014-05-05 05:05:05
    {id:"t_place",len:0,type:"n",val:0,in_tbl:"#T_PLACE_",tbl_type:"v"},
    {id:"t_budget",len:0,type:"n",val:0,in_tbl:"#T_BUDG_",tbl_type:"v"}];

/**
 * converts datetime string to display
 * @param {String} sDate
 * @returns {String}
 */
function format_date(sDate)
{
    var sRet;
    try
    {
        var d = new Date();
        d.parse(sDate);
        sRet = d.toISOString()
    }
    catch(e)
    {
        sRet = sDate.trim();
    }
    if(sRet!==undefined && sRet!==null && sRet.length>18)    {
        // 2014-06-01 08:05:50 -> 01/06 08:05:50
        sRet = sRet.substr(8, 2) + "/" + sRet.substr(5, 2) + " " +
                sRet.substr(11, 8);
    }
    return sRet;
}
/**
 * 
 * @param {object} s_msg
 * @returns {undefined}
 */
function console_debug_log(s_msg)
{
    if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1 ||
            navigator.userAgent.toLowerCase().indexOf('chrome') > -1)   {
        console.log(s_msg);
    }
}

function doCancel()
{
    if(jqxr!==null) {
        jqxr.abort();
        jqxr = null;
    }
    $("#dialog_box").hide();
    $("#sel_row_id").val("");
    $("#FRM_MODE").val("refresh");
}

function onTxComplete(jqXHR, textStatus )
{
    if(textStatus.indexOf("success")==0)    {
        var row_id = $("#HIDDEN_ID").val();
        if(row_id!==null&&row_id!==undefined&&row_id.length>0)  {
            var i, v;
            var s1 = '{"id":"1","err":"error"}';
            console_debug_log('response=' + jqXHR.responseText);
            if(jqXHR.responseText!==null && jqXHR.responseText!==undefined && 
                    jqXHR.responseText.length>0)  {
                i = JSON.parse(jqXHR.responseText);
                console_debug_log("id="+i.id);
                console_debug_log("error="+i.err);
                v = $("#FRM_MODE").val();
                console_debug_log("mode="+v);
                if(v.indexOf("delete")==0)
                {
                    $("#"+row_id).parent().parent().remove();
                }
                else {
                    var form_fields = $('#expenses').find(".form_field");
                    for(i=0; i<form_fields.length; i++)
                    {
                        var bt = form_fields[i].getAttribute("bind_row_type");
                        var bi = "#" + form_fields[i].getAttribute("bind_row_id") + row_id;
                        var v = form_fields[i].value;
                        if(v.substr(0,4)=='aci_')
                        {
                            v = v.substr(4);
                        }
                        if(form_fields[i].id.indexOf('t_date')==0)
                        {
                            $(bi).attr('title', v);
                            $(bi).text(format_date(v));
                        }
                        else
                        {
                            switch(bt)
                            {
                            case 'value':
                                $(bi).val(v);
                                break;
                            case 'label':
                                $(bi).text(v);
                                break;
                            case 'title':
                                $(bi).attr('title', v);
                                break;
                            }
                        }
                    }
//                    for(i=0; i<a_fields.length; i++)    {
//                        v = $("#"+a_fields[i].id).val();
//                        if(a_fields[i].tbl_type.indexOf("v"))
//                            $(a_fields[i].in_tbl + row_id).val(v);
//                        if(a_fields[i].tbl_type.indexOf("t"))   {
//                            if(a_fields[i].id.indexOf('t_date')==0)
//                                $(a_fields[i].in_tbl + row_id).text(format_date(v));
//                            else
//                                $(a_fields[i].in_tbl + row_id).text(v);
//                        }
//                        if(a_fields[i].tbl_type.indexOf("i"))
//                            $(a_fields[i].in_tbl + row_id).attr("title",v);
//                        if(a_fields[i].tbl_type.indexOf("x"))   {
//                            var t = $("#"+a_fields[i].id).text();
//                            $(a_fields[i].l_tbl + row_id).text(t);
//                        }
//                    }
                }
            }
            else
                alert("Пустой ответ!");
        }
        else
        {
            console_debug_log("new row.");
            console_debug_log('response=' + jqXHR.responseText);
            v = $("#FRM_MODE").val();
            console_debug_log("mode="+v);
            if(v.indexOf("insert")==0)
            {
                if(jqXHR.responseText!==null && jqXHR.responseText!==undefined && 
                        jqXHR.responseText.length>0)  {
                    i = JSON.parse(jqXHR.responseText);
                    console_debug_log("id="+i.id);
                    console_debug_log("error="+i.err);
                }                
            }
        }
    }
    else {
        console_debug_log(textStatus);
    }

    jqxr = null;
    $("#dialog_box").hide();
}

function tx_submit(submitURL)
{
    var i;
    var f,v;
    var inv_idx = -1;
    var msg = "";
    var currentdate = new Date(); 
    v = $("#FRM_MODE").val();
    if(v=='insert')
    {
        $('#expenses').submit();
        return;
    }
    var reqStr = "time=" + Date.now() + "&FRM_MODE=" + v + 
            "&HIDDEN_ID=" + $("#HIDDEN_ID").val();
    var fields = $('#expenses').find('.sendable');
    if(fields!=null && fields!=undefined)
    {
        for(i=0; i<fields.length; i++)
        {
            try {
                f = fields[i].getAttribute("id");;
                v = fields[i].value;
                if(v.substr(0,4)=='aci_')
                {
                    v = v.substr(4);
                }
                reqStr += ("&" + f.toString() + "=" + encodeURIComponent(v));
            } catch (e) {
                inv_idx = i;
                msg = e.toString();
            }
        }
    }
    console_debug_log("request:"+reqStr);
    if(inv_idx>=0&&inv_idx<a_fields.length)  {
        if(msg.length>0)
            alert(msg);
        console_debug_log("error at:"+inv_idx.toString());
        return;
    }
    console_debug_log("query!");
    jqxr = $.ajax({
        type: "POST",
        url: submitURL,
        data: reqStr,
        complete: onTxComplete
    });
//    if(jqxr.responseText.length>0)
//        onTxComplete(jqxr, 'success');
//    else
//        console_debug_log("empty response!");
    console_debug_log("end query!");
}

function onPageKey(key)
{
    switch(key)
    {
        case 27:
            if($('#dialog_box:visible').length > 0) {
                doCancel();
            }
            break;
        case 10:
            if($('#dialog_box:visible').length > 0) {
                if($('#OK_BTN:visible').length > 0) {
                    tx_submit();
                }
                else    {
                    if($('#ADD_BTN:visible').length > 0) {
                        $('#expenses').submit();
                    }
                }
            }
            break;
        case 116:
            $("#sel_row_id").val("");
            $("#FRM_MODE").val("refresh");
            $('#expenses').submit();
           break;
    }

}

/**
 * set selection range in text box
 * @param {Object} el - DOM element
 * @param {Number} nStart - start selection
 * @param {Number} nEnd - end of selection
 * @returns {undefined}
*/
function setInputSelection(el, nStart, nEnd) {
    var start = 0, end = 0, normalizedValue, range,
        textInputRange, len, endRange;

    try
    {
        if (typeof el.selectionStart === "number" && typeof el.selectionEnd === "number") {
            el.selectionStart = nStart;
            el.selectionEnd = nEnd;
        } 
    }
    catch(e)
    {
        console_debug_log("set date & time selection: " + e.toString());
    }
}

/**
 * get selection range from text box
 * @param {Object} el - DOM element
 * @returns {getInputSelection.index_ajAnonym$2}
 */
function getInputSelection(el) {
    var start = 0, end = 0, normalizedValue, range,
        textInputRange, len, endRange;

    try
    {
        if (typeof el.selectionStart === "number" && typeof el.selectionEnd === "number") {
            start = el.selectionStart;
            end = el.selectionEnd;
        }
        return {
            start: start,
            end: end
        };
    }
    catch(e)
    {
        console_debug_log("get date & time selection: " + e.toString());
    }
    return null;
}

/**
 * pad string from left to a desired legth
 * @param {String} i
 * @param {Number} l
 * @param {String} f
 * @returns {String} - padded string 
 */
function formatNumber(i, l, f)
{
    var s1 = i.toString();
    var len = s1.length;
    var sRet = "";
    if(len<l)   {
        for(i=0; i<l-len; i++)
            sRet += f.substring(0,1);
        sRet += s1;
    }
    else {
        if(len>l)   {
            sRet = s1.substring(0,l);
        }
        else
            sRet = s1;
    }
    return sRet;
}

/**
 * set event handlers with specified jQuery selector
 * @param {String} jqSelector
 * @returns {undefined}
 */
function setHandlers(jqSelector)
{
    bCtrlHit = false;
    $(jqSelector).focus(function(e)
    {
        setInputSelection(e.currentTarget, aParts[0][0], 
            aParts[0][0] + aParts[0][1]);
        e.preventDefault();
    });
    $(jqSelector).keydown(function(e)
    {
        switch(e.keyCode)
        {
            case 17:
                bCtrlHit = true;
                break;
            case 38://up
            case 40://down
                e.preventDefault();
                break;
        }
    });
    $(jqSelector).keyup(function(e)
    {
        var pos, len, val, i;
        var caret = getInputSelection(e.currentTarget);
        if(caret!=null)
        {
            var month;
            val = $(this).val();
            len = val.length;
            pos = caret.start;
            month = new Number(val.substring(aParts[1][0],aParts[1][0]+aParts[1][1])).valueOf();
            switch(e.keyCode)
            {
                case 17:
                    bCtrlHit = false;
                    break;
                case 37://left
                    if(bCtrlHit)    {
                        if(pos>=len)
                            break;
                        for(i=0; i<aParts.length; i++)
                        {
                            if(aParts[i][0]>=pos)    {
                                if(i<1)
                                    break;
                                setInputSelection(e.currentTarget, aParts[i-1][0], 
                                    aParts[i-1][0] + aParts[i-1][1]);
                                break;
                            }
                        }
                    }
                    break;
                case 39://right
                    if(bCtrlHit)    {
                        if(pos<=0)
                            break;
                        for(i=0; i<aParts.length; i++)
                        {
                            if(aParts[i][0]>=pos)    {
    //                                        if(i<1)
    //                                            break;
                                setInputSelection(e.currentTarget, aParts[i][0], 
                                    aParts[i][0] + aParts[i][1]);
                                break;
                            }
                        }
                    }
                    break;
                case 38://up
                    if(!bCtrlHit)    {
                        for(i=0; i<aParts.length; i++)
                        {
                            if(aParts[i][0]>=pos)    {
                                var n1 = val.substring(aParts[i][0], aParts[i][0] + aParts[i][1]).valueOf();
                                n1 ++;
                                if(aParts[i][2]>=0)  {
                                    var nLim;
                                    if(i===2)
                                        nLim = aDays[month];
                                    else
                                        nLim = aParts[i][2];
                                    if(n1>nLim)
                                        n1 = aParts[i][3];
                                }
                                $(this).val(val.substring(0,aParts[i][0]) +
                                        formatNumber(n1.toString(),aParts[i][1],"0")+
                                        val.substring(aParts[i][0]+aParts[i][1]));
                                setInputSelection(e.currentTarget, aParts[i][0], 
                                    aParts[i][0] + aParts[i][1]);
                                e.preventDefault();
                                break;
                            }
                        }
                    }
                    break;
                case 40://down
                    if(!bCtrlHit)    {
                        for(i=0; i<aParts.length; i++)
                        {
                            if(aParts[i][0]>=pos)    {
                                var n1 = val.substring(aParts[i][0], aParts[i][0] + aParts[i][1]).valueOf();
                                n1 --;
                                if(aParts[i][3]>=0)  {
                                    if(n1<aParts[i][3]) {
                                        var nLim;
                                        if(i===2)
                                            nLim = aDays[month];
                                        else
                                            nLim = aParts[i][2];
                                        if(nLim>0)
                                            n1 = nLim;
                                        else
                                            n1 = aParts[i][3];
                                    }
                                }
                                $(this).val(val.substring(0,aParts[i][0]) +
                                        formatNumber(n1.toString(),aParts[i][1],"0")+
                                        val.substring(aParts[i][0]+aParts[i][1]));
                                setInputSelection(e.currentTarget, aParts[i][0], 
                                    aParts[i][0] + aParts[i][1]);
                                e.preventDefault();
                                break;
                            }
                        }
                    }
                    break;
            }
            //e.preventDefault();
        }
    });
}
