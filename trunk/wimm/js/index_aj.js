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
    var sRet = sDate;
    if(sDate!==undefined && sDate!==null && sDate.length>18)    {
        // 2014-06-01 08:05:50 -> 01/06 08:05:50
        sRet = sDate.substring(8, 2) + "/" + sDate.substring(5, 2) + " " +
                sDate.substring(11, 8);
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
                if(v.indexOf("delete")==0)  {
                    $("#TR_"+row_id).remove();
                }
                else {
                    for(i=0; i<a_fields.length; i++)    {
                        v = $("#"+a_fields[i].id).val();
                        if(a_fields[i].tbl_type.indexOf("v"))
                            $(a_fields[i].in_tbl + row_id).val(v);
                        if(a_fields[i].tbl_type.indexOf("t"))   {
                            if(a_fields[i].id.indexOf('t_date')==0)
                                $(a_fields[i].in_tbl + row_id).text(format_date(v));
                            else
                                $(a_fields[i].in_tbl + row_id).text(v);
                        }
                        if(a_fields[i].tbl_type.indexOf("i"))
                            $(a_fields[i].in_tbl + row_id).attr("title",v);
                        if(a_fields[i].tbl_type.indexOf("x"))   {
                            var t = $("#"+a_fields[i].id).text();
                            $(a_fields[i].l_tbl + row_id).text(t);
                        }
                    }
                }
            }
            else
                alert("Пустой ответ!");
        }
    }
    else {
        console_debug_log(textStatus);
    }

    jqxr = null;
    $("#dialog_box").hide();
}

function tx_submit()
{
    var i;
    var f,v;
    var inv_idx = -1;
    var msg = "";
    var currentdate = new Date(); 
    var reqStr = "time=" + Date.now() + "&FRM_MODE=update&HIDDEN_ID=" + $("#HIDDEN_ID").val();
    for(i=0; i<a_fields.length; i++)    {
        f = $("#" + a_fields[i].id);
        v = f.val();
        if(v!==null&&v!==undefined) {
            if(v.length>a_fields[i].len)    {
                if(a_fields[i].type.indexOf("n")==0)    {
                    try {
                        var nn = new Number(v);
                        if(nn<=a_fields[i].val)   {
                            inv_idx = i;
                            msg = "Недопустимое значение <=" + a_fields[i].val.toString();
                        }
                        else {
                            reqStr += ("&" + a_fields[i].id + "=" + encodeURIComponent(v));
                        }

                    } catch (e) {
                        inv_idx = i;
                        msg = e.toString();
                    }
                }
                else {
                    reqStr += ("&" + a_fields[i].id + "=" + encodeURIComponent(v));
                }
            }
            else    {
                inv_idx = i;
                msg = "Слишком короткое значение"
            }
        }
        else    {
            inv_idx = i;
            msg = "Значение не установлено"
        }
    }
    console_debug_log("request:"+reqStr);
    if(inv_idx>=0&&inv_idx<a_fields.length)  {
        if(msg.length>0)
            alert(msg);
        $("#" + a_fields[i].id).select();
        return;
    }
    console_debug_log("query!");
    jqxr = $.ajax({
        type: "POST",
        url: "/wimm/wimm_edit2.php",
        data: reqStr,
        complete: onTxComplete
    });
//    if(jqxr.responseText.length>0)
//        onTxComplete(jqxr, 'success');
//    else
//        console_debug_log("empty response!");
    console_debug_log("end query!");
}

function sel_row(row_id)
{
    var focus2item = '#t_user';
    var objDiv = document.getElementById("dialog_box");
    objDiv.style.top = (f_get_scroll_y()+200).toString()+"px";
    //var x = (window.innerWidth||document.body.clientWidth);
    var x = (f_get_scroll_x()-600)/2+500;
    if(x<0)
        x = 500;
    objDiv.style.left = x.toString()+"px";
    //objDiv.style.display="inline";
    $("#dialog_box").show();
    var s1;
    var i;
    if(row_id===null || row_id===undefined || row_id.length<1) {
        $("#FRM_MODE").val("insert");
        $("#HIDDEN_ID").val(0);
        $("#ADD_BTN").show();
        $("#OK_BTN").hide();
        $("#DEL_BTN").hide();
        $("#dlg_box_cap").text("Добавление записи");
        s1 = "";
        for(i=0; i<a_fields.length; i++)    {
            if(a_fields[i].id.indexOf("t_user")==0)
                focus2item = '#t_name';
            else
                $("#" + a_fields[i].id).val($("#" + a_fields[i].id + "_a").val());
        }
    }
    else    {
        $("#FRM_MODE").val("update");
        $("#HIDDEN_ID").val(row_id);
        $("#ADD_BTN").hide();
        $("#OK_BTN").show();
        $("#DEL_BTN").show();
        $("#dlg_box_cap").text("Изменение записи");
        s1 = "";
        for(i=0; i<a_fields.length; i++)    {
            s1 = "";
            if(a_fields[i].tbl_type.indexOf("v")==0)
                s1 = $(a_fields[i].in_tbl + row_id).val();
            if(a_fields[i].tbl_type.indexOf("t")==0)    {
                if(a_fields[i].id.indexOf('t_sum')==0)  {
                    var s2 = $(a_fields[i].in_tbl + row_id).text();
                    s1 = s2.replace(" ","");
                }
                else
                    s1 = $(a_fields[i].in_tbl + row_id).text();
            }
            if(a_fields[i].tbl_type.indexOf("i")==0)
                s1 = $(a_fields[i].in_tbl + row_id).attr("title");
            console_debug_log(a_fields[i].in_tbl + row_id + "(" + a_fields[i].tbl_type + ")=" + s1);
            if(s1!==null && s1!==undefined && s1.length>0)
                $("#"+a_fields[i].id).val(s1);
        }
        console_debug_log('focus to: ' + focus2item);
         focus2item = document.getElementById('t_name');
         setInputSelection(focus2item,0,2);

        console_debug_log('focus out: ' + focus2item);
    }
}

function del_click()
{
    var i;
    var f,v;
    var inv_idx = -1;
    var msg = "";
    var reqStr = "time=" + Date.now() + "&FRM_MODE=delete&HIDDEN_ID=" + $("#HIDDEN_ID").val();
    $("#FRM_MODE").val("delete");
    jqxr = $.ajax({
        type: "POST",
        url: "/wimm/wimm_edit2.php",
        data: reqStr,
        complete: onTxComplete
    });
}

function onLoad()
{
    $('#dialog_box').draggable();
    $('#OK_BTN').draggable();
    $("body").keydown(function(e) {
        onPageKey(e.keyCode);
    });
    setHandlers(".dtp");
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

    if (typeof el.selectionStart === "number" && typeof el.selectionEnd === "number") {
        el.selectionStart = nStart;
        el.selectionEnd = nEnd;
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

    if (typeof el.selectionStart === "number" && typeof el.selectionEnd === "number") {
        start = el.selectionStart;
        end = el.selectionEnd;
    }
    return {
        start: start,
        end: end
    };
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
        }
    });
    $(jqSelector).keyup(function(e)
    {
        var pos, len, val, i;
        var caret = getInputSelection(e.currentTarget);
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
                        break;
                    }
                }
                break;
            case 40://down
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
                        break;
                    }
                }
                break;
        }
        //e.preventDefault();
    });
}
